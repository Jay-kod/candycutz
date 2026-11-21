import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ScheduleHoursCard from '../ScheduleHoursCard.vue';

describe('ScheduleHoursCard.vue', () => {
  const mockHours = [
    { day_of_week: 0, open_time: '09:00', close_time: '17:00', is_closed: true },
    { day_of_week: 1, open_time: '08:30', close_time: '18:00', is_closed: false },
  ];

  const dayNameFn = (day) => (day === 0 ? 'Sunday' : 'Monday');

  it('renders days with correct initial status', () => {
    const wrapper = mount(ScheduleHoursCard, {
      props: {
        workingHours: mockHours,
        dayName: dayNameFn,
      },
    });

    const rows = wrapper.findAll('[data-test="day-row"]');
    expect(rows.length).toBe(2);
    expect(rows[0].text()).toContain('Sunday');
    expect(rows[0].text()).toContain('Day Off / Closed');
    expect(rows[1].text()).toContain('Monday');
    expect(rows[1].text()).toContain('Chair Open');
  });

  it('toggles is_closed state when toggle button is clicked', async () => {
    const wrapper = mount(ScheduleHoursCard, {
      props: {
        workingHours: mockHours,
        dayName: dayNameFn,
      },
    });

    const toggles = wrapper.findAll('[data-test="toggle-day-closed"]');
    expect(mockHours[1].is_closed).toBe(false);
    await toggles[1].trigger('click');
    expect(mockHours[1].is_closed).toBe(true);
  });
});

