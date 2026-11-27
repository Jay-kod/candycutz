import { useAuthStore } from '../../modules/auth/store/auth.store';
import { syncThemeForRoute } from '../composables/useTheme';

// Pages a customer with a pending consent decision may still reach: the gate
// itself plus the legal documents they are being asked to accept.
const CONSENT_EXEMPT_ROUTES = new Set([
  'consent',
  'terms',
  'privacy',
  'legal',
  'security',
  'cookie-preferences',
  'login',
  'register',
  'admin-login',
  'superadmin-login',
  'barber-login',
  'forgot-password',
  'reset-password',
  'verify-email',
  'forbidden',
  'server-error',
  'offline',
  'not-found',
]);

function matchesRole(routeRoles, userRole) {
  if (!routeRoles || routeRoles.length === 0) {
    return true;
  }

  if (userRole === 'super_admin' && (routeRoles.includes('admin') || routeRoles.includes('super_admin'))) {
    return true;
  }

  return routeRoles.includes(userRole);
}

function userRole(auth) {
  return auth.user?.role?.value ?? auth.user?.role;
}

function needsConsent(auth) {
  return userRole(auth) === 'customer' && Boolean(auth.user?.consent_required);
}

function consentRedirect(to) {
  return {
    name: 'consent',
    query: to.fullPath && to.fullPath !== '/' ? { redirect: to.fullPath } : {},
  };
}

export function registerRouteGuards(router) {
  // Any API call rejected with CONSENT_REQUIRED opens the blocking screen,
  // even if the customer stayed on the same page.
  if (typeof window !== 'undefined') {
    window.addEventListener('candycutz:consent-required', () => {
      const current = router.currentRoute.value;
      if (current.name === 'consent') {
        return;
      }
      router.push(consentRedirect(current)).catch(() => {});
    });
  }

  router.beforeEach(async (to) => {
    syncThemeForRoute(to);
    const auth = useAuthStore();
    const routeRoles = to.meta.roles || [];
    const requiresAuth = Boolean(to.meta.requiresAuth);
    const isPublic = Boolean(to.meta.public);

    // Public routes mount immediately without waiting for auth resolution.
    // The cached user still carries the consent flag, so a signed-in customer
    // with a pending decision is redirected without a blocking request.
    if (isPublic) {
      if (auth.status !== 'booting' && needsConsent(auth) && !CONSENT_EXEMPT_ROUTES.has(to.name)) {
        return consentRedirect(to);
      }
      return true;
    }

    if (requiresAuth) {
      // Resolve the saved session before deciding whether to redirect to login.
      if (auth.status === 'booting') {
        await auth.bootstrap();
      }

      if (!auth.isAuthenticated) {
        auth.setIntendedRoute(to.fullPath);
        if (to.path.startsWith('/superadmin')) {
          return '/superadmin/login';
        }
        if (routeRoles.includes('admin') || routeRoles.includes('super_admin')) {
          return '/admin/login';
        }
        if (routeRoles.includes('barber')) {
          return '/barber/login';
        }
        return '/customer/login';
      }
    }

    if (needsConsent(auth) && !to.meta.skipConsent && !CONSENT_EXEMPT_ROUTES.has(to.name)) {
      return consentRedirect(to);
    }

    if (routeRoles.length > 0) {
      const currentRole = userRole(auth);

      if (!matchesRole(routeRoles, currentRole)) {
        return '/403';
      }
    }

    return true;
  });
}
