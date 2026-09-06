import React, { useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useQuery } from '@tanstack/react-query';
import { SafeAreaView } from 'react-native-safe-area-context';
import { servicesApi } from '../../src/api/client';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { Service } from '../../src/types';

const CATEGORIES = ['All', 'Haircut', 'Beard', 'Packages', 'Home VIP'];

export default function ServicesScreen() {
  const router = useRouter();
  const [selectedCategory, setSelectedCategory] = useState('All');

  const {
    data: services = [],
    isLoading,
    refetch,
  } = useQuery({
    queryKey: ['services'],
    queryFn: servicesApi.getAll,
  });

  const filteredServices = services.filter((service: Service) => {
    if (selectedCategory === 'All') return true;
    if (selectedCategory === 'Haircut') return service.name.toLowerCase().includes('cut') || service.name.toLowerCase().includes('fade');
    if (selectedCategory === 'Beard') return service.name.toLowerCase().includes('beard') || service.name.toLowerCase().includes('shave');
    if (selectedCategory === 'Packages') return service.name.toLowerCase().includes('combo') || service.name.toLowerCase().includes('package');
    if (selectedCategory === 'Home VIP') return service.name.toLowerCase().includes('vip') || service.home_service_price;
    return true;
  });

  const renderServiceItem = ({ item }: { item: Service }) => (
    <Card style={styles.serviceCard} elevated>
      <View style={styles.serviceTopRow}>
        <View style={styles.serviceMeta}>
          <Text style={styles.serviceName}>{item.name}</Text>
          <Text style={styles.serviceDuration}>⏱ {item.duration_minutes} minutes</Text>
        </View>
        <View style={styles.priceContainer}>
          <Text style={styles.currencySymbol}>₦</Text>
          <Text style={styles.priceAmount}>{Number(item.price).toLocaleString()}</Text>
        </View>
      </View>

      <Text style={styles.serviceDescription}>
        {item.description || 'Premium styling crafted with artisanal barbershop expertise.'}
      </Text>

      {item.home_service_price && (
        <View style={styles.homeServiceRateRow}>
          <Text style={styles.homeServiceLabel}>Home Service Available:</Text>
          <Text style={styles.homeServicePrice}>₦{Number(item.home_service_price).toLocaleString()}</Text>
        </View>
      )}

      <View style={styles.actionRow}>
        <Button
          title="Book Appointment"
          onPress={() => router.push(`/book/${item.id}`)}
          style={styles.bookButton}
        />
      </View>
    </Card>
  );

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>Services & Pricing</Text>
        <Text style={styles.subtitle}>Handcrafted grooming services in Keffi</Text>
      </View>

      {/* Category Filter Pills */}
      <View style={styles.categoriesContainer}>
        <FlatList
          horizontal
          showsHorizontalScrollIndicator={false}
          data={CATEGORIES}
          keyExtractor={(item) => item}
          contentContainerStyle={styles.categoriesList}
          renderItem={({ item }) => {
            const isSelected = selectedCategory === item;
            return (
              <TouchableOpacity
                activeOpacity={0.8}
                onPress={() => setSelectedCategory(item)}
                style={[
                  styles.categoryPill,
                  isSelected && styles.categoryPillActive,
                ]}
              >
                <Text
                  style={[
                    styles.categoryText,
                    isSelected && styles.categoryTextActive,
                  ]}
                >
                  {item}
                </Text>
              </TouchableOpacity>
            );
          }}
        />
      </View>

      {/* Service List */}
      {isLoading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
        </View>
      ) : (
        <FlatList
          data={filteredServices}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderServiceItem}
          contentContainerStyle={styles.servicesList}
          refreshControl={
            <RefreshControl
              refreshing={isLoading}
              onRefresh={refetch}
              tintColor={COLORS.primary}
            />
          }
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>No services found in this category.</Text>
            </View>
          }
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
    paddingHorizontal: SPACING.md,
    paddingTop: SPACING.sm,
    paddingBottom: SPACING.sm,
  },
  title: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.hero - 8,
    fontWeight: '800',
  },
  subtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    marginTop: 2,
  },
  categoriesContainer: {
    marginVertical: SPACING.sm,
  },
  categoriesList: {
    paddingHorizontal: SPACING.md,
    gap: 8,
  },
  categoryPill: {
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: RADIUS.full,
    backgroundColor: COLORS.surfaceElevated,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  categoryPillActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  categoryText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  categoryTextActive: {
    color: '#0A0A0C',
  },
  servicesList: {
    padding: SPACING.md,
    gap: 12,
    paddingBottom: 32,
  },
  serviceCard: {
    padding: SPACING.md,
  },
  serviceTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  serviceMeta: {
    flex: 1,
    marginRight: 12,
  },
  serviceName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '700',
    marginBottom: 4,
  },
  serviceDuration: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
    fontWeight: '500',
  },
  priceContainer: {
    flexDirection: 'row',
    alignItems: 'baseline',
    backgroundColor: COLORS.primaryLight,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: RADIUS.md,
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.3)',
  },
  currencySymbol: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
    marginRight: 2,
  },
  priceAmount: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.md,
    fontWeight: '800',
  },
  serviceDescription: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 20,
    marginVertical: 10,
  },
  homeServiceRateRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surfaceHighlight,
    padding: 8,
    borderRadius: RADIUS.sm,
    marginBottom: 12,
  },
  homeServiceLabel: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
    marginRight: 6,
  },
  homeServicePrice: {
    color: COLORS.textGold,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  actionRow: {
    marginTop: 4,
  },
  bookButton: {
    width: '100%',
  },
  centerContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyContainer: {
    padding: 40,
    alignItems: 'center',
  },
  emptyText: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.sm,
  },
});
