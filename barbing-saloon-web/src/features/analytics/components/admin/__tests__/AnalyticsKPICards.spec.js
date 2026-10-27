import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AnalyticsKPICards from '../AnalyticsKPICards.vue';

describe('AnalyticsKPICards.vue', () => {
  it('renders business stats values and formatted currency', () => {
    const businessStats = {
      total_revenue: 150000,
      realized_revenue: 120000,
      total_appointments: 45,
      new_customers: 12,
      total_customers: 60,
      average_ticket: 3333
    };

    const platformStats = {
      active_barbers: 4
    };

    const formatCurrency = (val) => Number(val).toLocaleString('en-NG');

    const wrapper = mount(AnalyticsKPICards, {
      props: {
        businessStats,
        platformStats,
        formatCurrency
      }
    });

    const text = wrapper.text();
    expect(text).toContain('150,000');
    expect(text).toContain('120,000');
    expect(text).toContain('45');
    expect(text).toContain('12');
    expect(text).toContain('60');
    expect(text).toContain('4 active barbers');
  });

  it('calculates customer acquisition rate correctly', () => {
    const businessStats = {
      new_customers: 25,
      total_customers: 100
    };

    const wrapper = mount(AnalyticsKPICards, {
      props: {
        businessStats,
        platformStats: {}
      }
    });

    // 25 / 100 = 25%
    expect(wrapper.text()).toContain('25% of client base');
  });
});
