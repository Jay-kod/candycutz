# Candycutz — Mobile Application Debugging Runbook (`candycutz-mobile-app`)

## 1. Problem: Mobile App Cannot Connect to Backend ("Network Error")

### What it looks like:
In Expo Go / Simulator, API calls fail immediately with:
> `AxiosError: Network Error` or `TypeError: Network request failed`

### Root Cause:
Mobile devices and emulators cannot access `localhost:8000` directly:
- **Physical Phone (Expo Go)**: Cannot resolve your computer's `localhost`. It requires your computer's actual local Wi-Fi IP (e.g. `192.168.1.50` or `10.252.94.238`).
- **Android Emulator**: `localhost` refers to the Android virtual device itself. Android emulators must use `http://10.0.2.2:8000` to reach the host computer.
- **iOS Simulator**: Runs on the Mac host network and can access `http://localhost:8000`.

### How to Fix:
1. In `candycutz-mobile-app/.env`:
   ```ini
   # For Physical Phone testing via Expo Go on Wi-Fi:
   EXPO_PUBLIC_API_URL=http://<YOUR_COMPUTER_WIFI_IP>:8000/api/v1

   # For Android Emulator:
   # EXPO_PUBLIC_API_URL=http://10.0.2.2:8000/api/v1

   # For iOS Simulator / Production:
   # EXPO_PUBLIC_API_URL=https://api.candycutz.com/api/v1
   ```
2. Ensure your computer's firewall allows incoming traffic on port `8000`.
3. Restart Expo with a clean cache:
   ```bash
   npx expo start --clear
   ```

---

## 2. Problem: Login Succeeds but Screen Doesn't Change

### What it looks like:
After pressing "Sign In", the loading spinner finishes, but the user remains stuck on the login modal or blank screen.

### Root Cause:
`router.back()` was executed on a cold-started app that had no prior navigation history to pop back to.

### How to Fix:
In the login handler, use replacement navigation or rely on the root auth listener:
```typescript
const handleLogin = async () => {
  const success = await login(identity, password);
  if (success) {
    if (router.canGoBack()) {
      router.back();
    } else {
      router.replace('/(customer)/(tabs)');
    }
  }
};
```

---

## 3. Problem: `SecureStore` Fails on Web Preview

### What it looks like:
When running `npm run web` inside the mobile project:
> `ExpoSecureStore is not available on web.`

### Root Cause:
`expo-secure-store` relies on native iOS Keychain and Android KeyStore.

### How to Fix:
In `src/api/client.ts`, provide a graceful fallback for web environments:
```typescript
import { Platform } from 'react-native';

export const tokenStorage = {
  get: async () => {
    if (Platform.OS === 'web') return localStorage.getItem('candycutz_auth_token');
    return SecureStore.getItemAsync('candycutz_auth_token');
  },
  set: async (token: string) => {
    if (Platform.OS === 'web') { localStorage.setItem('candycutz_auth_token', token); return; }
    await SecureStore.setItemAsync('candycutz_auth_token', token);
  },
  remove: async () => {
    if (Platform.OS === 'web') { localStorage.removeItem('candycutz_auth_token'); return; }
    await SecureStore.deleteItemAsync('candycutz_auth_token');
  },
};
```
