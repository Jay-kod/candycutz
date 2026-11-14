import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { registerRouteGuards } from './core/guards/routeGuards';
import { syncThemeForRoute } from './core/composables/useTheme';
import { useAuthStore } from './modules/auth/store/auth.store';
import { recordClientError, addBreadcrumb } from './core/api/errorReporter';
import './assets/css/main.css';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);
registerRouteGuards(router);

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

// Boot auth asynchronously in the background
const auth = useAuthStore();
auth.bootstrap();
