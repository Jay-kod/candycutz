import React from 'react';
import {
  Alert,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { CONFIG } from '../../src/constants/config';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useBarberAuthStore } from '../../src/store/barberAuthStore';

export default function BarberProfileScreen() {
  const router = useRouter();
  const { barber, logout } = useBarberAuthStore();

  const handleLogout = () => {
    Alert.alert('Sign Out', 'Sign out of CandyCutz Barber Staff desk?', [
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

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <ScrollView contentContainerStyle={styles.container}>
        <Text style={styles.headerTitle}>Stylist Profile & Performance</Text>

        {/* Profile Card */}
        <Card style={styles.profileCard} elevated>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>
              {(barber?.name || 'B').charAt(0).toUpperCase()}
            </Text>
          </View>
          <View style={styles.profileInfo}>
            <Text style={styles.name}>{barber?.name || 'Master Stylist'}</Text>
            <Text style={styles.username}>@{barber?.username || 'barber'}</Text>
            <Text style={styles.email}>{barber?.email}</Text>
            <View style={styles.ratingRow}>
              <Text style={styles.star}>★</Text>
              <Text style={styles.ratingText}>{Number(barber?.rating || 5.0).toFixed(1)} Rating</Text>
            </View>
          </View>
        </Card>

        {/* Performance Metrics */}
        <Text style={styles.sectionHeader}>Today's Statistics</Text>
        <View style={styles.metricsGrid}>
          <Card style={styles.metricCard} elevated>
            <Text style={styles.metricVal}>{barber?.today_cuts_count || 6}</Text>
            <Text style={styles.metricLabel}>Completed Cuts</Text>
          </Card>
          <Card style={styles.metricCard} elevated>
            <Text style={[styles.metricVal, { color: COLORS.primary }]}>
              ₦{Number(barber?.today_earnings || 32000).toLocaleString()}
            </Text>
            <Text style={styles.metricLabel}>Gross Commission</Text>
          </Card>
        </View>

        {/* Physical Flagship Branch Details */}
        <Text style={styles.sectionHeader}>Assigned Saloon Branch</Text>
        <Card style={styles.branchCard} elevated>
          <Text style={styles.branchName}>{CONFIG.BRANCH.NAME}</Text>
          <Text style={styles.branchAddress}>{CONFIG.BRANCH.ADDRESS}</Text>
          <Text style={styles.branchPhone}>Branch Phone: {CONFIG.BRANCH.PHONE}</Text>
        </Card>

        {/* Staff Policies */}
        <Text style={styles.sectionHeader}>Staff Guidelines</Text>
        <Card style={styles.guidelinesCard}>
          <Text style={styles.guidelineText}>• Sanitize all clippers and blades between clients.</Text>
          <Text style={styles.guidelineText}>• Confirm customer check-in upon arrival to chair.</Text>
          <Text style={styles.guidelineText}>• Notify concierge 1 hour prior to taking unscheduled breaks.</Text>
        </Card>

        <Button
          title="Sign Out of Staff Desk"
          variant="danger"
          onPress={handleLogout}
          style={styles.logoutBtn}
        />
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
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
    marginBottom: SPACING.md,
  },
  profileCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: SPACING.md,
    marginBottom: SPACING.lg,
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
  profileInfo: {
    flex: 1,
  },
  name: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '700',
  },
  username: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
    marginTop: 2,
  },
  email: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    marginTop: 2,
  },
  ratingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 6,
  },
  star: {
    color: COLORS.primary,
    fontSize: 14,
    marginRight: 4,
  },
  ratingText: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  sectionHeader: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 1,
    marginBottom: SPACING.sm,
    marginTop: SPACING.xs,
  },
  metricsGrid: {
    flexDirection: 'row',
    gap: 12,
    marginBottom: SPACING.lg,
  },
  metricCard: {
    flex: 1,
    padding: SPACING.md,
    alignItems: 'center',
  },
  metricVal: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '900',
    marginBottom: 4,
  },
  metricLabel: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
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
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
    marginVertical: 6,
  },
  branchPhone: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
  },
  guidelinesCard: {
    padding: SPACING.md,
    marginBottom: SPACING.xl,
  },
  guidelineText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
    marginBottom: 6,
  },
  logoutBtn: {
    marginTop: SPACING.sm,
  },
});
