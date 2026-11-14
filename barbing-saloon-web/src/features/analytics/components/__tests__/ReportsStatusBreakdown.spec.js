import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsStatusBreakdown from '../ReportsStatusBreakdown.vue';

describe('ReportsStatusBreakdown.vue', () => {
  it('renders empty state when statusBreakdown is empty', () => {
    const wrapper = mount(ReportsStatusBreakdown, {
      props: { statusBreakdown: [] },
    });

    expect(wrapper.text()).toContain('No status breakdown records available');
    expect(wrapper.text()).toContain('0 total');
    expect(wrapper.text()).toContain('0% Completed');
  });

  it('computes completion rate, percentages and displays status cards correctly', () => {
    const statusBreakdown = [
      { status: 'completed', count: 80 },
      { status: 'confirmed', count: 15 },
      { status: 'cancelled', count: 5 },
    ];

    const wrapper = mount(ReportsStatusBreakdown, {
      props: { statusBreakdown },
    });

    expect(wrapper.text()).toContain('100 total');
    expect(wrapper.text()).toContain('80% Completed');
    expect(wrapper.text()).toContain('80');
    expect(wrapper.text()).toContain('15');
    expect(wrapper.text()).toContain('5');
  });
});

