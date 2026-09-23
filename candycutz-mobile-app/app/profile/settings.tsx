import React, { useEffect, useState } from 'react';
import {
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Switch,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Fingerprint } from 'lucide-react-native';
import { Card } from '../../src/components/common/Card';
import { FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';
import { useAppTheme } from '../../src/hooks/useAppTheme';
import { useToastStore } from '../../src/store/toastStore';
import { biometricService, BiometricSupportInfo } from '../../src/services/biometricService';
import { tokenStorage } from '../../src/api/client';

export default function SettingsScreen() {
  const router = useRouter();
  const { user, isBarber, token } = useAuthStore();
  const { colors, themePreference, setThemePreference } = useAppTheme();
  const showToast = useToastStore((state) => state.show);

  const [pushNotifs, setPushNotifs] = useState(true);
  const [smsNotifs, setSmsNotifs] = useState(true);
  const [emailNotifs, setEmailNotifs] = useState(false);

  const [biometricsEnabled, setBiometricsEnabled] = useState(false);
  const [biometricSupport, setBiometricSupport] = useState<BiometricSupportInfo>({
    hasHardware: false,
    isEnrolled: false,
    supportedTypes: [],
    biometricName: 'Fingerprint',
  });
  const [isTogglingBiometrics, setIsTogglingBiometrics] = useState(false);

  const currentRole: 'customer' | 'barber' = isBarber ? 'barber' : 'customer';

  useEffect(() => {
    let isMounted = true;
    (async () => {
      const support = await biometricService.checkSupport();
      const enabled = await biometricService.isEnabled(currentRole);
      if (isMounted) {
        setBiometricSupport(support);
        setBiometricsEnabled(enabled);
      }
    })();
    return () => {
      isMounted = false;
    };
  }, [currentRole]);

  const handleToggleBiometrics = async (enable: boolean) => {
    if (enable) {
      if (!biometricSupport.hasHardware) {
        showToast({
          variant: 'warning',
          title: 'Not Supported',
          message: 'This device does not have biometric hardware.',
        });
        return;
      }
      if (!biometricSupport.isEnrolled) {
        showToast({
          variant: 'warning',
          title: 'Setup Required',
          message: 'No fingerprints enrolled on this device. Please register them in phone Settings.',
        });
        return;
      }

      setIsTogglingBiometrics(true);
      const currentToken = token || (await tokenStorage.get());
      if (!currentToken) {
        setIsTogglingBiometrics(false);
        showToast({
          variant: 'error',
          title: 'Session Error',
          message: 'Unable to retrieve active session token. Please re-login.',
        });
        return;
      }

      const result = await biometricService.enable(currentToken, {
        name: user?.name,
        email: user?.email,
        role: currentRole,
      });

      setIsTogglingBiometrics(false);
      if (result.success) {
        setBiometricsEnabled(true);
        showToast({
          variant: 'success',
          title: `${biometricSupport.biometricName} Enabled (${isBarber ? 'Barber' : 'Customer'})`,
          message: `You can now use your ${biometricSupport.biometricName.toLowerCase()} to sign in to your ${isBarber ? 'Barber Staff Desk' : 'Customer account'}.`,
        });
      } else {
        showToast({
          variant: 'error',
          title: 'Biometrics Failed',
          message: result.message || 'Verification was cancelled or failed.',
        });
      }
    } else {
      await biometricService.disable(currentRole);
      setBiometricsEnabled(false);
      showToast({
        variant: 'info',
        title: `${biometricSupport.biometricName} Disabled (${isBarber ? 'Barber' : 'Customer'})`,
        message: 'Biometric sign-in has been turned off for this account. Password login will be required.',
      });
    }
  };

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: colors.background }]}>
      <View style={[styles.header, { borderBottomColor: colors.border }]}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={[styles.backArrow, { color: colors.textPrimary }]}>←</Text>
        </TouchableOpacity>
        <Text style={[styles.headerTitle, { color: colors.textPrimary }]}>Account Settings</Text>
        <View style={styles.headerRight} />
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        {/* Appearance / Theme Selection */}
        <Text style={[styles.sectionTitle, { color: colors.primary }]}>Appearance</Text>
        <Card style={styles.card} elevated>
          <Text style={[styles.rowLabel, { color: colors.textPrimary, marginBottom: 4 }]}>
            Theme Mode
          </Text>
          <Text style={[styles.rowDesc, { color: colors.textSecondary, marginBottom: SPACING.sm }]}>
            Choose light mode, dark mode, or follow your system setting.
          </Text>

          <View style={styles.themeOptionsRow}>
            {[
              { id: 'light', label: 'Light mode', desc: 'Light appearance' },
              { id: 'dark', label: 'Dark mode', desc: 'Dark appearance' },
              { id: 'system', label: 'System', desc: 'Use device setting' },
            ].map((option) => {
              const isSelected = themePreference === option.id;
              return (
                <TouchableOpacity
                  key={option.id}
                  style={[
                    styles.themeOptionBtn,
                    {
                      backgroundColor: isSelected ? colors.surfaceElevated : colors.surface,
                      borderColor: isSelected ? colors.primary : colors.border,
                      borderWidth: isSelected ? 2 : 1,
                    },
                  ]}
                  onPress={() => setThemePreference(option.id as 'system' | 'dark' | 'light')}
                  accessibilityRole="button"
                  accessibilityState={{ selected: isSelected }}
                  accessibilityLabel={`Select ${option.label} theme`}
                >
                  <Text
                    style={[
                      styles.themeOptionLabel,
                      { color: isSelected ? colors.primary : colors.textPrimary },
                    ]}
                  >
                    {option.label}
                  </Text>
                  <Text
                    style={[
                      styles.themeOptionDesc,
                      { color: isSelected ? colors.primary : colors.textMuted },
                    ]}
                  >
                    {option.desc}
                  </Text>
                </TouchableOpacity>
              );
            })}
          </View>
        </Card>

        {/* Notification Preferences */}
        <Text style={[styles.sectionTitle, { color: colors.primary }]}>Notifications</Text>
        <Card style={styles.card} elevated>
          <View style={styles.row}>
            <View style={styles.rowText}>
              <Text style={[styles.rowLabel, { color: colors.textPrimary }]}>Push Notifications</Text>
              <Text style={[styles.rowDesc, { color: colors.textSecondary }]}>Real-time updates on appointment queue and chair status</Text>
            </View>
            <Switch
              value={pushNotifs}
              onValueChange={setPushNotifs}
              trackColor={{ false: colors.border, true: colors.primary }}
              thumbColor="#FFF"
            />
          </View>

          <View style={[styles.divider, { backgroundColor: colors.border }]} />

          <View style={styles.row}>
            <View style={styles.rowText}>
              <Text style={[styles.rowLabel, { color: colors.textPrimary }]}>SMS Alerts</Text>
              <Text style={[styles.rowDesc, { color: colors.textSecondary }]}>Direct SMS reminders 1 hour before scheduled time</Text>
            </View>
            <Switch
              value={smsNotifs}
              onValueChange={setSmsNotifs}
              trackColor={{ false: colors.border, true: colors.primary }}
              thumbColor="#FFF"
            />
          </View>

          <View style={[styles.divider, { backgroundColor: colors.border }]} />

          <View style={styles.row}>
            <View style={styles.rowText}>
              <Text style={[styles.rowLabel, { color: colors.textPrimary }]}>Email Receipts & Invoices</Text>
              <Text style={[styles.rowDesc, { color: colors.textSecondary }]}>Automatic PDF receipts sent after service payment</Text>
            </View>
            <Switch
              value={emailNotifs}
              onValueChange={setEmailNotifs}
              trackColor={{ false: colors.border, true: colors.primary }}
              thumbColor="#FFF"
            />
          </View>
        </Card>

        {/* Security & Account */}
        <Text style={[styles.sectionTitle, { color: colors.primary }]}>Security & Privacy</Text>
        <Card style={styles.card} elevated>
          <TouchableOpacity
            style={styles.navRow}
            onPress={() =>
              showToast({
                variant: 'success',
                title: 'Password Reset Sent',
                message: 'A secure password reset link has been sent to your registered email.',
              })
            }
          >
            <Text style={[styles.navLabel, { color: colors.textPrimary }]}>Change Password</Text>
            <Text style={[styles.navArrow, { color: colors.textMuted }]}>&rarr;</Text>
          </TouchableOpacity>

          <View style={[styles.divider, { backgroundColor: colors.border }]} />

          {isBarber && (
            <>
              <TouchableOpacity
                style={styles.navRow}
                onPress={() => router.push('/barber/profile-edit')}
              >
                <Text style={[styles.navLabel, { color: colors.textPrimary }]}>Edit Stylist Bio & Credentials</Text>
                <Text style={[styles.navArrow, { color: colors.textMuted }]}>&rarr;</Text>
              </TouchableOpacity>
              <View style={[styles.divider, { backgroundColor: colors.border }]} />
            </>
          )}

          <View style={styles.row}>
            <View style={styles.rowText}>
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 6, marginBottom: 2 }}>
                <Fingerprint size={18} color={colors.primary} />
                <Text style={[styles.rowLabel, { color: colors.textPrimary }]}>
                  {biometricSupport.biometricName} Login ({isBarber ? 'Barber' : 'Customer'})
                </Text>
              </View>
              <Text style={[styles.rowDesc, { color: colors.textSecondary }]}>
                {!biometricSupport.hasHardware
                  ? 'Biometric hardware is not supported on this device'
                  : !biometricSupport.isEnrolled
                  ? 'No fingerprints registered on device. Set up in phone Settings.'
                  : `Sign in instantly to your ${isBarber ? 'Barber Staff Desk' : 'Customer account'} using your ${biometricSupport.biometricName.toLowerCase()}`}
              </Text>
            </View>
            <Switch
              value={biometricsEnabled}
              disabled={!biometricSupport.hasHardware || !biometricSupport.isEnrolled || isTogglingBiometrics}
              onValueChange={handleToggleBiometrics}
              trackColor={{ false: colors.border, true: colors.primary }}
              thumbColor="#FFF"
            />
          </View>
        </Card>

        {/* Legal & Policies */}
        <Text style={[styles.sectionTitle, { color: colors.primary }]}>Legal & Policies</Text>
        <Card style={styles.card} elevated>
          <TouchableOpacity
            style={styles.navRow}
            onPress={() => router.push('/policy/terms' as any)}
            activeOpacity={0.7}
          >
            <Text style={[styles.navLabel, { color: colors.textPrimary }]}>Terms of Service</Text>
            <Text style={[styles.navArrow, { color: colors.textMuted }]}>&rarr;</Text>
          </TouchableOpacity>

          <View style={[styles.divider, { backgroundColor: colors.border }]} />

          <TouchableOpacity
            style={styles.navRow}
            onPress={() => router.push('/policy/privacy' as any)}
            activeOpacity={0.7}
          >
            <Text style={[styles.navLabel, { color: colors.textPrimary }]}>Privacy Policy</Text>
            <Text style={[styles.navArrow, { color: colors.textMuted }]}>&rarr;</Text>
          </TouchableOpacity>
        </Card>

        {/* App Info */}
        <Text style={[styles.sectionTitle, { color: colors.primary }]}>Application</Text>
        <Card style={styles.card} elevated>
          <View style={styles.navRow}>
            <Text style={[styles.navLabel, { color: colors.textPrimary }]}>App Version</Text>
            <Text style={[styles.navValue, { color: colors.textSecondary }]}>1.0.0 (Unified Build)</Text>
          </View>
          <View style={[styles.divider, { backgroundColor: colors.border }]} />
          <View style={styles.navRow}>
            <Text style={[styles.navLabel, { color: colors.textPrimary }]}>Environment</Text>
            <Text style={[styles.navValue, { color: colors.textSecondary }]}>Production (Lagos / Keffi)</Text>
          </View>
        </Card>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.sm,
    borderBottomWidth: 1,
  },
  backBtn: {
    padding: SPACING.xs,
  },
  backArrow: {
    fontSize: 22,
    fontWeight: '700',
  },
  headerTitle: {
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
  themeOptionsRow: {
    flexDirection: 'row',
    gap: SPACING.sm,
    marginTop: 4,
  },
  themeOptionBtn: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    paddingHorizontal: 8,
    borderRadius: RADIUS.md,
  },
  themeOptionLabel: {
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  themeOptionDesc: {
    fontSize: 10,
    fontWeight: '500',
    marginTop: 2,
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
    fontSize: FONTS.sizes.md,
    fontWeight: '600',
  },
  rowDesc: {
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
    fontSize: FONTS.sizes.md,
    fontWeight: '600',
  },
  navArrow: {
    fontSize: 18,
  },
  navValue: {
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
  },
  divider: {
    height: 1,
    marginVertical: 4,
  },
});
