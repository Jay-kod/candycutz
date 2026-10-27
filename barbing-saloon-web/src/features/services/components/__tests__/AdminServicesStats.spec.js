import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ServicesStats from '../ServicesStats.vue';

describe('Admin ServicesStats.vue', () => {
  it('renders computed service stats correctly', () => {
    const stats = {
      totalCount: 12,
      activeCount: 10,
      pendingCount: 2,
      avgPrice: 8500,
      minPrice: 3000,
      maxPrice: 25000,
      avgDuration: 45
    };

    const wrapper = mount(ServicesStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('12');
    expect(wrapper.text()).toContain('10 active & bookable');
    expect(wrapper.text()).toContain('2');
    expect(wrapper.text()).toContain('Barber services require review');
    expect(wrapper.text()).toContain('8,500');
    expect(wrapper.text()).toContain('45');
    expect(wrapper.text()).toContain('3,000 – ₦25,000');
  });

  it('renders zero counts and empty state safely', () => {
    const stats = {
      totalCount: 0,
      activeCount: 0,
      pendingCount: 0,
      avgPrice: 0,
      minPrice: 0,
      maxPrice: 0,
      avgDuration: 0
    };

    const wrapper = mount(ServicesStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('0');
    expect(wrapper.text()).toContain('All barber creations reviewed');
    expect(wrapper.text()).toContain('₦0');
  });
});
