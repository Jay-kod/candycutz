import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import FeedbackStats from '../FeedbackStats.vue';

describe('FeedbackStats.vue', () => {
  it('renders computed customer feedback metrics correctly', () => {
    const stats = {
      totalCount: 26,
      newCount: 5,
      inProgressCount: 9,
      completedCount: 12,
      maintenanceCount: 8,
      featureCount: 14
    };

    const wrapper = mount(FeedbackStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('26');
    expect(wrapper.text()).toContain('12 resolved & completed');
    expect(wrapper.text()).toContain('5');
    expect(wrapper.text()).toContain('Fresh feedback awaiting review');
    expect(wrapper.text()).toContain('9');
    expect(wrapper.text()).toContain('14');
    expect(wrapper.text()).toContain('8');
  });

  it('handles empty and zero metrics gracefully', () => {
    const stats = {
      totalCount: 0,
      newCount: 0,
      inProgressCount: 0,
      completedCount: 0,
      maintenanceCount: 0,
      featureCount: 0
    };

    const wrapper = mount(FeedbackStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('0');
    expect(wrapper.text()).toContain('Inbox up to date');
  });
});

