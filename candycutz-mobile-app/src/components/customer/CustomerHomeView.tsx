import React from 'react';
import {
  ActivityIndicator,
  Linking,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useQuery } from '@tanstack/react-query';
import { SafeAreaView } from 'react-native-safe-area-context';
import { barbersApi, servicesApi } from '../../api/client';
import { Button } from '../common/Button';
import { Card } from '../common/Card';
import { Header } from '../common/Header';
import { CONFIG } from '../../constants/config';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { Barber, Service } from '../../types';

export function CustomerHomeView() {
  const router = useRouter();

  const {
    data: services = [],
    isLoading: loadingServices,
    refetch: refetchServices,
  } = useQuery({
    queryKey: ['services'],
    queryFn: servicesApi.getAll,
  });

  const {
    data: barbers = [],
    isLoading: loadingBarbers,
    refetch: refetchBarbers,
  } = useQuery({
    queryKey: ['barbers'],
    queryFn: barbersApi.getAll,
  });

  const onRefresh = () => {
    refetchServices();
    refetchBarbers();
  };

  const handleOpenMaps = () => {
    Linking.openURL(CONFIG.BRANCH.MAPS_URL);
  };

  const featuredServices = services.slice(0, 4);

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <Header />
      <ScrollView
        style={styles.container}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl
            refreshing={loadingServices || loadingBarbers}
            onRefresh={onRefresh}
            tintColor={COLORS.primary}
          />
        }
      >
        {/* Hero Section */}
        <View style={styles.heroCard}>
          <Text style={styles.heroBadge}>KEFFI FLAGSHIP SALOON</Text>
          <Text style={styles.heroTitle}>Master Cuts & Luxury Grooming</Text>
          <Text style={styles.heroSubtitle}>
            Precision haircuts, beard sculpts, and premium home services right here in Keffi, Nasarawa State.
          </Text>
          <View style={styles.heroActionRow}>
            <Button
              title="Book In-Shop"
              onPress={() => router.push('/(tabs)/services')}
              style={styles.heroBtn}
            />
            <Button
              title="Home Service"
              variant="outline"
              onPress={() => router.push('/(tabs)/services')}
              style={styles.heroBtnOutline}
            />
          </View>
        </View>

        {/* Physical Branch Location Banner */}
        <Card style={styles.branchCard} elevated>
          <View style={styles.branchHeader}>
            <View>
              <Text style={styles.branchLabel}>PHYSICAL SALOON LOCATION</Text>
              <Text style={styles.branchName}>{CONFIG.BRANCH.NAME}</Text>
            </View>
            <TouchableOpacity onPress={handleOpenMaps} style={styles.mapsIconButton}>
              <Text style={styles.mapsIconText}>🗺</Text>
            </TouchableOpacity>
          </View>
          <Text style={styles.branchAddress}>{CONFIG.BRANCH.ADDRESS}</Text>
          <View style={styles.branchFooter}>
            <Text style={styles.branchHours}>Hours: {CONFIG.OPENING_TIME} - {CONFIG.CLOSING_TIME} Daily</Text>
            <TouchableOpacity onPress={handleOpenMaps}>
              <Text style={styles.directionsLink}>Open in Google Maps &rarr;</Text>
            </TouchableOpacity>
          </View>
        </Card>

        {/* Featured Services */}
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Popular Services</Text>
          <TouchableOpacity onPress={() => router.push('/(tabs)/services')}>
            <Text style={styles.seeAllLink}>View All</Text>
          </TouchableOpacity>
        </View>

        {loadingServices ? (
          <ActivityIndicator color={COLORS.primary} style={styles.loader} />
        ) : (
          <View style={styles.servicesGrid}>
            {featuredServices.map((service: Service) => (
              <Card
                key={service.id}
                style={styles.serviceItemCard}
                onPress={() => router.push(`/book/${service.id}`)}
              >
                <View style={styles.servicePricePill}>
                  <Text style={styles.servicePriceText}>₦{Number(service.price).toLocaleString()}</Text>
                </View>
                <Text style={styles.serviceName}>{service.name}</Text>
                <Text style={styles.serviceDesc} numberOfLines={2}>
                  {service.description || 'Premium styling crafted to perfection.'}
                </Text>
                <View style={styles.serviceBottom}>
                  <Text style={styles.serviceDuration}>⏱ {service.duration_minutes} mins</Text>
                  <Text style={styles.bookArrow}>Book &rarr;</Text>
                </View>
              </Card>
            ))}
          </View>
        )}

        {/* Master Barbers Carousel */}
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Master Stylists</Text>
        </View>

        {loadingBarbers ? (
          <ActivityIndicator color={COLORS.primary} style={styles.loader} />
        ) : (
          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.barbersScroll}>
            {barbers.map((barber: Barber) => (
              <Card key={barber.id} style={styles.barberCard}>
                <View style={styles.barberAvatar}>
                  <Text style={styles.barberInitials}>
                    {(barber.name || 'B').substring(0, 2).toUpperCase()}
                  </Text>
                </View>
                <Text style={styles.barberName}>{barber.name}</Text>
                <View style={styles.ratingRow}>
                  <Text style={styles.starIcon}>★</Text>
                  <Text style={styles.ratingText}>{Number(barber.rating || 5.0).toFixed(1)}</Text>
                  <Text style={styles.reviewsCount}>({barber.total_reviews || 48})</Text>
                </View>
                <Text style={styles.specialtyText}>Precision Beard & Fade</Text>
                <Button
                  title="Select Stylist"
                  size="sm"
                  variant="outline"
                  onPress={() => router.push('/(tabs)/services')}
                  style={styles.barberBtn}
                />
              </Card>
            ))}
          </ScrollView>
        )}

        {/* Home Service Coverage in Keffi */}
        <Card style={styles.homeServicePromo} elevated>
          <Text style={styles.promoTag}>VIP CONCIERGE</Text>
          <Text style={styles.promoTitle}>Can't make it to the shop?</Text>
          <Text style={styles.promoDesc}>
            Our certified master barbers travel directly to your doorstep anywhere across Keffi: Angwan Kare, High Court, Total, Gidan Zakara, and NSUK campuses.
          </Text>
          <Button
            title="Book Home Service"
            onPress={() => router.push('/(tabs)/services')}
            style={styles.promoBtn}
          />
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
  container: {
    flex: 1,
  },
  scrollContent: {
    padding: SPACING.md,
    gap: SPACING.md,
    paddingBottom: 40,
  },
  loader: {
    marginVertical: SPACING.lg,
  },
  heroCard: {
    backgroundColor: COLORS.surfaceElevated,
    borderRadius: RADIUS.lg,
    padding: SPACING.lg,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  heroBadge: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1.5,
    marginBottom: 8,
  },
  heroTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xxl,
    fontWeight: '800',
    lineHeight: 32,
    marginBottom: 8,
  },
  heroSubtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 20,
    marginBottom: 16,
  },
  heroActionRow: {
    flexDirection: 'row',
    gap: 12,
  },
  heroBtn: {
    flex: 1,
  },
  heroBtnOutline: {
    flex: 1,
  },
  branchCard: {
    padding: SPACING.md,
    backgroundColor: '#16140E',
    borderColor: COLORS.primaryLight,
  },
  branchHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 6,
  },
  branchLabel: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
  },
  branchName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginTop: 2,
  },
  mapsIconButton: {
    backgroundColor: COLORS.surfaceHighlight,
    padding: 8,
    borderRadius: RADIUS.sm,
  },
  mapsIconText: {
    fontSize: 18,
  },
  branchAddress: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
    marginBottom: 8,
  },
  branchFooter: {
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    paddingTop: 8,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  branchHours: {
    color: COLORS.textMuted,
    fontSize: 11,
  },
  directionsLink: {
    color: COLORS.primary,
    fontSize: 11,
    fontWeight: '700',
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: SPACING.sm,
  },
  sectionTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
  },
  seeAllLink: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  servicesGrid: {
    gap: 12,
  },
  serviceItemCard: {
    padding: SPACING.md,
  },
  servicePricePill: {
    alignSelf: 'flex-start',
    backgroundColor: COLORS.primaryLight,
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: RADIUS.full,
    marginBottom: 8,
  },
  servicePriceText: {
    color: COLORS.primary,
    fontSize: 11,
    fontWeight: '800',
  },
  serviceName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginBottom: 4,
  },
  serviceDesc: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
    marginBottom: 10,
  },
  serviceBottom: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    paddingTop: 8,
  },
  serviceDuration: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
  },
  bookArrow: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  barbersScroll: {
    marginHorizontal: -SPACING.md,
    paddingHorizontal: SPACING.md,
  },
  barberCard: {
    width: 170,
    padding: SPACING.md,
    alignItems: 'center',
    marginRight: 12,
  },
  barberAvatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1.5,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 8,
  },
  barberInitials: {
    color: COLORS.primary,
    fontSize: 20,
    fontWeight: '800',
  },
  barberName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
    textAlign: 'center',
    marginBottom: 4,
  },
  ratingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    marginBottom: 4,
  },
  starIcon: {
    color: COLORS.primary,
    fontSize: 12,
  },
  ratingText: {
    color: COLORS.textPrimary,
    fontSize: 12,
    fontWeight: '700',
  },
  reviewsCount: {
    color: COLORS.textMuted,
    fontSize: 11,
  },
  specialtyText: {
    color: COLORS.textSecondary,
    fontSize: 10,
    textAlign: 'center',
    marginBottom: 10,
  },
  barberBtn: {
    width: '100%',
  },
  homeServicePromo: {
    padding: SPACING.lg,
    backgroundColor: COLORS.surfaceElevated,
  },
  promoTag: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
    marginBottom: 4,
  },
  promoTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
    marginBottom: 6,
  },
  promoDesc: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
    marginBottom: 14,
  },
  promoBtn: {
    width: '100%',
  },
});
