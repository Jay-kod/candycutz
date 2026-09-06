import { create } from 'zustand';
import { barberTokenStorage, staffAuthApi } from '../api/client';
import { BarberProfile, ChairStatus } from '../types';

interface BarberAuthState {
  barber: BarberProfile | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;

  initializeAuth: () => Promise<void>;
  login: (identity: string, password: string) => Promise<boolean>;
  logout: () => Promise<void>;
  setChairStatus: (status: ChairStatus) => Promise<void>;
  refreshProfile: () => Promise<void>;
}

export const useBarberAuthStore = create<BarberAuthState>((set, get) => ({
  barber: null,
  token: null,
  isAuthenticated: false,
  isLoading: true,
  error: null,

  initializeAuth: async () => {
    set({ isLoading: true });
    try {
      const token = await barberTokenStorage.get();
      if (!token) {
        set({ barber: null, token: null, isAuthenticated: false, isLoading: false });
        return;
      }

      const barber = await staffAuthApi.me();
      set({ barber, token, isAuthenticated: true, isLoading: false, error: null });
    } catch (e) {
      await barberTokenStorage.remove();
      set({ barber: null, token: null, isAuthenticated: false, isLoading: false });
    }
  },

  login: async (identity, password) => {
    set({ isLoading: true, error: null });
    try {
      const res = await staffAuthApi.login(identity, password);
      set({
        barber: res.barber,
        token: res.token,
        isAuthenticated: true,
        isLoading: false,
        error: null,
      });
      return true;
    } catch (e: any) {
      const msg = e.response?.data?.message || 'Staff login failed. Please verify credentials.';
      set({ isLoading: false, error: msg });
      return false;
    }
  },

  logout: async () => {
    try {
      await staffAuthApi.logout();
    } catch (e) {
    } finally {
      set({ barber: null, token: null, isAuthenticated: false, isLoading: false, error: null });
    }
  },

  setChairStatus: async (status: ChairStatus) => {
    try {
      const updated = await staffAuthApi.updateChairStatus(status);
      set((state) => ({
        barber: state.barber ? { ...state.barber, chair_status: status } : null,
      }));
    } catch (e) {
      // Optimistic fallback
      set((state) => ({
        barber: state.barber ? { ...state.barber, chair_status: status } : null,
      }));
    }
  },

  refreshProfile: async () => {
    try {
      const barber = await staffAuthApi.me();
      set({ barber });
    } catch (e) {}
  },
}));
