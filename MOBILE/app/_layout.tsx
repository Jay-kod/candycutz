import React, { useEffect, useState } from 'react';
import { Linking } from 'react-native';
import { Stack, useRouter, useSegments } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import * as SplashScreen from 'expo-splash-screen';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useAuthStore } from '../src/store/authStore';
import { useThemeStore } from '../src/store/themeStore';
import { usePushNotificationSetup } from '../src/services/notifications';
import { useAppTheme } from '../src/hooks/useAppTheme';
import { SplashScreenView } from '../src/components/common/SplashScreenView';
import { NavigationLoadingOverlay } from '../src/components/common/NavigationLoadingOverlay';
import { LogoutTransitionOverlay } from '../src/components/common/LogoutTransitionOverlay';
import { onboardingStorage } from '../src/utils/onboardingStorage';
import { mobileCmsStorage } from '../src/utils/mobileCmsStorage';
import { apiClient } from '../src/api/client';
import { AppToast } from '../src/components/common/AppToast';
import { ThemePatternOverlay } from '../src/components/common/ThemePatternOverlay';
import { ErrorScreen } from '../src/components/common/ErrorScreen';
import { useSystemStatusStore } from '../src/store/systemStatusStore';
import { reportCrash, initGlobalErrorHandler } from '../src/services/telemetry';

// Prevent native OS splash from auto-hiding before JS splash is mounted
SplashScreen.preventAutoHideAsync().catch(() => {});
initGlobalErrorHandler();

export function ErrorBoundary({ error, retry }: { error: Error; retry: () => void }) {
  useEffect(() => {
    if (error) {
      reportCrash(error);
    }
  }, [error]);

  return (
    <ErrorScreen
      variant="server"
      title="Application Error"
      message={error?.message || 'An unexpected error occurred in the application.'}
      onRetry={retry}
    />
  );
}

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      staleTime: 1000 * 60 * 5, // 5 minutes
    },
  },
});

export default function RootLayout() {
  const initializeAuth = useAuthStore((state) => state.initializeAuth);
  const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
  const isLoading = useAuthStore((state) => state.isLoading);
  const isLoggingOut = useAuthStore((state) => state.isLoggingOut);
  const { colors, isDark } = useAppTheme();
  const initializeTheme = useThemeStore((state) => state.initializeTheme);
  const isThemeInitialized = useThemeStore((state) => state.isInitialized);
  const router = useRouter();
  const segments = useSegments();

  const isMaintenance = useSystemStatusStore((state) => state.isMaintenance);
  const maintenanceMessage = useSystemStatusStore((state) => state.maintenanceMessage);
  const isUpgradeRequired = useSystemStatusStore((state) => state.isUpgradeRequired);
  const upgradeData = useSystemStatusStore((state) => state.upgradeData);
  const clearStatus = useSystemStatusStore((state) => state.clearStatus);

  // Initialize push notifications when authenticated
  usePushNotificationSetup();

  // Splash screen lifecycle states
  const [isSplashActive, setIsSplashActive] = useState(true);
  const [isSplashExiting, setIsSplashExiting] = useState(false);
  const [hasSeenOnboarding, setHasSeenOnboarding] = useState<boolean | null>(null);
  const [splashBgUri, setSplashBgUri] = useState<string | null>(null);

  useEffect(() => {
    let isMounted = true;
    const startTime = Date.now();
    const MIN_SPLASH_TIME = 2000;

    // Immediately load cached splash background for zero-delay display
    mobileCmsStorage.getStoredCms().then((cms) => {
      if (isMounted && cms.splashBg) {
        setSplashBgUri(cms.splashBg);
      }
    });

    const init = async () => {
      try {
        const [_, __, seen] = await Promise.all([
          initializeAuth(),
          initializeTheme(),
          onboardingStorage.hasSeenOnboarding().catch(() => false),
          // Background sync of mobile CMS settings
          apiClient
            .get('/settings')
            .then((res) => {
              const data = res.data?.data;
              if (data) {
                const mobile = data.mobile || data;
                const splash = mobile.splash_background_image || null;
                const onboarding = mobile.onboarding_background_image || null;
                const login = mobile.login_background_image || null;

                mobileCmsStorage.setStoredCms({
                  splashBg: splash,
                  onboardingBg: onboarding,
                  loginBg: login,
                });

                if (isMounted && splash) {
                  setSplashBgUri(splash);
                }
              }
            })
            .catch(() => {
              // Silently fallback to cached or bundled assets if offline
            }),
        ]);

        if (!isMounted) return;
        setHasSeenOnboarding(seen);

        const elapsed = Date.now() - startTime;
        const remaining = Math.max(0, MIN_SPLASH_TIME - elapsed);

        setTimeout(() => {
          if (!isMounted) return;
          setIsSplashExiting(true);
        }, remaining);
      } catch (e) {
        if (!isMounted) return;
        // Never leave user stranded; safely resolve onboarding and exit splash
        const seen = await onboardingStorage.hasSeenOnboarding().catch(() => false);
        setHasSeenOnboarding(seen);
        setIsSplashExiting(true);
      }
    };

    init();

    return () => {
      isMounted = false;
    };
  }, []);

  // Post-splash routing logic
  useEffect(() => {
    if (isSplashActive || isLoading || !isThemeInitialized || hasSeenOnboarding === null) return;

    const currentSegment = segments[0] as string | undefined;
    if (!currentSegment || onboardingStorage.isHandoffPending()) return;

    const inAuthStack = currentSegment === 'auth';
    const inOnboarding = currentSegment === 'onboarding';

    if (!hasSeenOnboarding) {
      if (!inOnboarding && !inAuthStack) {
        router.replace('/onboarding');
      }
    } else if (!isAuthenticated && !inAuthStack) {
      // Let LogoutTransitionOverlay handle navigation smoothly during logout
      if (!isLoggingOut) {
        router.replace('/auth/login');
      }
    } else if (isAuthenticated && (inAuthStack || inOnboarding)) {
      router.replace('/(tabs)');
    }
  }, [isSplashActive, isLoading, isThemeInitialized, hasSeenOnboarding, isAuthenticated, isLoggingOut, router, segments]);

  if (isUpgradeRequired) {
    return (
      <SafeAreaProvider>
        <ErrorScreen
          variant="upgrade"
          title="Update Required"
          message={
            upgradeData?.release_notes ||
            `A newer version (${upgradeData?.latest_version || 'latest'}) of CandyCutz is required to continue. Please update your app to the latest release.`
          }
          onRetry={() => {
            if (upgradeData?.store_url) {
              Linking.openURL(upgradeData.store_url).catch(() => {});
            }
          }}
          showSupportLink={false}
        />
      </SafeAreaProvider>
    );
  }

  if (isMaintenance) {
    return (
      <SafeAreaProvider>
        <ErrorScreen
          variant="maintenance"
          title="Under Maintenance"
          message={maintenanceMessage || 'CandyCutz is undergoing scheduled maintenance to upgrade your experience. We will be back shortly!'}
          onRetry={() => {
            clearStatus();
            apiClient.get('/settings').catch(() => {});
          }}
          showSupportLink={false}
        />
      </SafeAreaProvider>
    );
  }

  return (
    <SafeAreaProvider>
      <QueryClientProvider client={queryClient}>
        <StatusBar style={isDark ? 'light' : 'dark'} />
        <Stack
          screenOptions={{
            headerStyle: {
              backgroundColor: colors.background,
            },
            headerTintColor: colors.primary,
            headerTitleStyle: {
              fontWeight: '700',
              color: colors.textPrimary,
            },
            contentStyle: {
              backgroundColor: colors.background,
            },
          }}
        >
          <Stack.Screen name="onboarding" options={{ headerShown: false }} />
          <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
          <Stack.Screen name="profile/edit" options={{ title: 'Edit Profile', presentation: 'modal' }} />
          <Stack.Screen name="barber/profile-edit" options={{ title: 'Edit Profile', presentation: 'modal' }} />
          <Stack.Screen name="profile/wishlist" options={{ title: 'Wishlist' }} />
          <Stack.Screen name="profile/notifications" options={{ title: 'Notifications' }} />
          <Stack.Screen name="profile/reviews" options={{ title: 'My Reviews' }} />
          <Stack.Screen name="profile/settings" options={{ title: 'Settings' }} />
          <Stack.Screen name="profile/services" options={{ title: 'My Services' }} />
          <Stack.Screen name="profile/gallery" options={{ title: 'Gallery' }} />
          <Stack.Screen name="profile/blog" options={{ title: 'Blog Posts' }} />
          <Stack.Screen name="profile/analytics" options={{ title: 'Analytics' }} />
          <Stack.Screen
            name="book/[serviceId]"
            options={{
              title: 'Book Appointment',
              headerBackTitle: 'Back',
            }}
          />
          <Stack.Screen
            name="booking/confirmation"
            options={{
              title: 'Booking Confirmed',
              headerShown: false,
            }}
          />
          <Stack.Screen
            name="auth/login"
            options={{
              headerShown: false,
              presentation: 'card',
            }}
          />
          <Stack.Screen
            name="auth/register"
            options={{
              headerShown: false,
              presentation: 'card',
            }}
          />
          <Stack.Screen
            name="auth/forgot-password"
            options={{
              title: 'Reset Password',
              presentation: 'card',
            }}
          />
          <Stack.Screen
            name="walkin"
            options={{
              title: 'New Walk-In Client',
              presentation: 'modal',
            }}
          />
          <Stack.Screen
            name="notifications"
            options={{
              title: 'Notifications',
              headerShown: false,
              presentation: 'card',
            }}
          />
          <Stack.Screen
            name="policy/terms"
            options={{
              title: 'Terms of Service',
              headerShown: false,
              presentation: 'card',
            }}
          />
          <Stack.Screen
            name="policy/privacy"
            options={{
              title: 'Privacy Policy',
              headerShown: false,
              presentation: 'card',
            }}
          />
          <Stack.Screen
            name="+not-found"
            options={{
              title: 'Page Not Found',
              headerShown: false,
            }}
          />
        </Stack>

        <ThemePatternOverlay
          visible={
            !isSplashActive &&
            segments[0] !== 'onboarding' &&
            segments.join('/') !== 'auth/login'
          }
        />

        {isSplashActive && (
          <SplashScreenView
            isExiting={isSplashExiting}
            backgroundImageUri={splashBgUri}
            onReady={() => SplashScreen.hideAsync().catch(() => {})}
            onAnimationComplete={() => setIsSplashActive(false)}
          />
        )}
        {!isSplashActive && <NavigationLoadingOverlay />}
        <LogoutTransitionOverlay />
        <AppToast />
      </QueryClientProvider>
    </SafeAreaProvider>
  );
}
