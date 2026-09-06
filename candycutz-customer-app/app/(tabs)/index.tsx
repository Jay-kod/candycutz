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
import { barbersApi, servicesApi } from '../../src/api/client';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { Header } from '../../src/components/common/Header';
import { CONFIG } from '../../src/constants/config';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { Barber, Service } from '../../src/types';

export default function HomeScreen() {
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
    paddingBottom: 40,
  },
  heroCard: {
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.lg,
    padding: SPACING.lg,
    borderWidth: 1,
    borderColor: COLORS.primaryLight,
    marginBottom: SPACING.md,
  },
  heroBadge: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
    letterSpacing: 1.5,
    marginBottom: 6,
  },
  heroTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.hero - 6,
    fontWeight: '900',
    lineHeight: 34,
    marginBottom: 8,
  },
  heroSubtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 20,
    marginBottom: 18,
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
    marginBottom: SPACING.lg,
    borderLeftWidth: 4,
    borderLeftColor: COLORS.primary,
  },
  branchHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  branchLabel: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
    marginBottom: 2,
  },
  branchName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
  },
  mapsIconButton: {
    backgroundColor: COLORS.surfaceHighlight,
    padding: 8,
    borderRadius: RADIUS.md,
  },
  mapsIconText: {
    fontSize: 18,
  },
  branchAddress: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 18,
    marginTop: 6,
    marginBottom: 10,
  },
  branchFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    paddingTop: 10,
  },
  branchHours: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
  },
  directionsLink: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: SPACING.md,
    marginTop: SPACING.sm,
  },
  sectionTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
  },
  seeAllLink: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  loader: {
    marginVertical: 20,
  },
  servicesGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    gap: 12,
  },
  serviceItemCard: {
    width: '48%',
    padding: SPACING.md,
  },
  servicePricePill: {
    backgroundColor: COLORS.primaryLight,
    borderRadius: RADIUS.full,
    paddingHorizontal: 8,
    paddingVertical: 3,
    alignSelf: 'flex-start',
    marginBottom: 8,
  },
  servicePriceText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
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
    lineHeight: 16,
    marginBottom: 12,
  },
  serviceBottom: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 'auto',
  },
  serviceDuration: {
    color: COLORS.textMuted,
    fontSize: 11,
  },
  bookArrow: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  barbersScroll: {
    marginBottom: SPACING.lg,
  },
  barberCard: {
    width: 160,
    alignItems: 'center',
    marginRight: 12,
    padding: SPACING.md,
  },
  barberAvatar: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 2,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 10,
  },
  barberInitials: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.lg,
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
    marginBottom: 4,
  },
  starIcon: {
    color: COLORS.primary,
    fontSize: 12,
    marginRight: 3,
  },
  ratingText: {
    color: COLORS.textPrimary,
    fontSize: 12,
    fontWeight: '700',
    marginRight: 3,
  },
  reviewsCount: {
    color: COLORS.textMuted,
    fontSize: 11,
  },
  specialtyText: {
    color: COLORS.textSecondary,
    fontSize: 11,
    marginBottom: 12,
    textAlign: 'center',
  },
  barberBtn: {
    width: '100%',
  },
  homeServicePromo: {
    marginTop: SPACING.sm,
    borderWidth: 1,
    borderColor: COLORS.primaryLight,
    padding: SPACING.lg,
  },
  promoTag: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1.5,
    marginBottom: 4,
  },
  promoTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
    marginBottom: 8,
  },
  promoDesc: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 20,
    marginBottom: 16,
  },
  promoBtn: {
    width: '100%',
  },
});
