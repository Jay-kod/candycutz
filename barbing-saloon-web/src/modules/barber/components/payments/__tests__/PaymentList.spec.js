import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import PaymentList from '../PaymentList.vue';

describe('PaymentList.vue', () => {
  it('renders empty state when paymentsList is empty', () => {
    const wrapper = mount(PaymentList, {
      props: { paymentsList: [] },
    });

    expect(wrapper.text()).toContain('All Caught Up!');
    expect(wrapper.find('[data-test="payment-row"]').exists()).toBe(false);
  });

  it('renders payment records and emits view-receipt when inspect button is clicked', async () => {
    const mockBookings = [
      {
        id: 42,
        customer_name: 'Burna Boy',
        service_name: 'VIP Razor Lineup',
        appointment_date: '2026-10-28',
        appointment_time: '16:00',
        price: 15000,
        payment_status: 'awaiting_verification',
      },
    ];

    const wrapper = mount(PaymentList, {
      props: { paymentsList: mockBookings },
    });

    expect(wrapper.text()).toContain('Burna Boy');
    expect(wrapper.text()).toContain('VIP Razor Lineup');
    expect(wrapper.text()).toContain('15,000');
    expect(wrapper.text()).toContain('Awaiting Verification');

    const btn = wrapper.find('[data-test="view-receipt-btn"]');
    expect(btn.exists()).toBe(true);
    await btn.trigger('click');

    expect(wrapper.emitted('view-receipt')).toBeTruthy();
    expect(wrapper.emitted('view-receipt')[0][0]).toEqual(mockBookings[0]);
  });
});
