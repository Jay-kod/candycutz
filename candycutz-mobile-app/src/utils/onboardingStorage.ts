import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

const ONBOARDING_KEY = 'candycutz_has_seen_onboarding';

// In-memory fallback
let memorySeen: boolean | null = null;
let handoffPending = false;

export const onboardingStorage = {
  beginHandoff: (): void => {
    handoffPending = true;
  },

  isHandoffPending: (): boolean => handoffPending,

  hasSeenOnboarding: async (): Promise<boolean> => {
    try {
      if (Platform.OS === 'web') {
        if (typeof localStorage !== 'undefined') {
          const val = localStorage.getItem(ONBOARDING_KEY);
          return val === 'true';
        }
        return memorySeen === true;
      }
      const val = await SecureStore.getItemAsync(ONBOARDING_KEY);
      return val === 'true';
    } catch (e) {
      return memorySeen === true;
    }
  },

  setHasSeenOnboarding: async (seen: boolean = true): Promise<void> => {
    const val = seen ? 'true' : 'false';
    memorySeen = seen;
    try {
      if (Platform.OS === 'web') {
        if (typeof localStorage !== 'undefined') {
          localStorage.setItem(ONBOARDING_KEY, val);
        }
        return;
      }
      await SecureStore.setItemAsync(ONBOARDING_KEY, val);
    } catch (e) {
      // Memory fallback holds the state
    }
  },

  resetOnboarding: async (): Promise<void> => {
    memorySeen = false;
    handoffPending = false;
    try {
      if (Platform.OS === 'web') {
        if (typeof localStorage !== 'undefined') {
          localStorage.removeItem(ONBOARDING_KEY);
        }
        return;
      }
      await SecureStore.deleteItemAsync(ONBOARDING_KEY);
    } catch (e) {
      // Ignored fallback
    }
  },
};
