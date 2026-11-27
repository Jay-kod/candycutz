import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import CustomerProfileActivityCard from '../CustomerProfileActivityCard.vue';

describe('CustomerProfileActivityCard.vue', () => {
  it('shows sign-in date and time and resets only the visible activity', async () => {
    const signInAt = '2026-11-25T10:00:00Z';
    const securityActivities = [
      {
        id: 8,
        action: 'auth.login',
        title: 'Signed in',
        message: 'You signed in to your account.',
        created_at: signInAt,
      },
    ];
    const wrapper = mount(CustomerProfileActivityCard, {
      props: { securityActivities },
      global: {
        stubs: { ThemeToggleSwitch: true },
      },
    });

    expect(wrapper.text()).toContain(new Date(signInAt).toLocaleString());
    await wrapper.find('[aria-label="Reset activity list for this view"]').trigger('click');

    expect(wrapper.text()).not.toContain('Signed in');
    expect(wrapper.text()).toContain('Activity cleared from this view.');
    expect(wrapper.props('securityActivities')).toEqual(securityActivities);
  });
});
