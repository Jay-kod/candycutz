import React, { useEffect, useRef } from 'react';
import {
  Animated,
  Dimensions,
  Image,
  ImageBackground,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, FONTS } from '../../constants/theme';
import { getStorageUrl } from '../../constants/config';

interface SplashScreenViewProps {
  onAnimationComplete?: () => void;
  onReady?: () => void;
  isExiting?: boolean;
  backgroundImageUri?: string | null;
}

const { width, height } = Dimensions.get('window');

export function SplashScreenView({
  onAnimationComplete,
  onReady,
  isExiting = false,
  backgroundImageUri,
}: SplashScreenViewProps) {
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const scaleAnim = useRef(new Animated.Value(0.92)).current;
  const glowAnim = useRef(new Animated.Value(0.4)).current;
  const exitFade = useRef(new Animated.Value(1)).current;
  const taglineFade = useRef(new Animated.Value(0)).current;
  const taglineSlide = useRef(new Animated.Value(8)).current;

  useEffect(() => {
    // Entrance animation for logo
    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 900,
        useNativeDriver: true,
      }),
      Animated.spring(scaleAnim, {
        toValue: 1,
        friction: 7,
        tension: 40,
        useNativeDriver: true,
      }),
    ]).start(() => {
      onReady?.();
    });

    // Tagline fades in slightly after the logo
    Animated.sequence([
      Animated.delay(600),
      Animated.parallel([
        Animated.timing(taglineFade, {
          toValue: 1,
          duration: 700,
          useNativeDriver: true,
        }),
        Animated.timing(taglineSlide, {
          toValue: 0,
          duration: 700,
          useNativeDriver: true,
        }),
      ]),
    ]).start();

    // Subtle breathing/glow effect behind the logo
    const pulse = Animated.loop(
      Animated.sequence([
        Animated.timing(glowAnim, {
          toValue: 0.85,
          duration: 1200,
          useNativeDriver: true,
        }),
        Animated.timing(glowAnim, {
          toValue: 0.4,
          duration: 1200,
          useNativeDriver: true,
        }),
      ])
    );
    pulse.start();

    return () => {
      pulse.stop();
    };
  }, [fadeAnim, scaleAnim, glowAnim, taglineFade, taglineSlide]);

  useEffect(() => {
    if (isExiting) {
      Animated.timing(exitFade, {
        toValue: 0,
        duration: 450,
        useNativeDriver: true,
      }).start(() => {
        onAnimationComplete?.();
      });
    }
  }, [isExiting, exitFade, onAnimationComplete]);

  return (
    <Animated.View style={[styles.wrapper, { opacity: exitFade }]}>
      <ImageBackground
        source={
          backgroundImageUri
            ? { uri: getStorageUrl(backgroundImageUri) }
            : require('../../../assets/images/splash-bg.jpg')
        }
        style={styles.backgroundImage}
        resizeMode="cover"
      >
        {/* Obsidian vignette gradients */}
        <LinearGradient
          colors={['rgba(10, 10, 12, 0.75)', 'rgba(10, 10, 12, 0.45)', 'rgba(10, 10, 12, 0.9)']}
          style={StyleSheet.absoluteFill}
        />

        {/* Center glowing halo behind logo */}
        <Animated.View
          style={[
            styles.glowHalo,
            {
              opacity: glowAnim,
              transform: [{ scale: scaleAnim }],
            },
          ]}
        />

        {/* Animated CandyCutz Logo */}
        <Animated.View
          style={[
            styles.logoContainer,
            {
              opacity: fadeAnim,
              transform: [{ scale: scaleAnim }],
            },
          ]}
        >
          <Image
            source={require('../../../assets/icon.png')}
            style={styles.logoImage}
            resizeMode="contain"
          />
        </Animated.View>

        {/* Tagline */}
        <Animated.View
          style={[
            styles.taglineContainer,
            {
              opacity: taglineFade,
              transform: [{ translateY: taglineSlide }],
            },
          ]}
        >
          <Text style={styles.taglineText}>Fresh cuts, clean vibes…</Text>
        </Animated.View>
      </ImageBackground>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  wrapper: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    zIndex: 9999,
    backgroundColor: COLORS.background,
  },
  backgroundImage: {
    width,
    height,
    alignItems: 'center',
    justifyContent: 'center',
  },
  glowHalo: {
    position: 'absolute',
    width: 280,
    height: 280,
    borderRadius: 140,
    backgroundColor: 'rgba(212, 175, 55, 0.12)',
  },
  logoContainer: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoImage: {
    width: 160,
    height: 160,
    borderRadius: 80,
  },
  taglineContainer: {
    marginTop: 20,
    alignItems: 'center',
  },
  taglineText: {
    color: 'rgba(229, 186, 115, 0.85)',
    fontSize: 14,
    fontWeight: '300',
    letterSpacing: 2.5,
    fontFamily: FONTS.regular,
  },
});
