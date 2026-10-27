import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import WorkingHoursStats from '../WorkingHoursStats.vue';

describe('WorkingHoursStats.vue', () => {
  it('renders computed staff schedule metrics accurately', () => {
    const stats = {
      totalBarbers: 5,
      workingTodayCount: 4,
      totalWeeklyShifts: 28,
      averageDaysPerBarber: '5.6',
      coverageRate: 80,
      todayName: 'Tuesday',
    };

    const wrapper = mount(WorkingHoursStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('5');
    expect(wrapper.text()).toContain('80% on duty');
    expect(wrapper.text()).toContain('4');
    expect(wrapper.text()).toContain('on Tuesday');
    expect(wrapper.text()).toContain('Accepting client appointments');
    expect(wrapper.text()).toContain('28');
    expect(wrapper.text()).toContain('5.6');
    expect(wrapper.text()).toContain('days / week');
  });

  it('handles zero-state metrics cleanly when no barbers are scheduled', () => {
    const stats = {
      totalBarbers: 0,
      workingTodayCount: 0,
      totalWeeklyShifts: 0,
      averageDaysPerBarber: '0.0',
      coverageRate: 0,
      todayName: 'Today',
    };

    const wrapper = mount(WorkingHoursStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('0');
    expect(wrapper.text()).toContain('0% on duty');
    expect(wrapper.text()).toContain('0.0');
  });
});
