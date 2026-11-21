import { describe, it, expect, beforeEach } from 'vitest';
import {
  isDark,
  themePreference,
  toggleTheme,
  setTheme,
  setThemeContext,
  syncThemeForRoute,
  isDashboardPath,
  activeContext,
  useTheme,
} from '../useTheme';

describe('useTheme Composable', () => {
  beforeEach(() => {
    localStorage.clear();
    document.documentElement.className = '';
    document.documentElement.removeAttribute('data-theme');
    setThemeContext('website');
  });

  it('provides useTheme returning isDark, themePreference, toggleTheme, and setTheme', () => {
    const theme = useTheme();
    expect(theme.isDark).toBeDefined();
    expect(theme.themePreference).toBeDefined();
    expect(typeof theme.toggleTheme).toBe('function');
    expect(typeof theme.setTheme).toBe('function');
  });

  it('can set theme to light mode and updates root element', () => {
    setTheme('light');
    expect(themePreference.value).toBe('light');
    expect(isDark.value).toBe(false);
    expect(document.documentElement.getAttribute('data-theme')).toBe('light');
    expect(document.documentElement.classList.contains('light')).toBe(true);
    expect(document.documentElement.classList.contains('dark')).toBe(false);
    expect(localStorage.getItem('theme')).toBe('light');
  });

  it('can set theme to dark mode and updates root element', () => {
    setTheme('dark');
    expect(themePreference.value).toBe('dark');
    expect(isDark.value).toBe(true);
    expect(document.documentElement.getAttribute('data-theme')).toBe('dark');
    expect(document.documentElement.classList.contains('dark')).toBe(true);
    expect(document.documentElement.classList.contains('light')).toBe(false);
    expect(localStorage.getItem('theme')).toBe('dark');
  });

  it('toggles theme back and forth between light and dark', () => {
    setTheme('light');
    expect(isDark.value).toBe(false);

    toggleTheme();
    expect(isDark.value).toBe(true);
    expect(themePreference.value).toBe('dark');

    toggleTheme();
    expect(isDark.value).toBe(false);
    expect(themePreference.value).toBe('light');
  });

  it('supports system preference mode', () => {
    setTheme('system');
    expect(themePreference.value).toBe('system');
    expect(localStorage.getItem('theme')).toBe('system');
    expect(localStorage.getItem('vueuse-color-scheme')).toBe('auto');
  });

  it('correctly identifies dashboard vs website paths', () => {
    expect(isDashboardPath('/')).toBe(false);
    expect(isDashboardPath('/about')).toBe(false);
    expect(isDashboardPath('/services')).toBe(false);
    expect(isDashboardPath('/customer/login')).toBe(false);
    expect(isDashboardPath('/barber/login')).toBe(false);
    expect(isDashboardPath('/admin/login')).toBe(false);

    expect(isDashboardPath('/customer/dashboard')).toBe(true);
    expect(isDashboardPath('/customer/dashboard/bookings')).toBe(true);
    expect(isDashboardPath('/customer/barber/5')).toBe(true);
    expect(isDashboardPath('/barber/dashboard')).toBe(true);
    expect(isDashboardPath('/barber/appointments')).toBe(true);
    expect(isDashboardPath('/admin/dashboard')).toBe(true);
    expect(isDashboardPath('/admin/settings')).toBe(true);
    expect(isDashboardPath('/superadmin/dashboard')).toBe(true);
  });

  it('guarantees that setting website to light mode does not affect dashboard and vice versa', () => {
    // 1. User sets dashboard theme to dark
    syncThemeForRoute('/customer/dashboard');
    expect(activeContext.value).toBe('dashboard');
    setTheme('dark');
    expect(isDark.value).toBe(true);
    expect(localStorage.getItem('candycutz_dashboard_theme')).toBe('dark');

    // 2. User navigates to public website and sets website to light mode
    syncThemeForRoute('/');
    expect(activeContext.value).toBe('website');
    setTheme('light');
    expect(isDark.value).toBe(false);
    expect(document.documentElement.getAttribute('data-theme')).toBe('light');
    expect(localStorage.getItem('candycutz_website_theme')).toBe('light');

    // Dashboard preference is still dark
    expect(localStorage.getItem('candycutz_dashboard_theme')).toBe('dark');

    // 3. User navigates back to admin/barber/customer dashboard -> theme is dark, not light!
    syncThemeForRoute('/admin/dashboard');
    expect(activeContext.value).toBe('dashboard');
    expect(isDark.value).toBe(true);
    expect(document.documentElement.getAttribute('data-theme')).toBe('dark');

    // 4. In dashboard, user switches dashboard to light mode
    toggleTheme();
    expect(isDark.value).toBe(false);
    expect(document.documentElement.getAttribute('data-theme')).toBe('light');
    expect(localStorage.getItem('candycutz_dashboard_theme')).toBe('light');

    // 5. User navigates to website and switches website to dark mode
    syncThemeForRoute('/');
    expect(activeContext.value).toBe('website');
    setTheme('dark');
    expect(isDark.value).toBe(true);
    expect(document.documentElement.getAttribute('data-theme')).toBe('dark');

    // 6. User navigates back to dashboard -> dashboard is still in light mode!
    syncThemeForRoute('/barber/dashboard');
    expect(activeContext.value).toBe('dashboard');
    expect(isDark.value).toBe(false);
    expect(document.documentElement.getAttribute('data-theme')).toBe('light');
    expect(localStorage.getItem('candycutz_dashboard_theme')).toBe('light');
  });
});
