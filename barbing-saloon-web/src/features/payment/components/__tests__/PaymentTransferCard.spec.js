import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import PaymentTransferCard from '../PaymentTransferCard.vue';

describe('PaymentTransferCard Component', () => {
  const dummyBank = {
    bank_name: 'Sterling Bank',
    account_name: 'NextGen PG',
    account_number: '5261935770',
    currency: 'NGN'
  };

  it('renders bank details, amount, and countdown timer correctly', () => {
    const wrapper = mount(PaymentTransferCard, {
      props: {
        bankDetails: dummyBank,
        formattedAmount: '₦30,000.00',
        amount: 30000,
        formattedTimeRemaining: '119:21',
        submitting: false
      }
    });

    expect(wrapper.text()).toContain('Transfer to account details below:');
    expect(wrapper.text()).toContain('₦30,000.00');
    expect(wrapper.text()).toContain('5261935770');
    expect(wrapper.text()).toContain('Sterling Bank');
    expect(wrapper.text()).toContain('NextGen PG');
    expect(wrapper.text()).toContain('119:21');
    expect(wrapper.text()).toContain('I Have Paid');
  });

  it('emits confirm when I Have Paid is clicked', async () => {
    const wrapper = mount(PaymentTransferCard, {
      props: {
        bankDetails: dummyBank,
        formattedAmount: '₦30,000.00',
        amount: 30000,
        formattedTimeRemaining: '119:21',
        submitting: false
      }
    });

    const button = wrapper.find('button.bg-\\[\\#FDB913\\]');
    expect(button.exists()).toBe(true);
    await button.trigger('click');

    expect(wrapper.emitted('confirm')).toBeTruthy();
  });
});
