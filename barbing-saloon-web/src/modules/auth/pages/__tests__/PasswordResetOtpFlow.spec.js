import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { ref } from 'vue';
import ForgotPasswordPage from '../ForgotPasswordPage.vue';
import ResetPasswordPage from '../ResetPasswordPage.vue';
import { authApi } from '@/shared/api/old_authApi';

vi.mock('@/shared/composables/useBrandIdentity', () => ({
  useBrandIdentity: () => ({
    brandName: ref('CandyCutz'),
    brandNameFirst: ref('Candy'),
    brandNameSecond: ref('Cutz'),
    brandNameSeparator: ref(''),
    brandGoldPart: ref('second'),
  }),
}));

vi.mock('vue-router', () => ({
  useRoute: () => ({
    query: {
      email: 'client@example.com',
      code: '654321',
    },
    path: '/reset-password',
  }),
  RouterLink: {
    template: '<a><slot /></a>',
  },
}));

vi.mock('@/shared/api/old_authApi', () => ({
  authApi: {
    forgotPassword: vi.fn(),
    resetPassword: vi.fn(),
  },
}));

describe('Password Reset OTP Flow', () => {
  it('renders ForgotPasswordPage with OTP instructions and handles submission', async () => {
    vi.mocked(authApi.forgotPassword).mockResolvedValueOnce({
      data: { message: 'A 6-digit password reset code has been sent to your verified email address.' },
    });

    const wrapper = mount(ForgotPasswordPage, {
      global: {
        stubs: {
          BrandWordmark: true,
          RouterLink: true,
        },
      },
    });

    expect(wrapper.text()).toContain('6-digit OTP code');

    const emailInput = wrapper.find('input[type="email"]');
    await emailInput.setValue('verified@example.com');
    await wrapper.find('form').trigger('submit.prevent');

    expect(authApi.forgotPassword).toHaveBeenCalledWith('verified@example.com');
  });

  it('renders ResetPasswordPage with email and 6-digit code inputs prefilled from route query', async () => {
    vi.mocked(authApi.resetPassword).mockResolvedValueOnce({
      data: { message: 'Your password has been reset.' },
    });

    const wrapper = mount(ResetPasswordPage, {
      global: {
        stubs: {
          BrandWordmark: true,
          PasswordStrengthMeter: true,
          RouterLink: true,
        },
      },
    });

    expect(wrapper.text()).toContain('6-Digit Reset Code (OTP)');

    const emailInput = wrapper.find('#rp-email');
    const codeInput = wrapper.find('#rp-code');
    const passwordInput = wrapper.find('#rp-password');
    const confirmInput = wrapper.find('#rp-password-confirm');

    expect(emailInput.element.value).toBe('client@example.com');
    expect(codeInput.element.value).toBe('654321');

    await passwordInput.setValue('NewSecretPass123!');
    await confirmInput.setValue('NewSecretPass123!');

    await wrapper.find('form').trigger('submit.prevent');

    expect(authApi.resetPassword).toHaveBeenCalledWith({
      email: 'client@example.com',
      token: '654321',
      code: '654321',
      password: 'NewSecretPass123!',
      password_confirmation: 'NewSecretPass123!',
    });
  });
});
