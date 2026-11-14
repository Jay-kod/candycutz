import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BarberReportsKPICards from '../BarberReportsKPICards.vue';

describe('BarberReportsKPICards.vue', () => {
  it('renders revenue, appointments, completion rate, and rating', () => {
    const performanceMetrics = {
      total_revenue: 145000,
      clients_served: 28,
      completion_rate: 96,
      average_rating: 4.9,
    };

    const wrapper = mount(BarberReportsKPICards, {
      props: { performanceMetrics },
    });

    expect(wrapper.text()).toContain('Total Revenue');
    expect(wrapper.text()).toContain('145,000');
    expect(wrapper.text()).toContain('28');
    expect(wrapper.text()).toContain('96');
    expect(wrapper.text()).toContain('4.9');
  });
});

