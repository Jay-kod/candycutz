import { describe, expect, it } from 'vitest';
import adminRoutes from '../routes';
import barberRoutes from '../../barber/routes';

describe('admin routes', () => {
  it('does not expose standalone app-management pages in the admin portal', () => {
    expect(adminRoutes.some(route => route.path.startsWith('/admin/mobile-app'))).toBe(false);
  });

  it('exposes service category management to admins', () => {
    expect(adminRoutes.some(route => route.path === '/admin/service-categories' && route.meta.roles.includes('admin'))).toBe(true);
  });

  it('exposes resend setup page to admins', () => {
    expect(adminRoutes.some(route => route.path === '/admin/resend' && route.meta.roles.includes('admin'))).toBe(true);
  });

  it('exposes category requests to barbers', () => {
    expect(barberRoutes.some(route => route.path === '/barber/service-categories' && route.meta.roles.includes('barber'))).toBe(true);
  });
});
