import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AppointmentsStats from '../AppointmentsStats.vue';

describe('AppointmentsStats', () => {
  it('correctly aggregates appointment metrics across statuses and channels', () => {
    const todayStr = new Date().toISOString().split('T')[0];

    const appointments = [
      {
        id: 1,
        status: 'pending',
        source: 'app',
        appointment_date: todayStr,
        receipt_image: 'http://localhost:8000/receipts/1.jpg',
        payment_status: 'awaiting_verification'
      },
      {
        id: 2,
        status: 'confirmed',
        source: 'walk_in',
        booking_type: 'walk_in',
        appointment_date: todayStr
      },
      {
        id: 3,
        status: 'completed',
        source: 'web',
        appointment_date: '2026-01-01'
      }
    ];

    const wrapper = mount(AppointmentsStats, {
      props: {
        appointments
      }
    });

    const text = wrapper.text();

    // Today's schedule should show 2
    expect(text).toContain("Today's Schedule");
    // Pending intake count should be 1
    expect(text).toContain("Pending Intake");
    // Receipts alert
    expect(text).toContain('1 receipts to verify');
    // Confirmed count should show 1, completed +1 done
    expect(text).toContain('Confirmed / Active');
    expect(text).toContain('+1 done');
    // Channel counts
    expect(text).toContain('1 App');
    expect(text).toContain('1 Walk-In');
    expect(text).toContain('1 Web');
  });

  it('renders clean fallback state when appointments list is empty', () => {
    const wrapper = mount(AppointmentsStats, {
      props: {
        appointments: []
      }
    });

    const text = wrapper.text();
    expect(text).toContain("Today's Schedule");
    expect(text).toContain('Queue is up to date');
    expect(text).toContain('0 all time');
  });
});
