import { useDark, useToggle } from '@vueuse/core';

export const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
  initialValue: 'dark',
  storageKey: 'vueuse-color-scheme',
  onChanged(dark) {
    if (typeof document !== 'undefined') {
      const el = document.documentElement;
      const theme = dark ? 'dark' : 'light';
      el.setAttribute('data-theme', theme);
      if (dark) {
        el.classList.add('dark');
        el.classList.remove('light');
      } else {
        el.classList.remove('dark');
        el.classList.add('light');
      }
      try {
        localStorage.setItem('theme', theme);
      } catch (e) {
        // Ignore localStorage access restrictions
      }
    }
  },
});

export const toggleTheme = useToggle(isDark);

export function useTheme() {
  return {
    isDark,
    toggleTheme,
  };
}
