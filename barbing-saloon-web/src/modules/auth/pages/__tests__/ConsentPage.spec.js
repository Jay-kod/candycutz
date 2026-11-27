import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { ref } from 'vue';
import ConsentPage from '../ConsentPage.vue';

const replaceMock = vi.fn();
const acceptConsentMock = vi.fn();
const fetchUserMock = vi.fn();
const logoutMock = vi.fn();
const redirectAfterLoginMock = vi.fn(() => '/customer/dashboard');

const mockUser = ref({
  name: 'Alex Barber',
  email: 'alex@candycutz.com',
  consent_required: true,
  consent: { terms_version: '2026-11-26' },
});

vi.mock('vue-router', () => ({
  useRouter: () => ({ replace: replaceMock }),
  useRoute: () => ({ query: {} }),
}));

vi.mock('@/modules/auth/store/auth.store', () => ({
  useAuthStore: () => ({
    user: mockUser.value,
    isAuthenticated: true,
    fetchUser: fetchUserMock,
    logout: logoutMock,
    redirectAfterLogin: redirectAfterLoginMock,
  }),
}));

vi.mock('@/shared/api/old_authApi', () => ({
  authApi: {
    acceptConsent: (...args) => acceptConsentMock(...args),
  },
}));

vi.mock('@/shared/composables/useBrandIdentity', () => ({
  useBrandIdentity: () => ({
    brandName: ref('CandyCutz'),
    brandNameFirst: ref('Candy'),
    brandNameSecond: ref('Cutz'),
    brandNameSeparator: ref('none'),
    brandGoldPart: ref('second'),
  }),
}));

describe('ConsentPage.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    acceptConsentMock.mockResolvedValue({});
    fetchUserMock.mockResolvedValue({});
    logoutMock.mockResolvedValue({});
  });

  it('renders clear policy cards, version badge, and signed-in account context', () => {
    const wrapper = mount(ConsentPage);

    expect(wrapper.text()).toContain('Review & accept our policies');
    expect(wrapper.text()).toContain('Version 2026-11-26');
    expect(wrapper.text()).toContain('alex@candycutz.com');
    expect(wrapper.find('a[href="/terms"]').exists()).toBe(true);
    expect(wrapper.find('a[href="/privacy"]').exists()).toBe(true);
  });

  it('disables the accept button until the confirmation checkbox is checked', async () => {
    const wrapper = mount(ConsentPage);

    const acceptBtn = wrapper.findAll('button').find((b) => b.text().includes('Accept and continue'));
    expect(acceptBtn.attributes('disabled')).toBeDefined();

    await acceptBtn.trigger('click');
    expect(acceptConsentMock).not.toHaveBeenCalled();

    await wrapper.find('#consent-accept').setValue(true);
    expect(acceptBtn.attributes('disabled')).toBeUndefined();
  });

  it('records consent and navigates on confirmation', async () => {
    const wrapper = mount(ConsentPage);

    await wrapper.find('#consent-accept').setValue(true);
    const acceptBtn = wrapper.findAll('button').find((b) => b.text().includes('Accept and continue'));
    await acceptBtn.trigger('click');

    expect(acceptConsentMock).toHaveBeenCalledWith({
      type: 'terms',
      version: '2026-11-26',
    });
    expect(fetchUserMock).toHaveBeenCalled();
    expect(replaceMock).toHaveBeenCalledWith('/customer/dashboard');
  });

  it('logs out and navigates to login on sign out', async () => {
    const wrapper = mount(ConsentPage);

    const signOutBtn = wrapper.findAll('button').find((b) => b.text().includes('Sign out'));
    await signOutBtn.trigger('click');

    expect(logoutMock).toHaveBeenCalled();
    expect(replaceMock).toHaveBeenCalledWith('/customer/login');
  });
});
