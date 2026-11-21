import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import NotificationStats from '../NotificationStats.vue';

describe('NotificationStats.vue', () => {
  it('renders all 4 notification metric cards accurately', () => {
    const stats = {
      total: 1250,
      unread: 14,
      bookings: 890,
      alerts: 360
    };

    const wrapper = mount(NotificationStats, {
      props: { stats }
    });

    const text = wrapper.text();
    expect(text).toContain('Total Dispatches');
    expect(text).toContain('1,250');
    expect(text).toContain('Logged in stream');

    expect(text).toContain('Unread Alerts');
    expect(text).toContain('14');
    expect(text).toContain('Require admin attention');

    expect(text).toContain('Bookings & Service');
    expect(text).toContain('890');
    expect(text).toContain('Appointment dispatches');

    expect(text).toContain('System & Security');
    expect(text).toContain('360');
    expect(text).toContain('Payments & platform signals');
  });

  it('handles default zeroed stats gracefully without errors', () => {
    const wrapper = mount(NotificationStats, {
      props: { stats: {} }
    });

    expect(wrapper.exists()).toBe(true);
    const text = wrapper.text();
    expect(text).toContain('Total Dispatches');
    expect(text).toContain('0');
    expect(text).toContain('Unread Alerts');
    expect(text).toContain('Bookings & Service');
    expect(text).toContain('System & Security');
  });
});

