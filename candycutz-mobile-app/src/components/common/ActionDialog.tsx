import React, { useEffect, useRef } from 'react';
import {
  Animated,
  Modal,
  Pressable,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {
  AlertCircle,
  AlertTriangle,
  CheckCircle2,
  HelpCircle,
  Info,
} from 'lucide-react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useAppTheme } from '../../hooks/useAppTheme';

export type ActionDialogVariant = 'primary' | 'danger' | 'warning' | 'info' | 'success';

export interface ActionDialogAction {
  label: string;
  onPress: () => void;
  /** Mark as destructive to use red styling */
  destructive?: boolean;
}

export interface ActionDialogProps {
  visible: boolean;
  title: string;
  message: string;
  variant?: ActionDialogVariant;
  actions: ActionDialogAction[];
  dismissLabel?: string;
  onDismiss: () => void;
}

export function ActionDialog({
  visible,
  title,
  message,
  variant = 'primary',
  actions,
  dismissLabel = 'Dismiss',
  onDismiss,
}: ActionDialogProps) {
  const { colors, isDark } = useAppTheme();

  // Animations
  const backdropAnim = useRef(new Animated.Value(0)).current;
  const cardScaleAnim = useRef(new Animated.Value(0.9)).current;
  const cardOpacityAnim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    if (visible) {
      Animated.parallel([
        Animated.timing(backdropAnim, {
          toValue: 1,
          duration: 200,
          useNativeDriver: true,
        }),
        Animated.spring(cardScaleAnim, {
          toValue: 1,
          friction: 8,
          tension: 65,
          useNativeDriver: true,
        }),
        Animated.timing(cardOpacityAnim, {
          toValue: 1,
          duration: 200,
          useNativeDriver: true,
        }),
      ]).start();
    } else {
      Animated.parallel([
        Animated.timing(backdropAnim, {
          toValue: 0,
          duration: 150,
          useNativeDriver: true,
        }),
        Animated.timing(cardScaleAnim, {
          toValue: 0.95,
          duration: 150,
          useNativeDriver: true,
        }),
        Animated.timing(cardOpacityAnim, {
          toValue: 0,
          duration: 150,
          useNativeDriver: true,
        }),
      ]).start();
    }
  }, [visible, backdropAnim, cardScaleAnim, cardOpacityAnim]);

  const getVariantStyles = () => {
    switch (variant) {
      case 'danger':
        return {
          icon: <AlertTriangle size={28} color="#EF4444" strokeWidth={2.2} />,
          haloBg: 'rgba(239, 68, 68, 0.12)',
          ringBorder: 'rgba(239, 68, 68, 0.28)',
          topRimColor: 'rgba(239, 68, 68, 0.4)',
          actionBg: '#DC2626',
          actionPressedBg: '#B91C1C',
        };
      case 'warning':
        return {
          icon: <AlertCircle size={28} color="#F59E0B" strokeWidth={2.2} />,
          haloBg: 'rgba(245, 158, 11, 0.12)',
          ringBorder: 'rgba(245, 158, 11, 0.28)',
          topRimColor: 'rgba(245, 158, 11, 0.4)',
          actionBg: '#D97706',
          actionPressedBg: '#B45309',
        };
      case 'info':
        return {
          icon: <Info size={28} color="#3B82F6" strokeWidth={2.2} />,
          haloBg: 'rgba(59, 130, 246, 0.12)',
          ringBorder: 'rgba(59, 130, 246, 0.28)',
          topRimColor: 'rgba(59, 130, 246, 0.4)',
          actionBg: '#2563EB',
          actionPressedBg: '#1D4ED8',
        };
      case 'success':
        return {
          icon: <CheckCircle2 size={28} color="#22C55E" strokeWidth={2.2} />,
          haloBg: 'rgba(34, 197, 94, 0.12)',
          ringBorder: 'rgba(34, 197, 94, 0.28)',
          topRimColor: 'rgba(34, 197, 94, 0.4)',
          actionBg: '#16A34A',
          actionPressedBg: '#15803D',
        };
      case 'primary':
      default:
        return {
          icon: <HelpCircle size={28} color={colors.primary} strokeWidth={2.2} />,
          haloBg: isDark ? 'rgba(212, 175, 55, 0.12)' : 'rgba(197, 155, 39, 0.12)',
          ringBorder: isDark ? 'rgba(229, 186, 115, 0.3)' : 'rgba(197, 155, 39, 0.28)',
          topRimColor: isDark ? 'rgba(229, 186, 115, 0.45)' : 'rgba(197, 155, 39, 0.4)',
          actionBg: colors.primary,
          actionPressedBg: colors.primaryDark,
        };
    }
  };

  const vs = getVariantStyles();

  return (
    <Modal
      visible={visible}
      transparent
      animationType="none"
      onRequestClose={onDismiss}
      statusBarTranslucent
    >
      <View style={styles.overlayContainer}>
        {/* Animated backdrop */}
        <Animated.View
          style={[
            styles.backdrop,
            {
              opacity: backdropAnim,
              backgroundColor: colors.scrim,
            },
          ]}
        >
          <Pressable style={StyleSheet.absoluteFill} onPress={onDismiss} />
        </Animated.View>

        {/* Card */}
        <Animated.View
          style={[
            styles.cardShell,
            {
              opacity: cardOpacityAnim,
              transform: [{ scale: cardScaleAnim }],
            },
          ]}
        >
          <LinearGradient
            colors={isDark ? [colors.surfaceHighlight, colors.surfaceElevated] : [colors.surface, colors.surfaceElevated]}
            style={[styles.card, { borderColor: colors.border }]}
          >
            <View style={[styles.statusRule, { backgroundColor: vs.topRimColor }]} />

            {/* Icon halo */}
            <View
              style={[
                styles.iconHalo,
                {
                  backgroundColor: vs.haloBg,
                  borderColor: vs.ringBorder,
                },
              ]}
            >
              <View style={styles.iconInnerDisc}>{vs.icon}</View>
            </View>

            {/* Title & Message */}
            <View style={styles.textBlock}>
              <Text style={[styles.statusLabel, { color: vs.topRimColor }]}>ACCOUNT UPDATE</Text>
              <Text style={[styles.title, { color: colors.textPrimary }]}>{title}</Text>
              <Text style={[styles.message, { color: colors.textSecondary }]}>{message}</Text>
            </View>

            {/* Primary action and dismiss stay side by side */}
            <View style={styles.actionsRow}>
              <Pressable
                onPress={onDismiss}
                style={({ pressed }) => [
                  styles.dismissButton,
                  pressed && { opacity: 0.6 },
                ]}
                hitSlop={12}
              >
                <Text style={[styles.dismissText, { color: colors.textMuted }]}>{dismissLabel}</Text>
              </Pressable>
              {actions.map((action, index) => (
                <Pressable
                  key={index}
                  onPress={() => {
                    action.onPress();
                    onDismiss();
                  }}
                  style={({ pressed }) => [
                    styles.actionButton,
                    {
                      backgroundColor: pressed
                        ? action.destructive
                          ? '#B91C1C'
                          : vs.actionPressedBg
                        : action.destructive
                        ? '#DC2626'
                        : vs.actionBg,
                    },
                    pressed && styles.actionButtonPressed,
                  ]}
                  accessibilityRole="button"
                  accessibilityLabel={action.label}
                >
                  <Text style={styles.actionButtonText}>{action.label}</Text>
                </Pressable>
              ))}
            </View>
          </LinearGradient>
        </Animated.View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlayContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: SPACING.lg,
  },
  backdrop: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
  },
  cardShell: {
    width: '100%',
    maxWidth: 380,
    borderRadius: 24,
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 16 },
    shadowOpacity: 0.3,
    shadowRadius: 28,
    elevation: 20,
  },
  card: {
    width: '100%',
    alignItems: 'center',
    paddingTop: 18,
    paddingBottom: 20,
    paddingHorizontal: 24,
    borderRadius: 24,
    borderWidth: 1,
    overflow: 'hidden',
  },
  statusRule: {
    width: '100%',
    height: 2,
    borderRadius: 1,
    marginBottom: 22,
  },
  iconHalo: {
    width: 68,
    height: 68,
    borderRadius: 34,
    borderWidth: 1.5,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 18,
  },
  iconInnerDisc: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  textBlock: {
    alignItems: 'center',
    width: '100%',
    marginBottom: 24,
  },
  statusLabel: {
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1.8,
    marginBottom: 7,
  },
  title: {
    fontSize: 19,
    fontWeight: '700',
    textAlign: 'center',
    letterSpacing: 0.2,
    lineHeight: 26,
  },
  message: {
    marginTop: 8,
    fontSize: 14,
    lineHeight: 21,
    textAlign: 'center',
    paddingHorizontal: 4,
  },
  actionsRow: {
    width: '100%',
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  actionButton: {
    flex: 1,
    minHeight: 48,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    paddingHorizontal: 8,
    borderRadius: 14,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 4,
  },
  actionButtonPressed: {
    transform: [{ scale: 0.98 }],
  },
  actionButtonText: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '700',
    letterSpacing: 0.2,
  },
  dismissButton: {
    flex: 1,
    minHeight: 48,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 8,
    paddingHorizontal: 16,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(127, 127, 127, 0.22)',
  },
  dismissText: {
    fontSize: 14,
    fontWeight: '500',
  },
});
