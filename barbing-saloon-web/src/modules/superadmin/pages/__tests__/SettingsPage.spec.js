import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import SettingsPage from '../SettingsPage.vue';

const { adminApiMock } = vi.hoisted(() => ({
  adminApiMock: {
    settings: vi.fn(),
    updateSettings: vi.fn(),
  },
}));

vi.mock('vue-router', () => ({
  useRoute: () => ({ path: '/admin/settings' }),
}));
vi.mock('@vueuse/core', () => ({
  useDark: () => ({ value: true }),
}));
vi.mock('@/portals/admin/layouts/AdminLayout.vue', () => ({
  default: { template: '<div><slot /></div>' },
}));
vi.mock('@/portals/superadmin/layouts/SuperAdminLayout.vue', () => ({
  default: { template: '<div><slot /></div>' },
}));
vi.mock('@/shared/api/old_adminApi', () => ({ adminApi: adminApiMock }));
vi.mock('@/shared/api/old_superadminApi', () => ({
  superadminApi: { settings: vi.fn(), updateSettings: vi.fn() },
}));
vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({ error: vi.fn(), success: vi.fn() }),
}));

describe('SettingsPage.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    adminApiMock.settings.mockResolvedValue({
      data: {
        data: {
          general: { business_name: 'CandyCutz' },
          mobile: { splash_background_image: '/private/app-image.png' },
          splash_background_image: '/private/app-image.png',
        },
      },
    });
    adminApiMock.updateSettings.mockResolvedValue({ data: { success: true } });
  });

  it('keeps general settings available without rendering app CMS controls', async () => {
    const wrapper = mount(SettingsPage);
    await flushPromises();

    expect(wrapper.text()).toContain('System Configuration');
    expect(wrapper.text()).toContain('business_name');
    expect(wrapper.text()).not.toContain('Mobile App');
    expect(wrapper.text()).not.toContain('Onboarding');
    expect(wrapper.text()).not.toContain('splash_background_image');

    await wrapper.find('input').setValue('Updated Salon');
    await wrapper.get('button').trigger('click');
    await flushPromises();

    const settings = adminApiMock.updateSettings.mock.calls[0][0];
    expect(settings.get('settings[business_name]')).toBe('Updated Salon');
    expect(settings.get('splash_image')).toBeNull();
    expect(settings.get('settings[splash_background_image]')).toBeNull();
  });
});
