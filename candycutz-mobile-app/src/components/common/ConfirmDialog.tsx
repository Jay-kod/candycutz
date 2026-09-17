import React from 'react';
import { Modal, StyleSheet, Text, TouchableOpacity, View } from 'react-native';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';

interface ConfirmDialogProps {
  visible: boolean;
  title: string;
  message: string;
  confirmLabel?: string;
  destructive?: boolean;
  onCancel: () => void;
  onConfirm: () => void;
}

export function ConfirmDialog({
  visible,
  title,
  message,
  confirmLabel = 'Confirm',
  destructive = false,
  onCancel,
  onConfirm,
}: ConfirmDialogProps) {
  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onCancel}>
      <View style={styles.overlay}>
        <View style={styles.dialog}>
          <View style={[styles.icon, destructive ? styles.iconDanger : styles.iconApproval]}>
            <Text style={styles.iconText}>{destructive ? '!' : '?'}</Text>
          </View>
          <Text style={styles.title}>{title}</Text>
          <Text style={styles.message}>{message}</Text>
          <View style={styles.actions}>
            <TouchableOpacity onPress={onCancel} style={styles.cancelButton}>
              <Text style={styles.cancelText}>Cancel</Text>
            </TouchableOpacity>
            <TouchableOpacity onPress={onConfirm} style={[styles.confirmButton, destructive && styles.confirmDanger]}>
              <Text style={styles.confirmText}>{confirmLabel}</Text>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: SPACING.lg, backgroundColor: COLORS.overlay },
  dialog: { width: '100%', maxWidth: 380, alignItems: 'center', padding: SPACING.lg, borderRadius: RADIUS.lg, backgroundColor: COLORS.surfaceElevated, borderWidth: 1, borderColor: COLORS.borderLight },
  icon: { width: 48, height: 48, borderRadius: RADIUS.full, alignItems: 'center', justifyContent: 'center', marginBottom: SPACING.md },
  iconApproval: { backgroundColor: COLORS.primaryLight, borderWidth: 1, borderColor: COLORS.primary },
  iconDanger: { backgroundColor: COLORS.errorLight, borderWidth: 1, borderColor: COLORS.error },
  iconText: { color: COLORS.primary, fontSize: FONTS.sizes.xl, fontWeight: '800' },
  title: { color: COLORS.textPrimary, fontSize: FONTS.sizes.lg, fontWeight: '800', textAlign: 'center' },
  message: { marginTop: SPACING.sm, color: COLORS.textSecondary, fontSize: FONTS.sizes.sm, lineHeight: 21, textAlign: 'center' },
  actions: { width: '100%', flexDirection: 'row', gap: SPACING.sm, marginTop: SPACING.lg },
  cancelButton: { flex: 1, alignItems: 'center', paddingVertical: 13, borderRadius: RADIUS.md, borderWidth: 1, borderColor: COLORS.borderLight },
  cancelText: { color: COLORS.textSecondary, fontWeight: '700' },
  confirmButton: { flex: 1, alignItems: 'center', paddingVertical: 13, borderRadius: RADIUS.md, backgroundColor: COLORS.primary },
  confirmDanger: { backgroundColor: COLORS.error },
  confirmText: { color: '#0A0A0C', fontWeight: '800' },
});
