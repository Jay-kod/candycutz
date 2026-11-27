import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import CustomerRecentNotifications from '../CustomerRecentNotifications.vue';

describe('CustomerRecentNotifications.vue', () => {
  it('shows recent booking activity even when there are no notification alerts', () => {
    const wrapper = mount(CustomerRecentNotifications, {
      props: {
        notifications: [],
        appointments: [
          {
            id: 12,
            status: 'completed',
            appointment_date: '2026-11-25',
            appointment_time: '14:30:00',
            created_at: '2026-11-20T10:00:00Z',
            updated_at: '2026-11-25T15:00:00Z',
            service: { name: 'Executive Skin Fade' },
          },
        ],
      },
      global: {
        stubs: { RouterLink: true },
      },
    });

    expect(wrapper.text()).toContain('Appointment completed');
    expect(wrapper.text()).toContain('Executive Skin Fade');
    expect(wrapper.text()).not.toContain('No recent activity or alerts yet.');
  });

  it('does not duplicate a booking already represented by a notification', () => {
    const wrapper = mount(CustomerRecentNotifications, {
      props: {
        notifications: [
          {
            id: 99,
            related_entity_id: 12,
            category: 'booking',
            title: 'Booking completed',
            message: 'Your appointment is complete.',
            created_at: '2026-11-25T15:00:00Z',
          },
        ],
        appointments: [
          {
            id: 12,
            status: 'completed',
            appointment_date: '2026-11-25',
            appointment_time: '14:30:00',
            created_at: '2026-11-20T10:00:00Z',
            updated_at: '2026-11-25T15:00:00Z',
            service: { name: 'Executive Skin Fade' },
          },
        ],
      },
      global: {
        stubs: { RouterLink: true },
      },
    });

    expect(wrapper.text()).toContain('Your appointment is complete.');
    expect(wrapper.text()).not.toContain('Executive Skin Fade');
  });

  it('shows login and password events in the recent activity feed', () => {
    const signInAt = '2026-11-25T10:00:00Z';
    const wrapper = mount(CustomerRecentNotifications, {
      props: {
        notifications: [],
        appointments: [],
        accountActivities: [
          {
            id: 8,
            action: 'auth.login',
            title: 'Signed in',
            message: 'You signed in to your account.',
            created_at: signInAt,
          },
          {
            id: 9,
            action: 'auth.change_password',
            title: 'Password changed',
            message: 'Your account password was changed.',
            created_at: '2026-11-25T11:00:00Z',
          },
        ],
      },
      global: {
        stubs: { RouterLink: true },
      },
    });

    expect(wrapper.text()).toContain('Signed in');
    expect(wrapper.text()).toContain('Password changed');
    expect(wrapper.text()).not.toContain('No recent activity or alerts yet.');
    expect(wrapper.text()).toContain(new Date(signInAt).toLocaleString());
  });

  it('resets the activity list on this screen without changing its source data', async () => {
    const accountActivities = [
      {
        id: 8,
        action: 'auth.login',
        title: 'Signed in',
        message: 'You signed in to your account.',
        created_at: '2026-11-25T10:00:00Z',
      },
    ];
    const wrapper = mount(CustomerRecentNotifications, {
      props: { accountActivities },
      global: {
        stubs: { RouterLink: true },
      },
    });

    await wrapper.find('[aria-label="Reset activity list for this view"]').trigger('click');

    expect(wrapper.text()).not.toContain('Signed in');
    expect(wrapper.text()).toContain('Activity cleared from this view.');
    expect(wrapper.props('accountActivities')).toEqual(accountActivities);
  });
});
