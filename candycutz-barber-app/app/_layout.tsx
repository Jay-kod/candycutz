import React, { useEffect } from 'react';
import { Stack } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { useBarberAuthStore } from '../src/store/barberAuthStore';
import { COLORS } from '../src/constants/theme';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      staleTime: 1000 * 30, // 30 seconds for live barber queues
    },
  },
});

export default function RootLayout() {
  const initializeAuth = useBarberAuthStore((state) => state.initializeAuth);

  useEffect(() => {
    initializeAuth();
  }, []);

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
          <Stack.Screen name="(tabs)" options={{ headerShown: false }} />
          <Stack.Screen
            name="walkin"
            options={{
              title: 'New Walk-In Client',
              presentation: 'modal',
            }}
          />
          <Stack.Screen
            name="auth/login"
            options={{
              title: 'Staff Sign In',
              presentation: 'modal',
            }}
          />
        </Stack>
      </QueryClientProvider>
    </SafeAreaProvider>
  );
}
