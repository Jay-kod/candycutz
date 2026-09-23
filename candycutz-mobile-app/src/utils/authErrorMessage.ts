export function resolveAuthErrorMessage(error: any, fallback = 'Login failed. Please check your credentials.') {
  const payload = error?.response?.data ?? error?.data ?? {};

  if (typeof payload?.message === 'string' && payload.message.trim()) {
    return payload.message;
  }

  if (typeof payload?.error?.message === 'string' && payload.error.message.trim()) {
    return payload.error.message;
  }

  if (typeof payload?.errors === 'object' && payload.errors) {
    const firstError = Object.values(payload.errors).find((value) => {
      if (Array.isArray(value)) {
        return typeof value[0] === 'string' && value[0].trim().length > 0;
      }
      return typeof value === 'string' && value.trim().length > 0;
    });

    if (Array.isArray(firstError)) {
      const firstValue = firstError[0];
      if (typeof firstValue === 'string' && firstValue.trim()) {
        return firstValue;
      }
    }

    if (typeof firstError === 'string' && firstError.trim()) {
      return firstError;
    }
  }

  if (typeof error?.message === 'string' && error.message.trim()) {
    return error.message;
  }

  return fallback;
}
