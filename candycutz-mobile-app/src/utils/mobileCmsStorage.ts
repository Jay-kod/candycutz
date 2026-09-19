import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

export interface MobileCmsSettings {
  splashBg: string | null;
  onboardingBg: string | null;
  loginBg: string | null;
}

const STORAGE_KEYS = {
  SPLASH_BG: 'candycutz_cms_splash_bg',
  ONBOARDING_BG: 'candycutz_cms_onboarding_bg',
  LOGIN_BG: 'candycutz_cms_login_bg',
};

// In-memory fallback
let memoryCms: MobileCmsSettings = {
  splashBg: null,
  onboardingBg: null,
  loginBg: null,
};

async function getStoredItem(key: string): Promise<string | null> {
  try {
    if (Platform.OS === 'web') {
      if (typeof localStorage !== 'undefined') {
        return localStorage.getItem(key);
      }
      return null;
    }
    return await SecureStore.getItemAsync(key);
  } catch (e) {
    return null;
  }
}

async function setStoredItem(key: string, value: string | null): Promise<void> {
  try {
    if (Platform.OS === 'web') {
      if (typeof localStorage !== 'undefined') {
        if (value === null) {
          localStorage.removeItem(key);
        } else {
          localStorage.setItem(key, value);
        }
      }
      return;
    }

    if (value === null) {
      await SecureStore.deleteItemAsync(key);
    } else {
      await SecureStore.setItemAsync(key, value);
    }
  } catch (e) {
    // Graceful fallback
  }
}

export const mobileCmsStorage = {
  getStoredCms: async (): Promise<MobileCmsSettings> => {
    try {
      const [splashBg, onboardingBg, loginBg] = await Promise.all([
        getStoredItem(STORAGE_KEYS.SPLASH_BG),
        getStoredItem(STORAGE_KEYS.ONBOARDING_BG),
        getStoredItem(STORAGE_KEYS.LOGIN_BG),
      ]);

      memoryCms = {
        splashBg: splashBg ?? memoryCms.splashBg,
        onboardingBg: onboardingBg ?? memoryCms.onboardingBg,
        loginBg: loginBg ?? memoryCms.loginBg,
      };

      return memoryCms;
    } catch (e) {
      return memoryCms;
    }
  },

  setStoredCms: async (cms: Partial<MobileCmsSettings>): Promise<void> => {
    if (cms.splashBg !== undefined) {
      memoryCms.splashBg = cms.splashBg;
      await setStoredItem(STORAGE_KEYS.SPLASH_BG, cms.splashBg);
    }
    if (cms.onboardingBg !== undefined) {
      memoryCms.onboardingBg = cms.onboardingBg;
      await setStoredItem(STORAGE_KEYS.ONBOARDING_BG, cms.onboardingBg);
    }
    if (cms.loginBg !== undefined) {
      memoryCms.loginBg = cms.loginBg;
      await setStoredItem(STORAGE_KEYS.LOGIN_BG, cms.loginBg);
    }
  },

  getMemoryCms: (): MobileCmsSettings => {
    return memoryCms;
  },
};
