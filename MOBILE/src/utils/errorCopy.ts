/**
 * Honest error copy resolution for CandyCutz Mobile.
 * Maps exact ErrorCodes and network states to respectful, transparent user-facing copy.
 * Fixes finding AUTH-6: Never claims "check your credentials" for network or server issues.
 */
export function getErrorMessage(error: any, defaultFallback?: string): string {
  if (error && (error.isNetwork || error.isTimeout || error.code || error.status)) {
    if (error.isNetwork) {
      return "Can't reach CandyCutz. Check your connection and try again.";
    }
    if (error.isTimeout) {
      return "Connection timed out. Please check your internet and try again.";
    }

    switch (error.code) {
      case 'AUTH_INVALID_CREDENTIALS':
        return 'Incorrect username/email or password. Please check your credentials and try again.';
      case 'AUTH_ACCOUNT_SUSPENDED':
        return 'Your account has been suspended or deactivated. Please contact support.';
      case 'AUTH_TOKEN_EXPIRED':
      case 'AUTH_TOKEN_INVALID':
      case 'AUTH_TOKEN_REVOKED':
        return 'Your session has expired. Please sign in again to continue.';
      case 'AUTH_FORBIDDEN_ROLE':
      case 'AUTH_FORBIDDEN_POLICY':
        return 'You do not have permission to access this section.';
      case 'AUTH_SOCIAL_VERIFICATION_FAILED':
        return 'Social authentication could not be verified. Please try again or sign in with password.';
      case 'BOOKING_SLOT_TAKEN':
        return 'That time slot was just taken. Please choose another time.';
      case 'BOOKING_OUTSIDE_HOURS':
        return 'The selected time is outside salon working hours.';
      case 'BOOKING_NOTICE_TOO_SHORT':
        return 'Appointments require advance notice. Please select a later time.';
      case 'BOOKING_BARBER_UNAVAILABLE':
        return 'The selected barber is unavailable during this time slot.';
      case 'RATE_LIMITED':
        return error.retryAfter
          ? `Too many attempts. Please wait ${error.retryAfter} seconds before trying again.`
          : 'Too many attempts. Please slow down and try again in a few moments.';
      case 'VALIDATION_FAILED':
        return error.message || 'Please review the entered information and try again.';
      case 'IDEMPOTENCY_KEY_REUSED':
        return 'This action has already been processed.';
      default:
        break;
    }

    const ref = error.requestId ? ` (Ref: ${error.requestId})` : '';
    if (error.status >= 500) {
      return `Our servers encountered an issue. Please try again shortly.${ref}`;
    }

    if (error.message && error.message !== 'An unexpected error occurred.') {
      return `${error.message}${ref}`;
    }
  }

  // Axios or standard error parsing
  const isNetwork =
    !error?.response &&
    (error?.message?.toLowerCase().includes('network') ||
      error?.code === 'ERR_NETWORK' ||
      error?.isNetwork);

  if (isNetwork) {
    return "Can't reach CandyCutz. Check your connection and try again.";
  }

  const payload = error?.response?.data || error?.data;
  const errorCode = payload?.error?.code || payload?.code;
  const requestId =
    payload?.error?.request_id ||
    payload?.request_id ||
    error?.response?.headers?.['x-request-id'] ||
    error?.requestId;

  if (errorCode === 'AUTH_INVALID_CREDENTIALS') {
    return 'Incorrect username/email or password. Please check your credentials and try again.';
  }

  if (typeof payload?.error?.message === 'string' && payload.error.message.trim()) {
    const ref = requestId ? ` (Ref: ${requestId})` : '';
    return `${payload.error.message}${ref}`;
  }

  if (typeof payload?.message === 'string' && payload.message.trim()) {
    const ref = requestId ? ` (Ref: ${requestId})` : '';
    return `${payload.message}${ref}`;
  }

  const ref = requestId ? ` (Ref: ${requestId})` : '';
  return defaultFallback ? `${defaultFallback}${ref}` : `Unable to complete request. Please try again.${ref}`;
}
