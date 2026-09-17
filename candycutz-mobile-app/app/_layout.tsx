import React, { useEffect } from 'react';
import { Stack, useRouter, useSegments } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useAuthStore } from '../src/store/authStore';
import { COLORS } from '../src/constants/theme';
import { AppPreloader } from '../src/components/common/AppPreloader';

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

  useEffect(() => {
    initializeAuth();
  }, []);

  useEffect(() => {
    if (isLoading) return;

    const inAuthStack = segments[0] === 'auth';

    if (!isAuthenticated && !inAuthStack) {
      router.replace('/auth/login');
    } else if (isAuthenticated && inAuthStack) {
      router.replace('/(tabs)');
    }
  }, [isAuthenticated, isLoading, router, segments]);

  return (
    <SafeAreaProvider>
      <QueryClientProvider client={queryClient}>
        <StatusBar style="light" />
        {isLoading ? <AppPreloader /> : (
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
          <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
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
              title: 'Sign In',
              presentation: 'modal',
            }}
          />
          <Stack.Screen
            name="auth/register"
            options={{
              title: 'Create Account',
              presentation: 'modal',
            }}
          />
          <Stack.Screen
            name="auth/forgot-password"
            options={{
              title: 'Reset Password',
              presentation: 'modal',
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
        )}
      </QueryClientProvider>
    </SafeAreaProvider>
  );
}
