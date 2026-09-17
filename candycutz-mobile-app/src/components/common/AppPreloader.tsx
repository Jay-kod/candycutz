import React, { useEffect, useRef } from 'react';
import { Animated, Easing, Image, StyleSheet, View } from 'react-native';
import { COLORS } from '../../constants/theme';

export function AppPreloader() {
  const clockwiseRotation = useRef(new Animated.Value(0)).current;
  const counterClockwiseRotation = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    const clockwise = Animated.loop(
      Animated.timing(clockwiseRotation, {
        toValue: 1,
        duration: 1500,
        easing: Easing.linear,
        useNativeDriver: true,
      })
    );
    const counterClockwise = Animated.loop(
      Animated.timing(counterClockwiseRotation, {
        toValue: 1,
        duration: 2100,
        easing: Easing.linear,
        useNativeDriver: true,
      })
    );

    clockwise.start();
    counterClockwise.start();

    return () => {
      clockwise.stop();
      counterClockwise.stop();
    };
  }, [clockwiseRotation, counterClockwiseRotation]);

  const clockwiseTransform = clockwiseRotation.interpolate({
    inputRange: [0, 1],
    outputRange: ['0deg', '360deg'],
  });
  const counterClockwiseTransform = counterClockwiseRotation.interpolate({
    inputRange: [0, 1],
    outputRange: ['0deg', '-360deg'],
  });

  return (
    <View style={styles.container}>
      <View style={styles.loader}>
        <Animated.View style={[styles.outerRing, { transform: [{ rotate: clockwiseTransform }] }]} />
        <Animated.View style={[styles.innerRing, { transform: [{ rotate: counterClockwiseTransform }] }]} />
        <Image source={require('../../../assets/icon.png')} style={styles.icon} resizeMode="contain" />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: COLORS.background },
  loader: { width: 132, height: 132, alignItems: 'center', justifyContent: 'center' },
  outerRing: { position: 'absolute', width: 132, height: 132, borderRadius: 66, borderWidth: 2, borderTopColor: COLORS.primary, borderRightColor: COLORS.primary, borderBottomColor: 'transparent', borderLeftColor: 'transparent' },
  innerRing: { position: 'absolute', width: 108, height: 108, borderRadius: 54, borderWidth: 2, borderTopColor: COLORS.accent, borderLeftColor: COLORS.accent, borderBottomColor: 'transparent', borderRightColor: 'transparent' },
  icon: { width: 62, height: 62 },
});
