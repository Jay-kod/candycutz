import { useEffect, useRef } from 'react';
import { Platform } from 'react-native';
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import { notificationsApi } from '../api/client';
import { useAuthStore } from '../store/authStore';

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

// Configure how foreground notifications are presented
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
    shouldShowBanner: true,
    shouldShowList: true,
  }),
});

/**
 * Service to manage mobile push notification tokens and dispatch them to the CandyCutz API.
 */
export const pushNotificationService = {
  /**
   * Register push notifications and synchronize device token with backend.
   * On physical devices, obtains a real Expo Push Token.
   * On simulators/web, uses a fallback identifier.
   */
  async registerForPushNotifications(): Promise<PushRegistrationResult> {
    try {
      // Request permissions
      const { status: existingStatus } = await Notifications.getPermissionsAsync();
      let finalStatus = existingStatus;

      if (existingStatus !== 'granted') {
        const { status } = await Notifications.requestPermissionsAsync();
        finalStatus = status;
      }

      if (finalStatus !== 'granted') {
        return {
          success: false,
          error: 'Push notification permission not granted',
        };
      }

      let token: string;

      // On physical devices, get a real Expo Push Token
      if (Device.isDevice) {
        const tokenResponse = await Notifications.getExpoPushTokenAsync({
          projectId: 'candycutz-mobile-app-unified',
        });
        token = tokenResponse.data;
      } else {
        // Simulator/web fallback — deterministic but not a real push token
        token = `simulator-${Platform.OS}-${Date.now()}`;
      }

      const platform = Platform.OS === 'ios' ? 'ios' : Platform.OS === 'android' ? 'android' : 'web';

      // Send token to backend API
      await notificationsApi.registerDeviceToken(token, platform);

      // Set up Android notification channel
      if (Platform.OS === 'android') {
        await Notifications.setNotificationChannelAsync('candycutz-default', {
          name: 'CandyCutz',
          importance: Notifications.AndroidImportance.HIGH,
          vibrationPattern: [0, 250, 250, 250],
          lightColor: '#D4AF37',
          sound: 'default',
        });
      }

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

/**
 * Hook that initializes push notifications on mount (when authenticated).
 * Sets up listeners for received notifications and tap responses.
 */
export function usePushNotificationSetup() {
  const { isAuthenticated } = useAuthStore();
  const notificationListener = useRef<Notifications.EventSubscription | null>(null);
  const responseListener = useRef<Notifications.EventSubscription | null>(null);

  useEffect(() => {
    if (!isAuthenticated) return;

    // Register for push
    pushNotificationService.registerForPushNotifications().then((result) => {
      if (!result.success) {
        console.warn('Push registration failed:', result.error);
      }
    });

    // Listen for incoming notifications while app is foregrounded
    notificationListener.current = Notifications.addNotificationReceivedListener((_notification) => {
      // Notification received in foreground — the handler above shows it automatically
    });

    // Listen for user tapping on a notification
    responseListener.current = Notifications.addNotificationResponseReceivedListener((response) => {
      const data = response.notification.request.content.data;
      // Deep link routing can be added here based on notification type
      if (data?.type) {
        console.log('Notification tapped, type:', data.type, 'entity:', data.related_entity_id);
      }
    });

    return () => {
      notificationListener.current?.remove();
      responseListener.current?.remove();
    };
  }, [isAuthenticated]);
}
