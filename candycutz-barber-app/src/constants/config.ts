import { Platform } from 'react-native';

const DEFAULT_LOCAL_IP = Platform.OS === 'android' ? 'http://10.0.2.2:8000' : 'http://localhost:8000';

export const CONFIG = {
  API_BASE_URL: process.env.EXPO_PUBLIC_API_URL || `${DEFAULT_LOCAL_IP}/api/v1`,
  APP_NAME: 'CandyCutz Staff',
  DEFAULT_CURRENCY: 'NGN',
  CURRENCY_SYMBOL: '₦',

  BRANCH: {
    NAME: 'CandyCutz Keffi Flagship',
    ADDRESS: 'Angwan Kare, BCG, Beside Angwan Kare BCG Gas Station, beside Wealths Khort Apartments, BCG, Keffi 961101, Nasarawa State, Nigeria',
    SHORT_ADDRESS: 'Angwan Kare, BCG, Keffi',
    PHONE: '+234 812 345 6789',
  },
};
