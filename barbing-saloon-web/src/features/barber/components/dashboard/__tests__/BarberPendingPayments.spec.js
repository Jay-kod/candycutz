import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BarberPendingPayments from '../BarberPendingPayments.vue';

describe('BarberPendingPayments.vue', () => {
  it('does not render when payments is empty', () => {
    const wrapper = mount(BarberPendingPayments, {
      props: { payments: [] },
    });
    expect(wrapper.find('[data-test="pending-payment-item"]').exists()).toBe(false);
  });

  it('renders pending payment items and emits view-receipt when inspect button is clicked', async () => {
    const mockPayments = [
      {
        id: 101,
        customer_name: 'Wizkid Ayo',
        price: 25000,
        service_name: 'VIP Star Fade',
      },
    ];

    const wrapper = mount(BarberPendingPayments, {
      props: { payments: mockPayments },
    });

    expect(wrapper.text()).toContain('Wizkid Ayo');
    expect(wrapper.text()).toContain('25,000');
    expect(wrapper.text()).toContain('VIP Star Fade');

    const btn = wrapper.find('button');
    await btn.trigger('click');

    expect(wrapper.emitted('view-receipt')).toBeTruthy();
    expect(wrapper.emitted('view-receipt')[0][0]).toEqual(mockPayments[0]);
  });
});

