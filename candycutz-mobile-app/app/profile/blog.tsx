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

interface BlogPost {
  id: string;
  title: string;
  excerpt: string;
  readTime: string;
  date: string;
}

const SAMPLE_POSTS: BlogPost[] = [
  {
    id: '1',
    title: 'Top 5 Beard Grooming Secrets for Harmattan',
    excerpt: 'How to prevent beard dryness and split ends during the harsh weather with natural oils.',
    readTime: '3 min read',
    date: 'Sep 17, 2026',
  },
  {
    id: '2',
    title: 'Skin Fade Maintenance: Keeping It Fresh Between Visits',
    excerpt: 'Simple daily routines to maintain clean edges and avoid razor bumps at home.',
    readTime: '4 min read',
    date: 'Sep 08, 2026',
  },
  {
    id: '3',
    title: 'The Modern Barber: Precision, Hygiene, and Client Loyalty',
    excerpt: 'Best practices for professional tools sanitization and elevating customer hospitality.',
    readTime: '5 min read',
    date: 'Aug 29, 2026',
  },
];

export default function BlogScreen() {
  const router = useRouter();

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Grooming Blog</Text>
        <View style={styles.headerRight} />
      </View>

      <FlatList
        data={SAMPLE_POSTS}
        keyExtractor={(item) => item.id}
        contentContainerStyle={styles.content}
        ListHeaderComponent={
          <Card style={styles.headerCard} elevated>
            <Text style={styles.headerCardTitle}>CandyCutz Journal</Text>
            <Text style={styles.headerCardDesc}>
              Industry insights, styling tips, and updates published for our stylists and esteemed clients.
            </Text>
          </Card>
        }
        renderItem={({ item }) => (
          <Card style={styles.card} elevated>
            <Text style={styles.postTitle}>{item.title}</Text>
            <Text style={styles.postExcerpt}>{item.excerpt}</Text>
            <View style={styles.metaRow}>
              <Text style={styles.readTime}>📖 {item.readTime}</Text>
              <Text style={styles.date}>{item.date}</Text>
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
  postTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginBottom: 6,
  },
  postExcerpt: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 20,
    marginBottom: 10,
  },
  metaRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    paddingTop: 8,
  },
  readTime: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
  },
  date: {
    color: COLORS.textMuted,
    fontSize: 11,
  },
});
