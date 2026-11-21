import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BookingTimeSlotPicker from '../BookingTimeSlotPicker.vue';

describe('BookingTimeSlotPicker.vue', () => {
  const defaultProps = {
    appointmentDate: '2026-11-21',
    appointmentTime: '',
    todayStr: '2026-11-21',
    slots: ['09:00', '10:00', '14:00', '18:00'],
    loadingSlots: false
  };

  it('renders date carousel and slots correctly', () => {
    const wrapper = mount(BookingTimeSlotPicker, {
      props: defaultProps
    });

    expect(wrapper.text()).toContain('Select Date & Time');
    expect(wrapper.text()).toContain('4 slots');
    expect(wrapper.text()).toContain('09:00 AM');
    expect(wrapper.text()).toContain('02:00 PM');
  });

  it('emits update:appointmentTime when a slot button is clicked', async () => {
    const wrapper = mount(BookingTimeSlotPicker, {
      props: defaultProps
    });

    const slotButtons = wrapper.findAll('button').filter(b => b.text().includes('09:00 AM'));
    expect(slotButtons.length).toBeGreaterThan(0);

    await slotButtons[0].trigger('click');
    expect(wrapper.emitted('update:appointmentTime')).toBeTruthy();
    expect(wrapper.emitted('update:appointmentTime')[0]).toEqual(['09:00']);
  });

  it('selects next available day when slots are empty', async () => {
    const wrapper = mount(BookingTimeSlotPicker, {
      props: {
        ...defaultProps,
        appointmentDate: '2026-11-22', // Sunday
        slots: []
      }
    });

    expect(wrapper.text()).toContain('No Available Slots on This Date');
    const checkNextBtn = wrapper.findAll('button').find(b => b.text().includes('Check Next Open Day'));
    expect(checkNextBtn).toBeDefined();

    await checkNextBtn.trigger('click');
    expect(wrapper.emitted('update:appointmentDate')).toBeTruthy();
    // Next day after Sunday Nov 22 is Monday Nov 23
    expect(wrapper.emitted('update:appointmentDate')[0]).toEqual(['2026-11-23']);
  });
});
