import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BarbersStats from '../BarbersStats.vue';

describe('BarbersStats', () => {
  it('correctly aggregates staff roster, chair readiness, and rating metrics', () => {
    const barbers = [
      {
        id: 1,
        name: 'Marcus Master',
        status: 'active',
        chair_status: 'free',
        is_available: true,
        rating: 5.0,
        experience_years: 8
      },
      {
        id: 2,
        name: 'Alex Stylist',
        status: 'active',
        chair_status: 'busy',
        is_available: false,
        rating: 4.8,
        experience_years: 4
      },
      {
        id: 3,
        name: 'New Apprentice',
        status: 'pending_approval',
        chair_status: 'on_leave',
        is_available: false,
        rating: 5.0,
        experience_years: 2
      }
    ];

    const wrapper = mount(BarbersStats, {
      props: {
        barbers
      }
    });

    const text = wrapper.text();

    // 1. Staff Roster (3 total, 2 active)
    expect(text).toContain('Staff Roster');
    expect(text).toContain('3stylists');
    expect(text).toContain('2 active staff on-duty');

    // 2. Chair readiness (1 free chair)
    expect(text).toContain('Chair Readiness');
    expect(text).toContain('1chairs open');

    // 3. Master Rating ((5.0 + 4.8 + 5.0) / 3 = 4.9)
    expect(text).toContain('Master Rating');
    expect(text).toContain('★ 4.9');

    // 4. Experience index ((8 + 4 + 2) / 3 = 4.7)
    expect(text).toContain('Experience Index');
    expect(text).toContain('4.7');
    expect(text).toContain('yrs avg');
  });

  it('renders clean fallback state when barber roster is empty', () => {
    const wrapper = mount(BarbersStats, {
      props: {
        barbers: []
      }
    });

    const text = wrapper.text();
    expect(text).toContain('0stylists');
    expect(text).toContain('0chairs open');
    expect(text).toContain('★ 5.0');
  });
});
