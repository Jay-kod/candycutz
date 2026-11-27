import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import PasswordStrengthMeter from '../PasswordStrengthMeter.vue';

describe('PasswordStrengthMeter.vue', () => {
  it('renders default empty state when no password is provided', () => {
    const wrapper = mount(PasswordStrengthMeter, {
      props: { password: '' },
    });

    expect(wrapper.text()).toContain('Password strength');
    expect(wrapper.text()).toContain('Minimum 8 characters');
    const progressBar = wrapper.find('[role="progressbar"]');
    expect(progressBar.attributes('aria-valuenow')).toBe('0');
  });

  it('evaluates a weak password correctly', () => {
    const wrapper = mount(PasswordStrengthMeter, {
      props: { password: 'abc' },
    });

    const label = wrapper.find('[data-testid="strength-label"]');
    expect(label.exists()).toBe(true);
    expect(label.text()).toBe('Weak');
    expect(label.classes()).toContain('text-red-400');

    const progressBar = wrapper.find('[role="progressbar"]');
    expect(progressBar.attributes('aria-valuenow')).toBe('1');

    // Rule checks
    expect(wrapper.find('[data-testid="rule-min-length"]').classes()).toContain('text-theme-muted/70');
  });

  it('evaluates a fair password meeting length and numbers', () => {
    const wrapper = mount(PasswordStrengthMeter, {
      props: { password: 'password1' },
    });

    const label = wrapper.find('[data-testid="strength-label"]');
    expect(label.exists()).toBe(true);
    expect(label.text()).toBe('Fair');
    expect(label.classes()).toContain('text-amber-400');

    const progressBar = wrapper.find('[role="progressbar"]');
    expect(progressBar.attributes('aria-valuenow')).toBe('2');

    expect(wrapper.find('[data-testid="rule-min-length"]').classes()).toContain('text-emerald-400');
    expect(wrapper.find('[data-testid="rule-number"]').classes()).toContain('text-emerald-400');
  });

  it('evaluates a good password with uppercase, lowercase, and numbers', () => {
    const wrapper = mount(PasswordStrengthMeter, {
      props: { password: 'Password123' },
    });

    const label = wrapper.find('[data-testid="strength-label"]');
    expect(label.text()).toBe('Good');
    expect(label.classes()).toContain('text-gold');

    const progressBar = wrapper.find('[role="progressbar"]');
    expect(progressBar.attributes('aria-valuenow')).toBe('3');

    expect(wrapper.find('[data-testid="rule-min-length"]').classes()).toContain('text-emerald-400');
    expect(wrapper.find('[data-testid="rule-mixed-case"]').classes()).toContain('text-emerald-400');
    expect(wrapper.find('[data-testid="rule-number"]').classes()).toContain('text-emerald-400');
    expect(wrapper.find('[data-testid="rule-special"]').classes()).toContain('text-theme-muted/70');
  });

  it('evaluates a strong password with all criteria satisfied', () => {
    const wrapper = mount(PasswordStrengthMeter, {
      props: { password: 'P@ssw0rd2026!' },
    });

    const label = wrapper.find('[data-testid="strength-label"]');
    expect(label.text()).toBe('Strong');
    expect(label.classes()).toContain('text-emerald-400');

    const progressBar = wrapper.find('[role="progressbar"]');
    expect(progressBar.attributes('aria-valuenow')).toBe('4');

    expect(wrapper.find('[data-testid="rule-min-length"]').classes()).toContain('text-emerald-400');
    expect(wrapper.find('[data-testid="rule-mixed-case"]').classes()).toContain('text-emerald-400');
    expect(wrapper.find('[data-testid="rule-number"]').classes()).toContain('text-emerald-400');
    expect(wrapper.find('[data-testid="rule-special"]').classes()).toContain('text-emerald-400');
  });
});
