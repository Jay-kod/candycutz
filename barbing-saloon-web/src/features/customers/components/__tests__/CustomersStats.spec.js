import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import CustomersStats from '../CustomersStats.vue';

describe('CustomersStats', () => {
  it('correctly aggregates customer counts, lifetime spend, and engagement', () => {
    const customers = [
      {
        id: 1,
        name: 'Chinedu VIP',
        total_spent: 25000,
        total_bookings: 4
      },
      {
        id: 2,
        name: 'Regular Customer',
        total_spent: 5000,
        total_bookings: 2
      },
      {
        id: 3,
        name: 'New Registered',
        total_spent: 0,
        total_bookings: 0
      }
    ];

    const wrapper = mount(CustomersStats, {
      props: {
        customers
      }
    });

    const text = wrapper.text();

    // 1. Total count
    expect(text).toContain('Client Registry');
    expect(text).toContain('3registered');
    expect(text).toContain('2 active with bookings');

    // 2. Lifetime Volume (25000 + 5000 = 30,000)
    expect(text).toContain('Lifetime Volume');
    expect(text).toContain('₦30,000');
    expect(text).toContain('Avg. ₦10,000 / client');

    // 3. Top spenders
    expect(text).toContain('VIP & Top Spenders');
    expect(text).toContain('2VIP accounts');
    expect(text).toContain('Top: Chinedu VIP (₦25,000)');

    // 4. Bookings volume (4 + 2 = 6)
    expect(text).toContain('Booking Volume');
    expect(text).toContain('6appointments');
    expect(text).toContain('Engagement: 2.0 visits / client');
  });

  it('renders clean fallback state when customer array is empty', () => {
    const wrapper = mount(CustomersStats, {
      props: {
        customers: []
      }
    });

    const text = wrapper.text();
    expect(text).toContain('0registered');
    expect(text).toContain('₦0');
    expect(text).toContain('No spending recorded yet');
    expect(text).toContain('0appointments');
  });
});

