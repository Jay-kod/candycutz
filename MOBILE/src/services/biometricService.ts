import * as LocalAuthentication from 'expo-local-authentication';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

const BIOMETRIC_ENABLED_KEY = 'candycutz_biometric_enabled';
const BIOMETRIC_TOKEN_KEY = 'candycutz_biometric_token';
const BIOMETRIC_USER_KEY = 'candycutz_biometric_user';

export interface BiometricSupportInfo {
  hasHardware: boolean;
  isEnrolled: boolean;
  supportedTypes: LocalAuthentication.AuthenticationType[];
  biometricName: string;
}

export interface BiometricUserInfo {
  name: string;
  email: string;
  role?: 'customer' | 'barber' | string;
}

export interface BiometricProfiles {
  customer: BiometricUserInfo | null;
  barber: BiometricUserInfo | null;
}

export const biometricService = {
  /**
   * Check if device has biometric hardware and enrolled fingerprints/face
   */
  checkSupport: async (): Promise<BiometricSupportInfo> => {
    if (Platform.OS === 'web') {
      return {
        hasHardware: false,
        isEnrolled: false,
        supportedTypes: [],
        biometricName: 'Biometrics',
      };
    }

    try {
      const hasHardware = await LocalAuthentication.hasHardwareAsync();
      const isEnrolled = hasHardware ? await LocalAuthentication.isEnrolledAsync() : false;
      const supportedTypes = hasHardware
        ? await LocalAuthentication.supportedAuthenticationTypesAsync()
        : [];

      let biometricName = 'Fingerprint';
      if (supportedTypes.includes(LocalAuthentication.AuthenticationType.FACIAL_RECOGNITION)) {
        biometricName = Platform.OS === 'ios' ? 'Face ID' : 'Face / Fingerprint';
      } else if (supportedTypes.includes(LocalAuthentication.AuthenticationType.FINGERPRINT)) {
        biometricName = Platform.OS === 'ios' ? 'Touch ID' : 'Fingerprint';
      }

      return {
        hasHardware,
        isEnrolled,
        supportedTypes,
        biometricName,
      };
    } catch {
      return {
        hasHardware: false,
        isEnrolled: false,
        supportedTypes: [],
        biometricName: 'Fingerprint',
      };
    }
  },

  /**
   * Check if user has enabled biometric login in settings (globally or for role)
   */
  isEnabled: async (role?: 'customer' | 'barber'): Promise<boolean> => {
    if (Platform.OS === 'web') return false;
    try {
      if (role) {
        const val = await SecureStore.getItemAsync(`${BIOMETRIC_ENABLED_KEY}_${role}`);
        const token = await SecureStore.getItemAsync(`${BIOMETRIC_TOKEN_KEY}_${role}`);
        return val === 'true' && !!token;
      }
      const val = await SecureStore.getItemAsync(BIOMETRIC_ENABLED_KEY);
      const token = await SecureStore.getItemAsync(BIOMETRIC_TOKEN_KEY);
      const customerVal = await SecureStore.getItemAsync(`${BIOMETRIC_ENABLED_KEY}_customer`);
      const barberVal = await SecureStore.getItemAsync(`${BIOMETRIC_ENABLED_KEY}_barber`);
      return (val === 'true' && !!token) || customerVal === 'true' || barberVal === 'true';
    } catch {
      return false;
    }
  },

  /**
   * Retrieve saved user info for greeting on login screen
   */
  getSavedUser: async (role?: 'customer' | 'barber'): Promise<BiometricUserInfo | null> => {
    if (Platform.OS === 'web') return null;
    try {
      const key = role ? `${BIOMETRIC_USER_KEY}_${role}` : BIOMETRIC_USER_KEY;
      const raw = await SecureStore.getItemAsync(key);
      if (!raw) return null;
      return JSON.parse(raw);
    } catch {
      return null;
    }
  },

  /**
   * Retrieve both saved profiles (Customer and Barber)
   */
  getSavedProfiles: async (): Promise<BiometricProfiles> => {
    if (Platform.OS === 'web') return { customer: null, barber: null };
    try {
      const customerRaw = await SecureStore.getItemAsync(`${BIOMETRIC_USER_KEY}_customer`);
      const barberRaw = await SecureStore.getItemAsync(`${BIOMETRIC_USER_KEY}_barber`);
      const generalRaw = await SecureStore.getItemAsync(BIOMETRIC_USER_KEY);

      let customer: BiometricUserInfo | null = customerRaw ? JSON.parse(customerRaw) : null;
      let barber: BiometricUserInfo | null = barberRaw ? JSON.parse(barberRaw) : null;

      // Fallback for general key
      if (!customer && !barber && generalRaw) {
        const gen = JSON.parse(generalRaw);
        if (gen.role === 'barber') {
          barber = gen;
        } else {
          customer = gen;
        }
      }

      return { customer, barber };
    } catch {
      return { customer: null, barber: null };
    }
  },

  /**
   * Enable biometric login: authenticates fingerprint first, then stores token & user
   */
  enable: async (
    token: string,
    user: { name?: string; email?: string; role?: 'customer' | 'barber' | string }
  ): Promise<{ success: boolean; message?: string }> => {
    if (Platform.OS === 'web') {
      return { success: false, message: 'Biometrics not supported on web.' };
    }

    const support = await biometricService.checkSupport();
    if (!support.hasHardware) {
      return { success: false, message: 'Your device does not have biometric hardware.' };
    }
    if (!support.isEnrolled) {
      return {
        success: false,
        message: 'No fingerprints or biometrics are enrolled on this device. Please set them up in device settings.',
      };
    }

    const roleName = user.role === 'barber' ? 'Barber Staff' : 'Customer';

    try {
      const result = await LocalAuthentication.authenticateAsync({
        promptMessage: `Verify your ${support.biometricName} for ${roleName} Login`,
        cancelLabel: 'Cancel',
        disableDeviceFallback: false,
      });

      if (!result.success) {
        return { success: false, message: 'Biometric verification cancelled or failed.' };
      }

      const userData: BiometricUserInfo = {
        name: user.name || (user.role === 'barber' ? 'Stylist' : 'CandyCutz User'),
        email: user.email || '',
        role: user.role || 'customer',
      };

      const userJson = JSON.stringify(userData);

      // Store in role-specific keys
      const roleKey = user.role === 'barber' ? 'barber' : 'customer';
      await SecureStore.setItemAsync(`${BIOMETRIC_ENABLED_KEY}_${roleKey}`, 'true');
      await SecureStore.setItemAsync(`${BIOMETRIC_TOKEN_KEY}_${roleKey}`, token);
      await SecureStore.setItemAsync(`${BIOMETRIC_USER_KEY}_${roleKey}`, userJson);

      // Also maintain general key for backward compatibility
      await SecureStore.setItemAsync(BIOMETRIC_ENABLED_KEY, 'true');
      await SecureStore.setItemAsync(BIOMETRIC_TOKEN_KEY, token);
      await SecureStore.setItemAsync(BIOMETRIC_USER_KEY, userJson);

      return { success: true };
    } catch (e: any) {
      return { success: false, message: e.message || 'Failed to enable biometric login.' };
    }
  },

  /**
   * Disable biometric login and remove stored credentials for specific role or all
   */
  disable: async (role?: 'customer' | 'barber'): Promise<void> => {
    if (Platform.OS === 'web') return;
    try {
      if (role) {
        await SecureStore.deleteItemAsync(`${BIOMETRIC_ENABLED_KEY}_${role}`);
        await SecureStore.deleteItemAsync(`${BIOMETRIC_TOKEN_KEY}_${role}`);
        await SecureStore.deleteItemAsync(`${BIOMETRIC_USER_KEY}_${role}`);
      } else {
        await SecureStore.deleteItemAsync(BIOMETRIC_ENABLED_KEY);
        await SecureStore.deleteItemAsync(BIOMETRIC_TOKEN_KEY);
        await SecureStore.deleteItemAsync(BIOMETRIC_USER_KEY);
        await SecureStore.deleteItemAsync(`${BIOMETRIC_ENABLED_KEY}_customer`);
        await SecureStore.deleteItemAsync(`${BIOMETRIC_TOKEN_KEY}_customer`);
        await SecureStore.deleteItemAsync(`${BIOMETRIC_USER_KEY}_customer`);
        await SecureStore.deleteItemAsync(`${BIOMETRIC_ENABLED_KEY}_barber`);
        await SecureStore.deleteItemAsync(`${BIOMETRIC_TOKEN_KEY}_barber`);
        await SecureStore.deleteItemAsync(`${BIOMETRIC_USER_KEY}_barber`);
      }
    } catch {
      // Ignored
    }
  },

  /**
   * Prompt user for fingerprint / FaceID and return stored auth token
   */
  authenticate: async (
    role?: 'customer' | 'barber'
  ): Promise<{ success: boolean; token?: string; role?: string; message?: string }> => {
    if (Platform.OS === 'web') {
      return { success: false, message: 'Biometrics not supported on web.' };
    }

    const enabled = await biometricService.isEnabled(role);
    if (!enabled) {
      return { success: false, message: 'Biometric login is not enabled.' };
    }

    const support = await biometricService.checkSupport();
    const promptTitle = role === 'barber'
      ? `Log in as Barber with ${support.biometricName}`
      : role === 'customer'
      ? `Log in as Customer with ${support.biometricName}`
      : `Log in with ${support.biometricName}`;

    try {
      const result = await LocalAuthentication.authenticateAsync({
        promptMessage: promptTitle,
        cancelLabel: 'Use Password',
        disableDeviceFallback: false,
      });

      if (!result.success) {
        return { success: false, message: 'Biometric verification cancelled.' };
      }

      let token: string | null = null;
      let detectedRole = role;

      if (role) {
        token = await SecureStore.getItemAsync(`${BIOMETRIC_TOKEN_KEY}_${role}`);
      }

      // Fallback to general token
      if (!token) {
        token = await SecureStore.getItemAsync(BIOMETRIC_TOKEN_KEY);
        const userRaw = await SecureStore.getItemAsync(BIOMETRIC_USER_KEY);
        if (userRaw) {
          try {
            const u = JSON.parse(userRaw);
            detectedRole = u.role;
          } catch {}
        }
      }

      if (!token) {
        return {
          success: false,
          message: 'Saved login session expired. Please log in with password once.',
        };
      }

      return { success: true, token, role: detectedRole };
    } catch (e: any) {
      return { success: false, message: e.message || 'Biometric authentication failed.' };
    }
  },
};
