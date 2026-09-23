import React, { useEffect, useRef, useState } from 'react';
import { Animated, Easing, Image, StyleSheet, View } from 'react-native';
import { usePathname } from 'expo-router';
import { RADIUS } from '../../constants/theme';
import { useAppTheme } from '../../hooks/useAppTheme';

const TRANSITION_DURATION = 420;

export function NavigationLoadingOverlay() {
  const { colors } = useAppTheme();
  const pathname = usePathname();
  const [visible, setVisible] = useState(false);
  const opacity = useRef(new Animated.Value(0)).current;
  const progress = useRef(new Animated.Value(0)).current;
  const badgePulse = useRef(new Animated.Value(0.5)).current;
  const hasMounted = useRef(false);
  const pulseRef = useRef<Animated.CompositeAnimation | null>(null);

  useEffect(() => {
    if (!hasMounted.current) {
      hasMounted.current = true;
      return;
    }

    setVisible(true);
    opacity.stopAnimation();
    progress.stopAnimation();
    opacity.setValue(1);
    progress.setValue(0);
    badgePulse.setValue(0.5);

    // Start the pulsing glow on the badge
    const pulse = Animated.loop(
      Animated.sequence([
        Animated.timing(badgePulse, {
          toValue: 1,
          duration: 600,
          easing: Easing.inOut(Easing.ease),
          useNativeDriver: true,
        }),
        Animated.timing(badgePulse, {
          toValue: 0.5,
          duration: 600,
          easing: Easing.inOut(Easing.ease),
          useNativeDriver: true,
        }),
      ])
    );
    pulseRef.current = pulse;
    pulse.start();

    Animated.parallel([
      Animated.timing(progress, {
        toValue: 0.78,
        duration: TRANSITION_DURATION * 0.7,
        easing: Easing.out(Easing.cubic),
        useNativeDriver: true,
      }),
      Animated.sequence([
        Animated.delay(TRANSITION_DURATION * 0.65),
        Animated.timing(progress, {
          toValue: 1,
          duration: TRANSITION_DURATION * 0.35,
          easing: Easing.out(Easing.cubic),
          useNativeDriver: true,
        }),
        Animated.timing(opacity, {
          toValue: 0,
          duration: 160,
          useNativeDriver: true,
        }),
      ]),
    ]).start(({ finished }) => {
      if (finished) {
        pulseRef.current?.stop();
        setVisible(false);
      }
    });
  }, [opacity, pathname, progress, badgePulse]);

  if (!visible) return null;

  return (
    <Animated.View pointerEvents="none" style={[styles.overlay, { opacity }]}>
      <View style={[styles.track, { backgroundColor: colors.border }]}>
        <Animated.View style={[styles.progress, { backgroundColor: colors.primary, transform: [{ scaleX: progress }] }]} />
      </View>
      <Animated.View
        style={[
          styles.badge,
          {
            backgroundColor: colors.surfaceElevated,
            borderColor: colors.primary,
            shadowColor: colors.primary,
            opacity: badgePulse,
          },
        ]}
      >
        <Image
          source={require('../../../assets/favicon.png')}
          style={styles.badgeIcon}
          resizeMode="contain"
        />
      </Animated.View>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  overlay: {
    ...StyleSheet.absoluteFill as object,
    zIndex: 100,
  },
  track: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    height: 3,
    overflow: 'hidden',
  },
  progress: {
    width: '100%',
    height: '100%',
    transformOrigin: 'left',
  },
  badge: {
    position: 'absolute',
    top: 16,
    right: 16,
    width: 38,
    height: 38,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: RADIUS.full,
    borderWidth: 1,
    shadowOpacity: 0.35,
    shadowRadius: 10,
    shadowOffset: { width: 0, height: 0 },
    elevation: 6,
    overflow: 'hidden',
  },
  badgeIcon: {
    width: 28,
    height: 28,
    borderRadius: 14,
  },
});