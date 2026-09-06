import React from 'react';
import {
  ActivityIndicator,
  StyleSheet,
  Text,
  TextStyle,
  TouchableOpacity,
  ViewStyle,
} from 'react-native';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';

interface ButtonProps {
  title: string;
  onPress: () => void;
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  loading?: boolean;
  disabled?: boolean;
  style?: ViewStyle;
  textStyle?: TextStyle;
  icon?: React.ReactNode;
}

export const Button: React.FC<ButtonProps> = ({
  title,
  onPress,
  variant = 'primary',
  size = 'md',
  loading = false,
  disabled = false,
  style,
  textStyle,
  icon,
}) => {
  const getContainerStyle = (): ViewStyle => {
    let base: ViewStyle = {
      flexDirection: 'row',
      alignItems: 'center',
      justifyContent: 'center',
      borderRadius: RADIUS.md,
    };

    // Sizes
    if (size === 'sm') {
      base = { ...base, paddingVertical: 8, paddingHorizontal: 14 };
    } else if (size === 'lg') {
      base = { ...base, paddingVertical: 16, paddingHorizontal: 28 };
    } else {
      base = { ...base, paddingVertical: 12, paddingHorizontal: 20 };
    }

    // Variants
    if (variant === 'primary') {
      base = { ...base, backgroundColor: COLORS.primary };
    } else if (variant === 'secondary') {
      base = { ...base, backgroundColor: COLORS.surfaceElevated, borderWidth: 1, borderColor: COLORS.border };
    } else if (variant === 'outline') {
      base = { ...base, backgroundColor: 'transparent', borderWidth: 1, borderColor: COLORS.primary };
    } else if (variant === 'ghost') {
      base = { ...base, backgroundColor: 'transparent' };
    } else if (variant === 'danger') {
      base = { ...base, backgroundColor: COLORS.error };
    }

    if (disabled || loading) {
      base = { ...base, opacity: 0.5 };
    }

    return base;
  };

  const getTextStyle = (): TextStyle => {
    let base: TextStyle = {
      fontFamily: FONTS.bold,
      fontWeight: '700',
      textAlign: 'center',
    };

    if (size === 'sm') base.fontSize = FONTS.sizes.sm;
    else if (size === 'lg') base.fontSize = FONTS.sizes.lg;
    else base.fontSize = FONTS.sizes.md;

    if (variant === 'primary') base.color = '#0A0A0C';
    else if (variant === 'outline') base.color = COLORS.primary;
    else if (variant === 'ghost') base.color = COLORS.textSecondary;
    else base.color = COLORS.textPrimary;

    return base;
  };

  return (
    <TouchableOpacity
      activeOpacity={0.8}
      onPress={onPress}
      disabled={disabled || loading}
      style={[getContainerStyle(), style]}
    >
      {loading ? (
        <ActivityIndicator color={variant === 'primary' ? '#0A0A0C' : COLORS.primary} />
      ) : (
        <>
          {icon && <>{icon}</>}
          <Text style={[getTextStyle(), icon ? { marginLeft: 8 } : null, textStyle]}>
            {title}
          </Text>
        </>
      )}
    </TouchableOpacity>
  );
};
