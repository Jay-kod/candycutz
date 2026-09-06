import React from 'react';
import {
  Alert,
  Linking,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { CONFIG } from '../../src/constants/config';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';

export default function ProfileScreen() {
  const router = useRouter();
  const { user, isAuthenticated, logout } = useAuthStore();

  const handleLogout = () => {
    Alert.alert('Sign Out', 'Are you sure you want to sign out of CandyCutz?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Sign Out',
        style: 'destructive',
        onPress: async () => {
          await logout();
          router.replace('/(tabs)');
        },
      },
    ]);
  };

  const openBranchLocation = () => {
    Linking.openURL(CONFIG.BRANCH.MAPS_URL);
  };

  const callBranchPhone = () => {
    Linking.openURL(`tel:${CONFIG.BRANCH.PHONE}`);
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <ScrollView contentContainerStyle={styles.container}>
        <Text style={styles.headerTitle}>My Profile</Text>

        {isAuthenticated && user ? (
          <>
            {/* User Info Card */}
            <Card style={styles.userCard} elevated>
              <View style={styles.avatar}>
                <Text style={styles.avatarText}>
                  {(user.real_name || user.name || 'U').charAt(0).toUpperCase()}
                </Text>
              </View>
              <View style={styles.userInfo}>
                <Text style={styles.userName}>{user.real_name || user.name}</Text>
                <Text style={styles.userUsername}>@{user.username}</Text>
                <Text style={styles.userEmail}>{user.email}</Text>
                {user.phone && <Text style={styles.userPhone}>{user.phone}</Text>}
              </View>
            </Card>

            {/* Wallet Balance Card */}
            <Card style={styles.walletCard} elevated>
              <View>
                <Text style={styles.walletLabel}>CANDYCUTZ WALLET BALANCE</Text>
                <Text style={styles.walletAmount}>
                  ₦{Number(user.wallet_balance || 0).toLocaleString()}
                </Text>
              </View>
              <Button
                title="Top Up"
                size="sm"
                variant="outline"
                onPress={() => Alert.alert('Wallet Top-up', 'Instant online wallet top-up via Paystack / Flutterwave.')}
              />
            </Card>
          </>
        ) : (
          <Card style={styles.guestCard} elevated>
            <Text style={styles.guestTitle}>Welcome to CandyCutz</Text>
            <Text style={styles.guestSubtitle}>
              Sign in or create an account to book your appointments, track live queue progress, and save your home service addresses.
            </Text>
            <View style={styles.authButtonsRow}>
              <Button
                title="Sign In"
                onPress={() => router.push('/auth/login')}
                style={styles.authBtn}
              />
              <Button
                title="Create Account"
                variant="outline"
                onPress={() => router.push('/auth/register')}
                style={styles.authBtn}
              />
            </View>
          </Card>
        )}

        {/* Physical Branch Information */}
        <Text style={styles.sectionHeader}>Flagship Saloon</Text>
        <Card style={styles.branchCard} elevated>
          <Text style={styles.branchName}>{CONFIG.BRANCH.NAME}</Text>
          <Text style={styles.branchAddress}>{CONFIG.BRANCH.ADDRESS}</Text>
          
          <View style={styles.branchActions}>
            <TouchableOpacity onPress={openBranchLocation} style={styles.branchActionBtn}>
              <Text style={styles.branchActionText}>🗺 Open Google Maps</Text>
            </TouchableOpacity>
            <TouchableOpacity onPress={callBranchPhone} style={styles.branchActionBtn}>
              <Text style={styles.branchActionText}>📞 Call {CONFIG.BRANCH.PHONE}</Text>
            </TouchableOpacity>
          </View>
        </Card>

        {/* App Settings & Policies */}
        <Text style={styles.sectionHeader}>Support & Details</Text>
        <Card style={styles.menuCard}>
          <TouchableOpacity
            style={styles.menuRow}
            onPress={() => Linking.openURL('https://candycutz.ng/terms')}
          >
            <Text style={styles.menuLabel}>Terms of Service & Booking Policy</Text>
            <Text style={styles.menuArrow}>&rarr;</Text>
          </TouchableOpacity>
          <View style={styles.menuDivider} />
          <TouchableOpacity
            style={styles.menuRow}
            onPress={() => Linking.openURL('https://candycutz.ng/privacy')}
          >
            <Text style={styles.menuLabel}>Privacy Policy</Text>
            <Text style={styles.menuArrow}>&rarr;</Text>
          </TouchableOpacity>
          <View style={styles.menuDivider} />
          <View style={styles.menuRow}>
            <Text style={styles.menuLabel}>App Version</Text>
            <Text style={styles.menuValue}>1.0.0 (Production Build)</Text>
          </View>
        </Card>

        {isAuthenticated && (
          <Button
            title="Sign Out"
            variant="danger"
            onPress={handleLogout}
            style={styles.logoutBtn}
          />
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  container: {
    padding: SPACING.md,
    paddingBottom: 40,
  },
  headerTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.hero - 8,
    fontWeight: '800',
    marginBottom: SPACING.md,
  },
  userCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: SPACING.md,
    marginBottom: SPACING.md,
  },
  avatar: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 2,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 16,
  },
  avatarText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xxl,
    fontWeight: '800',
  },
  userInfo: {
    flex: 1,
  },
  userName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '700',
  },
  userUsername: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
    marginTop: 1,
  },
  userEmail: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    marginTop: 2,
  },
  userPhone: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
    marginTop: 1,
  },
  walletCard: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: SPACING.md,
    marginBottom: SPACING.lg,
    borderLeftWidth: 4,
    borderLeftColor: COLORS.primary,
  },
  walletLabel: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
  },
  walletAmount: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
    marginTop: 2,
  },
  guestCard: {
    padding: SPACING.lg,
    marginBottom: SPACING.lg,
  },
  guestTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
    marginBottom: 6,
  },
  guestSubtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 20,
    marginBottom: 16,
  },
  authButtonsRow: {
    flexDirection: 'row',
    gap: 12,
  },
  authBtn: {
    flex: 1,
  },
  sectionHeader: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 1,
    marginBottom: SPACING.sm,
    marginTop: SPACING.sm,
  },
  branchCard: {
    padding: SPACING.md,
    marginBottom: SPACING.lg,
  },
  branchName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
  },
  branchAddress: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 20,
    marginVertical: 8,
  },
  branchActions: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 6,
  },
  branchActionBtn: {
    backgroundColor: COLORS.surfaceHighlight,
    paddingVertical: 8,
    paddingHorizontal: 12,
    borderRadius: RADIUS.sm,
  },
  branchActionText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
  },
  menuCard: {
    padding: 0,
    marginBottom: SPACING.xl,
  },
  menuRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: SPACING.md,
  },
  menuLabel: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
  },
  menuArrow: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.md,
  },
  menuValue: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
  },
  menuDivider: {
    height: 1,
    backgroundColor: COLORS.border,
  },
  logoutBtn: {
    marginTop: SPACING.sm,
  },
});
