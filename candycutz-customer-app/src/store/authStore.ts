import { create } from 'zustand';
import { authApi, tokenStorage } from '../api/client';
import { User } from '../types';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;

  initializeAuth: () => Promise<void>;
  login: (identity: string, password: string) => Promise<boolean>;
  register: (payload: {
    name: string;
    username: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
  }) => Promise<boolean>;
  logout: () => Promise<void>;
  refreshProfile: () => Promise<void>;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  token: null,
  isAuthenticated: false,
  isLoading: true,
  error: null,

  initializeAuth: async () => {
    set({ isLoading: true });
    try {
      const token = await tokenStorage.get();
      if (!token) {
        set({ user: null, token: null, isAuthenticated: false, isLoading: false });
        return;
      }

      const user = await authApi.me();
      set({ user, token, isAuthenticated: true, isLoading: false, error: null });
    } catch (e) {
      await tokenStorage.remove();
      set({ user: null, token: null, isAuthenticated: false, isLoading: false });
    }
  },

  login: async (identity, password) => {
    set({ isLoading: true, error: null });
    try {
      const res = await authApi.login(identity, password);
      set({
        user: res.user,
        token: res.token,
        isAuthenticated: true,
        isLoading: false,
        error: null,
      });
      return true;
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Login failed. Please verify your credentials.';
      set({ isLoading: false, error: msg });
      return false;
    }
  },

  register: async (payload) => {
    set({ isLoading: true, error: null });
    try {
      const res = await authApi.register(payload);
      set({
        user: res.user,
        token: res.token,
        isAuthenticated: true,
        isLoading: false,
        error: null,
      });
      return true;
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Registration failed. Please check your information.';
      set({ isLoading: false, error: msg });
      return false;
    }
  },

  logout: async () => {
    try {
      await authApi.logout();
    } catch (e) {
    } finally {
      set({ user: null, token: null, isAuthenticated: false, isLoading: false, error: null });
    }
  },

  refreshProfile: async () => {
    try {
      const user = await authApi.me();
      set({ user });
    } catch (e) {}
  },
}));
