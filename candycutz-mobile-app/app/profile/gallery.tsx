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

interface GalleryItem {
  id: string;
  title: string;
  category: string;
  likes: number;
  date: string;
}

const SAMPLE_GALLERY: GalleryItem[] = [
  { id: '1', title: 'Crisp Taper Fade with Texture', category: 'Fade', likes: 142, date: 'Sep 18, 2026' },
  { id: '2', title: 'South of France Burst Fade', category: 'Burst', likes: 98, date: 'Sep 15, 2026' },
  { id: '3', title: 'Executive Contour & Clean Shave', category: 'Classic', likes: 210, date: 'Sep 10, 2026' },
  { id: '4', title: 'Wave Sculpt with Razor Part', category: 'Waves', likes: 175, date: 'Sep 05, 2026' },
];

export default function GalleryScreen() {
  const router = useRouter();

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Barber Portfolio</Text>
        <View style={styles.headerRight} />
      </View>

      <FlatList
        data={SAMPLE_GALLERY}
        keyExtractor={(item) => item.id}
        contentContainerStyle={styles.content}
        ListHeaderComponent={
          <Card style={styles.headerCard} elevated>
            <Text style={styles.headerCardTitle}>Showcase Gallery</Text>
            <Text style={styles.headerCardDesc}>
              High-resolution client cuts and grooming styles featured in the CandyCutz customer inspiration gallery.
            </Text>
          </Card>
        }
        renderItem={({ item }) => (
          <Card style={styles.card} elevated>
            <View style={styles.imagePlaceholder}>
              <Text style={styles.imageIcon}>✂️</Text>
              <View style={styles.tagBadge}>
                <Text style={styles.tagText}>{item.category.toUpperCase()}</Text>
              </View>
            </View>

            <View style={styles.cardInfo}>
              <Text style={styles.itemTitle}>{item.title}</Text>
              <View style={styles.metaRow}>
                <Text style={styles.likes}>❤️ {item.likes} client likes</Text>
                <Text style={styles.date}>{item.date}</Text>
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
    overflow: 'hidden',
    padding: 0,
  },
  imagePlaceholder: {
    height: 160,
    backgroundColor: '#121214',
    alignItems: 'center',
    justifyContent: 'center',
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
    position: 'relative',
  },
  imageIcon: {
    fontSize: 36,
  },
  tagBadge: {
    position: 'absolute',
    top: 10,
    right: 10,
    backgroundColor: 'rgba(255, 153, 0, 0.25)',
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: RADIUS.sm,
  },
  tagText: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
  },
  cardInfo: {
    padding: SPACING.md,
  },
  itemTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginBottom: 6,
  },
  metaRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  likes: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  date: {
    color: COLORS.textMuted,
    fontSize: 11,
  },
});
