import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { ref } from 'vue';
import LoginPage from '../LoginPage.vue';
import RegisterPage from '../RegisterPage.vue';

// Mock dependencies
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
  useRouter: () => ({
    push: vi.fn(),
  }),
  useRoute: () => ({
    query: {},
    path: '/customer/login',
  }),
  RouterLink: {
    template: '<a><slot /></a>',
  },
}));

vi.mock('../../composables/useAuth', () => ({
  useAuth: () => ({
    login: vi.fn(),
    register: vi.fn(),
    redirectAfterLogin: vi.fn(() => '/customer/dashboard'),
    user: ref(null),
    logout: vi.fn(),
  }),
}));

vi.mock('../../composables/useSocialAuth', () => ({
  useSocialAuth: () => ({
    googleSignIn: vi.fn(),
    socialBusy: ref(false),
    socialError: ref(''),
  }),
}));

describe('Auth Password Inputs & Mobile Responsiveness', () => {
  describe('LoginPage.vue Password Visibility Toggle', () => {
    it('defaults password input to type="password" and toggles on click', async () => {
      const wrapper = mount(LoginPage, {
        global: {
          stubs: {
            SocialAuthButtons: true,
            BrandWordmark: true,
            RouterLink: true,
          },
        },
      });

      const passwordInput = wrapper.find('input#password');
      expect(passwordInput.attributes('type')).toBe('password');

      const toggleBtn = wrapper.find('button[aria-label="Show password"]');
      expect(toggleBtn.exists()).toBe(true);
      expect(toggleBtn.classes()).toContain('touch-manipulation');

      // Click to toggle to text
      await toggleBtn.trigger('click');
      expect(wrapper.find('input#password').attributes('type')).toBe('text');
      expect(wrapper.find('button[aria-label="Hide password"]').exists()).toBe(true);

      // Click again to toggle back
      await wrapper.find('button[aria-label="Hide password"]').trigger('click');
      expect(wrapper.find('input#password').attributes('type')).toBe('password');
    });

    it('renders improved Google sign-in button with "Sign in with Google"', () => {
      const wrapper = mount(LoginPage, {
        global: {
          stubs: {
            BrandWordmark: true,
            RouterLink: true,
          },
        },
      });

      const googleBtn = wrapper.find('[data-testid="google-signin-btn"]');
      expect(googleBtn.exists()).toBe(true);
      expect(googleBtn.text()).toContain('Sign in with Google');
      expect(googleBtn.classes()).toContain('touch-manipulation');
      expect(wrapper.text()).not.toContain('Apple');
    });
  });

  describe('RegisterPage.vue Password Visibility & Strength Meter', () => {
    it('renders password and confirm password inputs with independent eye toggles', async () => {
      const wrapper = mount(RegisterPage, {
        global: {
          stubs: {
            SocialAuthButtons: true,
            BrandWordmark: true,
            RouterLink: true,
          },
        },
      });

      const pwdInput = wrapper.find('input#reg-password');
      const confirmPwdInput = wrapper.find('input#reg-password-confirm');

      expect(pwdInput.attributes('type')).toBe('password');
      expect(confirmPwdInput.attributes('type')).toBe('password');

      // Find toggle buttons
      const toggleButtons = wrapper.findAll('button[aria-label="Show password"]');
      expect(toggleButtons.length).toBe(2);

      // Toggle first password input
      await toggleButtons[0].trigger('click');
      expect(wrapper.find('input#reg-password').attributes('type')).toBe('text');
      expect(wrapper.find('input#reg-password-confirm').attributes('type')).toBe('password');

      // Toggle confirm password input
      await toggleButtons[1].trigger('click');
      expect(wrapper.find('input#reg-password').attributes('type')).toBe('text');
      expect(wrapper.find('input#reg-password-confirm').attributes('type')).toBe('text');
    });

    it('renders PasswordStrengthMeter and reflects typing in password field', async () => {
      const wrapper = mount(RegisterPage, {
        global: {
          stubs: {
            SocialAuthButtons: true,
            BrandWordmark: true,
            RouterLink: true,
          },
        },
      });

      const strengthMeter = wrapper.findComponent({ name: 'PasswordStrengthMeter' });
      expect(strengthMeter.exists()).toBe(true);

      const pwdInput = wrapper.find('input#reg-password');
      await pwdInput.setValue('SuperSecret123!');

      expect(strengthMeter.props('password')).toBe('SuperSecret123!');
      expect(strengthMeter.text()).toContain('Strong');
    });

    it('shows real-time password matching status when confirm password is typed', async () => {
      const wrapper = mount(RegisterPage, {
        global: {
          stubs: {
            SocialAuthButtons: true,
            BrandWordmark: true,
            RouterLink: true,
          },
        },
      });

      const pwdInput = wrapper.find('input#reg-password');
      const confirmPwdInput = wrapper.find('input#reg-password-confirm');

      await pwdInput.setValue('SecurePassword123!');
      await confirmPwdInput.setValue('DifferentPassword');

      expect(wrapper.text()).toContain('Passwords do not match');

      await confirmPwdInput.setValue('SecurePassword123!');
      expect(wrapper.text()).toContain('Passwords match');
    });

    it('renders improved Google sign-in button with "Sign up with Google"', () => {
      const wrapper = mount(RegisterPage, {
        global: {
          stubs: {
            BrandWordmark: true,
            RouterLink: true,
            PasswordStrengthMeter: true,
          },
        },
      });

      const googleBtn = wrapper.find('[data-testid="google-signin-btn"]');
      expect(googleBtn.exists()).toBe(true);
      expect(googleBtn.text()).toContain('Sign up with Google');
      expect(googleBtn.classes()).toContain('touch-manipulation');
      expect(wrapper.text()).not.toContain('Apple');
    });
  });
});
