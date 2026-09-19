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
  Trash2,
} from 'lucide-react-native';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';

export type ConfirmDialogVariant = 'danger' | 'warning' | 'info' | 'primary';

export interface ConfirmDialogProps {
  visible: boolean;
  title: string;
  message: string;
  confirmLabel?: string;
  cancelLabel?: string;
  destructive?: boolean;
  variant?: ConfirmDialogVariant;
  onCancel: () => void;
  onConfirm: () => void;
}

export function ConfirmDialog({
  visible,
  title,
  message,
  confirmLabel = 'Confirm',
  cancelLabel = 'Cancel',
  destructive = false,
  variant,
  onCancel,
  onConfirm,
}: ConfirmDialogProps) {
  // Determine active visual variant
  const effectiveVariant: ConfirmDialogVariant =
    variant || (destructive ? 'danger' : 'primary');

  // Animation values
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

  // Icon and Accent Color Resolution
  const getVariantStyles = () => {
    switch (effectiveVariant) {
      case 'danger':
        return {
          icon: <AlertTriangle size={28} color="#EF4444" strokeWidth={2.2} />,
          haloBg: 'rgba(239, 68, 68, 0.12)',
          ringBorder: 'rgba(239, 68, 68, 0.28)',
          glowShadow: 'rgba(239, 68, 68, 0.35)',
          confirmBtnBg: '#DC2626',
          confirmBtnPressedBg: '#B91C1C',
          confirmText: '#FFFFFF',
          topRimColor: 'rgba(239, 68, 68, 0.4)',
        };
      case 'warning':
        return {
          icon: <AlertCircle size={28} color="#F59E0B" strokeWidth={2.2} />,
          haloBg: 'rgba(245, 158, 11, 0.12)',
          ringBorder: 'rgba(245, 158, 11, 0.28)',
          glowShadow: 'rgba(245, 158, 11, 0.35)',
          confirmBtnBg: '#D97706',
          confirmBtnPressedBg: '#B45309',
          confirmText: '#FFFFFF',
          topRimColor: 'rgba(245, 158, 11, 0.4)',
        };
      case 'info':
        return {
          icon: <Info size={28} color="#3B82F6" strokeWidth={2.2} />,
          haloBg: 'rgba(59, 130, 246, 0.12)',
          ringBorder: 'rgba(59, 130, 246, 0.28)',
          glowShadow: 'rgba(59, 130, 246, 0.35)',
          confirmBtnBg: '#2563EB',
          confirmBtnPressedBg: '#1D4ED8',
          confirmText: '#FFFFFF',
          topRimColor: 'rgba(59, 130, 246, 0.4)',
        };
      case 'primary':
      default:
        return {
          icon: <HelpCircle size={28} color="#E5BA73" strokeWidth={2.2} />,
          haloBg: 'rgba(212, 175, 55, 0.12)',
          ringBorder: 'rgba(229, 186, 115, 0.3)',
          glowShadow: 'rgba(212, 175, 55, 0.35)',
          confirmBtnBg: '#E5BA73',
          confirmBtnPressedBg: '#D4AF37',
          confirmText: '#0A0A0C',
          topRimColor: 'rgba(229, 186, 115, 0.45)',
        };
    }
  };

  const currentTheme = getVariantStyles();

  return (
    <Modal
      visible={visible}
      transparent
      animationType="none"
      onRequestClose={onCancel}
      statusBarTranslucent
    >
      <View style={styles.overlayContainer}>
        {/* Animated backdrop */}
        <Animated.View
          style={[
            styles.backdrop,
            {
              opacity: backdropAnim,
            },
          ]}
        >
          <Pressable style={StyleSheet.absoluteFill} onPress={onCancel} />
        </Animated.View>

        {/* Animated Card Modal */}
        <Animated.View
          style={[
            styles.card,
            {
              opacity: cardOpacityAnim,
              transform: [{ scale: cardScaleAnim }],
            },
          ]}
        >
          {/* Subtle Top Accent Rim Light */}
          <View
            style={[
              styles.topRimLight,
              { backgroundColor: currentTheme.topRimColor },
            ]}
          />

          {/* Hero Floating Icon Badge with ambient glow ring */}
          <View
            style={[
              styles.iconHalo,
              {
                backgroundColor: currentTheme.haloBg,
                borderColor: currentTheme.ringBorder,
              },
            ]}
          >
            <View style={styles.iconInnerDisc}>{currentTheme.icon}</View>
          </View>

          {/* Title & Message */}
          <View style={styles.textBlock}>
            <Text style={styles.title}>{title}</Text>
            <Text style={styles.message}>{message}</Text>
          </View>

          {/* Action Buttons */}
          <View style={styles.actionsRow}>
            {/* Cancel Button */}
            <Pressable
              onPress={onCancel}
              style={({ pressed }) => [
                styles.cancelButton,
                pressed && styles.cancelButtonPressed,
              ]}
              accessibilityRole="button"
              accessibilityLabel={cancelLabel}
            >
              <Text style={styles.cancelText}>{cancelLabel}</Text>
            </Pressable>

            {/* Confirm Button */}
            <Pressable
              onPress={onConfirm}
              style={({ pressed }) => [
                styles.confirmButton,
                {
                  backgroundColor: pressed
                    ? currentTheme.confirmBtnPressedBg
                    : currentTheme.confirmBtnBg,
                  shadowColor: currentTheme.glowShadow,
                },
                pressed && styles.confirmButtonPressed,
              ]}
              accessibilityRole="button"
              accessibilityLabel={confirmLabel}
            >
              <Text
                style={[
                  styles.confirmText,
                  { color: currentTheme.confirmText },
                ]}
              >
                {confirmLabel}
              </Text>
            </Pressable>
          </View>
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
    backgroundColor: 'rgba(5, 5, 8, 0.78)',
  },
  card: {
    width: '100%',
    maxWidth: 350,
    alignItems: 'center',
    paddingTop: 32,
    paddingBottom: 24,
    paddingHorizontal: 24,
    borderRadius: 24,
    backgroundColor: '#14141B',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.09)',
    shadowColor: '#000000',
    shadowOffset: { width: 0, height: 12 },
    shadowOpacity: 0.5,
    shadowRadius: 24,
    elevation: 16,
    overflow: 'hidden',
  },
  topRimLight: {
    position: 'absolute',
    top: 0,
    left: '20%',
    right: '20%',
    height: 2,
    borderRadius: 1,
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
  title: {
    color: '#FFFFFF',
    fontSize: 19,
    fontWeight: '700',
    textAlign: 'center',
    letterSpacing: 0.2,
    lineHeight: 26,
  },
  message: {
    marginTop: 8,
    color: '#9CA3AF',
    fontSize: 14,
    lineHeight: 21,
    textAlign: 'center',
    paddingHorizontal: 4,
  },
  actionsRow: {
    width: '100%',
    flexDirection: 'row',
    gap: 12,
  },
  cancelButton: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 13,
    borderRadius: 14,
    backgroundColor: 'rgba(255, 255, 255, 0.05)',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
  },
  cancelButtonPressed: {
    backgroundColor: 'rgba(255, 255, 255, 0.1)',
    transform: [{ scale: 0.98 }],
  },
  cancelText: {
    color: '#D1D5DB',
    fontSize: 15,
    fontWeight: '600',
  },
  confirmButton: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 13,
    borderRadius: 14,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.35,
    shadowRadius: 10,
    elevation: 4,
  },
  confirmButtonPressed: {
    transform: [{ scale: 0.98 }],
  },
  confirmText: {
    fontSize: 15,
    fontWeight: '700',
    letterSpacing: 0.2,
  },
});
