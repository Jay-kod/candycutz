import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import DashboardHeader from '../DashboardHeader.vue';

vi.mock('vue-router', () => ({
  useRouter: () => ({ push: vi.fn() }),
  useRoute: () => ({ path: '/customer/dashboard/notifications' }),
}));

vi.mock('@/core/components/NotificationBell.vue', () => ({
  default: {
    name: 'NotificationBell',
    template: '<div data-test="bell-stub"></div>',
  },
}));

describe('DashboardHeader.vue', () => {
  it('renders mobile page title without cramped 88px width truncation cap', () => {
    const wrapper = mount(DashboardHeader, {
      props: {
        currentRouteName: 'Customer Notifications',
        themeClasses: {},
        userInitials: 'CN',
        userName: 'Customer Name',
        profileRoute: '/customer/dashboard/profile',
      },
    });

    const heading = wrapper.find('h2');
    expect(heading.text()).toBe('Customer Notifications');
    expect(heading.classes()).toContain('truncate');
    expect(heading.classes()).not.toContain('max-w-[70px]');
    expect(heading.classes()).not.toContain('xs:max-w-[88px]');
  });
});
