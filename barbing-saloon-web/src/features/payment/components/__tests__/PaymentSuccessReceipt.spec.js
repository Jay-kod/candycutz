import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import PaymentSuccessReceipt from '../PaymentSuccessReceipt.vue';

describe('PaymentSuccessReceipt Component', () => {
  beforeEach(() => {
    Object.assign(navigator, {
      clipboard: {
        writeText: vi.fn().mockResolvedValue(undefined),
      },
    });
  });

  const baseProps = {
    formattedAmount: '₦30,000',
    reference: 'PSK-TX-889900',
    channel: 'card',
    selectedBarber: { id: 2, name: 'Adeola Vance', chair_number: 3 },
    appointment: {
      id: 42,
      booking_reference: 'CC-VIP999',
      verification_code: 'CC-VIP999',
      appointment_date: '2026-11-30',
      appointment_time: '15:30',
      service: { id: 5, name: 'VIP Cut & Beard' },
    },
    result: {
      payment: {
        id: 42,
        transaction_ref: 'PSK-TX-889900',
        payment_method: 'card',
        paid_at: '2026-11-25T13:00:00Z',
      },
      barber: { id: 2, name: 'Adeola Vance', chair_number: 3 },
      appointment: {
        id: 42,
        booking_reference: 'CC-VIP999',
        verification_code: 'CC-VIP999',
        appointment_date: '2026-11-30',
        appointment_time: '15:30',
        service: { id: 5, name: 'VIP Cut & Beard' },
      },
    },
  };

  it('displays official receipt metadata, booking verification code, and itemized ledger', () => {
    const wrapper = mount(PaymentSuccessReceipt, { props: baseProps });

    expect(wrapper.find('#printable-receipt').exists()).toBe(true);
    expect(wrapper.text()).toContain('Payment Confirmed & Saved');
    expect(wrapper.text()).toContain('Booking & Service Verification Code');
    expect(wrapper.text()).toContain('CC-VIP999');
    expect(wrapper.text()).toContain('RCP-000042');
    expect(wrapper.text()).toContain('VIP Cut & Beard');
    expect(wrapper.text()).toContain('Adeola Vance (Chair #3)');
    expect(wrapper.text()).toContain('PSK-TX-889900');
    expect(wrapper.text()).toContain('Paystack · Card');
    expect(wrapper.text()).toContain('₦30,000');
  });

  it('copies the booking verification code and emits bookings and print actions', async () => {
    const wrapper = mount(PaymentSuccessReceipt, { props: baseProps });

    const copyBtn = wrapper.find('button[title="Copy Booking Code"]');
    await copyBtn.trigger('click');
    await flushPromises();

    expect(navigator.clipboard.writeText).toHaveBeenCalledWith('CC-VIP999');
    expect(copyBtn.text()).toContain('Copied');

    const bookingsBtn = wrapper.findAll('button').find((btn) => btn.text().includes('View in My Bookings'));
    await bookingsBtn.trigger('click');
    expect(wrapper.emitted('go-bookings')).toHaveLength(1);

    const printBtn = wrapper.findAll('button').find((btn) => btn.text().includes('Print Receipt'));
    await printBtn.trigger('click');
    expect(wrapper.emitted('print')).toHaveLength(1);
  });
});
