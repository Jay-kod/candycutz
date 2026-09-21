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
  surface: '#121217',
  surfaceElevated: '#1A1A22',
  surfaceHighlight: '#22222D',

  // Borders
  border: '#23232E',
  borderLight: '#323242',

  // Text
  textPrimary: '#FFFFFF',
  textSecondary: '#9CA3AF',
  textMuted: '#6B7280',
  textGold: '#D4AF37',

  // Status & Feedback
  success: '#10B981',
  successLight: 'rgba(16, 185, 129, 0.15)',
  warning: '#F59E0B',
  warningLight: 'rgba(245, 158, 11, 0.15)',
  error: '#EF4444',
  errorLight: 'rgba(239, 68, 68, 0.15)',
  info: '#3B82F6',
  infoLight: 'rgba(59, 130, 246, 0.15)',

  // Overlays
  overlay: 'rgba(0, 0, 0, 0.75)',
};

export const LIGHT_COLORS: typeof DARK_COLORS = {
  // Brand Gold (Deep Metallic Gold tuned for light background contrast)
  primary: '#C59B27',
  primaryHover: '#B3891F',
  primaryDark: '#946E13',
  primaryLight: 'rgba(197, 155, 39, 0.12)',
  primaryGlow: 'rgba(197, 155, 39, 0.25)',
  accent: '#D4AF37',

  // Alabaster Light Theme
  background: '#F8F8F6',
  surface: '#FFFFFF',
  surfaceElevated: '#F1F1F4',
  surfaceHighlight: '#E7E7EC',

  // Borders
  border: '#E4E4E7',
  borderLight: '#D4D4D8',

  // Text
  textPrimary: '#0F0F12',
  textSecondary: '#4B5563',
  textMuted: '#9CA3AF',
  textGold: '#C59B27',

  // Status & Feedback
  success: '#059669',
  successLight: 'rgba(5, 150, 105, 0.12)',
  warning: '#D97706',
  warningLight: 'rgba(217, 119, 6, 0.12)',
  error: '#DC2626',
  errorLight: 'rgba(220, 38, 38, 0.12)',
  info: '#2563EB',
  infoLight: 'rgba(37, 99, 235, 0.12)',

  // Overlays
  overlay: 'rgba(0, 0, 0, 0.5)',
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
