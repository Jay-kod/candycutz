import { create } from 'zustand';
import { authApi, barbersApi, tokenStorage } from '../api/client';
import { BarberProfile, ChairStatus, User } from '../types';

interface AuthState {
  user: User | null;
  barber: BarberProfile | null;
  token: string | null;
  isAuthenticated: boolean;
  isBarber: boolean;
  isLoading: boolean;
  error: string | null;
  viewMode: 'customer' | 'barber';

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
  setChairStatus: (status: ChairStatus) => Promise<void>;
  setViewMode: (mode: 'customer' | 'barber') => void;
  refreshProfile: () => Promise<void>;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  barber: null,
  token: null,
  isAuthenticated: false,
  isBarber: false,
  isLoading: true,
  error: null,
  viewMode: 'customer',

  initializeAuth: async () => {
    set({ isLoading: true });
    try {
      const token = await tokenStorage.get();
      if (!token) {
        set({ user: null, barber: null, token: null, isAuthenticated: false, isBarber: false, isLoading: false, viewMode: 'customer' });
        return;
      }

      const user = await authApi.me();
      const isBarber = user.role === 'barber' || user.role === 'admin' || user.role === 'super_admin';
      const barberProfile: BarberProfile | null = isBarber && (user as any).barber ? (user as any).barber : null;

      set({
        user,
        barber: barberProfile,
        token,
        isAuthenticated: true,
        isBarber,
        isLoading: false,
        error: null,
        viewMode: isBarber ? 'barber' : 'customer',
      });
    } catch (e) {
      await tokenStorage.remove();
      set({ user: null, barber: null, token: null, isAuthenticated: false, isBarber: false, isLoading: false, viewMode: 'customer' });
    }
  },

  login: async (identity, password) => {
    set({ isLoading: true, error: null });
    try {
      const res = await authApi.login(identity, password);
      const isBarber = res.user.role === 'barber' || res.user.role === 'admin' || res.user.role === 'super_admin';
      const barberProfile: BarberProfile | null = isBarber && (res.user as any).barber ? (res.user as any).barber : null;

      set({
        user: res.user,
        barber: barberProfile,
        token: res.token,
        isAuthenticated: true,
        isBarber,
        isLoading: false,
        error: null,
        viewMode: isBarber ? 'barber' : 'customer',
      });
      return true;
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Login failed. Please check your credentials.';
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
        barber: null,
        token: res.token,
        isAuthenticated: true,
        isBarber: false,
        isLoading: false,
        error: null,
        viewMode: 'customer',
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
      set({
        user: null,
        barber: null,
        token: null,
        isAuthenticated: false,
        isBarber: false,
        isLoading: false,
        error: null,
        viewMode: 'customer',
      });
    }
  },

  setChairStatus: async (status: ChairStatus) => {
    try {
      await barbersApi.updateChairStatus(status);
      set((state) => ({
        barber: state.barber ? { ...state.barber, chair_status: status } : null,
      }));
    } catch (e) {
      // Optimistic update
      set((state) => ({
        barber: state.barber ? { ...state.barber, chair_status: status } : null,
      }));
    }
  },

  setViewMode: (mode: 'customer' | 'barber') => {
    set({ viewMode: mode });
  },

  refreshProfile: async () => {
    try {
      const user = await authApi.me();
      const isBarber = user.role === 'barber' || user.role === 'admin' || user.role === 'super_admin';
      const barberProfile: BarberProfile | null = isBarber && (user as any).barber ? (user as any).barber : null;
      set({ user, barber: barberProfile, isBarber });
    } catch (e) {}
  },
}));
