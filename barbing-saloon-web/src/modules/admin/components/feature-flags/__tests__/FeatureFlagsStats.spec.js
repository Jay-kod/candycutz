import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import FeatureFlagsStats from '../FeatureFlagsStats.vue';

describe('FeatureFlagsStats.vue', () => {
  it('renders computed runtime feature flag metrics accurately', () => {
    const stats = {
      totalCount: 12,
      activeCount: 10,
      inactiveCount: 2,
      webCount: 11,
      appCount: 9,
      crossPlatformCount: 8,
      activeRate: 83,
    };

    const wrapper = mount(FeatureFlagsStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('12');
    expect(wrapper.text()).toContain('83% enabled');
    expect(wrapper.text()).toContain('10');
    expect(wrapper.text()).toContain('Enforcing client behavior');
    expect(wrapper.text()).toContain('2');
    expect(wrapper.text()).toContain('Disabled from client traffic');
    expect(wrapper.text()).toContain('8');
    expect(wrapper.text()).toContain('11 web • 9 app');
  });

  it('handles zero-state metrics cleanly when no flags are configured', () => {
    const stats = {
      totalCount: 0,
      activeCount: 0,
      inactiveCount: 0,
      webCount: 0,
      appCount: 0,
      crossPlatformCount: 0,
      activeRate: 0,
    };

    const wrapper = mount(FeatureFlagsStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('0');
    expect(wrapper.text()).toContain('0% enabled');
    expect(wrapper.text()).toContain('0 web • 0 app');
  });
});
