import React from 'react';
import {
  FlatList,
  SafeAreaView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';

interface StylistService {
  id: string;
  name: string;
  category: string;
  price: number;
  duration: number;
  isActive: boolean;
}

const SAMPLE_SERVICES: StylistService[] = [
  { id: '1', name: 'Executive Fade & Beard Sculpt', category: 'Haircut', price: 7000, duration: 45, isActive: true },
  { id: '2', name: 'Luxury Royal Shave & Facial', category: 'Spa & Beard', price: 12000, duration: 60, isActive: true },
  { id: '3', name: 'Standard Gentlemen Cut', category: 'Haircut', price: 5000, duration: 30, isActive: true },
  { id: '4', name: 'Hair Dye & Gray Blending', category: 'Coloring', price: 8500, duration: 45, isActive: true },
  { id: '5', name: 'Scalp Detox & Therapy', category: 'Treatment', price: 10000, duration: 50, isActive: false },
];

export default function ServicesScreen() {
  const router = useRouter();
  const { barber } = useAuthStore();

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>My Services</Text>
        <View style={styles.headerRight} />
      </View>

      <FlatList
        data={SAMPLE_SERVICES}
        keyExtractor={(item) => item.id}
        contentContainerStyle={styles.content}
        ListHeaderComponent={
          <Card style={styles.headerCard} elevated>
            <Text style={styles.headerCardTitle}>Active Barber Menu</Text>
            <Text style={styles.headerCardDesc}>
              Services assigned to your chair. Commission is computed based on your verified ticket completions.
            </Text>
          </Card>
        }
        renderItem={({ item }) => (
          <Card style={styles.card} elevated>
            <View style={styles.cardTop}>
              <View style={styles.tag}>
                <Text style={styles.tagText}>{item.category.toUpperCase()}</Text>
              </View>
              <View style={[styles.statusBadge, item.isActive ? styles.badgeActive : styles.badgeInactive]}>
                <Text style={[styles.statusText, item.isActive ? styles.statusActiveText : styles.statusInactiveText]}>
                  {item.isActive ? 'Active on Chair' : 'Inactive'}
                </Text>
              </View>
            </View>

            <Text style={styles.serviceName}>{item.name}</Text>

            <View style={styles.cardFooter}>
              <View>
                <Text style={styles.footerLabel}>SERVICE FEE</Text>
                <Text style={styles.footerVal}>₦{item.price.toLocaleString()}</Text>
              </View>
              <View>
                <Text style={styles.footerLabel}>DURATION</Text>
                <Text style={styles.footerValSecondary}>{item.duration} mins</Text>
              </View>
            </View>
          </Card>
        )}
      />
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
    gap: SPACING.md,
  },
  headerCard: {
    padding: SPACING.md,
    backgroundColor: '#1E1B10',
    borderWidth: 1.5,
    borderColor: COLORS.primary,
    marginBottom: SPACING.sm,
  },
  headerCardTitle: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginBottom: 4,
  },
  headerCardDesc: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 16,
  },
  card: {
    padding: SPACING.md,
  },
  cardTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  tag: {
    backgroundColor: 'rgba(255, 153, 0, 0.12)',
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: RADIUS.sm,
  },
  tagText: {
    color: COLORS.primary,
    fontSize: 9,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: RADIUS.sm,
  },
  badgeActive: {
    backgroundColor: 'rgba(22, 163, 74, 0.15)',
  },
  badgeInactive: {
    backgroundColor: 'rgba(107, 114, 128, 0.15)',
  },
  statusText: {
    fontSize: 10,
    fontWeight: '700',
  },
  statusActiveText: {
    color: '#22C55E',
  },
  statusInactiveText: {
    color: COLORS.textMuted,
  },
  serviceName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginBottom: 12,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    paddingTop: 10,
  },
  footerLabel: {
    color: COLORS.textMuted,
    fontSize: 9,
    fontWeight: '700',
  },
  footerVal: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.md,
    fontWeight: '800',
    marginTop: 2,
  },
  footerValSecondary: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
    marginTop: 2,
  },
});
