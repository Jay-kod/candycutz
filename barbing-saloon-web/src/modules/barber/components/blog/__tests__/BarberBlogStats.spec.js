import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BarberBlogStats from '../BarberBlogStats.vue';

describe('BarberBlogStats.vue', () => {
  it('renders metric cards with correct numbers', () => {
    const mockStats = {
      totalCount: 12,
      publishedCount: 8,
      draftsCount: 4,
      totalLoves: 54,
      publishRate: 67,
    };

    const wrapper = mount(BarberBlogStats, {
      props: {
        stats: mockStats,
      },
    });

    expect(wrapper.text()).toContain('12');
    expect(wrapper.text()).toContain('8');
    expect(wrapper.text()).toContain('4');
    expect(wrapper.text()).toContain('54');
    expect(wrapper.text()).toContain('67% live');
  });
});

