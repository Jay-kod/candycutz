import axios from 'axios';
import { ApiError } from './ApiError';
import { recordClientError, addBreadcrumb } from './errorReporter';
import { API_BASE_URL } from '@/core/utils/url';
import { useConnectionStatus } from '../composables/useConnectionStatus';

const APP_VERSION = '1.0.0';

/**
 * Generates a standard request ID matching ^req_[A-Za-z0-9]{10,40}$
 */
export function generateRequestId() {
  const rand = Math.random().toString(36).substring(2, 12) + Math.random().toString(36).substring(2, 12);
  const timestamp = Date.now().toString(36);
  return `req_${timestamp}${rand}`.substring(0, 32);
}

function getRequestPath(url) {
  if (typeof url !== 'string') return '';

  try {
    return new URL(url, API_BASE_URL).pathname.slice(0, 255);
  } catch {
    return url.split(/[?#]/, 1)[0].slice(0, 255);
  }
}

export function setupApiClient() {
  const api = axios.create({
    baseURL: API_BASE_URL,
    timeout: 15000,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-Client': 'web',
      'X-Client-Type': 'web',
      'X-App-Version': APP_VERSION,
    },
  });

  // Request interceptor
  api.interceptors.request.use(
    (config) => {
      if (typeof config.__requestStartedAt !== 'number') {
        config.__requestStartedAt = Date.now();
      }

      // Normalize requested URL to guarantee clean /v1/ routing without duplicate prefixes
      if (config.url) {
        let url = config.url;
        if (url.startsWith('/api/v1/')) {
          config.url = url.replace('/api/v1/', '/v1/');
        } else if (url.startsWith('api/v1/')) {
          config.url = `/${url.replace('api/v1/', 'v1/')}`;
        } else if (!url.startsWith('/v1/') && !url.startsWith('http://') && !url.startsWith('https://')) {
          config.url = `/v1/${url.replace(/^\/+/, '')}`;
        }
      }

      // If sending FormData, delete Content-Type so browser sets boundary automatically
      if (typeof FormData !== 'undefined' && config.data instanceof FormData) {
        if (config.headers) {
          if (typeof config.headers.delete === 'function') {
            config.headers.delete('Content-Type');
            config.headers.delete('content-type');
          } else {
            delete config.headers['Content-Type'];
            delete config.headers['content-type'];
          }
        }
      }

      // Add unique client Request ID if not already provided
      if (!config.headers['X-Request-Id']) {
        config.headers['X-Request-Id'] = generateRequestId();
      }

      // Attach token
      if (typeof localStorage !== 'undefined') {
        const token = localStorage.getItem('candycutz_auth_token');
        if (token) {
          config.headers.Authorization = `Bearer ${token}`;
        }
      }

      // Breadcrumb tracking
      addBreadcrumb({
        type: 'http',
        category: 'request',
        message: `${(config.method || 'get').toUpperCase()} ${getRequestPath(config.url)}`,
        data: {
          requestId: config.headers['X-Request-Id'],
          method: config.method,
          path: getRequestPath(config.url),
        },
      });

      return config;
    },
    (error) => Promise.reject(error)
  );

  // Response interceptor
  api.interceptors.response.use(
    (response) => {
      if (!response.config?.__skipConnectionTracking) {
        try {
          const { isDisconnected, markConnected } = useConnectionStatus();
          if (isDisconnected.value) {
            markConnected();
          }
        } catch {
          // ignore connection status evaluation failures
        }
      }
      addBreadcrumb({
        type: 'http',
        category: 'response',
        message: `HTTP ${response.status} from ${(response.config.method || 'get').toUpperCase()} ${getRequestPath(response.config.url)}`,
        data: {
          requestId: response.headers['x-request-id'] || response.config.headers['X-Request-Id'],
          status: response.status,
        },
      });
      return response;
    },
    async (error) => {
      const config = error.config || {};
      const method = (config.method || 'get').toLowerCase();
      const hasIdempotencyKey = Boolean(config.headers && config.headers['Idempotency-Key']);
      const isIdempotent = method === 'get' || method === 'head' || method === 'options' || hasIdempotencyKey;
      const isCanceled = Boolean((typeof axios?.isCancel === 'function' && axios.isCancel(error)) || error?.code === 'ERR_CANCELED' || error?.name === 'CanceledError');

      // Jittered backoff retry for idempotent requests on network errors or 502/503/504 (never retry canceled requests)
      const retryCount = config.__retryCount || 0;
      const status = error.response?.status;
      const isRetryableError = !isCanceled && (!error.response || status === 502 || status === 503 || status === 504 || error.code === 'ECONNABORTED');

      if (isIdempotent && isRetryableError && retryCount < 2) {
        config.__retryCount = retryCount + 1;
        const delay = Math.pow(2, retryCount) * 300 + Math.random() * 200;
        await new Promise((resolve) => setTimeout(resolve, delay));
        return api(config);
      }

      // Transform raw error into standardized ApiError
      let apiError;
      const response = error.response;
      const requestId =
        response?.headers?.['x-request-id'] ||
        response?.data?.error?.request_id ||
        response?.data?.request_id ||
        config.headers?.['X-Request-Id'] ||
        null;

      if (isCanceled) {
        config.__skipConnectionTracking = true;
        apiError = new ApiError({
          message: 'Request was canceled',
          code: 'REQUEST_CANCELED',
          status: 0,
          requestId,
          details: {},
          retryable: false,
          action: 'none',
          retryAfter: null,
          isNetwork: false,
          isTimeout: false,
        });
      } else if (!response) {
        // Network or timeout failure
        const isTimeout = error.code === 'ECONNABORTED' || error.message?.includes('timeout');
        apiError = new ApiError({
          message: isTimeout
            ? 'Request timed out. Please check your network and try again.'
            : 'Unable to reach the server. Please check your internet connection.',
          code: isTimeout ? 'NETWORK_TIMEOUT' : 'NETWORK_UNREACHABLE',
          status: 0,
          requestId,
          details: {},
          retryable: true,
          action: 'retry',
          retryAfter: null,
          isNetwork: true,
          isTimeout,
        });
      } else {
        const data = response.data || {};
        const errObj = data.error || {};

        const code =
          errObj.code ||
          (status === 401
            ? 'AUTHENTICATION_REQUIRED'
            : status === 403
            ? 'AUTH_FORBIDDEN_ROLE'
            : status === 404
            ? 'RESOURCE_NOT_FOUND'
            : status === 422
            ? 'VALIDATION_FAILED'
            : status === 429
            ? 'RATE_LIMITED'
            : 'SERVER_ERROR');

        const message =
          errObj.message ||
          data.message ||
          (status === 401
            ? 'Authentication is required for this request.'
            : status === 403
            ? 'You do not have permission to perform this action.'
            : status === 404
            ? 'Requested resource not found.'
            : status === 422
            ? 'The given data was invalid.'
            : status === 429
            ? 'Too many requests. Please wait before retrying.'
            : 'A server error occurred. Please try again later.');

        const tokenFailureCodes = [
          'AUTH_TOKEN_INVALID',
          'AUTH_TOKEN_EXPIRED',
          'AUTH_TOKEN_REVOKED',
          'AUTH_ACCOUNT_SUSPENDED',
        ];
        const action =
          errObj.action ||
          (status === 401 && tokenFailureCodes.includes(errObj.code)
            ? 'logout'
            : status === 422
            ? 'fix_input'
            : status === 429
            ? 'wait'
            : 'none');

        const details = errObj.details || (data.errors ? { field_errors: data.errors } : {});
        const retryable = errObj.retryable !== undefined ? Boolean(errObj.retryable) : status >= 500;
        const retryAfter = errObj.retry_after || response.headers['retry-after'] || null;

        apiError = new ApiError({
          message,
          code,
          status,
          requestId,
          details,
          retryable,
          action,
          retryAfter,
          isNetwork: false,
          isTimeout: false,
        });
      }

      // Check for DB or Network disconnection
      if (!config.__skipConnectionTracking && !isCanceled) {
        try {
          const { markDisconnected, reportNetworkFailure } = useConnectionStatus();
          const dbDisconnected =
            (response?.status === 503 && response?.data?.data?.services?.database === 'disconnected') ||
            (apiError.code === 'DB_UNAVAILABLE' && response?.status === 503) ||
            (typeof apiError.message === 'string' &&
              /(?:SQLSTATE\[HY000\]\s*\[2002\]|SQLSTATE\[HY000\]:\s*General error:\s*2006|ECONNREFUSED|actively refused|No connection could be made)/i.test(apiError.message));

          if (dbDisconnected) {
            markDisconnected('database', apiError.message || 'Database connection is inactive.');
          } else if (apiError.isNetwork) {
            reportNetworkFailure(apiError.message);
          }
        } catch {
          // ignore connection status evaluation failures
        }
      }

      // Report 5xx, network failures, or unclassified SERVER_ERROR to client error ingestion
      if (!isCanceled && (apiError.status >= 500 || apiError.isNetwork || apiError.code === 'SERVER_ERROR')) {
        const apiRequest = {
          method: method.toUpperCase(),
          path: getRequestPath(config.url),
          timeout_ms: Number(config.timeout) || null,
          elapsed_ms: Math.max(0, Date.now() - (config.__requestStartedAt || Date.now())),
          retry_count: retryCount,
          error_code: apiError.code,
        };

        addBreadcrumb({
          type: 'http',
          category: 'error',
          message: `${apiRequest.method} ${apiRequest.path} failed with ${apiError.code}`,
          data: apiRequest,
        });

        recordClientError(apiError, {
          route: config.url,
          requestId: apiError.requestId,
          httpStatus: apiError.status,
          errorCode: apiError.code,
          apiRequest,
        });
      }

      return Promise.reject(apiError);
    }
  );

  return api;
}

const client = setupApiClient();
export const api = client;
export { client, ApiError };
export default client;
