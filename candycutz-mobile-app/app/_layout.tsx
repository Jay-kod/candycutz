import React, { useEffect, useState } from 'react';
import { Stack, useRouter, useSegments } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import * as SplashScreen from 'expo-splash-screen';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useAuthStore } from '../src/store/authStore';
import { COLORS } from '../src/constants/theme';
import { SplashScreenView } from '../src/components/common/SplashScreenView';
import { NavigationLoadingOverlay } from '../src/components/common/NavigationLoadingOverlay';
import { onboardingStorage } from '../src/utils/onboardingStorage';
import { mobileCmsStorage } from '../src/utils/mobileCmsStorage';
import { apiClient } from '../src/api/client';

// Prevent native OS splash from auto-hiding before JS splash is mounted
SplashScreen.preventAutoHideAsync().catch(() => {});

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
  const router = useRouter();
  const segments = useSegments();

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
        const [_, seen] = await Promise.all([
          initializeAuth(),
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
    if (isSplashActive || isLoading || hasSeenOnboarding === null) return;

    const currentSegment = segments[0] as string | undefined;
    const inAuthStack = currentSegment === 'auth';
    const inOnboarding = currentSegment === 'onboarding';

    if (!hasSeenOnboarding) {
      if (!inOnboarding && !inAuthStack) {
        router.replace('/onboarding');
      }
    } else if (!isAuthenticated && !inAuthStack) {
      router.replace('/auth/login');
    } else if (isAuthenticated && (inAuthStack || inOnboarding)) {
      router.replace('/(tabs)');
    }
  }, [isSplashActive, isLoading, hasSeenOnboarding, isAuthenticated, router, segments]);

  return (
    <SafeAreaProvider>
      <QueryClientProvider client={queryClient}>
        <StatusBar style="light" />
        <Stack
          screenOptions={{
            headerStyle: {
              backgroundColor: COLORS.background,
            },
            headerTintColor: COLORS.primary,
            headerTitleStyle: {
              fontWeight: '700',
              color: COLORS.textPrimary,
            },
            contentStyle: {
              backgroundColor: COLORS.background,
            },
          }}
        >
          <Stack.Screen name="onboarding" options={{ headerShown: false }} />
          <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
          <Stack.Screen name="barber/profile-edit" options={{ title: 'Edit Profile', presentation: 'modal' }} />
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
        </Stack>

        {isSplashActive && (
          <SplashScreenView
            isExiting={isSplashExiting}
            backgroundImageUri={splashBgUri}
            onReady={() => SplashScreen.hideAsync().catch(() => {})}
            onAnimationComplete={() => setIsSplashActive(false)}
          />
        )}
        {!isSplashActive && <NavigationLoadingOverlay />}
      </QueryClientProvider>
    </SafeAreaProvider>
  );
}
