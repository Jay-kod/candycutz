import { beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, h } from 'vue';
import { flushPromises, mount } from '@vue/test-utils';
import api from '@/shared/api/client';
import ScheduleUpcomingCard from '../../components/schedule/ScheduleUpcomingCard.vue';
import { useBarberSchedule } from '../useBarberSchedule';

vi.mock('@/shared/api/client', () => ({
  default: {
    get: vi.fn(),
  },
}));

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({ error: vi.fn(), success: vi.fn() }),
}));

describe('useBarberSchedule', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('loads upcoming bookings separately from the weekly roster and orders them chronologically', async () => {
    api.get.mockImplementation((path) => {
      if (path === '/barber/schedule') {
        return Promise.resolve({
          data: {
            data: [
              { day_of_week: 1, open_time: '10:00', close_time: '18:00', is_closed: false },
            ],
          },
        });
      }

      return Promise.resolve({
        data: {
          data: [
            {
              id: 2,
              client_name: 'June Client',
              service: { name: 'Haircut' },
              appointment_date: '2026-06-12',
              appointment_time: '11:00',
            },
            {
              id: 1,
              client_name: 'May Client',
              service: { name: 'Beard Trim' },
              appointment_date: '2026-05-10',
              appointment_time: '09:30',
            },
          ],
        },
      });
    });

    let schedule;
    const wrapper = mount(defineComponent({
      setup() {
        schedule = useBarberSchedule();
        return () => h('div');
      },
    }));

    await flushPromises();

    expect(api.get).toHaveBeenCalledWith('/barber/appointments', {
      params: { status: 'upcoming', per_page: 100 },
    });
    expect(schedule.workingHours.value).toHaveLength(7);
    expect(schedule.workingHours.value[1]).toMatchObject({
      open_time: '10:00',
      close_time: '18:00',
      is_closed: false,
    });
    expect(schedule.appointments.value.map((appointment) => appointment.id)).toEqual([1, 2]);

    const card = mount(ScheduleUpcomingCard, {
      props: {
        appointments: schedule.appointments.value,
        getMonth: schedule.getMonth,
        getDay: schedule.getDay,
        formatTimeDisplay: () => '09:30 AM',
      },
    });

    expect(card.text()).toContain('May Client');
    expect(card.text()).toContain('June Client');
    expect(card.text()).toContain('Beard Trim');
    expect(card.text()).toContain('Haircut');

    wrapper.unmount();
    card.unmount();
  });
});
