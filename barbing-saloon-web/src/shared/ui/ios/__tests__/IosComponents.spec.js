import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { markRaw } from 'vue';
import IosSegmentedControl from '../IosSegmentedControl.vue';
import IosSwitch from '../IosSwitch.vue';
import IosGroupedList from '../IosGroupedList.vue';
import IosListItem from '../IosListItem.vue';
import IosModalSheet from '../IosModalSheet.vue';
import IosTabBar from '../IosTabBar.vue';
import { useNavigationProgress } from '@/core/composables/useNavigationProgress';

describe('iOS HIG Component Suite', () => {
  describe('IosSegmentedControl', () => {
    it('renders all options and selects the active segment', async () => {
      const options = [
        { value: 'all', label: 'All', count: 12 },
        { value: 'active', label: 'Active', count: 5 },
        { value: 'archived', label: 'Archived' },
      ];

      const wrapper = mount(IosSegmentedControl, {
        props: {
          modelValue: 'all',
          options,
        },
      });

      expect(wrapper.text()).toContain('All');
      expect(wrapper.text()).toContain('12');
      expect(wrapper.text()).toContain('Active');
      expect(wrapper.text()).toContain('5');

      const buttons = wrapper.findAll('button');
      expect(buttons).toHaveLength(3);
      expect(buttons[0].attributes('aria-selected')).toBe('true');
      expect(buttons[1].attributes('aria-selected')).toBe('false');

      // Click second segment
      await buttons[1].trigger('click');
      expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['active']);
      expect(wrapper.emitted('change')?.[0]).toEqual(['active']);
    });
  });

  describe('IosSwitch', () => {
    it('renders with role switch and toggles on click', async () => {
      const wrapper = mount(IosSwitch, {
        props: {
          modelValue: false,
          label: 'Push notifications',
        },
      });

      const button = wrapper.find('button');
      expect(button.attributes('role')).toBe('switch');
      expect(button.attributes('aria-checked')).toBe('false');

      await button.trigger('click');
      expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([true]);
    });

    it('does not toggle when disabled', async () => {
      const wrapper = mount(IosSwitch, {
        props: {
          modelValue: true,
          disabled: true,
        },
      });

      const button = wrapper.find('button');
      expect(button.attributes('disabled')).toBeDefined();

      await button.trigger('click');
      expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    });
  });

  describe('IosGroupedList & IosListItem', () => {
    it('renders header, footer and items with title and value', () => {
      const wrapper = mount({
        components: { IosGroupedList, IosListItem },
        template: `
          <IosGroupedList header="Account Settings" footer="Manage your account preferences">
            <IosListItem title="Profile" subtitle="Edit personal details" value="Complete" chevron clickable />
            <IosListItem title="Security" value="2FA Active" chevron clickable />
          </IosGroupedList>
        `,
      });

      expect(wrapper.text()).toContain('Account Settings');
      expect(wrapper.text()).toContain('Manage your account preferences');
      expect(wrapper.text()).toContain('Profile');
      expect(wrapper.text()).toContain('Edit personal details');
      expect(wrapper.text()).toContain('Complete');
      expect(wrapper.text()).toContain('Security');
      expect(wrapper.text()).toContain('2FA Active');
    });

    it('emits click on IosListItem', async () => {
      const wrapper = mount(IosListItem, {
        props: {
          title: 'Notifications',
          clickable: true,
        },
      });

      await wrapper.trigger('click');
      expect(wrapper.emitted('click')).toHaveLength(1);
    });
  });

  describe('IosModalSheet', () => {
    it('renders grabber handle, title and content when show=true', () => {
      const wrapper = mount(IosModalSheet, {
        props: {
          show: true,
          title: 'Select Category',
        },
        slots: {
          default: '<div class="sheet-content">Haircuts & Shaves</div>',
        },
      });

      const bodyText = document.body.innerHTML;
      expect(bodyText).toContain('Select Category');
      expect(bodyText).toContain('Haircuts &amp; Shaves');
    });
  });

  describe('IosTabBar', () => {
    it('renders tab buttons with icons and names', () => {
      const dummyIcon = markRaw({ template: '<svg class="dummy-icon"></svg>' });
      const tabs = [
        { name: 'Home', to: '/customer/dashboard', icon: dummyIcon },
        { name: 'Bookings', to: '/customer/dashboard/bookings', icon: dummyIcon, badge: 2 },
      ];

      const wrapper = mount(IosTabBar, {
        props: { tabs },
        global: {
          stubs: {
            RouterLink: {
              template: '<a :href="to" class="router-link-stub"><slot /></a>',
              props: ['to'],
            },
          },
          mocks: {
            $route: { path: '/customer/dashboard' },
          },
        },
      });

      expect(wrapper.text()).toContain('Home');
      expect(wrapper.text()).toContain('Bookings');
      expect(wrapper.text()).toContain('2');
    });
  });

  describe('useNavigationProgress', () => {
    it('controls navigation progress state lifecycle', () => {
      const { isNavigating, progress, start, finish, fail } = useNavigationProgress();

      start();
      expect(isNavigating.value).toBe(true);
      expect(progress.value).toBeGreaterThanOrEqual(20);

      finish();
      expect(progress.value).toBe(100);

      fail();
      expect(progress.value).toBe(100);
    });
  });
});
