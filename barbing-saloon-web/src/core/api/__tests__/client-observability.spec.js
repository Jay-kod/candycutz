import { beforeEach, describe, expect, it, vi } from 'vitest';

const apiMocks = vi.hoisted(() => {
  const mocks = {
    requestHandler: null,
    responseErrorHandler: null,
    addBreadcrumb: vi.fn(),
    recordClientError: vi.fn(),
    api: {
      interceptors: {
        request: {
          use: vi.fn((fulfilled, rejected) => {
            mocks.requestHandler = fulfilled;
            mocks.requestRejectedHandler = rejected;
          }),
        },
        response: {
          use: vi.fn((fulfilled, rejected) => {
            mocks.responseHandler = fulfilled;
            mocks.responseErrorHandler = rejected;
          }),
        },
      },
    },
  };

  return mocks;
});

vi.mock('axios', () => ({
  default: {
    create: vi.fn(() => apiMocks.api),
  },
}));

vi.mock('../errorReporter', () => ({
  addBreadcrumb: apiMocks.addBreadcrumb,
  recordClientError: apiMocks.recordClientError,
}));

describe('API client error telemetry', () => {
  beforeEach(() => {
    apiMocks.addBreadcrumb.mockClear();
    apiMocks.recordClientError.mockClear();
  });

  it('records failed request details without query strings', async () => {
    await import('../client');

    const config = apiMocks.requestHandler({
      url: '/admin/system/health/detail?access_token=secret',
      method: 'post',
      timeout: 15000,
      headers: {},
    });

    await expect(
      apiMocks.responseErrorHandler({
        config,
        code: 'ECONNABORTED',
        message: 'timeout of 15000ms exceeded',
      })
    ).rejects.toMatchObject({
      code: 'NETWORK_TIMEOUT',
      isTimeout: true,
    });

    const [, context] = apiMocks.recordClientError.mock.calls[0];
    expect(context.apiRequest).toMatchObject({
      method: 'POST',
      path: '/v1/admin/system/health/detail',
      timeout_ms: 15000,
      retry_count: 0,
      error_code: 'NETWORK_TIMEOUT',
    });
    expect(context.apiRequest.elapsed_ms).toEqual(expect.any(Number));
    expect(JSON.stringify(context.apiRequest)).not.toContain('secret');
  });

  it('does not treat an unspecified 401 response as an expired session', async () => {
    await import('../client');

    await expect(
      apiMocks.responseErrorHandler({
        config: {
          method: 'get',
          url: '/v1/auth/me',
          headers: {},
          __skipConnectionTracking: true,
        },
        response: {
          status: 401,
          data: { success: false, message: 'Authentication temporarily unavailable.' },
          headers: {},
        },
      })
    ).rejects.toMatchObject({
      code: 'AUTHENTICATION_REQUIRED',
      action: 'none',
      status: 401,
    });
  });

  it('logs out when the API explicitly reports an invalid token', async () => {
    await import('../client');

    await expect(
      apiMocks.responseErrorHandler({
        config: {
          method: 'get',
          url: '/v1/auth/me',
          headers: {},
          __skipConnectionTracking: true,
        },
        response: {
          status: 401,
          data: {
            success: false,
            error: { code: 'AUTH_TOKEN_INVALID', message: 'Your session is invalid.' },
          },
          headers: {},
        },
      })
    ).rejects.toMatchObject({
      code: 'AUTH_TOKEN_INVALID',
      action: 'logout',
      status: 401,
    });
  });
});
