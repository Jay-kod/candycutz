import { create } from 'zustand';
import { authApi, barbersApi, tokenStorage } from '../api/client';
import { getStorageUrl } from '../constants/config';
import { biometricService } from '../services/biometricService';
import { BarberProfile, ChairStatus, User } from '../types';
import { resolveAuthErrorMessage } from '../utils/authErrorMessage';

const normalizeProfileMedia = <T extends Record<string, any> | null>(payload: T): T => {
  if (!payload) {
    return payload;
  }

  const normalized = { ...payload } as Record<string, any>;
  if (typeof normalized.avatar === 'string') {
    normalized.avatar = getStorageUrl(normalized.avatar);
  }
  if (typeof normalized.cover_image === 'string') {
    normalized.cover_image = getStorageUrl(normalized.cover_image);
  }
  if (typeof normalized.avatar_url === 'string') {
    normalized.avatar_url = getStorageUrl(normalized.avatar_url);
  }
  if (typeof normalized.cover_image_url === 'string') {
    normalized.cover_image_url = getStorageUrl(normalized.cover_image_url);
  }

  return normalized as T;
};

const normalizeAccountProfile = (account: { user?: User; barber?: BarberProfile | null } | null) => {
  if (!account) {
    return account;
  }

  return {
    ...account,
    user: account.user ? normalizeProfileMedia(account.user) : account.user,
    barber: account.barber ? normalizeProfileMedia(account.barber) : account.barber,
  };
};

interface AuthState {
  user: User | null;
  barber: BarberProfile | null;
  token: string | null;
  isAuthenticated: boolean;
  isBarber: boolean;
  isLoading: boolean;
  isLoggingOut: boolean;
  error: string | null;
  viewMode: 'customer' | 'barber';

  initializeAuth: () => Promise<void>;
  login: (identity: string, password: string, rememberMe?: boolean) => Promise<boolean>;
  biometricLogin: (role?: 'customer' | 'barber') => Promise<boolean>;
  socialLogin: (provider: 'google' | 'apple', idToken: string) => Promise<boolean>;
  register: (payload: {
    name: string;
    username: string;
    email: string;
    phone: string;
    password: string;
    password_confirmation: string;
  }) => Promise<boolean>;
  logout: () => Promise<void>;
  finishLogout: () => void;
  setChairStatus: (status: ChairStatus) => Promise<void>;
  setViewMode: (mode: 'customer' | 'barber') => void;
  refreshProfile: () => Promise<boolean>;
}

export const useAuthStore = create<AuthState>((set, get) => ({
  user: null,
  barber: null,
  token: null,
  isAuthenticated: false,
  isBarber: false,
  isLoading: true,
  isLoggingOut: false,
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

      const user = normalizeProfileMedia(await authApi.me());
      const isBarber = user.role === 'barber';
      let account: { user?: User; barber?: BarberProfile | null } | null = null;
      if (isBarber) {
        try {
          account = normalizeAccountProfile(await barbersApi.getAccount());
        } catch {
          // Keep the authenticated user available while barber data retries in the screen.
        }
      }
      const barberProfile: BarberProfile | null = account?.barber || (isBarber ? normalizeProfileMedia((user as any).barber) : null);

      set({
        user: normalizeProfileMedia(account?.user || user),
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

  login: async (identity, password, rememberMe = false) => {
    set({ isLoading: true, error: null });
    try {
      const res = await authApi.login(identity, password, rememberMe);
      const normalizedUser = normalizeProfileMedia(res.user);
      const isBarber = normalizedUser.role === 'barber';
      let account: { user?: User; barber?: BarberProfile | null } | null = null;
      if (isBarber) {
        try {
          account = normalizeAccountProfile(await barbersApi.getAccount());
        } catch {
          // Keep login successful; profile hydration can retry after navigation.
        }
      }
      const barberProfile: BarberProfile | null = account?.barber || (isBarber ? normalizeProfileMedia((normalizedUser as any).barber || null) : null);

      set({
        user: normalizeProfileMedia(account?.user || normalizedUser),
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
      const msg = resolveAuthErrorMessage(e, 'Login failed. Please check your credentials.');
      set({ isLoading: false, error: msg });
      return false;
    }
  },

  biometricLogin: async (role?: 'customer' | 'barber') => {
    set({ isLoading: true, error: null });
    try {
      const authResult = await biometricService.authenticate(role);
      if (!authResult.success || !authResult.token) {
        set({ isLoading: false, error: authResult.message || 'Biometric authentication cancelled.' });
        return false;
      }

      await tokenStorage.set(authResult.token, true);
      const user = normalizeProfileMedia(await authApi.me());
      const isBarber = user.role === 'barber';
      let account: { user?: User; barber?: BarberProfile | null } | null = null;
      if (isBarber) {
        try {
          account = normalizeAccountProfile(await barbersApi.getAccount());
        } catch {
          // Keep authentication successful while barber data retries later.
        }
      }

      set({
        user: normalizeProfileMedia(account?.user || user),
        barber: normalizeProfileMedia(account?.barber || (isBarber ? (user as any).barber || null : null)),
        token: authResult.token,
        isAuthenticated: true,
        isBarber,
        isLoading: false,
        error: null,
        viewMode: isBarber ? 'barber' : 'customer',
      });
      return true;
    } catch (e: any) {
      const msg = resolveAuthErrorMessage(
        e,
        'Biometric login session expired. Please sign in with your password to reconnect.'
      );
      set({ isLoading: false, error: msg });
      return false;
    }
  },

  socialLogin: async (provider, idToken) => {
    set({ isLoading: true, error: null });
    try {
      const res = await authApi.socialLogin({ provider, id_token: idToken, role: 'customer' });
      const normalizedUser = normalizeProfileMedia(res.user);
      const isBarber = normalizedUser.role === 'barber';
      let account: { user?: User; barber?: BarberProfile | null } | null = null;
      if (isBarber) {
        try {
          account = normalizeAccountProfile(await barbersApi.getAccount());
        } catch {
          // Keep authentication successful while barber data retries later.
        }
      }
      set({
        user: normalizeProfileMedia(account?.user || normalizedUser),
        barber: normalizeProfileMedia(account?.barber || (isBarber ? (normalizedUser as any).barber || null : null)),
        token: res.token,
        isAuthenticated: true,
        isBarber,
        isLoading: false,
        error: null,
        viewMode: isBarber ? 'barber' : 'customer',
      });
      return true;
    } catch (e: any) {
      set({ isLoading: false, error: resolveAuthErrorMessage(e, 'Google sign-in failed. Please try again.') });
      return false;
    }
  },

  register: async (payload) => {
    set({ isLoading: true, error: null });
    try {
      const res = await authApi.register(payload);
      set({
        user: normalizeProfileMedia(res.user),
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
      const msg = resolveAuthErrorMessage(e, 'Registration failed. Please check your information.');
      set({ isLoading: false, error: msg });
      return false;
    }
  },

  logout: async () => {
    set({ isLoggingOut: true });
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

  finishLogout: () => {
    set({ isLoggingOut: false });
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
      const user = normalizeProfileMedia(await authApi.me());
      const isBarber = user.role === 'barber';
      const account = isBarber ? normalizeAccountProfile(await barbersApi.getAccount()) : null;
      const barberProfile: BarberProfile | null = account?.barber || (isBarber ? normalizeProfileMedia((user as any).barber || null) : null);
      set({ user: normalizeProfileMedia(account?.user || user), barber: barberProfile, isBarber });
      return true;
    } catch (e) {}
    return false;
  },
}));
