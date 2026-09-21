import React, { useEffect, useRef, useState } from 'react';
import {
  Animated,
  Easing,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useAuthStore } from '../../store/authStore';
import { BrandEmblem } from './BrandEmblem';
import { UIcon } from './UIcon';
import { FONTS, RADIUS, SPACING } from '../../constants/theme';

export function LogoutTransitionOverlay() {
  const router = useRouter();
  const isLoggingOut = useAuthStore((state) => state.isLoggingOut);
  const finishLogout = useAuthStore((state) => state.finishLogout);

  const [visible, setVisible] = useState(false);

  // Animation values
  const backdropOpacity = useRef(new Animated.Value(0)).current;
  const contentScale = useRef(new Animated.Value(0.85)).current;
  const contentOpacity = useRef(new Animated.Value(0)).current;
  const progressBar = useRef(new Animated.Value(0)).current;
  const haloPulse = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    if (isLoggingOut) {
      setVisible(true);

      // Reset values
      backdropOpacity.setValue(0);
      contentScale.setValue(0.85);
      contentOpacity.setValue(0);
      progressBar.setValue(0);
      haloPulse.setValue(1);

      // Pulsing halo loop
      const pulseLoop = Animated.loop(
        Animated.sequence([
          Animated.timing(haloPulse, {
            toValue: 1.15,
            duration: 600,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
          Animated.timing(haloPulse, {
            toValue: 1,
            duration: 600,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
        ])
      );
      pulseLoop.start();

      // Entrance animation sequence
      Animated.parallel([
        Animated.timing(backdropOpacity, {
          toValue: 1,
          duration: 300,
          easing: Easing.out(Easing.cubic),
          useNativeDriver: true,
        }),
        Animated.spring(contentScale, {
          toValue: 1,
          friction: 7,
          tension: 60,
          useNativeDriver: true,
        }),
        Animated.timing(contentOpacity, {
          toValue: 1,
          duration: 350,
          useNativeDriver: true,
        }),
        Animated.timing(progressBar, {
          toValue: 1,
          duration: 850,
          easing: Easing.inOut(Easing.quad),
          useNativeDriver: false,
        }),
      ]).start(() => {
        pulseLoop.stop();

        // Navigate cleanly to /auth/login
        router.replace('/auth/login');

        // Smooth exit dissolve into the login page
        Animated.timing(backdropOpacity, {
          toValue: 0,
          duration: 350,
          delay: 150,
          easing: Easing.out(Easing.quad),
          useNativeDriver: true,
        }).start(() => {
          setVisible(false);
          finishLogout();
        });
      });
    }
  }, [isLoggingOut, backdropOpacity, contentScale, contentOpacity, progressBar, haloPulse, finishLogout, router]);

  if (!visible) return null;

  const progressWidth = progressBar.interpolate({
    inputRange: [0, 1],
    outputRange: ['0%', '100%'],
  });

  return (
    <Animated.View
      style={[
        styles.overlay,
        {
          opacity: backdropOpacity,
        },
      ]}
      pointerEvents="auto"
    >
      <Animated.View
        style={[
          styles.contentContainer,
          {
            opacity: contentOpacity,
            transform: [{ scale: contentScale }],
          },
        ]}
      >
        {/* Luminous Glowing Halo Ring */}
        <Animated.View
          style={[
            styles.haloRing,
            {
              transform: [{ scale: haloPulse }],
            },
          ]}
        />

        {/* Central Brand Emblem */}
        <View style={styles.emblemWrapper}>
          <BrandEmblem size={90} showText={false} />
          <View style={styles.scissorsIconBadge}>
            <UIcon name="scissors" size={18} color="#D4AF37" />
          </View>
        </View>

        {/* Text Details */}
        <Text style={styles.title}>LOGGING OUT</Text>
        <Text style={styles.brandSubtitle}>CANDYCUTZ LUXURY GROOMING</Text>
        <Text style={styles.message}>Securing your session. See you soon!</Text>

        {/* Shimmering Progress Bar */}
        <View style={styles.progressBarTrack}>
          <Animated.View
            style={[
              styles.progressBarFill,
              {
                width: progressWidth,
              },
            ]}
          />
        </View>
      </Animated.View>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  overlay: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: 'rgba(10, 10, 12, 0.97)',
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 99999,
    elevation: 99999,
  },
  contentContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: SPACING.xl,
    width: '100%',
    maxWidth: 340,
  },
  haloRing: {
    position: 'absolute',
    width: 140,
    height: 140,
    borderRadius: 70,
    backgroundColor: 'rgba(212, 175, 55, 0.1)',
    borderWidth: 1.5,
    borderColor: 'rgba(212, 175, 55, 0.35)',
    top: -25,
  },
  emblemWrapper: {
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: SPACING.md,
    position: 'relative',
  },
  scissorsIconBadge: {
    position: 'absolute',
    bottom: -6,
    right: -6,
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#14141B',
    borderWidth: 1.5,
    borderColor: '#D4AF37',
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#D4AF37',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.5,
    shadowRadius: 6,
    elevation: 6,
  },
  title: {
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
    color: '#D4AF37',
    letterSpacing: 3,
    textAlign: 'center',
    marginTop: SPACING.xs,
  },
  brandSubtitle: {
    fontSize: 9,
    fontWeight: '700',
    color: 'rgba(255, 255, 255, 0.45)',
    letterSpacing: 2,
    marginTop: 4,
    textTransform: 'uppercase',
  },
  message: {
    fontSize: FONTS.sizes.sm,
    fontWeight: '500',
    color: 'rgba(255, 255, 255, 0.75)',
    textAlign: 'center',
    marginTop: SPACING.md,
    lineHeight: 20,
  },
  progressBarTrack: {
    width: 180,
    height: 4,
    borderRadius: RADIUS.full,
    backgroundColor: 'rgba(255, 255, 255, 0.1)',
    marginTop: SPACING.lg,
    overflow: 'hidden',
  },
  progressBarFill: {
    height: '100%',
    borderRadius: RADIUS.full,
    backgroundColor: '#D4AF37',
    shadowColor: '#D4AF37',
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.8,
    shadowRadius: 8,
  },
});
