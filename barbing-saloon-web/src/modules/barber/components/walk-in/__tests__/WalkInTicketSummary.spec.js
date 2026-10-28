import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import WalkInTicketSummary from '../WalkInTicketSummary.vue';

describe('WalkInTicketSummary.vue', () => {
  const baseProps = {
    form: {
      customer_name: 'David Adeleke',
      customer_phone: '08031234567',
      payment_method: 'cash',
      appointment_time: '14:30',
    },
    currentDateFormatted: 'Monday, October 27, 2026',
    selectedServiceName: 'Executive Fade & Beard Sculpt',
    selectedServicePrice: 7500,
    isFormValid: true,
    submitting: false,
  };

  it('renders guest name, service name, and formatted total price correctly', () => {
    const wrapper = mount(WalkInTicketSummary, {
      props: baseProps,
    });

    expect(wrapper.text()).toContain('David Adeleke');
    expect(wrapper.text()).toContain('Executive Fade & Beard Sculpt');
    const priceEl = wrapper.find('[data-test="ticket-price"]');
    expect(priceEl.exists()).toBe(true);
    expect(priceEl.text()).toContain('7,500');
  });

  it('emits reset when reset button is clicked', async () => {
    const wrapper = mount(WalkInTicketSummary, {
      props: baseProps,
    });

    const resetBtn = wrapper.find('[data-test="reset-button"]');
    await resetBtn.trigger('click');
    expect(wrapper.emitted('reset')).toBeTruthy();
  });

  it('emits submit when confirm button is clicked and enabled', async () => {
    const wrapper = mount(WalkInTicketSummary, {
      props: baseProps,
    });

    const submitBtn = wrapper.find('[data-test="submit-walk-in"]');
    expect(submitBtn.attributes('disabled')).toBeUndefined();
    await submitBtn.trigger('click');
    expect(wrapper.emitted('submit')).toBeTruthy();
  });
});
