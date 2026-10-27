import React from 'react';
import { Text, TextStyle, View, ViewStyle } from 'react-native';
import { FONTS, RADIUS } from '../../constants/theme';
import { useAppTheme } from '../../hooks/useAppTheme';
import { AppointmentStatus } from '../../types';

interface BadgeProps {
  status: AppointmentStatus | string;
  label?: string;
  size?: 'sm' | 'md';
}

export const Badge: React.FC<BadgeProps> = ({ status, label, size = 'sm' }) => {
  const { colors } = useAppTheme();

  const getBadgeColors = () => {
    switch (status.toLowerCase()) {
      case 'confirmed':
        return { bg: colors.primaryLight, text: colors.primary, border: colors.primary };
      case 'in_progress':
        return { bg: colors.infoLight, text: colors.info, border: colors.info };
      case 'completed':
        return { bg: colors.successLight, text: colors.success, border: colors.success };
      case 'cancelled':
      case 'no_show':
        return { bg: colors.errorLight, text: colors.error, border: colors.error };
      case 'pending':
      default:
        return { bg: colors.warningLight, text: colors.warning, border: colors.warning };
    }
  };

  const badgeColors = getBadgeColors();
  const displayLabel = label || status.replace('_', ' ').toUpperCase();

  const containerStyle: ViewStyle = {
    backgroundColor: badgeColors.bg,
    borderColor: badgeColors.border,
    borderWidth: 1,
    borderRadius: RADIUS.full,
    paddingHorizontal: size === 'sm' ? 8 : 12,
    paddingVertical: size === 'sm' ? 3 : 5,
    alignSelf: 'flex-start',
  };

  const textStyle: TextStyle = {
    color: badgeColors.text,
    fontSize: size === 'sm' ? FONTS.sizes.xs : FONTS.sizes.sm,
    fontWeight: '700',
    letterSpacing: 0.5,
    textTransform: 'uppercase',
  };

  return (
    <View style={containerStyle}>
      <Text style={textStyle}>{displayLabel}</Text>
    </View>
  );
};
