import axios from 'axios';
import { ApiError } from './ApiError';
import { recordClientError, addBreadcrumb } from './errorReporter';
import { API_BASE_URL } from '@/core/utils/url';

const APP_VERSION = '1.0.0';

/**
 * Generates a standard request ID matching ^req_[A-Za-z0-9]{10,40}$
 */
export function generateRequestId() {
  const rand = Math.random().toString(36).substring(2, 12) + Math.random().toString(36).substring(2, 12);
  const timestamp = Date.now().toString(36);
  return `req_${timestamp}${rand}`.substring(0, 32);
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
        message: `${(config.method || 'get').toUpperCase()} ${config.url}`,
        data: {
          requestId: config.headers['X-Request-Id'],
          method: config.method,
          url: config.url,
        },
      });

      return config;
    },
    (error) => Promise.reject(error)
  );

  // Response interceptor
  api.interceptors.response.use(
    (response) => {
      addBreadcrumb({
        type: 'http',
        category: 'response',
        message: `HTTP ${response.status} from ${(response.config.method || 'get').toUpperCase()} ${response.config.url}`,
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

      // Jittered backoff retry for idempotent requests on network errors or 502/503/504
      const retryCount = config.__retryCount || 0;
      const status = error.response?.status;
      const isRetryableError = !error.response || status === 502 || status === 503 || status === 504 || error.code === 'ECONNABORTED';

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

      if (!response) {
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
            ? 'AUTH_TOKEN_INVALID'
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
            ? 'Session expired or unauthenticated.'
            : status === 403
            ? 'You do not have permission to perform this action.'
            : status === 404
            ? 'Requested resource not found.'
            : status === 422
            ? 'The given data was invalid.'
            : status === 429
            ? 'Too many requests. Please wait before retrying.'
            : 'A server error occurred. Please try again later.');

        const action =
          errObj.action ||
          (status === 401 ? 'logout' : status === 422 ? 'fix_input' : status === 429 ? 'wait' : 'none');

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

      // Report 5xx, network failures, or unclassified SERVER_ERROR to client error ingestion
      if (apiError.status >= 500 || apiError.isNetwork || apiError.code === 'SERVER_ERROR') {
        recordClientError(apiError, {
          route: config.url,
          requestId: apiError.requestId,
          httpStatus: apiError.status,
          errorCode: apiError.code,
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
