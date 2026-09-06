import React from 'react';
import { StyleSheet, Text, TextStyle, View, ViewStyle } from 'react-native';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { AppointmentStatus } from '../../types';

interface BadgeProps {
  status: AppointmentStatus | string;
  label?: string;
  size?: 'sm' | 'md';
}

export const Badge: React.FC<BadgeProps> = ({ status, label, size = 'sm' }) => {
  const getBadgeColors = () => {
    switch (status.toLowerCase()) {
      case 'confirmed':
        return { bg: COLORS.primaryLight, text: COLORS.primary, border: COLORS.primary };
      case 'in_progress':
        return { bg: COLORS.infoLight, text: COLORS.info, border: COLORS.info };
      case 'completed':
        return { bg: COLORS.successLight, text: COLORS.success, border: COLORS.success };
      case 'cancelled':
      case 'no_show':
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
    textTransform: 'uppercase',
  };

  return (
    <View style={containerStyle}>
      <Text style={textStyle}>{displayLabel}</Text>
    </View>
  );
};
