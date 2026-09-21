import React from 'react';
import { StyleProp, TouchableOpacity, View, ViewStyle } from 'react-native';
import { RADIUS, SPACING } from '../../constants/theme';
import { useAppTheme } from '../../hooks/useAppTheme';

interface CardProps {
  children: React.ReactNode;
  style?: StyleProp<ViewStyle>;
  onPress?: () => void;
  elevated?: boolean;
}

export const Card: React.FC<CardProps> = ({
  children,
  style,
  onPress,
  elevated = false,
}) => {
  const { colors } = useAppTheme();

  const containerStyle: ViewStyle = {
    backgroundColor: elevated ? colors.surfaceElevated : colors.surface,
    borderRadius: RADIUS.lg,
    borderWidth: 1,
    borderColor: colors.border,
    padding: SPACING.md,
    overflow: 'hidden',
  };

  if (onPress) {
    return (
      <TouchableOpacity
        activeOpacity={0.85}
        onPress={onPress}
        style={[containerStyle, style]}
      >
        {children}
      </TouchableOpacity>
    );
  }

  return <View style={[containerStyle, style]}>{children}</View>;
};
