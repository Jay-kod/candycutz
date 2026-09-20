import React, { useState } from 'react';
import {
  FlatList,
  SafeAreaView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';

interface WishlistItem {
  id: string;
  name: string;
  category: string;
  price: number;
  duration: number;
  description: string;
}

const SAMPLE_WISHLIST: WishlistItem[] = [
  {
    id: '1',
    name: 'Executive Fade & Beard Sculpt',
    category: 'Haircut & Styling',
    price: 7000,
    duration: 45,
    description: 'Precision clipper fade with razor edge lining, hot towel, and organic beard oil treatment.',
  },
  {
    id: '2',
    name: 'Luxury Royal Shave & Facial',
    category: 'Grooming & Spa',
    price: 12000,
    duration: 60,
    description: 'Traditional straight razor shave with essential oils, botanical exfoliation, and clay face mask.',
  },
  {
    id: '3',
    name: 'Kids Royal Cut',
    category: 'Kids Grooming',
    price: 4500,
    duration: 30,
    description: 'Patient and gentle styling for young kings, finished with sweet styling pomade.',
  },
];

export default function WishlistScreen() {
  const router = useRouter();
  const [items, setItems] = useState<WishlistItem[]>(SAMPLE_WISHLIST);

  const handleRemove = (id: string) => {
    setItems((prev) => prev.filter((item) => item.id !== id));
  };

  const handleBook = (item: WishlistItem) => {
    router.push({
      pathname: '/book/[id]',
      params: { id: item.id },
    });
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Saved Wishlist</Text>
        <View style={styles.headerRight} />
      </View>

      {items.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Text style={styles.emptyIcon}>❤️</Text>
          <Text style={styles.emptyTitle}>Your Wishlist is Empty</Text>
          <Text style={styles.emptySubtitle}>
            Browse our catalog and tap the heart icon on styles and treatments you love.
          </Text>
          <Button
            title="Browse Services"
            onPress={() => router.push('/(tabs)/services')}
            style={styles.browseBtn}
          />
        </View>
      ) : (
        <FlatList
          data={items}
          keyExtractor={(item) => item.id}
          contentContainerStyle={styles.listContent}
          renderItem={({ item }) => (
            <Card style={styles.card} elevated>
              <View style={styles.cardTop}>
                <View style={styles.tag}>
                  <Text style={styles.tagText}>{item.category.toUpperCase()}</Text>
                </View>
                <TouchableOpacity onPress={() => handleRemove(item.id)} hitSlop={12}>
                  <Text style={styles.removeIcon}>✕</Text>
                </TouchableOpacity>
              </View>

              <Text style={styles.itemName}>{item.name}</Text>
              <Text style={styles.itemDesc}>{item.description}</Text>

              <View style={styles.cardFooter}>
                <View>
                  <Text style={styles.priceLabel}>PRICE</Text>
                  <Text style={styles.priceVal}>₦{item.price.toLocaleString()}</Text>
                </View>
                <View style={styles.footerActions}>
                  <Text style={styles.duration}>⏱ {item.duration} mins</Text>
                  <Button
                    title="Book Now"
                    size="sm"
                    onPress={() => handleBook(item)}
                  />
                </View>
              </View>
            </Card>
          )}
        />
      )}
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
  removeIcon: {
    color: COLORS.textMuted,
    fontSize: 16,
    fontWeight: '700',
  },
  itemName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginBottom: 4,
  },
  itemDesc: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 18,
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
  priceLabel: {
    color: COLORS.textMuted,
    fontSize: 9,
    fontWeight: '700',
  },
  priceVal: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.md,
    fontWeight: '800',
  },
  footerActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  duration: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
  },
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: SPACING.xl,
  },
  emptyIcon: {
    fontSize: 48,
    marginBottom: SPACING.md,
  },
  emptyTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '700',
    marginBottom: 6,
  },
  emptySubtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: SPACING.lg,
  },
  browseBtn: {
    minWidth: 180,
  },
});
