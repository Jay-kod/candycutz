import React, { useEffect, useRef } from 'react';
import { Animated, Image, StyleSheet, Text, View } from 'react-native';
import { COLORS, FONTS, SPACING } from '../../constants/theme';

export function AppPreloader() {
  const opacity = useRef(new Animated.Value(0)).current;
  const scale = useRef(new Animated.Value(0.94)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(opacity, { toValue: 1, duration: 450, useNativeDriver: true }),
      Animated.spring(scale, { toValue: 1, friction: 8, tension: 50, useNativeDriver: true }),
    ]).start();
  }, [opacity, scale]);

  return (
    <View style={styles.container}>
      <Animated.View style={[styles.content, { opacity, transform: [{ scale }] }]}>
        <View style={styles.iconFrame}>
          <Image source={require('../../../assets/icon.png')} style={styles.icon} resizeMode="contain" />
        </View>
        <Text style={styles.brand}>Candy<Text style={styles.brandAccent}>Cutz</Text></Text>
        <Text style={styles.subtitle}>LUXURY GROOMING, KEFFI</Text>
        <View style={styles.progressTrack}>
          <View style={styles.progressFill} />
        </View>
      </Animated.View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: COLORS.background },
  content: { alignItems: 'center' },
  iconFrame: { width: 92, height: 92, borderRadius: 26, alignItems: 'center', justifyContent: 'center', backgroundColor: COLORS.surfaceElevated, borderWidth: 1, borderColor: COLORS.primaryGlow },
  icon: { width: 64, height: 64 },
  brand: { marginTop: SPACING.lg, color: COLORS.textPrimary, fontSize: FONTS.sizes.xxl, fontWeight: '800' },
  brandAccent: { color: COLORS.primary },
  subtitle: { marginTop: SPACING.sm, color: COLORS.textMuted, fontSize: 10, fontWeight: '700', letterSpacing: 2.2 },
  progressTrack: { width: 120, height: 3, marginTop: SPACING.xl, overflow: 'hidden', borderRadius: 2, backgroundColor: COLORS.border },
  progressFill: { width: '55%', height: '100%', backgroundColor: COLORS.primary },
});
