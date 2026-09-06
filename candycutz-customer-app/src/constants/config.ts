import { Platform } from 'react-native';

// Dynamically handle local development IP (Android emulator uses 10.0.2.2, iOS simulator uses localhost)
const DEFAULT_LOCAL_IP = Platform.OS === 'android' ? 'http://10.0.2.2:8000' : 'http://localhost:8000';

export const CONFIG = {
  API_BASE_URL: process.env.EXPO_PUBLIC_API_URL || `${DEFAULT_LOCAL_IP}/api/v1`,
  API_WEB_URL: process.env.EXPO_PUBLIC_WEB_URL || 'http://localhost:5173',
  APP_NAME: 'CandyCutz',
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
