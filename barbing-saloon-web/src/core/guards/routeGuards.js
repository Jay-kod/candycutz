import { useAuthStore } from '../../modules/auth/store/auth.store';

function matchesRole(routeRoles, userRole) {
  if (! routeRoles || routeRoles.length === 0) {
    return true;
  }

  return routeRoles.includes(userRole);
}

export function registerRouteGuards(router) {
  router.beforeEach(async (to) => {
    const auth = useAuthStore();
    const routeRoles = to.meta.roles || [];
    const requiresAuth = Boolean(to.meta.requiresAuth);
    const isPublic = Boolean(to.meta.public);

    if (isPublic) {
      return true;
    }

    if (requiresAuth && ! auth.isAuthenticated) {
      auth.setIntendedRoute(to.fullPath);
      if (routeRoles.includes('admin') || routeRoles.includes('super_admin')) {
        return '/admin/login';
      }
      if (routeRoles.includes('barber')) {
        return '/barber/login';
      }
      return '/customer/login';
    }

    if (routeRoles.length > 0) {
      const currentRole = auth.user?.role?.value ?? auth.user?.role;

      if (! matchesRole(routeRoles, currentRole)) {
        return '/403';
      }
    }

    return true;
  });
}