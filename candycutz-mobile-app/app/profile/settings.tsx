import React, { useState } from 'react';
import {
  Alert,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Switch,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';

export default function SettingsScreen() {
  const router = useRouter();
  const { user, isBarber } = useAuthStore();

  const [pushNotifs, setPushNotifs] = useState(true);
  const [smsNotifs, setSmsNotifs] = useState(true);
  const [emailNotifs, setEmailNotifs] = useState(false);
  const [marketingNotifs, setMarketingNotifs] = useState(false);

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Account Settings</Text>
        <View style={styles.headerRight} />
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        {/* Notification Preferences */}
        <Text style={styles.sectionTitle}>Notifications</Text>
        <Card style={styles.card} elevated>
          <View style={styles.row}>
            <View style={styles.rowText}>
              <Text style={styles.rowLabel}>Push Notifications</Text>
              <Text style={styles.rowDesc}>Real-time updates on appointment queue and chair status</Text>
            </View>
            <Switch
              value={pushNotifs}
              onValueChange={setPushNotifs}
              trackColor={{ false: COLORS.border, true: COLORS.primary }}
              thumbColor="#FFF"
            />
          </View>

          <View style={styles.divider} />

          <View style={styles.row}>
            <View style={styles.rowText}>
              <Text style={styles.rowLabel}>SMS Alerts</Text>
              <Text style={styles.rowDesc}>Direct SMS reminders 1 hour before scheduled time</Text>
            </View>
            <Switch
              value={smsNotifs}
              onValueChange={setSmsNotifs}
              trackColor={{ false: COLORS.border, true: COLORS.primary }}
              thumbColor="#FFF"
            />
          </View>

          <View style={styles.divider} />

          <View style={styles.row}>
            <View style={styles.rowText}>
              <Text style={styles.rowLabel}>Email Receipts & Invoices</Text>
              <Text style={styles.rowDesc}>Automatic PDF receipts sent after service payment</Text>
            </View>
            <Switch
              value={emailNotifs}
              onValueChange={setEmailNotifs}
              trackColor={{ false: COLORS.border, true: COLORS.primary }}
              thumbColor="#FFF"
            />
          </View>
        </Card>

        {/* Security & Account */}
        <Text style={styles.sectionTitle}>Security & Privacy</Text>
        <Card style={styles.card} elevated>
          <TouchableOpacity
            style={styles.navRow}
            onPress={() => Alert.alert('Change Password', 'A secure password reset link has been sent to your registered email.')}
          >
            <Text style={styles.navLabel}>Change Password</Text>
            <Text style={styles.navArrow}>&rarr;</Text>
          </TouchableOpacity>

          <View style={styles.divider} />

          {isBarber && (
            <>
              <TouchableOpacity
                style={styles.navRow}
                onPress={() => router.push('/barber/profile-edit')}
              >
                <Text style={styles.navLabel}>Edit Stylist Bio & Credentials</Text>
                <Text style={styles.navArrow}>&rarr;</Text>
              </TouchableOpacity>
              <View style={styles.divider} />
            </>
          )}

          <TouchableOpacity
            style={styles.navRow}
            onPress={() => Alert.alert('Biometric Login', 'Biometric authentication is managed via your device system settings.')}
          >
            <Text style={styles.navLabel}>Biometric Sign-In</Text>
            <Text style={styles.navArrow}>&rarr;</Text>
          </TouchableOpacity>
        </Card>

        {/* App Info */}
        <Text style={styles.sectionTitle}>Application</Text>
        <Card style={styles.card} elevated>
          <View style={styles.navRow}>
            <Text style={styles.navLabel}>App Version</Text>
            <Text style={styles.navValue}>1.0.0 (Unified Build)</Text>
          </View>
          <View style={styles.divider} />
          <View style={styles.navRow}>
            <Text style={styles.navLabel}>Environment</Text>
            <Text style={styles.navValue}>Production (Lagos / Keffi)</Text>
          </View>
        </Card>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  backBtn: {
    padding: SPACING.xs,
  },
  backArrow: {
    color: COLORS.textPrimary,
    fontSize: 22,
    fontWeight: '700',
  },
  headerTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '700',
  },
  headerRight: {
    width: 32,
  },
  content: {
    padding: SPACING.md,
    gap: SPACING.xs,
  },
  sectionTitle: {
    color: COLORS.primary,
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 1,
    textTransform: 'uppercase',
    marginTop: SPACING.md,
    marginBottom: SPACING.xs,
    marginLeft: 4,
  },
  card: {
    padding: SPACING.md,
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 6,
  },
  rowText: {
    flex: 1,
    paddingRight: SPACING.md,
  },
  rowLabel: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '600',
  },
  rowDesc: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    marginTop: 2,
  },
  navRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 10,
  },
  navLabel: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '600',
  },
  navArrow: {
    color: COLORS.textMuted,
    fontSize: 18,
  },
  navValue: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginVertical: 4,
  },
});
