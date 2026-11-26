import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { registerRouteGuards } from './core/guards/routeGuards';
import { syncThemeForRoute } from './core/composables/useTheme';
import { useAuthStore } from './modules/auth/store/auth.store';
import { recordClientError, addBreadcrumb } from './core/api/errorReporter';
import { useNavigationProgress } from './core/composables/useNavigationProgress';
import { initClickFeedback } from './core/utils/clickFeedback';
import './assets/css/main.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
registerRouteGuards(router);

// Initialize instant click & tap feedback
initClickFeedback();

const { start: startNavProgress, finish: finishNavProgress, fail: failNavProgress } = useNavigationProgress();

router.beforeEach((to, from, next) => {
  if (to.fullPath !== from.fullPath) {
    startNavProgress();
  }
  next();
});

// Global Error Handlers (Workstream E4)
app.config.errorHandler = (err, instance, info) => {
  console.error('[Vue Error]', err, info);
  recordClientError(err, {
    level: 'error',
    category: 'vue',
    componentInfo: info,
  });
};

if (typeof window !== 'undefined') {
  window.onerror = (message, source, lineno, colno, error) => {
    recordClientError(error || new Error(String(message)), {
      level: 'error',
      category: 'window',
      source: `${source}:${lineno}:${colno}`,
    });
  };

  window.onunhandledrejection = (event) => {
    const reason = event.reason;
    // Don't report if reason is already a handled ApiError that was rejected
    if (reason && reason.name !== 'ApiError') {
      recordClientError(reason instanceof Error ? reason : new Error(String(reason)), {
        level: 'error',
        category: 'unhandledrejection',
      });
    }
  };

  // Auto-reload on Vite chunk / CSS preload failure caused by asset rebuilds
  window.addEventListener('vite:preloadError', (event) => {
    event.preventDefault();
    const lastReload = sessionStorage.getItem('chunk_reload');
    const now = Date.now();
    if (!lastReload || now - Number(lastReload) > 8000) {
      sessionStorage.setItem('chunk_reload', String(now));
      window.location.reload();
    }
  });
}

router.onError((error) => {
  failNavProgress();
  console.error('[Router Error]', error);
  recordClientError(error, {
    level: 'error',
    category: 'router',
  });

  const isPreloadError = /Failed to fetch dynamically imported module|Unable to preload CSS|Importing a module script failed/i.test(error?.message);
  if (isPreloadError && typeof window !== 'undefined') {
    const lastReload = sessionStorage.getItem('chunk_reload');
    const now = Date.now();
    if (!lastReload || now - Number(lastReload) > 8000) {
      sessionStorage.setItem('chunk_reload', String(now));
      window.location.reload();
    }
  }
});

router.afterEach((to) => {
  finishNavProgress();
  syncThemeForRoute(to);
  addBreadcrumb({
    type: 'navigation',
    category: 'router',
    message: `Navigated to ${to.fullPath}`,
    data: { to: to.fullPath, name: to.name },
  });
});

// Mount the app immediately so public marketing and catalogue routes render with zero delay
app.mount('#app');

// Register service worker for instant Back/Refresh media and catalogue caching
if (typeof window !== 'undefined' && 'serviceWorker' in navigator && !import.meta.env.VITEST) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
  });
}

// Boot auth asynchronously in the background
const auth = useAuthStore();
auth.bootstrap();
