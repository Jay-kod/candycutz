import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import ResendSetupPage from '../ResendSetupPage.vue';

const clientMock = vi.hoisted(() => ({
  get: vi.fn(),
  post: vi.fn()
}));

const toastMock = vi.hoisted(() => ({
  success: vi.fn(),
  error: vi.fn()
}));

vi.mock('vue-router', () => ({
  useRoute: () => ({ path: '/admin/resend' })
}));

vi.mock('@/portals/admin/layouts/AdminLayout.vue', () => ({
  default: { template: '<div class="admin-layout"><slot /></div>' }
}));

vi.mock('@/portals/superadmin/layouts/SuperAdminLayout.vue', () => ({
  default: { template: '<div class="superadmin-layout"><slot /></div>' }
}));

vi.mock('@/shared/api/client', () => ({
  client: clientMock
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => toastMock
}));

describe('ResendSetupPage.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();

    clientMock.get.mockImplementation((url) => {
      if (url.includes('/admin/settings')) {
        return Promise.resolve({
          data: {
            data: {
              mail_host: 'smtp.resend.com',
              mail_port: '587',
              mail_username: 'resend',
              mail_password: '••••••••1234',
              resend_api_key: '••••••••5678',
              mail_from: 'noreply@candycutz.com',
              mail_from_name: 'CandyCutz'
            }
          }
        });
      }
      if (url === '/admin/notifications/analytics') {
        return Promise.resolve({
          data: {
            data: {
              deliveries_by_channel: [
                { channel: 'email', status: 'delivered', total: 40 },
                { channel: 'email', status: 'failed', total: 2 }
              ]
            }
          }
        });
      }
      if (url === '/admin/notifications/deliveries') {
        return Promise.resolve({
          data: {
            data: {
              data: [
                {
                  id: 1,
                  channel: 'email',
                  status: 'delivered',
                  attempts: 1,
                  provider_reference: 'resend-msg-abc-123',
                  delivered_at: '2026-11-21T10:00:00Z',
                  notification: {
                    title: 'Booking Confirmed #BK-100',
                    user: { email: 'client@example.com' }
                  }
                }
              ]
            }
          }
        });
      }
      return Promise.resolve({ data: {} });
    });

    clientMock.post.mockImplementation((url) => {
      if (url === '/admin/settings') {
        return Promise.resolve({ data: { success: true, message: 'Saved successfully' } });
      }
      if (url === '/admin/resend/test-connection') {
        return Promise.resolve({
          data: {
            success: true,
            message: 'Resend API connection successful. No email was sent.',
            data: {
              connected: true,
              domain_count: 1,
              verified_domain_count: 1,
              domains: [{ name: 'candycutz.com', status: 'verified' }]
            }
          }
        });
      }
      return Promise.resolve({
        data: {
          success: true,
          message: 'Test email dispatched successfully',
          data: { sent_to: 'verifier@candycutz.com' }
        }
      });
    });
  });

  it('shows credentials as configured without claiming a verified connection', async () => {
    const wrapper = mount(ResendSetupPage);
    await flushPromises();

    expect(wrapper.text()).toContain('Resend Email Gateway');
    expect(wrapper.text()).toContain('Credentials Present');
    expect(wrapper.text()).not.toContain('Connected & Verified');
    expect(wrapper.text()).toContain('Email Flow Diagnostics');
    expect(wrapper.text()).toContain('Password-reset OTP details');
    expect(wrapper.text()).toContain('noreply@candycutz.com');
    const diagnostics = wrapper.find('section[aria-labelledby="email-flow-diagnostics"] > details');
    expect(diagnostics.exists()).toBe(true);
    expect(diagnostics.element.open).toBe(false);
    await diagnostics.find('summary').trigger('click');
    expect(diagnostics.element.open).toBe(true);
    const checklist = wrapper.findAll('details').find(item => item.text().includes('Resend Pre-Flight Checklist'));
    expect(checklist.element.open).toBe(false);
    expect(clientMock.get).toHaveBeenCalledWith('/admin/notifications/deliveries', {
      params: { channel: 'email', per_page: 50 }
    });
    expect(clientMock.get).toHaveBeenCalledWith('/admin/notifications/analytics');
  });

  it('switches between configuration, setup guide, and deliveries tabs', async () => {
    const wrapper = mount(ResendSetupPage);
    await flushPromises();

    // Default tab is Credentials & Settings
    expect(wrapper.text()).toContain('Resend API Credentials');

    // Click Step-by-Step Setup Guide tab button
    const guideBtn = wrapper.findAll('button').find(b => b.text().includes('Step-by-Step Setup Guide'));
    expect(guideBtn).toBeDefined();
    await guideBtn.trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('How to Set Up Resend Transactional Email');
    expect(wrapper.text()).toContain('Create or Log In to Your Resend Account');
    expect(wrapper.text()).toContain('Add & Verify Your Sending Domain');

    // Click Email Templates & Studio tab button
    const templatesBtn = wrapper.findAll('button').find(b => b.text().includes('Email Templates & Studio'));
    expect(templatesBtn).toBeDefined();
    await templatesBtn.trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('Live Customer View');
    expect(wrapper.text()).toContain('Select Template To Edit');
    expect(wrapper.text()).toContain('C A N D Y C U T Z');

    // Click Email Deliveries & Audit tab button
    const deliveriesBtn = wrapper.findAll('button').find(b => b.text().includes('Email Deliveries & Audit'));
    expect(deliveriesBtn).toBeDefined();
    await deliveriesBtn.trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('Resend Transactional Email Audit');
  });

  it('submits settings to the backend using the registered POST route', async () => {
    const wrapper = mount(ResendSetupPage);
    await flushPromises();

    const saveBtn = wrapper.findAll('button').find(b => b.text().includes('Save Configuration'));
    expect(saveBtn).toBeDefined();
    await saveBtn.trigger('click');
    await flushPromises();

    expect(clientMock.post).toHaveBeenCalledWith('/admin/settings', expect.objectContaining({
      settings: expect.objectContaining({
        resend_api_key: '••••••••5678',
        mail_from: 'noreply@candycutz.com'
      })
    }));
    expect(toastMock.success).toHaveBeenCalledWith('Resend email settings saved. Run a test dispatch to verify the provider response.');
  });

  it('dispatches test email via POST /admin/test-email', async () => {
    const wrapper = mount(ResendSetupPage);
    await flushPromises();

    const testInput = wrapper.find('input[placeholder="admin@candycutz.com"]');
    expect(testInput.exists()).toBe(true);
    await testInput.setValue('verifier@candycutz.com');

    const dispatchBtn = wrapper.findAll('button').find(b => b.text().includes('Dispatch Test Email'));
    expect(dispatchBtn).toBeDefined();
    await dispatchBtn.trigger('click');
    await flushPromises();

    expect(clientMock.post).toHaveBeenCalledWith('/admin/test-email', {
      to: 'verifier@candycutz.com'
    });
    expect(toastMock.success).toHaveBeenCalledWith('Test dispatch accepted for verifier@candycutz.com. Check the recipient inbox and Resend events.');
    expect(wrapper.text()).toContain('The backend accepted the dispatch.');
  });

  it('tests the Resend API connection separately without dispatching an email', async () => {
    const wrapper = mount(ResendSetupPage);
    await flushPromises();

    const connectionButton = wrapper.findAll('button').find(button => button.text().includes('Test Resend Connection'));
    expect(connectionButton).toBeDefined();
    await connectionButton.trigger('click');
    await flushPromises();

    expect(clientMock.post).toHaveBeenCalledWith('/admin/resend/test-connection');
    expect(clientMock.post).not.toHaveBeenCalledWith('/admin/test-email', expect.anything());
    expect(wrapper.text()).toContain('Resend API Connected');
    expect(wrapper.text()).toContain('1 verified sending domain(s) · 1 total domain(s)');
    expect(toastMock.success).toHaveBeenCalledWith('Resend API connection succeeded. No email was sent.');
  });

  it('surfaces Resend API connection errors separately from email dispatch errors', async () => {
    clientMock.post.mockImplementation((url) => {
      if (url === '/admin/resend/test-connection') {
        return Promise.reject({
          response: {
            status: 422,
            data: {
              error: {
                code: 'RESEND_CONNECTION_FAILED',
                message: 'Resend API connection failed: API key is invalid'
              }
            }
          }
        });
      }
      return Promise.resolve({ data: { success: true } });
    });

    const wrapper = mount(ResendSetupPage);
    await flushPromises();
    await wrapper.findAll('button').find(button => button.text().includes('Test Resend Connection')).trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('Resend API Connection Failed');
    expect(wrapper.text()).toContain('Resend API connection failed: API key is invalid');
    expect(wrapper.text()).toContain('HTTP 422');
    expect(wrapper.text()).toContain('RESEND_CONNECTION_FAILED');
    expect(clientMock.post).not.toHaveBeenCalledWith('/admin/test-email', expect.anything());
  });

  it('clears the previous connection result when the API key is edited', async () => {
    const wrapper = mount(ResendSetupPage);
    await flushPromises();
    await wrapper.findAll('button').find(button => button.text().includes('Test Resend Connection')).trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('Resend API Connected');

    await wrapper.find('input[placeholder="re_123456789_abcdefghijklmnopqrstuvwxyz"]').setValue('re_updated_key');
    await flushPromises();

    expect(wrapper.text()).not.toContain('Resend API Connected');
    const connectionButton = wrapper.findAll('button').find(button => button.text().includes('Test Resend Connection'));
    expect(connectionButton.attributes('disabled')).toBeDefined();
  });

  it('does not report configured when only SMTP host and sender are present', async () => {
    clientMock.get.mockImplementation((url) => {
      if (url === '/admin/settings') {
        return Promise.resolve({
          data: { data: { mail_host: 'smtp.resend.com', mail_from: 'noreply@candycutz.com' } }
        });
      }
      return Promise.resolve({ data: { data: { data: [] } } });
    });

    const wrapper = mount(ResendSetupPage);
    await flushPromises();

    expect(wrapper.text()).toContain('Saved Resend Settings Incomplete');
    expect(wrapper.text()).toContain('No saved Resend key is visible');
    expect(wrapper.text()).not.toContain('Credentials Present');
  });

  it('surfaces provider test errors with the HTTP status and API code', async () => {
    clientMock.post.mockImplementation((url) => {
      if (url === '/admin/test-email') {
        return Promise.reject({
          response: {
            status: 422,
            data: {
              message: 'The sender domain is not verified.',
              error: { code: 'EMAIL_FAILED' }
            }
          }
        });
      }
      return Promise.resolve({ data: { success: true } });
    });

    const wrapper = mount(ResendSetupPage);
    await flushPromises();
    await wrapper.find('input[placeholder="admin@candycutz.com"]').setValue('verifier@candycutz.com');
    await wrapper.findAll('button').find(button => button.text().includes('Dispatch Test Email')).trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('The sender domain is not verified.');
    expect(wrapper.text()).toContain('HTTP 422');
    expect(wrapper.text()).toContain('EMAIL_FAILED');
  });

  it('requires saving changed credentials before the live test', async () => {
    const wrapper = mount(ResendSetupPage);
    await flushPromises();

    const keyInput = wrapper.find('input[placeholder="re_123456789_abcdefghijklmnopqrstuvwxyz"]');
    await keyInput.setValue('re_new_resend_key');
    const testInput = wrapper.find('input[placeholder="admin@candycutz.com"]');
    await testInput.setValue('verifier@candycutz.com');
    const testButton = wrapper.findAll('button').find(button => button.text().includes('Dispatch Test Email'));

    expect(testButton.attributes('disabled')).toBeDefined();
    expect(wrapper.text()).toContain('Save the current API key and sender address before testing.');
  });
});
