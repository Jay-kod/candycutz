import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import { ref } from 'vue';
import SocialAuthButtons from '../SocialAuthButtons.vue';

const mockGoogleSignIn = vi.fn();
const mockSocialBusy = ref(false);
const mockSocialError = ref('');

vi.mock('../../composables/useSocialAuth', () => ({
  useSocialAuth: () => ({
    googleSignIn: mockGoogleSignIn,
    socialBusy: mockSocialBusy,
    socialError: mockSocialError,
  }),
}));

describe('SocialAuthButtons.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockSocialBusy.value = false;
    mockSocialError.value = '';
  });

  it('renders Google sign-in button with "Continue with Google" by default', () => {
    const wrapper = mount(SocialAuthButtons);

    expect(wrapper.text()).toContain('Continue with Google');
    expect(wrapper.find('[data-testid="google-signin-btn"]').exists()).toBe(true);
    // Verifies Apple button is completely removed
    expect(wrapper.text()).not.toContain('Apple');
    expect(wrapper.find('button[aria-label="Apple"]').exists()).toBe(false);
  });

  it('displays "Sign up with Google" when label contains sign up', () => {
    const wrapper = mount(SocialAuthButtons, {
      props: { label: 'or sign up with' },
    });

    expect(wrapper.text()).toContain('Sign up with Google');
    expect(wrapper.text().toLowerCase()).toContain('or sign up with');
  });

  it('allows explicit override of buttonText', () => {
    const wrapper = mount(SocialAuthButtons, {
      props: {
        label: 'or',
        buttonText: 'Sign in with your Google Account',
      },
    });

    expect(wrapper.text()).toContain('Sign in with your Google Account');
  });

  it('triggers googleSignIn when clicked', async () => {
    const wrapper = mount(SocialAuthButtons);
    const googleBtn = wrapper.find('[data-testid="google-signin-btn"]');

    await googleBtn.trigger('click');
    expect(mockGoogleSignIn).toHaveBeenCalledTimes(1);
  });

  it('shows loading state when socialBusy is true', () => {
    mockSocialBusy.value = true;
    const wrapper = mount(SocialAuthButtons);

    expect(wrapper.text()).toContain('Connecting to Google...');
    const googleBtn = wrapper.find('[data-testid="google-signin-btn"]');
    expect(googleBtn.attributes('disabled')).toBeDefined();
  });

  it('renders socialError banner when error occurs', () => {
    mockSocialError.value = 'Google sign-in was blocked by this browser.';
    const wrapper = mount(SocialAuthButtons);

    expect(wrapper.text()).toContain('Google sign-in was blocked by this browser.');
  });
});
