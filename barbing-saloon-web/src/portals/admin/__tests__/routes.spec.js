import { describe, expect, it } from 'vitest';
import adminRoutes from '../routes';
import barberRoutes from '../../barber/routes';
import customerRoutes from '../../customer/routes';
import publicRoutes from '../../public/routes';

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

  it('exposes Google authentication setup to admins', () => {
    expect(adminRoutes.some(route => route.path === '/admin/google-auth' && route.meta.roles.includes('admin'))).toBe(true);
  });

  it('exposes category requests to barbers', () => {
    expect(barberRoutes.some(route => route.path === '/barber/service-categories' && route.meta.roles.includes('barber'))).toBe(true);
  });

  it('exposes no-show policy and refunds management to admins and customers', () => {
    expect(adminRoutes.some(route => route.path === '/admin/no-shows' && route.meta.roles.includes('admin'))).toBe(true);
    expect(adminRoutes.some(route => route.path === '/admin/refunds' && route.meta.roles.includes('admin'))).toBe(true);
    expect(customerRoutes.some(route => route.path === '/customer/dashboard/refunds' && route.meta.roles.includes('customer'))).toBe(true);
    expect(customerRoutes.some(route => route.path === '/customer/dashboard/directions' && route.meta.roles.includes('customer'))).toBe(true);
    expect(publicRoutes.some(route => route.path === '/directions' && route.meta.public === true)).toBe(true);
  });
});
