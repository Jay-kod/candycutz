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

const PARTICLE_COUNT = 8;

/** A single gold sparkle that drifts upward and fades */
function GoldParticle({ delay, offsetX }: { delay: number; offsetX: number }) {
  const opacity = useRef(new Animated.Value(0)).current;
  const translateY = useRef(new Animated.Value(0)).current;
  const translateX = useRef(new Animated.Value(0)).current;
  const scale = useRef(new Animated.Value(0)).current;
  const size = useRef(2 + Math.random() * 3).current;

  useEffect(() => {
    const drift = offsetX + (Math.random() - 0.5) * 30;

    Animated.sequence([
      Animated.delay(delay),
      Animated.parallel([
        Animated.sequence([
          Animated.timing(opacity, { toValue: 0.85, duration: 350, useNativeDriver: true }),
          Animated.timing(opacity, { toValue: 0, duration: 500, useNativeDriver: true }),
        ]),
        Animated.timing(translateY, {
          toValue: -70 - Math.random() * 40,
          duration: 850,
          easing: Easing.out(Easing.cubic),
          useNativeDriver: true,
        }),
        Animated.timing(translateX, {
          toValue: drift,
          duration: 850,
          easing: Easing.out(Easing.cubic),
          useNativeDriver: true,
        }),
        Animated.sequence([
          Animated.spring(scale, { toValue: 1, friction: 5, tension: 100, useNativeDriver: true }),
          Animated.timing(scale, { toValue: 0, duration: 350, useNativeDriver: true }),
        ]),
      ]),
    ]).start();
  }, [delay, offsetX, opacity, translateY, translateX, scale]);

  return (
    <Animated.View
      style={{
        position: 'absolute',
        width: size,
        height: size,
        borderRadius: size / 2,
        backgroundColor: '#D4AF37',
        opacity,
        transform: [{ translateX }, { translateY }, { scale }],
        shadowColor: '#D4AF37',
        shadowOffset: { width: 0, height: 0 },
        shadowOpacity: 1,
        shadowRadius: 4,
      }}
    />
  );
}

export function LogoutTransitionOverlay() {
  const router = useRouter();
  const isLoggingOut = useAuthStore((state) => state.isLoggingOut);
  const finishLogout = useAuthStore((state) => state.finishLogout);

  const [visible, setVisible] = useState(false);

  // Core animation values (preserved from original)
  const backdropOpacity = useRef(new Animated.Value(0)).current;
  const contentScale = useRef(new Animated.Value(0.85)).current;
  const contentOpacity = useRef(new Animated.Value(0)).current;
  const progressBar = useRef(new Animated.Value(0)).current;
  const haloPulse = useRef(new Animated.Value(1)).current;

  // Enhanced animation values
  const scissorsRock = useRef(new Animated.Value(0)).current;
  const titleOpacity = useRef(new Animated.Value(0)).current;
  const subtitleOpacity = useRef(new Animated.Value(0)).current;
  const messageOpacity = useRef(new Animated.Value(0)).current;
  const shimmerTranslate = useRef(new Animated.Value(0)).current;
  const outerGlowOpacity = useRef(new Animated.Value(0)).current;

  // Track running loops so we can stop them reliably
  const loopsRef = useRef<Animated.CompositeAnimation[]>([]);

  useEffect(() => {
    if (isLoggingOut) {
      setVisible(true);

      // Reset all values
      backdropOpacity.setValue(0);
      contentScale.setValue(0.85);
      contentOpacity.setValue(0);
      progressBar.setValue(0);
      haloPulse.setValue(1);
      scissorsRock.setValue(0);
      titleOpacity.setValue(0);
      subtitleOpacity.setValue(0);
      messageOpacity.setValue(0);
      shimmerTranslate.setValue(0);
      outerGlowOpacity.setValue(0);

      // ─── Ambient loops ───

      // Halo ring breathes
      const pulseLoop = Animated.loop(
        Animated.sequence([
          Animated.timing(haloPulse, {
            toValue: 1.18,
            duration: 700,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
          Animated.timing(haloPulse, {
            toValue: 1,
            duration: 700,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
        ])
      );

      // Scissors gentle rocking
      const scissorsLoop = Animated.loop(
        Animated.sequence([
          Animated.timing(scissorsRock, {
            toValue: 1,
            duration: 280,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
          Animated.timing(scissorsRock, {
            toValue: -1,
            duration: 280,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
          Animated.timing(scissorsRock, {
            toValue: 0,
            duration: 200,
            easing: Easing.out(Easing.ease),
            useNativeDriver: true,
          }),
          Animated.delay(500),
        ])
      );

      // Shimmer highlight sweep across progress bar
      const shimmerLoop = Animated.loop(
        Animated.sequence([
          Animated.timing(shimmerTranslate, {
            toValue: 1,
            duration: 1100,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
          Animated.delay(200),
          Animated.timing(shimmerTranslate, {
            toValue: 0,
            duration: 0,
            useNativeDriver: true,
          }),
        ])
      );

      // Outer glow pulse
      const glowLoop = Animated.loop(
        Animated.sequence([
          Animated.timing(outerGlowOpacity, {
            toValue: 0.6,
            duration: 900,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
          Animated.timing(outerGlowOpacity, {
            toValue: 0.15,
            duration: 900,
            easing: Easing.inOut(Easing.ease),
            useNativeDriver: true,
          }),
        ])
      );

      const loops = [pulseLoop, scissorsLoop, shimmerLoop, glowLoop];
      loopsRef.current = loops;
      loops.forEach((l) => l.start());

      // ─── Main entrance sequence ───
      Animated.parallel([
        Animated.timing(backdropOpacity, {
          toValue: 1,
          duration: 350,
          easing: Easing.out(Easing.cubic),
          useNativeDriver: true,
        }),
        Animated.spring(contentScale, {
          toValue: 1,
          friction: 7,
          tension: 55,
          useNativeDriver: true,
        }),
        Animated.timing(contentOpacity, {
          toValue: 1,
          duration: 400,
          useNativeDriver: true,
        }),
        // Staggered text appearance
        Animated.sequence([
          Animated.delay(220),
          Animated.timing(titleOpacity, {
            toValue: 1,
            duration: 280,
            easing: Easing.out(Easing.quad),
            useNativeDriver: true,
          }),
        ]),
        Animated.sequence([
          Animated.delay(380),
          Animated.timing(subtitleOpacity, {
            toValue: 1,
            duration: 280,
            easing: Easing.out(Easing.quad),
            useNativeDriver: true,
          }),
        ]),
        Animated.sequence([
          Animated.delay(520),
          Animated.timing(messageOpacity, {
            toValue: 1,
            duration: 280,
            easing: Easing.out(Easing.quad),
            useNativeDriver: true,
          }),
        ]),
        // Progress bar fill — slightly longer for premium feel
        Animated.timing(progressBar, {
          toValue: 1,
          duration: 1100,
          easing: Easing.bezier(0.25, 0.1, 0.25, 1),
          useNativeDriver: false,
        }),
      ]).start(() => {
        // Stop all ambient loops
        loops.forEach((l) => l.stop());
        loopsRef.current = [];

        // Navigate cleanly to /auth/login
        router.replace('/auth/login');

        // Smooth exit dissolve into the login page
        Animated.timing(backdropOpacity, {
          toValue: 0,
          duration: 400,
          delay: 180,
          easing: Easing.out(Easing.quad),
          useNativeDriver: true,
        }).start(() => {
          setVisible(false);
          finishLogout();
        });
      });
    }

    return () => {
      // Cleanup on unmount
      loopsRef.current.forEach((l) => l.stop());
      loopsRef.current = [];
    };
  }, [isLoggingOut, backdropOpacity, contentScale, contentOpacity, progressBar, haloPulse, finishLogout, router]);

  if (!visible) return null;

  const progressWidth = progressBar.interpolate({
    inputRange: [0, 1],
    outputRange: ['0%', '100%'],
  });

  const scissorsRotate = scissorsRock.interpolate({
    inputRange: [-1, 0, 1],
    outputRange: ['-12deg', '0deg', '12deg'],
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
        {/* Outer Diffuse Glow */}
        <Animated.View
          style={[
            styles.outerGlow,
            {
              opacity: outerGlowOpacity,
              transform: [{ scale: haloPulse }],
            },
          ]}
        />

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
          <Animated.View
            style={[
              styles.scissorsIconBadge,
              { transform: [{ rotate: scissorsRotate }] },
            ]}
          >
            <UIcon name="scissors" size={18} color="#D4AF37" />
          </Animated.View>
        </View>

        {/* Gold Sparkle Particles */}
        <View style={styles.particleField}>
          {Array.from({ length: PARTICLE_COUNT }).map((_, i) => (
            <GoldParticle
              key={i}
              delay={250 + i * 90}
              offsetX={(i - PARTICLE_COUNT / 2) * 8}
            />
          ))}
        </View>

        {/* Staggered Text Details */}
        <Animated.Text style={[styles.title, { opacity: titleOpacity }]}>
          LOGGING OUT
        </Animated.Text>
        <Animated.Text style={[styles.brandSubtitle, { opacity: subtitleOpacity }]}>
          CANDYCUTZ LUXURY GROOMING
        </Animated.Text>
        <Animated.Text style={[styles.message, { opacity: messageOpacity }]}>
          Securing your session. See you soon!
        </Animated.Text>

        {/* Premium Shimmering Progress Bar */}
        <View style={styles.progressBarTrack}>
          <Animated.View
            style={[
              styles.progressBarFill,
              {
                width: progressWidth,
              },
            ]}
          />
          <Animated.View
            style={[
              styles.shimmerHighlight,
              {
                transform: [
                  {
                    translateX: shimmerTranslate.interpolate({
                      inputRange: [0, 1],
                      outputRange: [-50, 230],
                    }),
                  },
                ],
              },
            ]}
          />
        </View>

        {/* Decorative Accent Dots */}
        <Animated.View style={[styles.accentRow, { opacity: messageOpacity }]}>
          <View style={[styles.accentDot, { opacity: 0.3 }]} />
          <View style={[styles.accentDot, { opacity: 0.55, width: 5, height: 5, borderRadius: 2.5 }]} />
          <View style={[styles.accentDot, { opacity: 0.3 }]} />
        </Animated.View>
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
  outerGlow: {
    position: 'absolute',
    width: 190,
    height: 190,
    borderRadius: 95,
    backgroundColor: 'rgba(212, 175, 55, 0.06)',
    top: -50,
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
  particleField: {
    position: 'absolute',
    top: 20,
    alignItems: 'center',
    justifyContent: 'center',
    width: 80,
    height: 40,
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
  shimmerHighlight: {
    position: 'absolute',
    top: 0,
    width: 40,
    height: '100%',
    backgroundColor: 'rgba(255, 242, 178, 0.35)',
    borderRadius: RADIUS.full,
  },
  accentRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginTop: SPACING.md,
  },
  accentDot: {
    width: 3,
    height: 3,
    borderRadius: 1.5,
    backgroundColor: '#D4AF37',
  },
});
