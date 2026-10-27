import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import IntegrationsStats from '../IntegrationsStats.vue';

describe('IntegrationsStats.vue', () => {
  it('renders computed gateway metrics accurately when fully configured', () => {
    const stats = {
      configuredCount: 3,
      totalServices: 3,
      percentageConfigured: 100,
      mailConfigured: true,
      googleConfigured: true,
      appleConfigured: true,
      googleAndroidConfigured: true,
    };

    const wrapper = mount(IntegrationsStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('3/3');
    expect(wrapper.text()).toContain('100% ready');
    expect(wrapper.text()).toContain('Connected');
    expect(wrapper.text()).toContain('Transactional delivery active');
    expect(wrapper.text()).toContain('Google Sign-In');
    expect(wrapper.text()).toContain('Apple Sign-In');
  });

  it('renders partial state accurately when services are pending', () => {
    const stats = {
      configuredCount: 1,
      totalServices: 3,
      percentageConfigured: 33,
      mailConfigured: false,
      googleConfigured: true,
      appleConfigured: false,
      googleAndroidConfigured: false,
    };

    const wrapper = mount(IntegrationsStats, {
      props: { stats }
    });

    expect(wrapper.text()).toContain('1/3');
    expect(wrapper.text()).toContain('33% ready');
    expect(wrapper.text()).toContain('Missing');
    expect(wrapper.text()).toContain('Credentials required');
  });
});
