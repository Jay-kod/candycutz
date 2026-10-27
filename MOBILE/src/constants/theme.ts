export const DARK_COLORS = {
  // Brand Gold
  primary: '#D4AF37',
  primaryHover: '#C59F2D',
  primaryDark: '#A8841B',
  primaryLight: 'rgba(212, 175, 55, 0.15)',
  primaryGlow: 'rgba(212, 175, 55, 0.35)',
  accent: '#EBB95A',

  // Obsidian Dark Theme
  background: '#0A0A0C',
  surface: '#0A0A0E',
  surfaceElevated: '#0E0E13',
  surfaceHighlight: '#15151C',

  // Borders
  border: '#23232E',
  borderLight: '#323242',
  borderStrong: '#454556',

  // Text
  textPrimary: '#FFFFFF',
  textSecondary: '#9CA3AF',
  textMuted: '#6B7280',
  textGold: '#D4AF37',
  onPrimary: '#0A0A0C',
  inputBackground: '#0E0E13',
  scrim: 'rgba(0, 0, 0, 0.75)',
  patternDot: '#F3D477',
  patternDotGlow: '#A87922',
  skeletonBase: '#15151C',
  skeletonHighlight: '#242432',

  // Status & Feedback
  success: '#10B981',
  successLight: 'rgba(16, 185, 129, 0.15)',
  warning: '#F59E0B',
  warningLight: 'rgba(245, 158, 11, 0.15)',
  error: '#EF4444',
  errorLight: 'rgba(239, 68, 68, 0.15)',
  danger: '#EF4444',
  dangerLight: 'rgba(239, 68, 68, 0.15)',
  info: '#3B82F6',
  infoLight: 'rgba(59, 130, 246, 0.15)',

  // Overlays
  overlay: 'rgba(0, 0, 0, 0.75)',
};

export const LIGHT_COLORS: typeof DARK_COLORS = {
  // Porcelain, ink, and bronze light theme
  primary: '#9A6A16',
  primaryHover: '#80550E',
  primaryDark: '#65420B',
  primaryLight: 'rgba(154, 106, 22, 0.12)',
  primaryGlow: 'rgba(154, 106, 22, 0.22)',
  accent: '#C08A2E',

  // Cool pearl canvas with crisp white surfaces
  background: '#F2F4F7',
  surface: '#FFFFFF',
  surfaceElevated: '#F8FAFC',
  surfaceHighlight: '#E9EEF3',

  // Borders
  border: '#D8DEE6',
  borderLight: '#C6CFDA',
  borderStrong: '#AAB6C4',

  // Text
  textPrimary: '#17212B',
  textSecondary: '#536170',
  textMuted: '#7B8794',
  textGold: '#8A5F13',
  onPrimary: '#FFFFFF',
  inputBackground: '#FFFFFF',
  scrim: 'rgba(23, 33, 43, 0.28)',
  patternDot: '#25252B',
  patternDotGlow: '#3A3A43',
  skeletonBase: '#E9EEF3',
  skeletonHighlight: '#FFFFFF',

  // Status & Feedback
  success: '#059669',
  successLight: 'rgba(5, 150, 105, 0.12)',
  warning: '#D97706',
  warningLight: 'rgba(217, 119, 6, 0.12)',
  error: '#DC2626',
  errorLight: 'rgba(220, 38, 38, 0.12)',
  danger: '#DC2626',
  dangerLight: 'rgba(220, 38, 38, 0.12)',
  info: '#2563EB',
  infoLight: 'rgba(37, 99, 235, 0.12)',

  // Overlays
  overlay: 'rgba(23, 33, 43, 0.28)',
};

export type ThemeColors = typeof DARK_COLORS;

// Default export retained for backward compatibility
export const COLORS: ThemeColors = DARK_COLORS;

export const SPACING = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  xxl: 48,
};

export const RADIUS = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  pill: 9999,
  full: 9999,
};

export const FONTS = {
  regular: 'System',
  medium: 'System',
  bold: 'System',
  sizes: {
    xs: 12,
    sm: 14,
    md: 16,
    lg: 18,
    xl: 20,
    xxl: 26,
    hero: 32,
  },
};
