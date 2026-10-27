import React from 'react';
import { Stack, useRouter } from 'expo-router';
import { ErrorScreen } from '../src/components/common/ErrorScreen';

export default function NotFoundScreen() {
  const router = useRouter();

  return (
    <>
      <Stack.Screen options={{ title: 'Page Not Found', headerShown: false }} />
      <ErrorScreen
        variant="notFound"
        title="Page Not Found"
        message="The screen or section you are looking for does not exist or has been relocated."
        onGoHome={() => router.replace('/(tabs)')}
        onGoBack={() => {
          if (router.canGoBack()) {
            router.back();
          } else {
            router.replace('/(tabs)');
          }
        }}
      />
    </>
  );
}
