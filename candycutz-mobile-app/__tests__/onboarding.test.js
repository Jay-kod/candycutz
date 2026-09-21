const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('Splash Screen & Onboarding Verification Suite', async (t) => {
  await t.test('All onboarding and splash assets exist and are non-empty', () => {
    const assetsDir = path.resolve(__dirname, '../assets/images');
    assert.ok(fs.existsSync(assetsDir), 'assets/images directory must exist');

    const expectedAssets = [
      'splash-bg.jpg',
      'onboarding-1.jpg',
      'onboarding-2.jpg',
      'onboarding-3.jpg',
    ];

    for (const file of expectedAssets) {
      const filePath = path.join(assetsDir, file);
      assert.ok(fs.existsSync(filePath), `Asset ${file} must exist`);
      const stat = fs.statSync(filePath);
      assert.ok(stat.size > 10000, `Asset ${file} must be non-empty image (got ${stat.size} bytes)`);
    }
  });

  await t.test('RootLayout registers onboarding route and splash screen component', () => {
    const layoutPath = path.resolve(__dirname, '../app/_layout.tsx');
    assert.ok(fs.existsSync(layoutPath), 'app/_layout.tsx must exist');

    const content = fs.readFileSync(layoutPath, 'utf8');
    assert.ok(
      content.includes('<Stack.Screen name="onboarding" options={{ headerShown: false }} />'),
      'RootLayout must register onboarding stack screen without header'
    );
    assert.ok(
      content.includes('SplashScreenView'),
      'RootLayout must render SplashScreenView component'
    );
    assert.ok(
      content.includes('onboardingStorage'),
      'RootLayout must check onboardingStorage on initialization'
    );
    assert.ok(
      content.includes('isSplashActive'),
      'RootLayout must maintain isSplashActive state'
    );
  });

  await t.test('OnboardingScreen defines 3 walkthrough slides matching requirements', () => {
    const onboardingPath = path.resolve(__dirname, '../app/onboarding.tsx');
    assert.ok(fs.existsSync(onboardingPath), 'app/onboarding.tsx must exist');

    const content = fs.readFileSync(onboardingPath, 'utf8');
    assert.ok(content.includes('Master Barbers at Your Service'), 'Slide 1 title must match');
    assert.ok(content.includes('Effortless Real-Time Booking'), 'Slide 2 title must match');
    assert.ok(content.includes('A Sharper Standard, Every Day'), 'Slide 3 title must match');
    assert.ok(content.includes('pagingEnabled'), 'FlatList must enable paging');
    assert.ok(content.includes('await onboardingStorage.setHasSeenOnboarding(true)'), 'Must persist onboarding completion before navigation');
    assert.ok(content.includes('onboardingStorage.beginHandoff()'), 'Must lock the root guard during auth handoff');
    assert.ok(content.includes("const target = `/auth/${destination}`"), 'Must honor the selected login or register destination');
    assert.ok(content.includes("router.replace(target"), 'Must replace directly with the selected destination');
    assert.ok(content.includes('ArrowRight'), 'Must display circular action button with arrow');
  });

  await t.test('onboardingStorage utility adheres to SecureStore without AsyncStorage', () => {
    const storagePath = path.resolve(__dirname, '../src/utils/onboardingStorage.ts');
    assert.ok(fs.existsSync(storagePath), 'src/utils/onboardingStorage.ts must exist');

    const content = fs.readFileSync(storagePath, 'utf8');
    assert.ok(content.includes('expo-secure-store'), 'Must use expo-secure-store');
    assert.ok(!content.includes('AsyncStorage'), 'Must NEVER use AsyncStorage per ARCHITECTURE.md');
    assert.ok(content.includes('hasSeenOnboarding'), 'Must export hasSeenOnboarding');
    assert.ok(content.includes('setHasSeenOnboarding'), 'Must export setHasSeenOnboarding');
    assert.ok(content.includes('resetOnboarding'), 'Must export resetOnboarding');
  });
});
