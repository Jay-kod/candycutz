import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import StatusBadge from '../StatusBadge.vue';
import StatCard from '../StatCard.vue';
import ActionDropdown from '../ActionDropdown.vue';
import DataTable from '../DataTable.vue';

describe('Shared UI Primitives', () => {
  describe('StatusBadge', () => {
    it('renders confirmed status with emerald theme and dot', () => {
      const wrapper = mount(StatusBadge, {
        props: { status: 'confirmed' },
      });
      expect(wrapper.text()).toContain('Confirmed');
      expect(wrapper.classes()).toEqual(expect.arrayContaining([expect.stringContaining('emerald')]));
    });

    it('renders pending status with custom label', () => {
      const wrapper = mount(StatusBadge, {
        props: { status: 'pending', label: 'Action Needed' },
      });
      expect(wrapper.text()).toContain('Action Needed');
      expect(wrapper.classes()).toEqual(expect.arrayContaining([expect.stringContaining('amber')]));
    });

    it('renders cancelled status with rose theme', () => {
      const wrapper = mount(StatusBadge, {
        props: { status: 'cancelled' },
      });
      expect(wrapper.text()).toContain('Cancelled');
      expect(wrapper.classes()).toEqual(expect.arrayContaining([expect.stringContaining('rose')]));
    });
  });

  describe('StatCard', () => {
    it('renders value and title properly', () => {
      const wrapper = mount(StatCard, {
        props: {
          title: 'Total Revenue',
          value: '₦450,000',
          subtitle: 'From 120 bookings',
        },
      });
      expect(wrapper.text()).toContain('Total Revenue');
      expect(wrapper.text()).toContain('₦450,000');
      expect(wrapper.text()).toContain('From 120 bookings');
    });

    it('renders trend badge when provided', () => {
      const wrapper = mount(StatCard, {
        props: {
          title: 'Active Barbers',
          value: '8',
          trend: '+12%',
          trendDirection: 'up',
          trendPeriod: 'vs yesterday',
        },
      });
      expect(wrapper.text()).toContain('+12%');
      expect(wrapper.text()).toContain('vs yesterday');
    });

    it('shows skeleton loading when loading is true', () => {
      const wrapper = mount(StatCard, {
        props: {
          title: 'Total Bookings',
          value: '95',
          loading: true,
        },
      });
      expect(wrapper.find('.animate-pulse').exists()).toBe(true);
      expect(wrapper.text()).not.toContain('Total Bookings');
    });
  });

  describe('ActionDropdown', () => {
    it('opens on button click and renders items', async () => {
      const actionFn = vi.fn();
      const items = [
        { label: 'Edit Profile', action: actionFn },
        { label: 'Delete', variant: 'danger' },
      ];
      const wrapper = mount(ActionDropdown, {
        props: { items },
      });

      expect(wrapper.text()).not.toContain('Edit Profile');
      const button = wrapper.find('button[aria-haspopup="true"]');
      await button.trigger('click');

      expect(wrapper.text()).toContain('Edit Profile');
      expect(wrapper.text()).toContain('Delete');

      const editBtn = wrapper.findAll('button').find((b) => b.text().includes('Edit Profile'));
      await editBtn.trigger('click');
      expect(actionFn).toHaveBeenCalledTimes(1);
    });
  });

  describe('DataTable', () => {
    const columns = [
      { key: 'name', label: 'Name', sortable: true },
      { key: 'role', label: 'Role' },
      { key: 'amount', label: 'Amount', align: 'right' },
    ];
    const items = [
      { id: 1, name: 'Alice Customer', role: 'Customer', amount: '₦15,000' },
      { id: 2, name: 'Bob Barber', role: 'Barber', amount: '₦30,000' },
    ];

    it('renders table headers and rows correctly', () => {
      const wrapper = mount(DataTable, {
        props: { columns, items, title: 'Clients Table' },
      });
      expect(wrapper.text()).toContain('Clients Table');
      expect(wrapper.text()).toContain('Alice Customer');
      expect(wrapper.text()).toContain('Bob Barber');
    });

    it('emits sort event when sortable header is clicked', async () => {
      const wrapper = mount(DataTable, {
        props: { columns, items },
      });
      const nameHeader = wrapper.findAll('th').find((th) => th.text().includes('Name'));
      await nameHeader.trigger('click');

      expect(wrapper.emitted('sort')).toBeTruthy();
      expect(wrapper.emitted('sort')[0][0]).toEqual({ key: 'name', order: 'asc' });
    });

    it('renders empty message when no items are present', () => {
      const wrapper = mount(DataTable, {
        props: { columns, items: [], emptyMessage: 'No clients found' },
      });
      expect(wrapper.text()).toContain('No clients found');
    });
  });
});

