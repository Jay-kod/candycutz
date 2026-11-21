import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BarberAppointmentsFilters from '../BarberAppointmentsFilters.vue';

describe('BarberAppointmentsFilters.vue', () => {
  const tabs = [
    { label: 'All', value: 'all', count: 12 },
    { label: 'Pending', value: 'pending', count: 3 },
    { label: 'Confirmed', value: 'confirmed', count: 7 },
  ];

  it('renders all filter tabs with correct labels and counts', () => {
    const wrapper = mount(BarberAppointmentsFilters, {
      props: {
        tabs,
        currentFilter: 'all',
      },
    });

    const buttons = wrapper.findAll('button');
    expect(buttons.length).toBe(3);
    expect(buttons[0].text()).toContain('All');
    expect(buttons[0].text()).toContain('12');
    expect(buttons[1].text()).toContain('Pending');
    expect(buttons[1].text()).toContain('3');
  });

  it('emits update:currentFilter when a tab is clicked', async () => {
    const wrapper = mount(BarberAppointmentsFilters, {
      props: {
        tabs,
        currentFilter: 'all',
      },
    });

    const buttons = wrapper.findAll('button');
    await buttons[1].trigger('click');

    expect(wrapper.emitted('update:currentFilter')).toBeTruthy();
    expect(wrapper.emitted('update:currentFilter')[0]).toEqual(['pending']);
  });
});

