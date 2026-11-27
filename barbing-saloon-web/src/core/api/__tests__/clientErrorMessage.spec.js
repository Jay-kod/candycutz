import { describe, expect, it } from 'vitest';
import { ApiError } from '../ApiError';
import { sanitizeApiErrorForPortal } from '../client';

describe('end-user API error messages', () => {
  it.each(['/customer/dashboard', '/barber/schedule'])(
    'hides technical server details in the %s portal',
    (pathname) => {
      const error = new ApiError({
        message: 'SQLSTATE[HY000]: database connection refused',
        status: 500,
      });

      expect(sanitizeApiErrorForPortal(error, pathname).message).toBe(
        "We couldn't confirm that request. Please review the page before trying again."
      );
    }
  );

  it('preserves detailed diagnostics for admin and non-server errors', () => {
    const adminError = new ApiError({ message: 'Database host unavailable', status: 500 });
    const validationError = new ApiError({ message: 'Email is invalid', status: 422 });

    expect(sanitizeApiErrorForPortal(adminError, '/admin/dashboard').message).toBe(
      'Database host unavailable'
    );
    expect(sanitizeApiErrorForPortal(validationError, '/customer/profile').message).toBe(
      'Email is invalid'
    );
  });
});
