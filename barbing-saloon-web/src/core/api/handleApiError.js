import { useToast } from '@/core/composables/useToast';
import { ApiError } from './ApiError';

/**
 * Authoritative UI Error Handler for CandyCutz Web Client.
 * Translates typed ApiError actions and codes into consistent UX behaviors.
 * 
 * @param {ApiError|Error|any} err 
 * @param {Object} [options={}]
 * @param {boolean} [options.silent=false] Suppress all UI notifications
 * @param {Object} [options.fieldForm=null] Form context to set validation errors on
 * @param {Function} [options.onRetry=null] Retry callback for retryable actions
 */
export function handleApiError(err, { silent = false, fieldForm = null, onRetry = null } = {}) {
  // If silent, completely skip UI notifications
  if (silent) {
    return;
  }

  const toast = useToast();

  // Ensure err is an ApiError instance
  const apiError =
    err instanceof ApiError
      ? err
      : new ApiError({
          message: err?.message || 'An unexpected error occurred.',
          status: err?.response?.status || 500,
          code: err?.code || 'SERVER_ERROR',
        });

  const { action, fieldErrors, message, requestId, retryAfter } = apiError;

  // 1. Input validation errors
  if (action === 'fix_input') {
    if (fieldForm && Object.keys(fieldErrors).length > 0) {
      if (typeof fieldForm.setErrors === 'function') {
        fieldForm.setErrors(fieldErrors);
      } else if (typeof fieldForm === 'object') {
        Object.entries(fieldErrors).forEach(([key, val]) => {
          fieldForm[key] = Array.isArray(val) ? val[0] : val;
        });
      }
      // Never toast when field errors are bound to the form
      return;
    }

    // If no form context was provided, show specific message without noise
    toast.warning(message);
    return;
  }

  // 2. Authentication expiration or suspension
  if (action === 'logout') {
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem('candycutz_auth_token');
      localStorage.removeItem('candycutz_cached_user');
    }

    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';
    const isAlreadyOnAuthPage =
      currentPath.includes('/login') ||
      currentPath.includes('/register') ||
      currentPath.includes('/forgot-password');

    if (!isAlreadyOnAuthPage) {
      const redirectPath = currentPath.startsWith('/superadmin')
        ? '/superadmin/login'
        : currentPath.startsWith('/admin')
        ? '/admin/login'
        : currentPath.startsWith('/barber')
        ? '/barber/login'
        : '/customer/login';

      toast.error(message || 'Session expired. Please log in again.');

      if (typeof window !== 'undefined') {
        // Small delay to let toast display
        setTimeout(() => {
          window.location.href = redirectPath;
        }, 300);
      }
    }
    return;
  }

  // 3. Rate limiting / wait
  if (action === 'wait') {
    const waitMsg = retryAfter
      ? `Too many requests. Please wait ${retryAfter}s before retrying.`
      : message;
    toast.warning(waitMsg);
    return;
  }

  // 4. Retryable action
  if (action === 'retry') {
    toast.error(message || 'Operation failed. Please try again.');
    if (typeof onRetry === 'function') {
      onRetry();
    }
    return;
  }

  // 5. App update required
  if (action === 'update_app') {
    toast.error('A critical app update is required. Please refresh or update your browser.');
    return;
  }

  // 6. Support / unexpected error
  const supportRef = requestId ? ` (Ref: ${requestId})` : '';
  toast.error(`${message}${supportRef}`);
}
