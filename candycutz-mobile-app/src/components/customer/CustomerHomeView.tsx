import React from 'react';
import {
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
import { ServiceSkeletons, Skeleton } from '../common/Skeleton';
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
        <View style={styles.welcomeRow}>
          <View style={styles.welcomeCopy}>
            <Text style={styles.eyebrow}>WELCOME TO CANDYCUTZ</Text>
            <Text style={styles.welcomeTitle}>Ready for your next look?</Text>
            <Text style={styles.welcomeSubtitle}>Premium grooming, booked around your day.</Text>
          </View>
          <View style={styles.openBadge}>
            <View style={styles.openDot} />
            <Text style={styles.openText}>Open</Text>
          </View>
        </View>

        <View style={styles.primaryAction}>
          <View style={styles.primaryActionCopy}>
            <Text style={styles.primaryActionLabel}>YOUR TIME, YOUR CHAIR</Text>
            <Text style={styles.primaryActionTitle}>Book a fresh cut</Text>
            <Text style={styles.primaryActionSubtitle}>Choose a service and a time that works for you.</Text>
          </View>
          <Button title="Book now" onPress={() => router.push('/(tabs)/services')} style={styles.primaryActionButton} />
        </View>

        <View style={styles.quickActions}>
          <TouchableOpacity style={styles.quickAction} onPress={() => router.push('/(tabs)/services')}>
            <Text style={styles.quickIcon}>✂</Text>
            <Text style={styles.quickLabel}>Services</Text>
            <Text style={styles.quickHint}>Browse menu</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.quickAction} onPress={() => router.push('/(tabs)/bookings')}>
            <Text style={styles.quickIcon}>◷</Text>
            <Text style={styles.quickLabel}>Bookings</Text>
            <Text style={styles.quickHint}>View visits</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.quickAction} onPress={handleOpenMaps}>
            <Text style={styles.quickIcon}>⌖</Text>
            <Text style={styles.quickLabel}>Find us</Text>
            <Text style={styles.quickHint}>Get directions</Text>
          </TouchableOpacity>
        </View>

        <View style={styles.branchStrip}>
          <View style={styles.branchStripCopy}>
            <Text style={styles.branchStripTitle}>{CONFIG.BRANCH.NAME}</Text>
            <Text style={styles.branchStripMeta}>{CONFIG.OPENING_TIME} - {CONFIG.CLOSING_TIME} daily · Keffi</Text>
          </View>
          <TouchableOpacity onPress={handleOpenMaps} style={styles.directionsButton}>
            <Text style={styles.directionsButtonText}>Map</Text>
          </TouchableOpacity>
        </View>

        {/* Featured Services */}
        <View style={styles.sectionHeader}>
          <View>
            <Text style={styles.sectionTitle}>Popular services</Text>
            <Text style={styles.sectionHint}>Quick picks for your next visit</Text>
          </View>
          <TouchableOpacity onPress={() => router.push('/(tabs)/services')}>
            <Text style={styles.seeAllLink}>View All</Text>
          </TouchableOpacity>
        </View>

        {loadingServices ? (
          <ServiceSkeletons />
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
          <View>
            <Text style={styles.sectionTitle}>Meet your stylists</Text>
            <Text style={styles.sectionHint}>Skilled hands, sharp results</Text>
          </View>
        </View>

        {loadingBarbers ? (
          <View style={styles.barberSkeletonRow}>
            {[1, 2].map((item) => <Skeleton key={item} style={styles.barberSkeleton} />)}
          </View>
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
  welcomeRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    paddingTop: SPACING.sm,
  },
  welcomeCopy: {
    flex: 1,
    paddingRight: SPACING.md,
  },
  eyebrow: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1.4,
    marginBottom: 6,
  },
  welcomeTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xxl,
    fontWeight: '800',
    lineHeight: 31,
  },
  welcomeSubtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    lineHeight: 19,
    marginTop: 6,
  },
  openBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: RADIUS.full,
    backgroundColor: COLORS.successLight,
    borderWidth: 1,
    borderColor: 'rgba(16, 185, 129, 0.35)',
  },
  openDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: COLORS.success,
    marginRight: 5,
  },
  openText: {
    color: COLORS.success,
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
  },
  primaryAction: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: SPACING.md,
    borderRadius: RADIUS.lg,
    backgroundColor: COLORS.primary,
  },
  primaryActionCopy: {
    flex: 1,
    paddingRight: SPACING.sm,
  },
  primaryActionLabel: {
    color: '#5C4610',
    fontSize: 9,
    fontWeight: '900',
    letterSpacing: 1.1,
    marginBottom: 4,
  },
  primaryActionTitle: {
    color: '#0A0A0C',
    fontSize: FONTS.sizes.lg,
    fontWeight: '900',
  },
  primaryActionSubtitle: {
    color: '#5C4610',
    fontSize: FONTS.sizes.xs,
    lineHeight: 16,
    marginTop: 3,
  },
  primaryActionButton: {
    backgroundColor: '#0A0A0C',
    paddingHorizontal: 14,
  },
  quickActions: {
    flexDirection: 'row',
    gap: SPACING.sm,
  },
  quickAction: {
    flex: 1,
    minHeight: 94,
    padding: SPACING.sm,
    borderRadius: RADIUS.md,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  quickIcon: {
    color: COLORS.primary,
    fontSize: 22,
    marginBottom: 5,
  },
  quickLabel: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '800',
  },
  quickHint: {
    color: COLORS.textMuted,
    fontSize: 10,
    marginTop: 2,
  },
  branchStrip: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 3,
  },
  branchStripCopy: {
    flex: 1,
  },
  branchStripTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  branchStripMeta: {
    color: COLORS.textMuted,
    fontSize: 11,
    marginTop: 3,
  },
  directionsButton: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: RADIUS.sm,
    backgroundColor: COLORS.surfaceHighlight,
  },
  directionsButtonText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
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
  sectionHint: {
    color: COLORS.textMuted,
    fontSize: 11,
    marginTop: 3,
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
  barberSkeletonRow: {
    flexDirection: 'row',
    gap: 12,
  },
  barberSkeleton: {
    width: 170,
    height: 190,
    borderRadius: 16,
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
