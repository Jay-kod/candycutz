import React, { useEffect, useRef } from 'react';
import { Animated, Easing, StyleSheet, Text, View } from 'react-native';
import { FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useAppTheme } from '../../hooks/useAppTheme';

interface LoadingStateProps {
  message?: string;
  compact?: boolean;
}

export function LoadingState({ message = 'Preparing your experience', compact = false }: LoadingStateProps) {
  const { colors } = useAppTheme();
  const rotation = useRef(new Animated.Value(0)).current;
  const pulse = useRef(new Animated.Value(0.7)).current;

  useEffect(() => {
    const spin = Animated.loop(
      Animated.timing(rotation, {
        toValue: 1,
        duration: 1500,
        easing: Easing.linear,
        useNativeDriver: true,
      })
    );
    const breathe = Animated.loop(
      Animated.sequence([
        Animated.timing(pulse, { toValue: 1, duration: 850, useNativeDriver: true }),
        Animated.timing(pulse, { toValue: 0.7, duration: 850, useNativeDriver: true }),
      ])
    );

    spin.start();
    breathe.start();
    return () => {
      spin.stop();
      breathe.stop();
    };
  }, [pulse, rotation]);

  const spinStyle = {
    transform: [
      {
        rotate: rotation.interpolate({
          inputRange: [0, 1],
          outputRange: ['0deg', '360deg'],
        }),
      },
    ],
  };

  return (
    <View style={[styles.container, { backgroundColor: colors.background }, compact && styles.compactContainer]} accessibilityRole="progressbar">
      <View style={[styles.loader, { backgroundColor: colors.surface, borderColor: colors.border }, compact && styles.compactLoader]}>
        <Animated.View style={[styles.arc, { borderColor: colors.primary, borderRightColor: 'transparent', borderBottomColor: 'transparent' }, compact && styles.compactArc, spinStyle]} />
        <Animated.View style={[styles.core, { backgroundColor: colors.primaryLight, opacity: pulse }, compact && styles.compactCore]}>
          <Text style={[styles.mark, { color: colors.primary }, compact && styles.compactMark]}>CC</Text>
        </Animated.View>
      </View>
      {!!message && <Text style={[styles.message, { color: colors.textSecondary }, compact && styles.compactMessage]}>{message}</Text>}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: SPACING.xl,
  },
  compactContainer: {
    flex: 0,
    paddingVertical: SPACING.md,
  },
  loader: {
    width: 76,
    height: 76,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: RADIUS.full,
    borderWidth: 1,
  },
  compactLoader: {
    width: 48,
    height: 48,
  },
  arc: {
    position: 'absolute',
    width: 76,
    height: 76,
    borderRadius: RADIUS.full,
    borderWidth: 3,
    borderRightColor: 'transparent',
    borderBottomColor: 'transparent',
  },
  compactArc: {
    width: 48,
    height: 48,
    borderWidth: 2,
  },
  core: {
    width: 48,
    height: 48,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: RADIUS.full,
  },
  compactCore: {
    width: 30,
    height: 30,
  },
  mark: {
    fontSize: FONTS.sizes.sm,
    fontWeight: '900',
    letterSpacing: 1,
  },
  compactMark: {
    fontSize: FONTS.sizes.xs,
  },
  message: {
    marginTop: SPACING.md,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
    textAlign: 'center',
  },
  compactMessage: {
    marginTop: SPACING.sm,
    fontSize: FONTS.sizes.xs,
  },
});