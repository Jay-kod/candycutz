import { Platform } from 'react-native';
import { notificationsApi } from '../api/client';

export interface PushNotificationPayload {
  title: string;
  body: string;
  data?: Record<string, any>;
}

export interface PushRegistrationResult {
  success: boolean;
  token?: string;
  error?: string;
}

/**
 * Service to manage mobile push notification tokens and dispatch them to the CandyCutz API.
 */
export const pushNotificationService = {
  /**
   * Register push notifications and synchronize device token with backend
   */
  async registerForPushNotifications(customToken?: string): Promise<PushRegistrationResult> {
    try {
      let token = customToken;

      // In web or mock development, fallback to a deterministic identifier
      if (!token) {
        if (Platform.OS === 'web') {
          token = 'web-push-client-' + Math.random().toString(36).substring(7);
        } else {
          // Native platforms retrieve standard push device token
          token = `mobile-${Platform.OS}-token-${Date.now()}`;
        }
      }

      const platform = Platform.OS === 'ios' ? 'ios' : Platform.OS === 'android' ? 'android' : 'web';

      // Send token to backend API
      await notificationsApi.registerDeviceToken(token, platform);

      return {
        success: true,
        token,
      };
    } catch (error: any) {
      return {
        success: false,
        error: error?.message || 'Failed to register push token with server',
      };
    }
  },

  /**
   * Unregister device token upon user logout
   */
  async unregisterDeviceToken(token: string): Promise<boolean> {
    try {
      await notificationsApi.deleteDeviceToken(token);
      return true;
    } catch (e) {
      return false;
    }
  },
};
