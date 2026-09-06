import React from 'react';
import { Text, TextStyle, View, ViewStyle } from 'react-native';
import { COLORS, FONTS, RADIUS } from '../../constants/theme';
import { AppointmentStatus, ChairStatus } from '../../types';

interface BadgeProps {
  status: AppointmentStatus | ChairStatus | string;
  label?: string;
  size?: 'sm' | 'md';
}

export const Badge: React.FC<BadgeProps> = ({ status, label, size = 'sm' }) => {
  const getBadgeColors = () => {
    switch (status.toLowerCase()) {
      case 'confirmed':
      case 'free':
        return { bg: COLORS.successLight, text: COLORS.success, border: COLORS.success };
      case 'in_progress':
      case 'busy':
        return { bg: COLORS.primaryLight, text: COLORS.primary, border: COLORS.primary };
      case 'break':
        return { bg: COLORS.warningLight, text: COLORS.warning, border: COLORS.warning };
      case 'cancelled':
      case 'no_show':
      case 'offline':
        return { bg: COLORS.errorLight, text: COLORS.error, border: COLORS.error };
      case 'pending':
      default:
        return { bg: COLORS.warningLight, text: COLORS.warning, border: COLORS.warning };
    }
  };

  const colors = getBadgeColors();
  const displayLabel = label || status.replace('_', ' ').toUpperCase();

  const containerStyle: ViewStyle = {
    backgroundColor: colors.bg,
    borderColor: colors.border,
    borderWidth: 1,
    borderRadius: RADIUS.full,
    paddingHorizontal: size === 'sm' ? 8 : 12,
    paddingVertical: size === 'sm' ? 3 : 5,
    alignSelf: 'flex-start',
  };

  const textStyle: TextStyle = {
    color: colors.text,
    fontSize: size === 'sm' ? FONTS.sizes.xs : FONTS.sizes.sm,
    fontWeight: '700',
    letterSpacing: 0.5,
  };

  return (
    <View style={containerStyle}>
      <Text style={textStyle}>{displayLabel}</Text>
    </View>
  );
};
