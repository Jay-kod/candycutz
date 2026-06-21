import { defineStore } from 'pinia';
import { authApi } from '../api/auth.api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('candycutz_auth_token') || null,
    isAuthenticated: false,
    intendedRoute: null,
  }),
  actions: {
    setUser(user) {
      this.user = user;
      this.isAuthenticated = Boolean(user);
    },

    setToken(token) {
      this.token = token;

      if (token) {
        localStorage.setItem('candycutz_auth_token', token);
        return;
      }

      localStorage.removeItem('candycutz_auth_token');
    },

    async login(credentials) {
      const response = await authApi.login(credentials);
      const payload = response.data.data;

      this.setToken(payload.token);
      this.setUser(payload.user.data ?? payload.user);

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
      if (! this.token) {
        this.clearAuth();
        return null;
      }

      try {
        const response = await authApi.me();
        this.setUser(response.data.data.data ?? response.data.data);
        return this.user;
      } catch (error) {
        this.clearAuth();
        return null;
      }
    },

    clearAuth() {
      this.user = null;
      this.token = null;
      this.isAuthenticated = false;
      this.intendedRoute = null;
      localStorage.removeItem('candycutz_auth_token');
    },

    setIntendedRoute(route) {
      this.intendedRoute = route;
    },

    redirectAfterLogin() {
      if (this.intendedRoute) {
        const intendedRoute = this.intendedRoute;
        this.intendedRoute = null;
        return intendedRoute;
      }

      const role = this.user?.role ?? this.user?.role?.value;

      if (role === 'barber') {
        return '/barber/dashboard';
      }

      if (role === 'admin') {
        return '/admin/dashboard';
      }

      if (role === 'super_admin') {
        return '/admin/dashboard';
      }

      return '/customer/dashboard';
    },
  },
  getters: {
    isCustomer: (state) => (state.user?.role?.value ?? state.user?.role) === 'customer',
    isBarber: (state) => (state.user?.role?.value ?? state.user?.role) === 'barber',
    isAdmin: (state) => (state.user?.role?.value ?? state.user?.role) === 'admin',
    isSuperAdmin: (state) => (state.user?.role?.value ?? state.user?.role) === 'super_admin',
  },
});