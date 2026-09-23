import React, { useEffect, useRef } from 'react';
import {
  Animated,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button } from './Button';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';

export type ErrorVariant =
  | 'network'
  | 'server'
  | 'notFound'
  | 'forbidden'
  | 'maintenance'
  | 'upgrade'
  | 'generic';

export interface ErrorScreenProps {
  variant?: ErrorVariant;
  title?: string;
  message?: string;
  errorCode?: string | number;
  onRetry?: () => void;
  onGoBack?: () => void;
  onGoHome?: () => void;
  showSupportLink?: boolean;
}

const VARIANT_CONFIG: Record<
  ErrorVariant,
  {
    icon: string;
    badge: string;
    defaultTitle: string;
    defaultMessage: string;
    accentColor: string;
  }
> = {
  network: {
    icon: '📡',
    badge: 'Offline',
    defaultTitle: 'No Connection',
    defaultMessage:
      'We cannot connect to the server right now. Please check your Wi-Fi or cellular data connection.',
    accentColor: '#F59E0B',
  },
  server: {
    icon: '⚙️',
    badge: 'Error 500',
    defaultTitle: 'Something Went Wrong',
    defaultMessage:
      'Our servers encountered an unexpected glitch. Our team has been notified. Please try again shortly.',
    accentColor: COLORS.danger,
  },
  notFound: {
    icon: '🔍',
    badge: 'Error 404',
    defaultTitle: 'Page Not Found',
    defaultMessage:
      'The screen you are looking for might have been moved, renamed, or is temporarily unavailable.',
    accentColor: COLORS.primary,
  },
  forbidden: {
    icon: '🔒',
    badge: 'Error 403',
    defaultTitle: 'Access Restricted',
    defaultMessage:
      'You do not have permission to view this section, or your session has expired. Please log in again.',
    accentColor: '#EF4444',
  },
  maintenance: {
    icon: '🛠️',
    badge: 'Maintenance',
    defaultTitle: 'Under Maintenance',
    defaultMessage:
      'CandyCutz is undergoing scheduled maintenance to upgrade your experience. We will be back shortly!',
    accentColor: '#8B5CF6',
  },
  upgrade: {
    icon: '🚀',
    badge: 'Update Required',
    defaultTitle: 'Update Required',
    defaultMessage:
      'A newer version of CandyCutz is required to continue. Please update your app from the App Store or Google Play.',
    accentColor: COLORS.primary,
  },
  generic: {
    icon: '⚠️',
    badge: 'Notice',
    defaultTitle: 'Unexpected Error',
    defaultMessage:
      'An unexpected issue occurred while processing your request. Please try again.',
    accentColor: COLORS.primary,
  },
};

export function ErrorScreen({
  variant = 'generic',
  title,
  message,
  errorCode,
  onRetry,
  onGoBack,
  onGoHome,
  showSupportLink = true,
}: ErrorScreenProps) {
  const router = useRouter();
  const config = VARIANT_CONFIG[variant] || VARIANT_CONFIG.generic;

  const displayTitle = title || config.defaultTitle;
  const displayMessage = message || config.defaultMessage;
  const displayBadge = errorCode ? `Error ${errorCode}` : config.badge;

  const scaleAnim = useRef(new Animated.Value(0.85)).current;
  const opacityAnim = useRef(new Animated.Value(0)).current;
  const pulseAnim = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.spring(scaleAnim, {
        toValue: 1,
        friction: 6,
        tension: 40,
        useNativeDriver: true,
      }),
      Animated.timing(opacityAnim, {
        toValue: 1,
        duration: 350,
        useNativeDriver: true,
      }),
    ]).start();

    const pulse = Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, {
          toValue: 1.06,
          duration: 1600,
          useNativeDriver: true,
        }),
        Animated.timing(pulseAnim, {
          toValue: 1,
          duration: 1600,
          useNativeDriver: true,
        }),
      ])
    );
    pulse.start();

    return () => pulse.stop();
  }, []);

  const handleGoBack = () => {
    if (onGoBack) {
      onGoBack();
    } else if (router.canGoBack()) {
      router.back();
    } else {
      router.replace('/(tabs)');
    }
  };

  const handleGoHome = () => {
    if (onGoHome) {
      onGoHome();
    } else {
      router.replace('/(tabs)');
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.container}>
        <Animated.View
          style={[
            styles.contentCard,
            {
              opacity: opacityAnim,
              transform: [{ scale: scaleAnim }],
            },
          ]}
        >
          {/* Badge */}
          <View
            style={[
              styles.badgeContainer,
              { borderColor: `${config.accentColor}55` },
            ]}
          >
            <View
              style={[
                styles.badgeDot,
                { backgroundColor: config.accentColor },
              ]}
            />
            <Text style={[styles.badgeText, { color: config.accentColor }]}>
              {displayBadge}
            </Text>
          </View>

          {/* Animated Icon Orb */}
          <Animated.View
            style={[
              styles.iconOrbOuter,
              {
                borderColor: `${config.accentColor}40`,
                transform: [{ scale: pulseAnim }],
              },
            ]}
          >
            <View
              style={[
                styles.iconOrbInner,
                { backgroundColor: `${config.accentColor}18` },
              ]}
            >
              <Text style={styles.iconEmoji}>{config.icon}</Text>
            </View>
          </Animated.View>

          {/* Text Information */}
          <Text style={styles.title}>{displayTitle}</Text>
          <Text style={styles.message}>{displayMessage}</Text>

          {/* Actions */}
          <View style={styles.actionGroup}>
            {onRetry && (
              <Button
                title="Try Again"
                onPress={onRetry}
                style={styles.primaryButton}
              />
            )}

            <View style={styles.secondaryRow}>
              <TouchableOpacity
                onPress={handleGoBack}
                style={styles.secondaryButton}
                activeOpacity={0.7}
              >
                <Text style={styles.secondaryButtonText}>← Go Back</Text>
              </TouchableOpacity>

              <TouchableOpacity
                onPress={handleGoHome}
                style={[styles.secondaryButton, styles.homeButton]}
                activeOpacity={0.7}
              >
                <Text style={[styles.secondaryButtonText, styles.homeButtonText]}>
                  Home Screen
                </Text>
              </TouchableOpacity>
            </View>
          </View>

          {showSupportLink && (
            <TouchableOpacity
              onPress={() => router.push('/policy/terms' as any)}
              style={styles.footerLink}
              activeOpacity={0.6}
            >
              <Text style={styles.footerLinkText}>
                Need assistance? View Terms & Policies
              </Text>
            </TouchableOpacity>
          )}
        </Animated.View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: SPACING.lg,
  },
  contentCard: {
    width: '100%',
    maxWidth: 420,
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.xl,
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.xl,
    alignItems: 'center',
  },
  badgeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 5,
    borderRadius: RADIUS.pill,
    borderWidth: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.4)',
    marginBottom: SPACING.lg,
    gap: 6,
  },
  badgeDot: {
    width: 7,
    height: 7,
    borderRadius: 4,
  },
  badgeText: {
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
    letterSpacing: 0.8,
    textTransform: 'uppercase',
  },
  iconOrbOuter: {
    width: 104,
    height: 104,
    borderRadius: 52,
    borderWidth: 2,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: SPACING.lg,
    backgroundColor: 'rgba(0, 0, 0, 0.35)',
  },
  iconOrbInner: {
    width: 82,
    height: 82,
    borderRadius: 41,
    alignItems: 'center',
    justifyContent: 'center',
  },
  iconEmoji: {
    fontSize: 42,
  },
  title: {
    fontSize: FONTS.sizes.xxl,
    fontWeight: '800',
    color: COLORS.textPrimary,
    textAlign: 'center',
    marginBottom: SPACING.xs,
    letterSpacing: -0.3,
  },
  message: {
    fontSize: FONTS.sizes.sm,
    color: COLORS.textMuted,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: SPACING.xl,
    paddingHorizontal: SPACING.xs,
  },
  actionGroup: {
    width: '100%',
    gap: SPACING.sm,
  },
  primaryButton: {
    width: '100%',
  },
  secondaryRow: {
    flexDirection: 'row',
    gap: SPACING.sm,
    width: '100%',
  },
  secondaryButton: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: RADIUS.md,
    borderWidth: 1,
    borderColor: COLORS.border,
    backgroundColor: 'rgba(255, 255, 255, 0.04)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  secondaryButtonText: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  homeButton: {
    borderColor: 'rgba(202, 138, 4, 0.3)',
    backgroundColor: 'rgba(202, 138, 4, 0.08)',
  },
  homeButtonText: {
    color: COLORS.primary,
  },
  footerLink: {
    marginTop: SPACING.lg,
    paddingVertical: 4,
  },
  footerLinkText: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
    textDecorationLine: 'underline',
  },
});
