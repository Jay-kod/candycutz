import React from 'react';
import {
  ActivityIndicator,
  Text,
  TextStyle,
  TouchableOpacity,
  ViewStyle,
} from 'react-native';
import { FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useAppTheme } from '../../hooks/useAppTheme';

interface ButtonProps {
  title: string;
  onPress: () => void;
  variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
  size?: 'sm' | 'md' | 'lg';
  loading?: boolean;
  loadingTitle?: string;
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
  loadingTitle,
  disabled = false,
  style,
  textStyle,
  icon,
}) => {
  const { colors } = useAppTheme();

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
      base = { ...base, backgroundColor: colors.primary };
    } else if (variant === 'secondary') {
      base = {
        ...base,
        backgroundColor: colors.surfaceElevated,
        borderWidth: 1,
        borderColor: colors.border,
      };
    } else if (variant === 'outline') {
      base = {
        ...base,
        backgroundColor: 'transparent',
        borderWidth: 1,
        borderColor: colors.primary,
      };
    } else if (variant === 'ghost') {
      base = { ...base, backgroundColor: 'transparent' };
    } else if (variant === 'danger') {
      base = { ...base, backgroundColor: colors.error };
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

    if (variant === 'primary') base.color = colors.onPrimary;
    else if (variant === 'outline') base.color = colors.primary;
    else if (variant === 'ghost') base.color = colors.textSecondary;
    else base.color = colors.textPrimary;

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
        <>
          <ActivityIndicator color={variant === 'primary' ? colors.onPrimary : colors.primary} />
          {!!loadingTitle && (
            <Text style={[getTextStyle(), { marginLeft: SPACING.sm }, textStyle]}>
              {loadingTitle}
            </Text>
          )}
        </>
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
