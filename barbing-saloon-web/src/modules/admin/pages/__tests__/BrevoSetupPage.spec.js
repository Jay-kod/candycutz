import { flushPromises, mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import BrevoSetupPage from '../BrevoSetupPage.vue';

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

describe('BrevoSetupPage.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks();

    clientMock.get.mockImplementation((url) => {
      if (url.includes('/admin/settings')) {
        return Promise.resolve({
          data: {
            data: {
              mail_host: 'smtp-relay.brevo.com',
              mail_port: '587',
              mail_username: 'test-user@smtp-brevo.com',
              mail_password: '••••••••1234',
              resend_api_key: '••••••••5678',
              mail_from: 'noreply@candycutz.com',
              mail_from_name: 'CandyCutz'
            }
          }
        });
      }
      if (url.includes('/admin/notifications/deliveries')) {
        return Promise.resolve({
          data: {
            data: [
              {
                id: 1,
                channel: 'email',
                status: 'delivered',
                attempts: 1,
                provider_reference: 'brevo-msg-abc-123',
                delivered_at: '2026-11-21T10:00:00Z',
                notification: {
                  title: 'Booking Confirmed #BK-100',
                  user: { email: 'client@example.com' }
                }
              }
            ]
          }
        });
      }
      if (url.includes('/admin/notifications/analytics')) {
        return Promise.resolve({
          data: {
            data: {
              deliveries_by_channel: [
                { channel: 'email', status: 'delivered', total: 42 },
                { channel: 'email', status: 'failed', total: 2 }
              ]
            }
          }
        });
      }
      return Promise.resolve({ data: {} });
    });

    clientMock.post.mockResolvedValue({
      data: { success: true, message: 'Saved successfully' }
    });
  });

  it('loads settings and renders hero with active status and KPI stats', async () => {
    const wrapper = mount(BrevoSetupPage);
    await flushPromises();

    expect(wrapper.text()).toContain('Resend Email Gateway');
    expect(wrapper.text()).toContain('Resend Gateway Active');
    expect(wrapper.text()).toContain('Connected & Verified');
    expect(wrapper.text()).toContain('noreply@candycutz.com');
  });

  it('switches between configuration, setup guide, and deliveries tabs', async () => {
    const wrapper = mount(BrevoSetupPage);
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

    // Click Email Deliveries & Audit tab button
    const deliveriesBtn = wrapper.findAll('button').find(b => b.text().includes('Email Deliveries & Audit'));
    expect(deliveriesBtn).toBeDefined();
    await deliveriesBtn.trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('Resend Transactional Email Audit');
    expect(wrapper.text()).toContain('Booking Confirmed #BK-100');
    expect(wrapper.text()).toContain('client@example.com');
  });

  it('submits updated settings to backend via POST /admin/settings', async () => {
    const wrapper = mount(BrevoSetupPage);
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
    expect(toastMock.success).toHaveBeenCalledWith('Resend configuration saved successfully');
  });

  it('dispatches test email via POST /admin/test-email', async () => {
    const wrapper = mount(BrevoSetupPage);
    await flushPromises();

    // Find the test recipient input in BrevoConfigForm
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
    expect(toastMock.success).toHaveBeenCalledWith('Test email dispatched to verifier@candycutz.com!');
    expect(wrapper.text()).toContain('Delivery Confirmed via Resend');
  });
});
