import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ReportsKPICards from '../ReportsKPICards.vue';

describe('ReportsKPICards.vue', () => {
  it('renders all 5 executive KPI cards with proper formatting', () => {
    const businessStats = {
      total_revenue: 250000,
      realized_revenue: 200000,
      total_appointments: 75,
      new_customers: 18,
      total_customers: 120,
      average_ticket: 2667
    };

    const platformStats = {
      active_barbers: 6,
      total_services: 14,
      avg_rating: 4.9
    };

    const wrapper = mount(ReportsKPICards, {
      props: {
        businessStats,
        platformStats
      }
    });

    const text = wrapper.text();
    expect(text).toContain('250,000');
    expect(text).toContain('200,000');
    expect(text).toContain('75');
    expect(text).toContain('18');
    expect(text).toContain('120');
    expect(text).toContain('6 active barbers');
    expect(text).toContain('14');
    expect(text).toContain('4.9');
  });

  it('handles default empty stats gracefully without throwing', () => {
    const wrapper = mount(ReportsKPICards, {
      props: {
        businessStats: {},
        platformStats: {}
      }
    });

    expect(wrapper.exists()).toBe(true);
    const text = wrapper.text();
    expect(text).toContain('₦0');
    expect(text).toContain('0 appts');
  });
});
