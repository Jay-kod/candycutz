import React from 'react';
import {
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';

export default function AnalyticsScreen() {
  const router = useRouter();
  const { barber } = useAuthStore();

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Barber Analytics</Text>
        <View style={styles.headerRight} />
      </View>

      <ScrollView contentContainerStyle={styles.content}>
        {/* Today's Summary */}
        <Text style={styles.sectionTitle}>Today's Metrics</Text>
        <View style={styles.grid}>
          <Card style={styles.metricCard} elevated>
            <Text style={styles.metricValue}>{barber?.today_cuts_count || 6}</Text>
            <Text style={styles.metricLabel}>Cuts Completed</Text>
          </Card>
          <Card style={styles.metricCard} elevated>
            <Text style={[styles.metricValue, { color: COLORS.primary }]}>
              ₦{Number(barber?.today_earnings || 32000).toLocaleString()}
            </Text>
            <Text style={styles.metricLabel}>Daily Commission</Text>
          </Card>
        </View>

        {/* Monthly Summary */}
        <Text style={styles.sectionTitle}>Monthly Performance</Text>
        <Card style={styles.card} elevated>
          <View style={styles.statRow}>
            <Text style={styles.statLabel}>Total Clients Served</Text>
            <Text style={styles.statVal}>128</Text>
          </View>
          <View style={styles.divider} />
          <View style={styles.statRow}>
            <Text style={styles.statLabel}>Average Ticket Value</Text>
            <Text style={styles.statVal}>₦7,800</Text>
          </View>
          <View style={styles.divider} />
          <View style={styles.statRow}>
            <Text style={styles.statLabel}>Customer Satisfaction</Text>
            <Text style={[styles.statVal, { color: COLORS.primary }]}>
              ★ {Number(barber?.rating || 4.9).toFixed(1)} / 5.0
            </Text>
          </View>
          <View style={styles.divider} />
          <View style={styles.statRow}>
            <Text style={styles.statLabel}>Estimated Monthly Net</Text>
            <Text style={[styles.statVal, { color: '#22C55E' }]}>₦485,000</Text>
          </View>
        </Card>

        {/* Top Services Breakdown */}
        <Text style={styles.sectionTitle}>Top Performing Services</Text>
        <Card style={styles.card} elevated>
          <View style={styles.serviceRow}>
            <Text style={styles.serviceName}>Executive Fade & Beard Sculpt</Text>
            <Text style={styles.serviceCount}>68 cuts (53%)</Text>
          </View>
          <View style={styles.divider} />
          <View style={styles.serviceRow}>
            <Text style={styles.serviceName}>Standard Gentlemen Cut</Text>
            <Text style={styles.serviceCount}>34 cuts (27%)</Text>
          </View>
          <View style={styles.divider} />
          <View style={styles.serviceRow}>
            <Text style={styles.serviceName}>Luxury Royal Shave & Facial</Text>
            <Text style={styles.serviceCount}>26 cuts (20%)</Text>
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
  grid: {
    flexDirection: 'row',
    gap: 12,
    marginBottom: SPACING.sm,
  },
  metricCard: {
    flex: 1,
    padding: SPACING.md,
    alignItems: 'center',
  },
  metricValue: {
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
  card: {
    padding: SPACING.md,
  },
  statRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
  },
  statLabel: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
  },
  statVal: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  serviceRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 8,
  },
  serviceName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
  },
  serviceCount: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginVertical: 2,
  },
});
