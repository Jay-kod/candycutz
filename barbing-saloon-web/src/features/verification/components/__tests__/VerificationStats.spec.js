import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import VerificationStats from '../VerificationStats.vue';

describe('VerificationStats.vue', () => {
  it('renders all 4 verification stats correctly', () => {
    const stats = {
      total: 142,
      verified_today: 18,
      pending: 7,
      expired: 3
    };

    const wrapper = mount(VerificationStats, {
      props: { stats }
    });

    const text = wrapper.text();
    expect(text).toContain('142');
    expect(text).toContain('18');
    expect(text).toContain('7');
    expect(text).toContain('3');
    expect(text).toContain('Awaiting check-in');
  });

  it('handles default empty stats gracefully', () => {
    const wrapper = mount(VerificationStats, {
      props: { stats: {} }
    });

    expect(wrapper.exists()).toBe(true);
    const text = wrapper.text();
    expect(text).toContain('Total Codes');
    expect(text).toContain('Verified Today');
    expect(text).toContain('Pending');
    expect(text).toContain('Expired');
  });
});
