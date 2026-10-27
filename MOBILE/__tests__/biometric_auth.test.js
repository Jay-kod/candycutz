import test from 'node:test';
import assert from 'node:assert';
import fs from 'node:fs';
import path from 'node:path';

const projectRoot = path.resolve('c:/xampp/htdocs/1/candycutz/candycutz-mobile-app');

test('Biometric Authentication & Fingerprint Login Suite', async (t) => {
  await t.test('biometricService.ts exists and exports multi-role interface', () => {
    const servicePath = path.join(projectRoot, 'src/services/biometricService.ts');
    assert.ok(fs.existsSync(servicePath), 'biometricService.ts must exist');

    const content = fs.readFileSync(servicePath, 'utf8');
    assert.ok(content.includes('checkSupport'), 'must export checkSupport');
    assert.ok(content.includes('isEnabled'), 'must export isEnabled');
    assert.ok(content.includes('enable'), 'must export enable');
    assert.ok(content.includes('disable'), 'must export disable');
    assert.ok(content.includes('authenticate'), 'must export authenticate');
    assert.ok(content.includes('getSavedUser'), 'must export getSavedUser');
    assert.ok(content.includes('getSavedProfiles'), 'must export getSavedProfiles');
    assert.ok(content.includes('expo-local-authentication'), 'must use expo-local-authentication');
    assert.ok(content.includes('expo-secure-store'), 'must use expo-secure-store');
  });

  await t.test('authStore.ts implements role-aware biometricLogin method', () => {
    const storePath = path.join(projectRoot, 'src/store/authStore.ts');
    const content = fs.readFileSync(storePath, 'utf8');

    assert.ok(content.includes('biometricLogin: (role?: \'customer\' | \'barber\') => Promise<boolean>'), 'AuthState must include role in biometricLogin');
    assert.ok(content.includes('biometricLogin: async (role?: \'customer\' | \'barber\') => {'), 'authStore must implement biometricLogin with role');
    assert.ok(content.includes('biometricService.authenticate(role)'), 'biometricLogin must invoke biometricService.authenticate with role');
  });

  await t.test('settings.tsx supports Fingerprint toggle for both Customer and Barber accounts', () => {
    const settingsPath = path.join(projectRoot, 'app/profile/settings.tsx');
    const content = fs.readFileSync(settingsPath, 'utf8');

    assert.ok(content.includes('biometricService'), 'settings.tsx must consume biometricService');
    assert.ok(content.includes('handleToggleBiometrics'), 'must implement handleToggleBiometrics');
    assert.ok(content.includes('biometricsEnabled'), 'must bind biometricsEnabled switch state');
    assert.ok(content.includes('isBarber'), 'must inspect isBarber role');
    assert.ok(content.includes('Fingerprint'), 'must render Fingerprint icon');
  });

  await t.test('login.tsx has quick Fingerprint sign-in for both Customer and Barber roles', () => {
    const loginPath = path.join(projectRoot, 'app/auth/login.tsx');
    const content = fs.readFileSync(loginPath, 'utf8');

    assert.ok(content.includes('biometricLogin'), 'login.tsx must consume biometricLogin');
    assert.ok(content.includes('biometricProfiles'), 'login.tsx must check biometricProfiles');
    assert.ok(content.includes('handleBiometricLogin(\'customer\')'), 'must handle customer biometric login');
    assert.ok(content.includes('handleBiometricLogin(\'barber\')'), 'must handle barber biometric login');
    assert.ok(!content.includes('expo-auth-session/providers/google'), 'login.tsx must not use Google auth session');
    assert.ok(!content.includes('GoogleLogo'), 'login.tsx must not render GoogleLogo');
  });

  await t.test('profile.tsx renders Fingerprint menu entry in both Customer Profile and Barber Staff Desk', () => {
    const profilePath = path.join(projectRoot, 'app/(tabs)/profile.tsx');
    const content = fs.readFileSync(profilePath, 'utf8');

    assert.ok(content.includes('Fingerprint & Settings'), 'Customer profile must include Fingerprint & Settings');
    assert.ok(content.includes('Fingerprint Login'), 'Barber Staff Desk must include Fingerprint Login');
  });
});
