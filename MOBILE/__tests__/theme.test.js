const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('Theme System & Night/Day Dynamic Mode Suite', async (t) => {
  await t.test('theme.ts defines DARK_COLORS, LIGHT_COLORS, and backward compatible COLORS', () => {
    const themePath = path.resolve(__dirname, '../src/constants/theme.ts');
    assert.ok(fs.existsSync(themePath), 'theme.ts must exist');

    const content = fs.readFileSync(themePath, 'utf8');
    assert.ok(content.includes('DARK_COLORS'), 'theme.ts must export DARK_COLORS');
    assert.ok(content.includes('LIGHT_COLORS'), 'theme.ts must export LIGHT_COLORS');
    assert.ok(content.includes('ThemeColors'), 'theme.ts must define ThemeColors type');
    assert.ok(content.includes('export const COLORS: ThemeColors = DARK_COLORS'), 'theme.ts must export COLORS for backward compatibility');

    // Color values check
    assert.ok(content.includes("'#0A0A0C'"), 'DARK_COLORS must feature obsidian dark background');
    assert.ok(content.includes("'#D4AF37'"), 'DARK_COLORS must feature luminous gold');
    assert.ok(content.includes("'#F2F4F7'"), 'LIGHT_COLORS must feature a cool pearl background');
    assert.ok(content.includes("'#9A6A16'"), 'LIGHT_COLORS must feature a restrained bronze-gold accent');
    for (const token of ['onPrimary', 'inputBackground', 'scrim', 'patternDot', 'patternDotGlow', 'skeletonBase', 'skeletonHighlight']) {
      assert.ok(content.includes(`${token}:`), `Theme palettes must define ${token}`);
    }
  });

  await t.test('themeStore.ts uses expo-secure-store and does NOT use AsyncStorage', () => {
    const storePath = path.resolve(__dirname, '../src/store/themeStore.ts');
    assert.ok(fs.existsSync(storePath), 'themeStore.ts must exist');

    const content = fs.readFileSync(storePath, 'utf8');
    assert.ok(content.includes("from 'expo-secure-store'"), 'themeStore must persist via expo-secure-store');
    assert.ok(!content.includes('@react-native-async-storage/async-storage'), 'themeStore must not use AsyncStorage');
    assert.ok(content.includes('useThemeStore'), 'themeStore must export useThemeStore');
    assert.ok(content.includes('setThemePreference'), 'themeStore must support setThemePreference');
    assert.ok(content.includes('initializeTheme'), 'themeStore must support initializeTheme');
  });

  await t.test('useAppTheme.ts dynamically follows system colorScheme and manual preference', () => {
    const hookPath = path.resolve(__dirname, '../src/hooks/useAppTheme.ts');
    assert.ok(fs.existsSync(hookPath), 'useAppTheme.ts must exist');

    const content = fs.readFileSync(hookPath, 'utf8');
    assert.ok(content.includes('useColorScheme'), 'useAppTheme must consume useColorScheme');
    assert.ok(content.includes('useThemeStore'), 'useAppTheme must consume useThemeStore');
    assert.ok(content.includes('export function useAppTheme'), 'useAppTheme must be exported');
    assert.ok(content.includes('DARK_COLORS'), 'useAppTheme must reference DARK_COLORS');
    assert.ok(content.includes('LIGHT_COLORS'), 'useAppTheme must reference LIGHT_COLORS');
    assert.ok(content.includes("themePreference === 'dark'"), 'Manual dark preference must override system scheme');
    assert.ok(content.includes("themePreference === 'light'"), 'Manual light preference must override system scheme');
    assert.ok(content.includes("systemColorScheme !== 'light'"), 'System mode must resolve from the OS scheme');
  });

  await t.test('theme hydration is part of root startup gating', () => {
    const rootPath = path.resolve(__dirname, '../app/_layout.tsx');
    const content = fs.readFileSync(rootPath, 'utf8');
    assert.ok(content.includes('isThemeInitialized'), 'Root layout must read theme hydration state');
    assert.ok(content.includes('!isThemeInitialized'), 'Routing must wait for theme hydration');
  });

  await t.test('Common components consume dynamic useAppTheme', () => {
    const components = [
      '../src/components/common/Card.tsx',
      '../src/components/common/Button.tsx',
      '../src/components/common/Badge.tsx',
      '../src/components/common/Header.tsx',
      '../src/components/common/ConfirmDialog.tsx',
    ];

    for (const compRel of components) {
      const compPath = path.resolve(__dirname, compRel);
      assert.ok(fs.existsSync(compPath), `${compRel} must exist`);
      const content = fs.readFileSync(compPath, 'utf8');
      assert.ok(
        content.includes('useAppTheme'),
        `${compRel} must consume dynamic colors via useAppTheme`
      );
    }
  });

  await t.test('Root and Tab Layouts adapt to dynamic theme', () => {
    const rootLayoutPath = path.resolve(__dirname, '../app/_layout.tsx');
    const tabLayoutPath = path.resolve(__dirname, '../app/(tabs)/_layout.tsx');

    const rootContent = fs.readFileSync(rootLayoutPath, 'utf8');
    assert.ok(rootContent.includes('useAppTheme'), 'Root layout must use useAppTheme');
    assert.ok(rootContent.includes('initializeTheme'), 'Root layout must initialize theme');
    assert.ok(rootContent.includes("StatusBar style={isDark ? 'light' : 'dark'}"), 'StatusBar must dynamically switch style');

    const tabContent = fs.readFileSync(tabLayoutPath, 'utf8');
    assert.ok(tabContent.includes('useAppTheme'), 'Tab layout must use useAppTheme');
    assert.ok(tabContent.includes('tabBarStyle'), 'Tab layout must style tabBarStyle dynamically');
  });

  await t.test('Settings screen provides appearance switcher', () => {
    const settingsPath = path.resolve(__dirname, '../app/profile/settings.tsx');
    assert.ok(fs.existsSync(settingsPath), 'settings.tsx must exist');

    const content = fs.readFileSync(settingsPath, 'utf8');
    assert.ok(content.includes('Appearance'), 'Settings must have Appearance section');
    assert.ok(content.includes('themePreference'), 'Settings must read themePreference');
    assert.ok(content.includes('setThemePreference'), 'Settings must call setThemePreference');
    assert.ok(content.includes('useAppTheme'), 'Settings must consume useAppTheme');
  });
});
