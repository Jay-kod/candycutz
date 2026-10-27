import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import ApiDirectoryStats from '../ApiDirectoryStats.vue';

describe('ApiDirectoryStats.vue', () => {
  it('renders computed API directory metrics accurately when gateway is online', () => {
    const stats = {
      totalEndpoints: 48,
      publicCount: 16,
      protectedCount: 32,
      groupCount: 6,
      healthState: 'online',
      latencyMs: 38,
    };

    const wrapper = mount(ApiDirectoryStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('48');
    expect(wrapper.text()).toContain('6 groups');
    expect(wrapper.text()).toContain('38ms');
    expect(wrapper.text()).toContain('/api/v1/health probe');
    expect(wrapper.text()).toContain('100%');
    expect(wrapper.text()).toContain('/api/v1/*');
    expect(wrapper.text()).toContain('32');
    expect(wrapper.text()).toContain('16 unauthenticated');
  });

  it('renders checking state accurately during probe execution', () => {
    const stats = {
      totalEndpoints: 48,
      publicCount: 16,
      protectedCount: 32,
      groupCount: 6,
      healthState: 'checking',
      latencyMs: null,
    };

    const wrapper = mount(ApiDirectoryStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('Checking');
  });
});

