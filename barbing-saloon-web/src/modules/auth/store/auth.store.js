import { defineStore } from 'pinia';
import { authApi } from '@/shared/api/old_authApi';

function getStoredToken() {
  try {
    return typeof localStorage !== 'undefined' ? localStorage.getItem('candycutz_auth_token') : null;
  } catch (e) {
    return null;
  }
}

function getStoredCachedUser() {
  try {
    if (typeof localStorage === 'undefined') return null;
    const raw = localStorage.getItem('candycutz_cached_user');
    return raw ? JSON.parse(raw) : null;
  } catch (e) {
    return null;
  }
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: getStoredCachedUser(),
    token: getStoredToken(),
    status: 'booting', // 'booting' | 'guest' | 'authenticated' | 'degraded' | 'expired'
    intendedRoute: null,
    _readyPromise: null,
    _readyResolve: null,
  }),

  actions: {
    get ready() {
      if (this.status !== 'booting') {
        return Promise.resolve();
      }
      if (!this._readyPromise) {
        this.bootstrap();
      }
      return this._readyPromise;
    },

    setUser(user) {
      this.user = user;
      if (user && typeof localStorage !== 'undefined') {
        try {
          const minimal = {
            id: user.id,
            name: user.name,
            role: user.role,
            email: user.email,
          };
          localStorage.setItem('candycutz_cached_user', JSON.stringify(minimal));
        } catch (e) {}
      }
    },

    setToken(token) {
      this.token = token;
      if (typeof localStorage === 'undefined') return;

      if (token) {
        localStorage.setItem('candycutz_auth_token', token);
      } else {
        localStorage.removeItem('candycutz_auth_token');
      }
    },

    async bootstrap() {
      if (this._readyPromise) {
        return this._readyPromise;
      }

      this._readyPromise = new Promise((resolve) => {
        this._readyResolve = resolve;
      });

      if (!this.token) {
        this.status = 'guest';
        this.user = null;
        if (this._readyResolve) this._readyResolve();
        return;
      }

      try {
        const response = await authApi.me();
        const userData = response.data?.data?.data ?? response.data?.data ?? response.data;
        this.setUser(userData);
        this.status = 'authenticated';
      } catch (err) {
        // If server explicitly commanded a logout (e.g. token expired, revoked, or account suspended)
        if (
          err?.action === 'logout' ||
          err?.code?.startsWith('AUTH_TOKEN_') ||
          err?.code === 'AUTH_ACCOUNT_SUSPENDED'
        ) {
          this.clearAuth('expired');
        } else {
          // Network, timeout, or 5xx: preserve session in degraded mode!
          this.status = 'degraded';
        }
      } finally {
        if (this._readyResolve) this._readyResolve();
      }

      return this._readyPromise;
    },

    async login(credentials) {
      const response = await authApi.login(credentials);
      const payload = response.data?.data ?? response.data;

      this.setToken(payload.token);
      this.setUser(payload.user?.data ?? payload.user);
      this.status = 'authenticated';

      return payload;
    },

    async socialLogin(credentials) {
      const response = await authApi.socialLogin(credentials);
      const payload = response.data?.data ?? response.data;

      this.setToken(payload.token);
      this.setUser(payload.user?.data ?? payload.user);
      this.status = 'authenticated';

      return payload;
    },

    async register(data) {
      await authApi.register(data);

      return this.login({
        email: data.email,
        password: data.password,
      });
    },

    async logout() {
      try {
        await authApi.logout();
      } finally {
        this.clearAuth();
      }
    },

    async fetchUser() {
      if (!this.token) {
        this.clearAuth();
        return null;
      }

      try {
        const response = await authApi.me();
        const userData = response.data?.data?.data ?? response.data?.data ?? response.data;
        this.setUser(userData);
        this.status = 'authenticated';
        return this.user;
      } catch (err) {
        // Only clear authentication if explicitly commanded to logout
        if (
          err?.action === 'logout' ||
          err?.code?.startsWith('AUTH_TOKEN_') ||
          err?.code === 'AUTH_ACCOUNT_SUSPENDED'
        ) {
          this.clearAuth('expired');
          return null;
        }

        // On network/5xx/timeout keep token and cached user in degraded mode
        this.status = 'degraded';
        return this.user;
      }
    },

    clearAuth(newStatus = 'guest') {
      this.user = null;
      this.token = null;
      this.status = newStatus;
      this.intendedRoute = null;
      if (typeof localStorage !== 'undefined') {
        localStorage.removeItem('candycutz_auth_token');
        localStorage.removeItem('candycutz_cached_user');
      }
    },

    setIntendedRoute(route) {
      this.intendedRoute = route;
    },

    redirectAfterLogin(preferredRoute = null) {
      const role = this.user?.role?.value ?? this.user?.role;

      if (this.intendedRoute) {
        const intended = this.intendedRoute;
        this.intendedRoute = null;

        const isSuperAdminRoute = intended.startsWith('/superadmin');
        const isAdminRoute = intended.startsWith('/admin');
        const isBarberRoute = intended.startsWith('/barber');

        if (role === 'super_admin' && (isSuperAdminRoute || isAdminRoute)) {
          return intended;
        }
        if (role === 'admin' && isAdminRoute) {
          return intended;
        }
        if (role === 'barber' && isBarberRoute) {
          return intended;
        }
        if (role === 'customer' && !isAdminRoute && !isBarberRoute && !isSuperAdminRoute) {
          return intended;
        }
      }

      if (preferredRoute) {
        return preferredRoute;
      }

      if (role === 'super_admin') {
        return '/superadmin/dashboard';
      }
      if (role === 'admin') {
        return '/admin/dashboard';
      }
      if (role === 'barber') {
        return '/barber/dashboard';
      }

      return '/customer/dashboard';
    },
  },

  getters: {
    isAuthenticated: (state) =>
      state.status === 'authenticated' ||
      (state.status === 'degraded' && Boolean(state.user && state.token)),
    isBooting: (state) => state.status === 'booting',
    isDegraded: (state) => state.status === 'degraded',
    isGuest: (state) => state.status === 'guest',
    isCustomer: (state) => (state.user?.role?.value ?? state.user?.role) === 'customer',
    isBarber: (state) => (state.user?.role?.value ?? state.user?.role) === 'barber',
    isAdmin: (state) => (state.user?.role?.value ?? state.user?.role) === 'admin',
    isSuperAdmin: (state) => (state.user?.role?.value ?? state.user?.role) === 'super_admin',
  },
});