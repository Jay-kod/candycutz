import { describe, it, expect, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { ApiError } from '../ApiError';
import { handleApiError } from '../handleApiError';
import { useAuthStore } from '@/modules/auth/store/auth.store';
import { authApi } from '@/shared/api/old_authApi';
import * as toastModule from '@/core/composables/useToast';

describe('Workstream E: Web Client Resilient API & Auth State Machine', () => {
  let mockToast;

  beforeEach(() => {
    setActivePinia(createPinia());
    localStorage.clear();

    mockToast = {
      success: vi.fn(),
      error: vi.fn(),
      warning: vi.fn(),
      info: vi.fn(),
    };
    vi.spyOn(toastModule, 'useToast').mockReturnValue(mockToast);
  });

  describe('E1: ApiError Normalization', () => {
    it('constructs a standardized ApiError with default attributes', () => {
      const error = new ApiError();
      expect(error.name).toBe('ApiError');
      expect(error.code).toBe('SERVER_ERROR');
      expect(error.status).toBe(500);
      expect(error.action).toBe('none');
      expect(error.retryable).toBe(false);
      expect(error.isNetwork).toBe(false);
    });

    it('identifies network and timeout failures', () => {
      const netError = new ApiError({
        isNetwork: true,
        code: 'NETWORK_UNREACHABLE',
        status: 0,
      });
      expect(netError.isNetwork).toBe(true);
      expect(netError.code).toBe('NETWORK_UNREACHABLE');
      expect(netError.status).toBe(0);

      const timeoutError = new ApiError({
        isTimeout: true,
        code: 'NETWORK_TIMEOUT',
      });
      expect(timeoutError.isTimeout).toBe(true);
    });

    it('exposes fieldErrors helper from details', () => {
      const err = new ApiError({
        code: 'VALIDATION_FAILED',
        status: 422,
        action: 'fix_input',
        details: {
          field_errors: { email: ['Email is invalid'], password: ['Too short'] },
        },
      });

      expect(err.fieldErrors).toEqual({
        email: ['Email is invalid'],
        password: ['Too short'],
      });
    });
  });

  describe('E2: handleApiError UX Rules', () => {
    it('sets field errors without showing toast on 422 fix_input', () => {
      const fieldForm = {
        setErrors: vi.fn(),
      };

      const err = new ApiError({
        code: 'VALIDATION_FAILED',
        status: 422,
        action: 'fix_input',
        details: {
          field_errors: { email: ['Invalid email address'] },
        },
      });

      handleApiError(err, { fieldForm });

      expect(fieldForm.setErrors).toHaveBeenCalledWith({ email: ['Invalid email address'] });
      expect(mockToast.error).not.toHaveBeenCalled();
      expect(mockToast.warning).not.toHaveBeenCalled();
    });

    it('suppresses all UI toasts when silent option is true', () => {
      const err = new ApiError({
        code: 'SERVER_ERROR',
        status: 500,
        message: 'Fatal error',
      });

      handleApiError(err, { silent: true });

      expect(mockToast.error).not.toHaveBeenCalled();
      expect(mockToast.warning).not.toHaveBeenCalled();
    });

    it('displays error message with reference ID on server errors', () => {
      const err = new ApiError({
        code: 'SERVER_ERROR',
        status: 500,
        message: 'Something went wrong',
        requestId: 'req_1234567890abcdef',
        action: 'contact_support',
      });

      handleApiError(err);

      expect(mockToast.error).toHaveBeenCalledWith('Something went wrong (Ref: req_1234567890abcdef)');
    });
  });

  describe('E3: Auth State Machine & Degraded Mode', () => {
    it('initializes in guest mode when no token is present', async () => {
      const auth = useAuthStore();
      await auth.bootstrap();

      expect(auth.status).toBe('guest');
      expect(auth.isAuthenticated).toBe(false);
      expect(auth.user).toBeNull();
    });

    it('transitions to authenticated mode when token and /auth/me succeed', async () => {
      localStorage.setItem('candycutz_auth_token', 'valid-token');
      vi.spyOn(authApi, 'me').mockResolvedValueOnce({
        data: {
          data: {
            id: 1,
            name: 'Jane Doe',
            email: 'jane@example.com',
            role: 'customer',
          },
        },
      });

      const auth = useAuthStore();
      await auth.bootstrap();

      expect(auth.status).toBe('authenticated');
      expect(auth.isAuthenticated).toBe(true);
      expect(auth.user.name).toBe('Jane Doe');
      expect(localStorage.getItem('candycutz_auth_token')).toBe('valid-token');
    });

    it('transitions to degraded mode on network error without wiping token or cached user', async () => {
      localStorage.setItem('candycutz_auth_token', 'existing-token');
      localStorage.setItem(
        'candycutz_cached_user',
        JSON.stringify({ id: 2, name: 'John Barber', role: 'barber' })
      );

      // Simulate network outage
      vi.spyOn(authApi, 'me').mockRejectedValueOnce(
        new ApiError({
          isNetwork: true,
          code: 'NETWORK_UNREACHABLE',
          status: 0,
          action: 'retry',
        })
      );

      const auth = useAuthStore();
      await auth.bootstrap();

      expect(auth.status).toBe('degraded');
      expect(auth.isDegraded).toBe(true);
      expect(auth.isAuthenticated).toBe(true); // Degraded session still authenticated with cached identity
      expect(auth.user.name).toBe('John Barber');
      expect(auth.token).toBe('existing-token');
      expect(localStorage.getItem('candycutz_auth_token')).toBe('existing-token');
    });

    it('wipes token and enters expired state on AUTH_TOKEN_INVALID / action=logout', async () => {
      localStorage.setItem('candycutz_auth_token', 'stale-token');

      vi.spyOn(authApi, 'me').mockRejectedValueOnce(
        new ApiError({
          code: 'AUTH_TOKEN_INVALID',
          status: 401,
          action: 'logout',
          message: 'Session has expired.',
        })
      );

      const auth = useAuthStore();
      await auth.bootstrap();

      expect(auth.status).toBe('expired');
      expect(auth.isAuthenticated).toBe(false);
      expect(auth.token).toBeNull();
      expect(localStorage.getItem('candycutz_auth_token')).toBeNull();
    });

    it('does not wipe token or clear state on login AUTH_INVALID_CREDENTIALS', async () => {
      vi.spyOn(authApi, 'login').mockRejectedValueOnce(
        new ApiError({
          code: 'AUTH_INVALID_CREDENTIALS',
          status: 401,
          action: 'fix_input',
          message: 'Invalid credentials provided.',
        })
      );

      const auth = useAuthStore();
      await expect(auth.login({ email: 'test@example.com', password: 'wrong' })).rejects.toThrow();

      // State is preserved on the form, no forced logout / wipe
      expect(auth.status).toBe('booting');
      expect(auth.token).toBeNull();
    });
  });
});
