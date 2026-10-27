import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import TestimonialsStats from '../TestimonialsStats.vue';

describe('TestimonialsStats.vue', () => {
  it('renders computed review moderation metrics correctly', () => {
    const stats = {
      totalCount: 38,
      pendingCount: 4,
      approvedCount: 34,
      featuredCount: 12,
      avgRating: 4.9,
      fiveStarCount: 32,
      satisfactionPct: 98
    };

    const wrapper = mount(TestimonialsStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('38');
    expect(wrapper.text()).toContain('34 approved reviews');
    expect(wrapper.text()).toContain('4');
    expect(wrapper.text()).toContain('Requires admin review');
    expect(wrapper.text()).toContain('12');
    expect(wrapper.text()).toContain('★ 4.9');
    expect(wrapper.text()).toContain('98% client satisfaction');
    expect(wrapper.text()).toContain('32 perfect 5★');
  });

  it('handles empty and zero stats gracefully', () => {
    const stats = {
      totalCount: 0,
      pendingCount: 0,
      approvedCount: 0,
      featuredCount: 0,
      avgRating: 5.0,
      fiveStarCount: 0,
      satisfactionPct: 100
    };

    const wrapper = mount(TestimonialsStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('0');
    expect(wrapper.text()).toContain('All testimonials approved');
  });
});
