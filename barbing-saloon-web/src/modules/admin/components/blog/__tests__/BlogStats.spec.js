import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import BlogStats from '../BlogStats.vue';

describe('BlogStats.vue', () => {
  it('renders computed article metrics correctly', () => {
    const stats = {
      totalCount: 18,
      publishedCount: 14,
      draftsCount: 4,
      totalReactions: 86,
      authorsCount: 3,
      publishRate: 78,
    };

    const wrapper = mount(BlogStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('18');
    expect(wrapper.text()).toContain('78% live');
    expect(wrapper.text()).toContain('4');
    expect(wrapper.text()).toContain('Unpublished drafts pending');
    expect(wrapper.text()).toContain('14');
    expect(wrapper.text()).toContain('Accessible on public website');
    expect(wrapper.text()).toContain('86');
    expect(wrapper.text()).toContain('3');
  });

  it('handles zero-state metrics cleanly when no articles exist', () => {
    const stats = {
      totalCount: 0,
      publishedCount: 0,
      draftsCount: 0,
      totalReactions: 0,
      authorsCount: 0,
      publishRate: 0,
    };

    const wrapper = mount(BlogStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('0');
    expect(wrapper.text()).toContain('0% live');
    expect(wrapper.text()).toContain('Queue fully cleared');
  });
});

