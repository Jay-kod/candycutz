import { useColorScheme } from 'react-native';
import { DARK_COLORS, LIGHT_COLORS, ThemeColors } from '../constants/theme';
import { ThemePreference, useThemeStore } from '../store/themeStore';

export interface AppTheme {
  colors: ThemeColors;
  isDark: boolean;
  themePreference: ThemePreference;
  setThemePreference: (preference: ThemePreference) => Promise<void>;
}

export function useAppTheme(): AppTheme {
  const systemColorScheme = useColorScheme();
  const { themePreference, setThemePreference } = useThemeStore();

  const isDark =
    themePreference === 'dark'
      ? true
      : themePreference === 'light'
      ? false
      : systemColorScheme !== 'light';

  const colors = isDark ? DARK_COLORS : LIGHT_COLORS;

  return {
    colors,
    isDark,
    themePreference,
    setThemePreference,
  };
}
