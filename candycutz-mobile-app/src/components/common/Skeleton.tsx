import React, { useEffect, useRef } from 'react';
import { Animated, StyleProp, StyleSheet, View, ViewStyle } from 'react-native';
import { COLORS, RADIUS, SPACING } from '../../constants/theme';

export function Skeleton({ style }: { style?: StyleProp<ViewStyle> }) {
  const opacity = useRef(new Animated.Value(0.45)).current;

  useEffect(() => {
    const animation = Animated.loop(
      Animated.sequence([
        Animated.timing(opacity, { toValue: 0.9, duration: 650, useNativeDriver: true }),
        Animated.timing(opacity, { toValue: 0.45, duration: 650, useNativeDriver: true }),
      ])
    );
    animation.start();
    return () => animation.stop();
  }, [opacity]);

  return <Animated.View style={[styles.block, style, { opacity }]} />;
}

export function ServiceSkeletons() {
  return (
    <View style={styles.grid}>
      {[1, 2, 3, 4].map((item) => (
        <View key={item} style={styles.serviceCard}>
          <Skeleton style={styles.price} />
          <Skeleton style={styles.title} />
          <Skeleton style={styles.line} />
          <Skeleton style={styles.lineShort} />
          <Skeleton style={styles.footer} />
        </View>
      ))}
    </View>
  );
}

export function BookingSkeletons() {
  return (
    <View style={styles.bookingList}>
      {[1, 2, 3].map((item) => (
        <View key={item} style={styles.bookingCard}>
          <View style={styles.row}>
            <View style={styles.flex}>
              <Skeleton style={styles.ref} />
              <Skeleton style={styles.bookingTitle} />
            </View>
            <Skeleton style={styles.badge} />
          </View>
          <Skeleton style={styles.line} />
          <Skeleton style={styles.lineShort} />
        </View>
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  block: { backgroundColor: COLORS.surfaceHighlight, borderRadius: RADIUS.sm },
  grid: { flexDirection: 'row', flexWrap: 'wrap', gap: SPACING.sm },
  serviceCard: { width: '48%', minHeight: 150, padding: SPACING.md, borderRadius: RADIUS.lg, backgroundColor: COLORS.surface, borderWidth: 1, borderColor: COLORS.border },
  price: { width: 62, height: 20, marginBottom: SPACING.md },
  title: { width: '80%', height: 18, marginBottom: SPACING.sm },
  line: { width: '100%', height: 11, marginBottom: SPACING.sm },
  lineShort: { width: '65%', height: 11 },
  footer: { width: '85%', height: 12, marginTop: SPACING.lg },
  bookingList: { padding: SPACING.md, gap: SPACING.md },
  bookingCard: { padding: SPACING.md, borderRadius: RADIUS.lg, backgroundColor: COLORS.surface, borderWidth: 1, borderColor: COLORS.border },
  row: { flexDirection: 'row', alignItems: 'flex-start' },
  flex: { flex: 1 },
  ref: { width: 100, height: 11, marginBottom: SPACING.sm },
  bookingTitle: { width: '75%', height: 18 },
  badge: { width: 70, height: 24, borderRadius: RADIUS.full },
});
