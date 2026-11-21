import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import PaymentGatewayControlsForm from '../PaymentGatewayControlsForm.vue';

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
});
