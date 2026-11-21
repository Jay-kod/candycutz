import { ref } from 'vue';

const STORAGE_KEY_GLOBAL = 'theme';
const STORAGE_KEY_VUEUSE = 'vueuse-color-scheme';
const STORAGE_KEY_WEBSITE = 'candycutz_website_theme';
const STORAGE_KEY_DASHBOARD = 'candycutz_dashboard_theme';

export const isDashboardPath = (path) => {
  if (!path || typeof path !== 'string') return false;
  // Auth routes are public website pages, not dashboards
  if (
    path.startsWith('/customer/login') ||
    path.startsWith('/customer/register') ||
    path.startsWith('/customer/forgot-password') ||
    path.startsWith('/customer/reset-password') ||
    path.startsWith('/customer/verify-email') ||
    path.startsWith('/barber/login') ||
    path.startsWith('/barber/register') ||
    path.startsWith('/admin/login') ||
    path.startsWith('/superadmin/login')
  ) {
    return false;
  }
  return (
    path.startsWith('/customer/dashboard') ||
    path.startsWith('/customer/barber') ||
    path.startsWith('/barber') ||
    path.startsWith('/admin') ||
    path.startsWith('/superadmin')
  );
};

export const detectInitialContext = () => {
  if (typeof window === 'undefined') return 'website';
  return isDashboardPath(window.location.pathname) ? 'dashboard' : 'website';
};

export const getStoredPreference = (context = 'website') => {
  if (typeof localStorage === 'undefined') {
    return context === 'dashboard' ? 'dark' : 'light';
  }
  try {
    const key = context === 'dashboard' ? STORAGE_KEY_DASHBOARD : STORAGE_KEY_WEBSITE;
    const val = localStorage.getItem(key);
    if (val === 'dark' || val === 'light' || val === 'system') return val;

    // Fallback to legacy theme key if specific key not yet set
    const legacy = localStorage.getItem(STORAGE_KEY_GLOBAL);
    if (legacy === 'dark' || legacy === 'light') return legacy;

    const vueuse = localStorage.getItem(STORAGE_KEY_VUEUSE);
    if (vueuse === 'dark' || vueuse === 'light') return vueuse;
  } catch (e) {
    void e;
  }
  return context === 'dashboard' ? 'dark' : 'light';
};

const initialContext = detectInitialContext();
export const activeContext = ref(initialContext);

const initialPref = getStoredPreference(initialContext);
export const themePreference = ref(initialPref);
export const isDark = ref(
  initialPref === 'dark' ||
  (initialPref === 'system' && typeof window !== 'undefined' && window.matchMedia?.('(prefers-color-scheme: dark)').matches)
);

export const applyThemeToDom = (dark) => {
  if (typeof document === 'undefined') return;
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
  const metaThemeColor = document.querySelector('meta[name="theme-color"]');
  if (metaThemeColor) {
    metaThemeColor.setAttribute('content', dark ? '#0D0D0D' : '#FAF9F6');
  }
};

const resolveIsDark = (pref) => {
  if (pref === 'system' || pref === 'auto') {
    return (
      typeof window !== 'undefined' &&
      window.matchMedia &&
      window.matchMedia('(prefers-color-scheme: dark)').matches
    );
  }
  return pref === 'dark';
};

export const syncContextTheme = () => {
  const pref = getStoredPreference(activeContext.value);
  themePreference.value = pref;
  const dark = resolveIsDark(pref);
  isDark.value = dark;
  applyThemeToDom(dark);

  try {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(STORAGE_KEY_GLOBAL, pref === 'system' ? 'system' : dark ? 'dark' : 'light');
      localStorage.setItem(STORAGE_KEY_VUEUSE, pref === 'system' ? 'auto' : dark ? 'dark' : 'light');
    }
  } catch (e) {
    void e;
  }
};

export const setThemeContext = (context) => {
  if (context !== 'website' && context !== 'dashboard') return;
  if (activeContext.value !== context) {
    activeContext.value = context;
  }
  syncContextTheme();
};

export const syncThemeForRoute = (routeOrPath) => {
  const path = typeof routeOrPath === 'string' ? routeOrPath : routeOrPath?.path || '';
  const isDash = isDashboardPath(path);
  setThemeContext(isDash ? 'dashboard' : 'website');
};

export const setTheme = (mode, targetContext = null) => {
  const ctx = (typeof targetContext === 'string' && (targetContext === 'website' || targetContext === 'dashboard'))
    ? targetContext
    : activeContext.value || 'website';
  const key = ctx === 'dashboard' ? STORAGE_KEY_DASHBOARD : STORAGE_KEY_WEBSITE;

  let normalized = mode;
  if (mode === 'auto') normalized = 'system';
  if (!['dark', 'light', 'system'].includes(normalized)) normalized = 'light';

  try {
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem(key, normalized);
    }
  } catch (e) {
    void e;
  }

  // If setting theme for current active context, update reactive values & DOM
  if (ctx === activeContext.value) {
    themePreference.value = normalized;
    const dark = resolveIsDark(normalized);
    isDark.value = dark;
    applyThemeToDom(dark);

    try {
      if (typeof localStorage !== 'undefined') {
        localStorage.setItem(STORAGE_KEY_GLOBAL, normalized);
        localStorage.setItem(STORAGE_KEY_VUEUSE, normalized === 'system' ? 'auto' : normalized);
      }
    } catch (e) {
      void e;
    }
  }
};

export const toggleTheme = (targetContext = null) => {
  const ctx = (typeof targetContext === 'string' && (targetContext === 'website' || targetContext === 'dashboard'))
    ? targetContext
    : activeContext.value || 'website';
  const currentPref = ctx === activeContext.value ? isDark.value : (getStoredPreference(ctx) === 'dark');
  if (currentPref) {
    setTheme('light', ctx);
  } else {
    setTheme('dark', ctx);
  }
};

// Listen to storage changes across tabs to keep theme in sync
if (typeof window !== 'undefined') {
  window.addEventListener('storage', (e) => {
    if (
      e.key === STORAGE_KEY_GLOBAL ||
      e.key === STORAGE_KEY_VUEUSE ||
      e.key === STORAGE_KEY_WEBSITE ||
      e.key === STORAGE_KEY_DASHBOARD
    ) {
      syncContextTheme();
    }
  });

  // Apply initial theme to DOM immediately on load
  syncContextTheme();
}

export function useTheme() {
  return {
    isDark,
    themePreference,
    activeContext,
    toggleTheme,
    setTheme,
    setThemeContext,
    syncThemeForRoute,
    applyThemeToDom,
  };
}
