import { create } from 'zustand';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

export type ThemePreference = 'system' | 'dark' | 'light';

const THEME_PREFERENCE_KEY = 'candycutz_theme_preference';

interface ThemeState {
  themePreference: ThemePreference;
  isInitialized: boolean;
  setThemePreference: (preference: ThemePreference) => Promise<void>;
  initializeTheme: () => Promise<void>;
}

export const useThemeStore = create<ThemeState>((set) => ({
  themePreference: 'system',
  isInitialized: false,

  initializeTheme: async () => {
    try {
      let saved: string | null = null;
      if (Platform.OS === 'web') {
        if (typeof localStorage !== 'undefined') {
          saved = localStorage.getItem(THEME_PREFERENCE_KEY);
        }
      } else {
        saved = await SecureStore.getItemAsync(THEME_PREFERENCE_KEY);
      }

      if (saved === 'dark' || saved === 'light' || saved === 'system') {
        set({ themePreference: saved as ThemePreference, isInitialized: true });
      } else {
        set({ themePreference: 'system', isInitialized: true });
      }
    } catch {
      set({ themePreference: 'system', isInitialized: true });
    }
  },

  setThemePreference: async (preference: ThemePreference) => {
    set({ themePreference: preference });
    try {
      if (Platform.OS === 'web') {
        if (typeof localStorage !== 'undefined') {
          localStorage.setItem(THEME_PREFERENCE_KEY, preference);
        }
      } else {
        await SecureStore.setItemAsync(THEME_PREFERENCE_KEY, preference);
      }
    } catch {
      // Memory fallback holds the state
    }
  },
}));
