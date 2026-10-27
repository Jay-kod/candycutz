import { Platform } from 'react-native';

// Dynamically handle local development IP (Android emulator uses 10.0.2.2, iOS simulator uses localhost)
const DEFAULT_LOCAL_IP = Platform.OS === 'android' ? 'http://10.0.2.2:8000' : 'http://localhost:8000';
const rawApiUrl = (process.env.EXPO_PUBLIC_API_URL || DEFAULT_LOCAL_IP).trim().replace(/\/+$/, '');

// Extract host origin root by stripping any trailing /api/v1, /api, or /v1
const API_ROOT = rawApiUrl.replace(/\/(api\/v1|api|v1)$/i, '') || DEFAULT_LOCAL_IP;
const API_BASE_URL = `${API_ROOT}/api/v1`;
const STORAGE_BASE_URL = `${API_ROOT}/storage`;

export const CONFIG = {
  API_ROOT,
  API_BASE_URL,
  STORAGE_BASE_URL,
  API_WEB_URL: (process.env.EXPO_PUBLIC_WEB_URL || 'http://localhost:5173').trim().replace(/\/+$/, ''),
  APP_NAME: 'CandyCutz',
  APP_VERSION: '1.0.0',
  DEFAULT_CURRENCY: 'NGN',
  CURRENCY_SYMBOL: '₦',
  
  // Physical Keffi Headquarters Details
  BRANCH: {
    NAME: 'CandyCutz Keffi Flagship',
    ADDRESS: 'Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa State, Nigeria',
    SHORT_ADDRESS: 'Angwan Kare, BCG, Keffi',
    PHONE: '+234 812 345 6789',
    EMAIL: 'keffi@candycutz.ng',
    MAPS_URL: 'https://maps.app.goo.gl/RtpPCeBRobajKmwS7',
    LATITUDE: 8.8486,
    LONGITUDE: 7.8736,
  },

  // Operating Hours
  OPENING_TIME: '08:00',
  CLOSING_TIME: '20:00',
};

/**
 * Resolves an image or media path to an absolute, accessible URL.
 */
export function getStorageUrl(path?: string | null, fallback = ''): string {
  if (!path) {
    return fallback;
  }

  if (/^(https?:|\/\/|data:|file:|content:|blob:)/i.test(path)) {
    return path;
  }

  if (path.startsWith('/storage/')) {
    return `${CONFIG.API_ROOT}${path}`;
  }
  if (path.startsWith('storage/')) {
    return `${CONFIG.API_ROOT}/${path}`;
  }

  const cleanPath = path.replace(/^\/+/, '');
  return `${CONFIG.STORAGE_BASE_URL}/${cleanPath}`;
}

export function normalizeMediaUrl(path?: string | null, fallback = ''): string {
  if (!path) {
    return fallback;
  }

  return getStorageUrl(path, fallback);
}

