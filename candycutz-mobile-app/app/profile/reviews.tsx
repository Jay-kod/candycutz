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

interface ReviewItem {
  id: string;
  serviceName: string;
  barberName: string;
  rating: number;
  comment: string;
  date: string;
}

const SAMPLE_REVIEWS: ReviewItem[] = [
  {
    id: '1',
    serviceName: 'Executive Fade & Beard Sculpt',
    barberName: 'Master Barber Keffi',
    rating: 5,
    comment: 'Exceptional attention to detail! The beard line was razor sharp and the hot towel finish was pure luxury.',
    date: 'Sep 12, 2026',
  },
  {
    id: '2',
    serviceName: 'Luxury Royal Shave',
    barberName: 'Alex Fadez',
    rating: 5,
    comment: 'Smooth shave with zero irritation. Clean tools and great customer lounge hospitality.',
    date: 'Aug 28, 2026',
  },
];

export default function ReviewsScreen() {
  const router = useRouter();

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>My Reviews</Text>
        <View style={styles.headerRight} />
      </View>

      <FlatList
        data={SAMPLE_REVIEWS}
        keyExtractor={(item) => item.id}
        contentContainerStyle={styles.listContent}
        ListHeaderComponent={
          <Card style={styles.summaryCard} elevated>
            <Text style={styles.summaryTitle}>Your Verified Reviews</Text>
            <Text style={styles.summarySubtitle}>
              Reviews help our master stylists maintain world-class grooming standards.
            </Text>
          </Card>
        }
        renderItem={({ item }) => (
          <Card style={styles.card} elevated>
            <View style={styles.cardTop}>
              <View>
                <Text style={styles.serviceName}>{item.serviceName}</Text>
                <Text style={styles.barberName}>with {item.barberName}</Text>
              </View>
              <View style={styles.starsRow}>
                {Array.from({ length: 5 }).map((_, i) => (
                  <Text
                    key={i}
                    style={[
                      styles.star,
                      { color: i < item.rating ? COLORS.primary : COLORS.border },
                    ]}
                  >
                    ★
                  </Text>
                ))}
              </View>
            </View>

            <Text style={styles.comment}>{item.comment}</Text>
            <Text style={styles.date}>{item.date}</Text>
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
  listContent: {
    padding: SPACING.md,
    gap: SPACING.md,
  },
  summaryCard: {
    padding: SPACING.md,
    marginBottom: SPACING.sm,
    backgroundColor: '#161410',
    borderWidth: 1,
    borderColor: 'rgba(255, 153, 0, 0.2)',
  },
  summaryTitle: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginBottom: 4,
  },
  summarySubtitle: {
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
    alignItems: 'flex-start',
    marginBottom: 8,
  },
  serviceName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
  },
  barberName: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    marginTop: 2,
  },
  starsRow: {
    flexDirection: 'row',
    gap: 2,
  },
  star: {
    fontSize: 14,
  },
  comment: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 19,
    marginBottom: 8,
  },
  date: {
    color: COLORS.textMuted,
    fontSize: 10,
    fontWeight: '600',
  },
});
