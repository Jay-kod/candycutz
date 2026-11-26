import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import { createPinia, setActivePinia } from 'pinia';
import ProfilePage from '../ProfilePage.vue';
import { client } from '@/shared/api/client';
import { useAuthStore } from '@/modules/auth/store/auth.store';

const mockToastSuccess = vi.fn();
const mockToastError = vi.fn();

vi.mock('@/shared/api/client', () => ({
  client: {
    get: vi.fn(),
    post: vi.fn(),
    delete: vi.fn(),
  },
}));

vi.mock('@/portals/customer/layouts/CustomerLayout.vue', () => ({
  default: {
    name: 'CustomerLayout',
    template: '<div><slot /></div>',
  },
}));

vi.mock('vue-router', () => ({
  useRouter: () => ({ push: vi.fn() }),
  useRoute: () => ({ path: '/customer/dashboard/profile' }),
  RouterLink: {
    name: 'RouterLink',
    props: ['to'],
    template: '<a :href="typeof to === \'string\' ? to : to.path"><slot /></a>',
  },
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: mockToastSuccess,
    error: mockToastError,
    info: vi.fn(),
    warning: vi.fn(),
  }),
}));

describe('Customer ProfilePage.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    setActivePinia(createPinia());
    const authStore = useAuthStore();
    authStore.user = {
      id: 10,
      name: 'Damilola Client',
      email: 'damilola@example.com',
      role: 'customer',
      is_email_verified: true,
      email_verified_at: '2026-11-20T10:00:00Z',
    };
    authStore.fetchUser = vi.fn().mockResolvedValue(undefined);

    client.get.mockImplementation((url) => {
      if (url === '/customer/profile') {
        return Promise.resolve({
          data: {
            success: true,
            data: {
              profile: {
                name: 'Damilola Client',
                username: 'damilola_c',
                email: 'damilola@example.com',
                phone: '+2348012345678',
                bio: 'Low skin fade with crisp lineup.',
                avatar_url: null,
              },
              history: {
                latest_bookings: [
                  {
                    id: 77,
                    service: { name: 'Executive Skin Fade' },
                    appointment_date: '2026-11-25',
                    appointment_time: '12:00 PM',
                    status: 'completed',
                  },
                ],
              },
            },
          },
        });
      }
      if (url === '/auth/email/status') {
        return Promise.resolve({
          data: { success: true, data: { is_email_verified: true, email_verified_at: '2026-11-20T10:00:00Z' } },
        });
      }
      return Promise.resolve({ data: { success: true, data: {} } });
    });

    client.post.mockResolvedValue({
      data: { success: true, message: 'Profile updated successfully!' },
    });
  });

  it('loads and renders profile details, verification card, cookie preferences, and recent activity', async () => {
    const wrapper = mount(ProfilePage, {
      global: {
        stubs: {
          'router-link': true,
        },
      },
    });
    await flushPromises();

    expect(wrapper.text()).toContain('Account Settings');
    expect(wrapper.text()).toContain('Personal Details');
    expect(wrapper.text()).toContain('Email Verification & Security');
    expect(wrapper.text()).toContain('Cookie & Tracking Preferences');
    expect(wrapper.text()).toContain('Executive Skin Fade');
    expect(wrapper.find('#profile-name').element.value).toBe('Damilola Client');
    expect(wrapper.find('#profile-bio').element.value).toBe('Low skin fade with crisp lineup.');
  });

  it('submits updated profile form details cleanly', async () => {
    const wrapper = mount(ProfilePage, {
      global: {
        stubs: {
          'router-link': true,
        },
      },
    });
    await flushPromises();

    await wrapper.find('#profile-name').setValue('Damilola Updated');
    await wrapper.find('form').trigger('submit');
    await flushPromises();

    expect(client.post).toHaveBeenCalledWith('/customer/profile', expect.any(FormData));
    expect(mockToastSuccess).toHaveBeenCalledWith('Profile updated');
  });
});
