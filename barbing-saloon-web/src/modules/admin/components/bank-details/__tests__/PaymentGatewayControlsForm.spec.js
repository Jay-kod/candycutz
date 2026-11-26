import { describe, it, expect, vi, beforeEach } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import { reactive } from 'vue';
import PaymentGatewayControlsForm from '../PaymentGatewayControlsForm.vue';
import { client } from '@/shared/api/client';
import { API_V1_BASE_URL } from '@/core/utils/url';

vi.mock('@/shared/api/client', () => ({
  client: {
    get: vi.fn(),
    post: vi.fn(),
  },
}));

beforeEach(() => {
  vi.clearAllMocks();
  client.get.mockResolvedValue({ data: { data: {} } });
});

describe('PaymentGatewayControlsForm', () => {
  it('renders all control fields with default values', () => {
    const form = reactive({
      payment_gateway_title: 'MTN FlyX Registration • CandyCutz',
      payment_expiry_minutes: 120,
      payment_notice_text: 'Before transferring, make sure the account name shown by your bank matches the one provided below.',
      payment_channels: ['bank_transfer', 'ussd', 'bank_account', 'nqr', 'card'],
      ussd_code_template: '*737*2*{amount}*{account_number}#',
      auto_confirm: true,
    });

    const wrapper = mount(PaymentGatewayControlsForm, {
      props: {
        form,
        saving: false
      }
    });

    expect(wrapper.text()).toContain('Checkout Page & Gateway Controls');
    expect(wrapper.text()).toContain('Checkout Header / Brand Title');
    expect(wrapper.text()).toContain('Expiry Countdown (Minutes)');
    expect(wrapper.text()).toContain('Active Payment Channels');
    expect(wrapper.text()).toContain('Auto-Confirm');
  });

  it('emits save when form is submitted', async () => {
    const form = reactive({
      payment_gateway_title: 'CandyCutz VIP',
      payment_expiry_minutes: 60,
      payment_notice_text: 'Notice',
      payment_channels: ['bank_transfer'],
      ussd_code_template: '*737#',
      auto_confirm: true,
    });

    const wrapper = mount(PaymentGatewayControlsForm, {
      props: {
        form,
        saving: false
      }
    });

    await wrapper.find('form').trigger('submit');
    expect(wrapper.emitted('save')).toBeTruthy();
  });

  it('guides Paystack setup and blocks tests until matching keys are entered', async () => {
    const wrapper = mount(PaymentGatewayControlsForm, {
      props: {
        form: reactive({}),
        section: 'paystack',
      },
    });

    await flushPromises();

    expect(wrapper.text()).toContain('Connect Paystack in three steps');
    expect(wrapper.text()).not.toContain('Checkout Page & Gateway Controls');

    const testButton = wrapper.findAll('button').find((button) => button.text().includes('Test Paystack Connection'));
    expect(testButton.attributes('disabled')).toBeDefined();

    await wrapper.find('#paystack-public-key').setValue('pk_test_example');
    await wrapper.find('#paystack-secret-key').setValue('sk_live_example');
    expect(testButton.attributes('disabled')).toBeDefined();

    await wrapper.find('#paystack-secret-key').setValue('sk_test_example');
    expect(testButton.attributes('disabled')).toBeUndefined();

    await wrapper.find('form').trigger('submit');
    expect(wrapper.emitted('save')).toBeUndefined();
  });

  it('uses a public same-origin webhook URL when the configured API is local', async () => {
    client.get.mockResolvedValue({
      data: {
        data: {
          webhook_url: 'http://localhost:8000/api/v1/payments/webhook',
        },
      },
    });

    const wrapper = mount(PaymentGatewayControlsForm, {
      props: {
        form: reactive({}),
        section: 'paystack',
      },
    });

    await flushPromises();

    const apiHost = new URL(API_V1_BASE_URL).hostname.replace(/^\[|\]$/g, '');
    const expectedUrl = ['localhost', '127.0.0.1', '::1'].includes(apiHost)
      ? `${window.location.origin}/api/v1/payments/webhook`
      : `${API_V1_BASE_URL}/payments/webhook`;

    expect(wrapper.find('input[readonly]').element.value).toBe(expectedUrl);
  });
});
