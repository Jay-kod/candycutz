import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import EmailVerificationCard from '../EmailVerificationCard.vue';
import { useAuthStore } from '@/modules/auth/store/auth.store';

const clientMock = vi.hoisted(() => ({
  get: vi.fn(),
  post: vi.fn()
}));

const toastMock = vi.hoisted(() => ({
  success: vi.fn(),
  error: vi.fn()
}));

vi.mock('@/shared/api/client', () => ({
  client: clientMock
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => toastMock
}));

describe('EmailVerificationCard.vue', () => {
  let pinia;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();

    clientMock.get.mockResolvedValue({
      data: { data: { email: 'client@example.com', is_email_verified: false } }
    });
    clientMock.post.mockResolvedValue({
      data: { success: true, message: 'Code dispatched' }
    });
  });

  it('renders unverified status when user is not yet verified', async () => {
    const authStore = useAuthStore();
    authStore.user = { id: 1, email: 'client@example.com', is_email_verified: false };

    const wrapper = mount(EmailVerificationCard, {
      global: { plugins: [pinia] }
    });
    await flushPromises();

    expect(wrapper.text()).toContain('Unverified');
    expect(wrapper.text()).toContain('Send Verification Code');
    expect(wrapper.text()).toContain('client@example.com');
  });

  it('renders verified badge and protection notice when user is verified', async () => {
    const authStore = useAuthStore();
    authStore.user = {
      id: 1,
      email: 'client@example.com',
      is_email_verified: true,
      email_verified_at: '2026-11-21T10:00:00Z'
    };

    const wrapper = mount(EmailVerificationCard, {
      global: { plugins: [pinia] }
    });
    await flushPromises();

    expect(wrapper.text()).toContain('Verified & Protected');
    expect(wrapper.text()).toContain('Permanently Secured to Your Account');
  });

  it('requests 6-digit verification code and reveals OTP input', async () => {
    const authStore = useAuthStore();
    authStore.user = { id: 1, email: 'client@example.com', is_email_verified: false };

    const wrapper = mount(EmailVerificationCard, {
      global: { plugins: [pinia] }
    });
    await flushPromises();

    const sendBtn = wrapper.find('button');
    expect(sendBtn.text()).toContain('Send Verification Code');
    await sendBtn.trigger('click');
    await flushPromises();

    expect(clientMock.post).toHaveBeenCalledWith('/auth/email/verification-notification');
    expect(toastMock.success).toHaveBeenCalledWith('Code dispatched');
    expect(wrapper.text()).toContain('Enter 6-Digit Code');
  });

  it('verifies 6-digit code and transitions to verified state', async () => {
    const authStore = useAuthStore();
    authStore.user = { id: 1, email: 'client@example.com', is_email_verified: false };

    clientMock.post.mockImplementation((url) => {
      if (url === '/auth/email/verification-notification') {
        return Promise.resolve({ data: { success: true, message: 'Code dispatched' } });
      }
      if (url === '/auth/email/verify-code') {
        return Promise.resolve({
          data: {
            success: true,
            message: 'Email address verified successfully.',
            data: { is_email_verified: true, email_verified_at: '2026-11-21T20:00:00Z' }
          }
        });
      }
      return Promise.resolve({ data: {} });
    });

    const wrapper = mount(EmailVerificationCard, {
      global: { plugins: [pinia] }
    });
    await flushPromises();

    // Click send
    await wrapper.find('button').trigger('click');
    await flushPromises();

    // Enter 6-digit code
    const input = wrapper.find('input[type="text"]');
    expect(input.exists()).toBe(true);
    await input.setValue('654321');

    // Click verify
    const verifyBtn = wrapper.findAll('button').find(b => b.text().includes('Verify'));
    expect(verifyBtn).toBeDefined();
    await verifyBtn.trigger('click');
    await flushPromises();

    expect(clientMock.post).toHaveBeenCalledWith('/auth/email/verify-code', { code: '654321' });
    expect(toastMock.success).toHaveBeenCalledWith('Email address verified successfully.');
    expect(authStore.user.is_email_verified).toBe(true);
  });
});
