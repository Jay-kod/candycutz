import { useEffect, useRef } from 'react';
import { Animated, Easing } from 'react-native';

/**
 * Returns an Animated.Value that fades content in (opacity 0 → 1)
 * when `isLoading` transitions from true → false.
 * While loading, content stays at opacity 0.
 */
export function useSkeletonTransition(isLoading: boolean, duration = 280) {
  const opacity = useRef(new Animated.Value(isLoading ? 0 : 1)).current;

  useEffect(() => {
    if (isLoading) {
      opacity.setValue(0);
    } else {
      Animated.timing(opacity, {
        toValue: 1,
        duration,
        easing: Easing.out(Easing.cubic),
        useNativeDriver: true,
      }).start();
    }
  }, [isLoading, opacity, duration]);

  return opacity;
}
