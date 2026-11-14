import { describe, it, expect, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import ThemeToggleSwitch from '../ThemeToggleSwitch.vue';
import { setTheme, isDark, themePreference } from '@/core/composables/useTheme';

describe('ThemeToggleSwitch', () => {
  beforeEach(() => {
    localStorage.clear();
    setTheme('light');
  });

  it('renders single switch with LIGHT and DARK states, and exactly one button', () => {
    const wrapper = mount(ThemeToggleSwitch);
    expect(wrapper.text()).toContain('LIGHT');
    expect(wrapper.text()).toContain('DARK');
    // Exactly ONE button is rendered: the toggle switch
    const buttons = wrapper.findAll('button');
    expect(buttons.length).toBe(1);
    expect(buttons[0].attributes('role')).toBe('switch');
    expect(buttons[0].attributes('aria-checked')).toBe('false');
  });

  it('toggles theme between light and dark when switch button is clicked', async () => {
    const wrapper = mount(ThemeToggleSwitch);
    const switchBtn = wrapper.find('button[role="switch"]');

    expect(isDark.value).toBe(false);

    await switchBtn.trigger('click');
    expect(isDark.value).toBe(true);
    expect(themePreference.value).toBe('dark');
    expect(switchBtn.attributes('aria-checked')).toBe('true');
    expect(localStorage.getItem('theme')).toBe('dark');

    await switchBtn.trigger('click');
    expect(isDark.value).toBe(false);
    expect(themePreference.value).toBe('light');
    expect(switchBtn.attributes('aria-checked')).toBe('false');
    expect(localStorage.getItem('theme')).toBe('light');
  });

  it('persists light mode and updates root element attributes properly', async () => {
    const wrapper = mount(ThemeToggleSwitch);
    const switchBtn = wrapper.find('button[role="switch"]');

    // Start dark
    setTheme('dark');
    expect(isDark.value).toBe(true);

    // Toggle to light
    await switchBtn.trigger('click');
    expect(isDark.value).toBe(false);
    expect(document.documentElement.getAttribute('data-theme')).toBe('light');
    expect(document.documentElement.classList.contains('light')).toBe(true);
    expect(document.documentElement.classList.contains('dark')).toBe(false);
    expect(localStorage.getItem('theme')).toBe('light');
    expect(localStorage.getItem('vueuse-color-scheme')).toBe('light');
  });
});
