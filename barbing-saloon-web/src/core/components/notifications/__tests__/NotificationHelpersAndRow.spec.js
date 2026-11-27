import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import NotificationRow from '../NotificationRow.vue';
import {
  getLabelForType,
  getBgColorForType,
  resolveNotificationTarget,
} from '../notificationUtils';

const pushMock = vi.fn();

vi.mock('vue-router', () => ({
  useRouter: () => ({ push: pushMock }),
  useRoute: () => ({ path: '/customer/dashboard/notifications' }),
}));

describe('notificationUtils and NotificationRow', () => {
  beforeEach(() => {
    pushMock.mockReset();
  });

  it('labels and styles booking reminders distinctly', () => {
    expect(getLabelForType('booking_reminder')).toBe('Reminder');
    expect(getBgColorForType('booking_reminder')).toContain('text-');
  });

  it('normalizes legacy customer and admin action URLs to active portal routes', () => {
    const legacyCustomer = resolveNotificationTarget(
      { type: 'payment_verified', action_url: '/customer/bookings/44', related_entity_id: 44 },
      '/customer/dashboard/notifications'
    );
    expect(legacyCustomer).toEqual({
      to: '/customer/dashboard/bookings',
      label: 'View Bookings',
    });

    const legacyAdmin = resolveNotificationTarget(
      { type: 'booking_reminder', action_url: '/admin/appointments/44', related_entity_id: 44 },
      '/admin/notifications'
    );
    expect(legacyAdmin).toEqual({
      to: '/admin/appointments',
      label: 'View Details',
    });
  });

  it('renders booking reminder metadata chip, copy action, and routes safely on action click', async () => {
    const notif = {
      id: 91,
      type: 'booking_reminder',
      priority: 'high',
      is_read: false,
      title: 'Appointment Reminder: Starts in 30 Minutes',
      message: 'Your Executive Skin Fade begins at 2:30 PM. Keep your Booking Code ready: CC-REM25M',
      action_url: '/customer/bookings/44',
      related_entity_id: 44,
      created_at: new Date().toISOString(),
      data: {
        verification_code: 'CC-REM25M',
        booking_reference: 'CC-REM25M',
        amount: '5,000.00',
      },
    };

    const wrapper = mount(NotificationRow, {
      props: { notif },
    });

    expect(wrapper.text()).toContain('Reminder');
    expect(wrapper.text()).toContain('Important');
    expect(wrapper.text()).toContain('Code: CC-REM25M');

    const copyButton = wrapper.find('button[title="Copy booking code"]');
    expect(copyButton.exists()).toBe(true);
    await copyButton.trigger('click');
    expect(wrapper.emitted('copy')).toEqual([[{ value: 'CC-REM25M', label: 'Booking code' }]]);

    const actionButton = wrapper.findAll('button').find((btn) => btn.text().includes('View Details'));
    expect(actionButton).toBeDefined();
    await actionButton.trigger('click');

    expect(wrapper.emitted('read')).toEqual([[91]]);
    expect(pushMock).toHaveBeenCalledWith('/customer/dashboard/bookings');
  });
});
