const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('Logout Animation & Redirection Suite', async (t) => {
  await t.test('authStore defines isLoggingOut and finishLogout', () => {
    const authStorePath = path.resolve(__dirname, '../src/store/authStore.ts');
    assert.ok(fs.existsSync(authStorePath), 'authStore.ts must exist');

    const content = fs.readFileSync(authStorePath, 'utf8');
    assert.ok(content.includes('isLoggingOut: boolean'), 'authStore interface must define isLoggingOut');
    assert.ok(content.includes('finishLogout: () => void'), 'authStore interface must define finishLogout');
    assert.ok(content.includes('isLoggingOut: false'), 'authStore initial state must have isLoggingOut: false');
    assert.ok(content.includes('isLoggingOut: true'), 'logout() must set isLoggingOut: true');
    assert.ok(content.includes('finishLogout: () =>'), 'authStore must implement finishLogout');
  });

  await t.test('LogoutTransitionOverlay component is implemented with luxury animations', () => {
    const overlayPath = path.resolve(__dirname, '../src/components/common/LogoutTransitionOverlay.tsx');
    assert.ok(fs.existsSync(overlayPath), 'LogoutTransitionOverlay.tsx must exist');

    const content = fs.readFileSync(overlayPath, 'utf8');
    assert.ok(content.includes('export function LogoutTransitionOverlay'), 'Must export LogoutTransitionOverlay component');
    assert.ok(content.includes('BrandEmblem'), 'Must display BrandEmblem');
    assert.ok(content.includes('UIcon'), 'Must use UIcon');
    assert.ok(content.includes('name="scissors"'), 'Must feature the scissors icon');
    assert.ok(content.includes("router.replace('/auth/login')"), 'Must redirect to /auth/login upon logout');
    assert.ok(content.includes('finishLogout()'), 'Must call finishLogout to reset transition state');
    assert.ok(content.includes('backdropOpacity'), 'Must animate backdrop opacity');
    assert.ok(content.includes('progressWidth'), 'Must animate progress bar track');
    assert.ok(content.includes('haloPulse'), 'Must animate luminous halo pulse');
  });

  await t.test('Root layout mounts LogoutTransitionOverlay and guards navigation', () => {
    const layoutPath = path.resolve(__dirname, '../app/_layout.tsx');
    assert.ok(fs.existsSync(layoutPath), 'Root _layout.tsx must exist');

    const content = fs.readFileSync(layoutPath, 'utf8');
    assert.ok(content.includes('LogoutTransitionOverlay'), 'Root layout must import LogoutTransitionOverlay');
    assert.ok(content.includes('<LogoutTransitionOverlay />'), 'Root layout must render LogoutTransitionOverlay');
    assert.ok(content.includes('!isLoggingOut'), 'Root layout routing effect must guard against abrupt navigation during logout');
  });

  await t.test('Profile screen executes logout smoothly', () => {
    const profilePath = path.resolve(__dirname, '../app/(tabs)/profile.tsx');
    assert.ok(fs.existsSync(profilePath), 'Profile screen must exist');

    const content = fs.readFileSync(profilePath, 'utf8');
    assert.ok(content.includes('await logout()'), 'Profile screen must call logout()');
    
    // confirmLogout must not perform an immediate conflicting router.replace
    const confirmLogoutMatch = content.match(/const confirmLogout = async \(\) => {([\s\S]*?)};/);
    assert.ok(confirmLogoutMatch, 'confirmLogout function must be defined');
    assert.ok(!confirmLogoutMatch[1].includes('router.replace'), 'confirmLogout must delegate navigation to LogoutTransitionOverlay');
  });

  await t.test('Login screen implements entrance transition animations', () => {
    const loginPath = path.resolve(__dirname, '../app/auth/login.tsx');
    assert.ok(fs.existsSync(loginPath), 'Login screen must exist');

    const content = fs.readFileSync(loginPath, 'utf8');
    assert.ok(content.includes('entranceOpacity'), 'Login screen must define entranceOpacity animation');
    assert.ok(content.includes('entranceTranslateY'), 'Login screen must define entranceTranslateY animation');
    assert.ok(content.includes('Animated.View'), 'Login screen must use Animated.View for card entrance');
  });
});
