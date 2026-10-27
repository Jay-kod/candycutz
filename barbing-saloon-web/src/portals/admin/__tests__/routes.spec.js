import { describe, expect, it } from 'vitest';
import adminRoutes from '../routes';

describe('admin routes', () => {
  it('does not expose standalone app-management pages in the admin portal', () => {
    expect(adminRoutes.some(route => route.path.startsWith('/admin/mobile-app'))).toBe(false);
  });
});
