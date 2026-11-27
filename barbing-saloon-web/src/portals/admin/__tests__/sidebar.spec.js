import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import AdminLayout from '../layouts/AdminLayout.vue';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';

vi.mock('vue-router', async (importOriginal) => {
  const actual = await importOriginal();
  return {
    ...actual,
    useRoute: () => ({ path: '/admin/dashboard', name: 'admin-dashboard' }),
    useRouter: () => ({ push: vi.fn() }),
  };
});

vi.mock('@/modules/auth/store/auth.store', () => ({
  useAuthStore: () => ({
    user: { name: 'Admin User', email: 'admin@candycutz.com', role: 'admin' },
    isCustomer: false,
    isBarber: false,
    logout: vi.fn(),
  }),
}));

vi.mock('@/core/composables/useTheme', () => ({
  useTheme: () => ({ isDark: { value: true } }),
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({ success: vi.fn(), error: vi.fn() }),
}));

vi.mock('@/core/composables/useConfirm', () => ({
  useConfirm: () => ({ confirm: vi.fn() }),
}));

vi.mock('@/core/composables/useConnectionStatus', () => ({
  useConnectionStatus: () => ({ hasActionFailure: { value: false } }),
}));

describe('Portal Sidebar Organization', () => {
  const stubs = {
    RouterLink: { template: '<a><slot /></a>' },
    DashboardHeader: { template: '<div />' },
    ConnectionActionNotice: { template: '<div />' },
    IosTabBar: { template: '<div />' },
    SidebarBrand: { template: '<div />' },
    SidebarUserProfile: { template: '<div />' },
  };

  it('places every AdminLayout nav item into an explicit professional section with zero items in Other', () => {
    const wrapper = mount(AdminLayout, { global: { stubs } });
    const text = wrapper.text();

    expect(text).not.toContain('Other');
    expect(text).toContain('Operations');
    expect(text).toContain('Financials & Revenue');
    expect(text).toContain('Catalogue & Content');
    expect(text).toContain('Website CMS');
    expect(text).toContain('Integrations & Comms');
    expect(text).toContain('Governance & System');

    expect(text).toContain('No-Shows');
    expect(text).toContain('Refunds & Policy');
    expect(text).toContain('Gallery Categories');
    expect(text).toContain('Resend Email Setup');
    expect(text).toContain('Consent Log');
  });

  it('places every Barber, Customer, and SuperAdmin nav item into explicit sections without falling into Other', () => {
    for (const Layout of [BarberLayout, CustomerLayout, SuperAdminLayout]) {
      const wrapper = mount(Layout, { global: { stubs } });
      expect(wrapper.text()).not.toContain('Other');
    }
  });
});
