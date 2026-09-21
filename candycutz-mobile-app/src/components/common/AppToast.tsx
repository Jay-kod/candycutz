import React, { useEffect, useRef, useCallback } from 'react';
import {
  Animated,
  PanResponder,
  Pressable,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {
  CheckCircle2,
  XCircle,
  AlertTriangle,
  Info,
  X,
} from 'lucide-react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useAppTheme } from '../../hooks/useAppTheme';
import { useToastStore, ToastVariant } from '../../store/toastStore';

/* ─── Variant visual config ─── */
const VARIANT_CONFIG: Record<
  ToastVariant,
  {
    icon: (color: string) => React.ReactNode;
    accentColor: string;
    bgTint: string;
    bgTintLight: string;
    defaultDuration: number;
  }
> = {
  success: {
    icon: (c) => <CheckCircle2 size={22} color={c} strokeWidth={2.4} />,
    accentColor: '#22C55E',
    bgTint: 'rgba(34, 197, 94, 0.12)',
    bgTintLight: 'rgba(34, 197, 94, 0.08)',
    defaultDuration: 3000,
  },
  error: {
    icon: (c) => <XCircle size={22} color={c} strokeWidth={2.4} />,
    accentColor: '#EF4444',
    bgTint: 'rgba(239, 68, 68, 0.12)',
    bgTintLight: 'rgba(239, 68, 68, 0.08)',
    defaultDuration: 5000,
  },
  warning: {
    icon: (c) => <AlertTriangle size={22} color={c} strokeWidth={2.4} />,
    accentColor: '#F59E0B',
    bgTint: 'rgba(245, 158, 11, 0.12)',
    bgTintLight: 'rgba(245, 158, 11, 0.08)',
    defaultDuration: 3000,
  },
  info: {
    icon: (c) => <Info size={22} color={c} strokeWidth={2.4} />,
    accentColor: '#3B82F6',
    bgTint: 'rgba(59, 130, 246, 0.12)',
    bgTintLight: 'rgba(59, 130, 246, 0.08)',
    defaultDuration: 3000,
  },
};

export function AppToast() {
  const insets = useSafeAreaInsets();
  const { colors, isDark } = useAppTheme();
  const current = useToastStore((s) => s.current);
  const dismiss = useToastStore((s) => s.dismiss);

  const slideY = useRef(new Animated.Value(-200)).current;
  const opacity = useRef(new Animated.Value(0)).current;
  const progressAnim = useRef(new Animated.Value(1)).current;
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const isVisible = useRef(false);

  const clearTimer = useCallback(() => {
    if (timerRef.current) {
      clearTimeout(timerRef.current);
      timerRef.current = null;
    }
  }, []);

  const animateOut = useCallback(() => {
    Animated.parallel([
      Animated.timing(slideY, {
        toValue: -200,
        duration: 250,
        useNativeDriver: true,
      }),
      Animated.timing(opacity, {
        toValue: 0,
        duration: 250,
        useNativeDriver: true,
      }),
    ]).start(() => {
      isVisible.current = false;
      dismiss();
    });
  }, [slideY, opacity, dismiss]);

  // Pan responder for swipe-up dismiss
  const panResponder = useRef(
    PanResponder.create({
      onStartShouldSetPanResponder: () => true,
      onMoveShouldSetPanResponder: (_, gestureState) =>
        Math.abs(gestureState.dy) > 5,
      onPanResponderMove: (_, gestureState) => {
        if (gestureState.dy < 0) {
          slideY.setValue(gestureState.dy);
        }
      },
      onPanResponderRelease: (_, gestureState) => {
        if (gestureState.dy < -30) {
          clearTimer();
          animateOut();
        } else {
          Animated.spring(slideY, {
            toValue: 0,
            friction: 8,
            tension: 65,
            useNativeDriver: true,
          }).start();
        }
      },
    })
  ).current;

  useEffect(() => {
    if (current) {
      const config = VARIANT_CONFIG[current.variant];
      const duration = current.duration ?? config.defaultDuration;

      // Reset and animate in
      slideY.setValue(-200);
      opacity.setValue(0);
      progressAnim.setValue(1);
      isVisible.current = true;

      Animated.parallel([
        Animated.spring(slideY, {
          toValue: 0,
          friction: 8,
          tension: 60,
          useNativeDriver: true,
        }),
        Animated.timing(opacity, {
          toValue: 1,
          duration: 200,
          useNativeDriver: true,
        }),
      ]).start();

      // Progress bar countdown
      Animated.timing(progressAnim, {
        toValue: 0,
        duration: duration,
        useNativeDriver: false,
      }).start();

      // Auto-dismiss timer
      clearTimer();
      timerRef.current = setTimeout(() => {
        if (isVisible.current) {
          animateOut();
        }
      }, duration);
    }

    return () => clearTimer();
  }, [current, slideY, opacity, progressAnim, clearTimer, animateOut]);

  if (!current) return null;

  const config = VARIANT_CONFIG[current.variant];

  return (
    <Animated.View
      {...panResponder.panHandlers}
      style={[
        styles.container,
        {
          top: insets.top + 8,
          opacity,
          transform: [{ translateY: slideY }],
        },
      ]}
      pointerEvents="box-none"
    >
      <View
        style={[
          styles.card,
          {
            backgroundColor: isDark
              ? 'rgba(18, 18, 23, 0.92)'
              : 'rgba(255, 255, 255, 0.95)',
            borderColor: isDark
              ? 'rgba(255, 255, 255, 0.08)'
              : 'rgba(0, 0, 0, 0.06)',
          },
        ]}
      >
        {/* Left accent strip */}
        <View
          style={[
            styles.accentStrip,
            { backgroundColor: config.accentColor },
          ]}
        />

        {/* Icon badge */}
        <View
          style={[
            styles.iconBadge,
            {
              backgroundColor: isDark ? config.bgTint : config.bgTintLight,
            },
          ]}
        >
          {config.icon(config.accentColor)}
        </View>

        {/* Text content */}
        <View style={styles.textContent}>
          <Text
            style={[styles.title, { color: colors.textPrimary }]}
            numberOfLines={1}
          >
            {current.title}
          </Text>
          {current.message ? (
            <Text
              style={[styles.message, { color: colors.textSecondary }]}
              numberOfLines={2}
            >
              {current.message}
            </Text>
          ) : null}
        </View>

        {/* Close button */}
        <Pressable
          onPress={() => {
            clearTimer();
            animateOut();
          }}
          style={styles.closeButton}
          hitSlop={12}
        >
          <X size={16} color={colors.textMuted} strokeWidth={2.2} />
        </Pressable>

        {/* Bottom progress bar */}
        <View style={styles.progressTrack}>
          <Animated.View
            style={[
              styles.progressFill,
              {
                backgroundColor: config.accentColor,
                width: progressAnim.interpolate({
                  inputRange: [0, 1],
                  outputRange: ['0%', '100%'],
                }),
              },
            ]}
          />
        </View>
      </View>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  container: {
    position: 'absolute',
    left: 16,
    right: 16,
    zIndex: 9999,
    elevation: 9999,
  },
  card: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 14,
    paddingLeft: 16,
    paddingRight: 12,
    borderRadius: 16,
    borderWidth: 1,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.18,
    shadowRadius: 16,
    elevation: 12,
    overflow: 'hidden',
  },
  accentStrip: {
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    width: 4,
    borderTopLeftRadius: 16,
    borderBottomLeftRadius: 16,
  },
  iconBadge: {
    width: 40,
    height: 40,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  textContent: {
    flex: 1,
    marginRight: 8,
  },
  title: {
    fontSize: 15,
    fontWeight: '700',
    letterSpacing: 0.1,
    lineHeight: 20,
  },
  message: {
    fontSize: 13,
    lineHeight: 18,
    marginTop: 2,
  },
  closeButton: {
    width: 28,
    height: 28,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  progressTrack: {
    position: 'absolute',
    bottom: 0,
    left: 4,
    right: 0,
    height: 2,
    backgroundColor: 'transparent',
    borderBottomLeftRadius: 16,
    borderBottomRightRadius: 16,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    borderRadius: 1,
  },
});
