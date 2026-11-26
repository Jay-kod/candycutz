import { ref, reactive, computed } from 'vue';
import client from '@/shared/api/client';

export const STORAGE_KEY = 'candycutz_cookie_preferences';
export const GUEST_CONSENT_ID_KEY = 'candycutz_consent_id';
export const CONSENT_EVENT = 'candycutz:consent-updated';
export const OPEN_MODAL_EVENT = 'candycutz:open-cookie-preferences';

/**
 * Stable anonymous identifier so guest cookie decisions can be audited
 * server-side and later linked to an account.
 */
const getGuestConsentId = () => {
  if (typeof window === 'undefined' || !window.localStorage) return null;

  try {
    const existing = window.localStorage.getItem(GUEST_CONSENT_ID_KEY);
    if (existing) return existing;

    const generated =
      typeof window.crypto?.randomUUID === 'function'
        ? window.crypto.randomUUID()
        : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (char) => {
            const random = (Math.random() * 16) | 0;
            const value = char === 'x' ? random : (random & 0x3) | 0x8;
            return value.toString(16);
          });

    window.localStorage.setItem(GUEST_CONSENT_ID_KEY, generated);
    return generated;
  } catch {
    return null;
  }
};

const syncConsentToServer = (payload) => {
  const consentId = getGuestConsentId();

  client
    .post('/consents/cookies', {
      consent_id: consentId,
      necessary: true,
      preferences: payload.preferences,
      analytics: payload.analytics,
      marketing: payload.marketing,
    })
    .catch(() => {
      // The local decision still stands; the server copy is retried on the next save.
    });
};

export const COOKIE_CATEGORIES = [
  {
    key: 'necessary',
    label: 'Strictly Necessary',
    titleKey: 'cookie_necessary_title',
    descriptionKey: 'cookie_necessary_description',
    required: true,
    badgeText: 'Always Active • Core Security & Booking',
  },
  {
    key: 'preferences',
    label: 'User Preferences',
    titleKey: 'cookie_preferences_title',
    descriptionKey: 'cookie_preferences_description',
    required: false,
    badgeText: 'Optional • Remembers Choices & Theme',
  },
  {
    key: 'analytics',
    label: 'Performance & Analytics',
    titleKey: 'cookie_analytics_title',
    descriptionKey: 'cookie_analytics_description',
    required: false,
    badgeText: 'Optional • Anonymous Speed & Telemetry',
  },
  {
    key: 'marketing',
    label: 'Marketing & Outreach',
    titleKey: 'cookie_marketing_title',
    descriptionKey: 'cookie_marketing_description',
    required: false,
    badgeText: 'Optional • Campaign & Loyalty Promotions',
  },
];

export const DEFAULT_COOKIE_CONTENT = {
  banner_text: 'We use cookies.',
  cookie_title: 'Cookie & Privacy Preferences',
  cookie_intro:
    'Choose which optional cookie categories you want to allow. Necessary storage is always active so the site can provide secure bookings and core features.',
  cookie_necessary_title: 'Strictly Necessary',
  cookie_necessary_description:
    'Required for core platform operations such as CSRF verification, authentication sessions, and appointment scheduling. These cannot be switched off.',
  cookie_preferences_title: 'User Preferences',
  cookie_preferences_description:
    'Remembers customized settings such as your favorite barbers, theme mode, and localized calendar formats for convenience.',
  cookie_analytics_title: 'Performance & Analytics',
  cookie_analytics_description:
    'Helps us understand visitor traffic, booking flow latency, and navigation patterns so we can continuously optimize platform speed.',
  cookie_marketing_title: 'Marketing & Promotions',
  cookie_marketing_description:
    'Enables attribution for exclusive grooming discounts, VIP promotions, and seasonal events if enabled.',
};

// Module-level reactive singleton state shared across the application
const hasConsented = ref(false);
const isBannerVisible = ref(false);
const isModalOpen = ref(false);
const consentedAt = ref(null);
const content = reactive({ ...DEFAULT_COOKIE_CONTENT });

const preferences = reactive({
  necessary: true,
  preferences: false,
  analytics: false,
  marketing: false,
});

let isInitialized = false;
let bannerTimer = null;

const emitConsentChange = (data) => {
  if (typeof window !== 'undefined') {
    try {
      window.dispatchEvent(
        new CustomEvent(CONSENT_EVENT, {
          detail: { ...data },
        })
      );
    } catch (e) {
      console.warn('Failed to dispatch consent event:', e);
    }
  }
};

const persistConsent = (prefs) => {
  if (bannerTimer) {
    clearTimeout(bannerTimer);
    bannerTimer = null;
  }

  const timestamp = new Date().toISOString();
  const payload = {
    necessary: true,
    preferences: Boolean(prefs.preferences),
    analytics: Boolean(prefs.analytics),
    marketing: Boolean(prefs.marketing),
    updatedAt: timestamp,
    version: 1,
  };

  try {
    if (typeof window !== 'undefined' && window.localStorage) {
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
    }
  } catch (err) {
    console.error('Failed to save cookie consent to localStorage:', err);
  }

  preferences.necessary = true;
  preferences.preferences = payload.preferences;
  preferences.analytics = payload.analytics;
  preferences.marketing = payload.marketing;
  consentedAt.value = timestamp;
  hasConsented.value = true;
  isBannerVisible.value = false;
  isModalOpen.value = false;

  syncConsentToServer(payload);
  emitConsentChange(payload);
  return payload;
};

export const initCookieConsent = () => {
  if (typeof window === 'undefined') return;

  try {
    const raw = window.localStorage ? window.localStorage.getItem(STORAGE_KEY) : null;
    if (raw) {
      const parsed = JSON.parse(raw);
      preferences.necessary = true;
      preferences.preferences = parsed.preferences === true;
      preferences.analytics = parsed.analytics === true;
      preferences.marketing = parsed.marketing === true;
      consentedAt.value = parsed.updatedAt || parsed.consentedAt || null;
      hasConsented.value = true;
      isBannerVisible.value = false;
    } else {
      hasConsented.value = false;
      if (bannerTimer) clearTimeout(bannerTimer);
      bannerTimer = setTimeout(() => {
        if (!hasConsented.value) {
          isBannerVisible.value = true;
        }
      }, 350);
    }
  } catch (err) {
    console.warn('Failed to parse stored cookie consent:', err);
    hasConsented.value = false;
    isBannerVisible.value = true;
  }

  if (!isInitialized) {
    isInitialized = true;
    window.addEventListener('storage', (event) => {
      if (event.key === STORAGE_KEY && event.newValue) {
        try {
          const parsed = JSON.parse(event.newValue);
          preferences.necessary = true;
          preferences.preferences = parsed.preferences === true;
          preferences.analytics = parsed.analytics === true;
          preferences.marketing = parsed.marketing === true;
          consentedAt.value = parsed.updatedAt || null;
          hasConsented.value = true;
          isBannerVisible.value = false;
        } catch {
          // ignore malformed external storage
        }
      }
    });

    window.addEventListener(OPEN_MODAL_EVENT, () => {
      isModalOpen.value = true;
    });
  }
};

export const useCookieConsent = () => {
  const consentStatus = computed(() => {
    if (!hasConsented.value) return 'unspecified';
    if (preferences.preferences && preferences.analytics && preferences.marketing) {
      return 'all';
    }
    if (!preferences.preferences && !preferences.analytics && !preferences.marketing) {
      return 'necessary_only';
    }
    return 'custom';
  });

  const acceptAll = () => {
    return persistConsent({
      necessary: true,
      preferences: true,
      analytics: true,
      marketing: true,
    });
  };

  const rejectNonEssential = () => {
    return persistConsent({
      necessary: true,
      preferences: false,
      analytics: false,
      marketing: false,
    });
  };

  const savePreferences = (customPrefs = {}) => {
    return persistConsent({
      necessary: true,
      preferences: customPrefs.preferences ?? preferences.preferences,
      analytics: customPrefs.analytics ?? preferences.analytics,
      marketing: customPrefs.marketing ?? preferences.marketing,
    });
  };

  const openModal = () => {
    isModalOpen.value = true;
  };

  const closeModal = () => {
    isModalOpen.value = false;
  };

  const openBanner = () => {
    if (bannerTimer) clearTimeout(bannerTimer);
    isBannerVisible.value = true;
  };

  const hideBanner = () => {
    if (bannerTimer) clearTimeout(bannerTimer);
    isBannerVisible.value = false;
  };

  const resetConsent = () => {
    if (bannerTimer) {
      clearTimeout(bannerTimer);
      bannerTimer = null;
    }
    try {
      if (typeof window !== 'undefined' && window.localStorage) {
        window.localStorage.removeItem(STORAGE_KEY);
      }
    } catch (e) {
      console.warn('Failed to clear cookie consent:', e);
    }
    preferences.necessary = true;
    preferences.preferences = false;
    preferences.analytics = false;
    preferences.marketing = false;
    consentedAt.value = null;
    hasConsented.value = false;
    isBannerVisible.value = true;
    emitConsentChange({
      necessary: true,
      preferences: false,
      analytics: false,
      marketing: false,
      reset: true,
    });
  };

  const applyCmsSettings = (settings = {}) => {
    if (!settings || typeof settings !== 'object') return;
    for (const key of Object.keys(content)) {
      if (typeof settings[key] === 'string' && settings[key].trim()) {
        content[key] = settings[key].trim();
      }
    }
  };

  return {
    categories: COOKIE_CATEGORIES,
    STORAGE_KEY,
    CONSENT_EVENT,
    OPEN_MODAL_EVENT,
    hasConsented,
    isBannerVisible,
    isModalOpen,
    consentedAt,
    content,
    preferences,
    consentStatus,
    initCookieConsent,
    acceptAll,
    rejectNonEssential,
    savePreferences,
    openModal,
    closeModal,
    openBanner,
    hideBanner,
    resetConsent,
    applyCmsSettings,
  };
};
