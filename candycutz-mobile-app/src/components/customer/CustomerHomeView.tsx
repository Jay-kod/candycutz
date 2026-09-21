import React, { useMemo, useState } from 'react';
import {
  Linking,
  Platform,
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
import { LinearGradient } from 'expo-linear-gradient';
import {
  Scissors,
  Calendar,
  Sparkles,
  MapPin,
  Clock,
  Star,
  ChevronRight,
  ShieldCheck,
  Navigation,
  User as UserIcon,
  ArrowRight,
  CheckCircle2,
} from 'lucide-react-native';

import { barbersApi, bookingsApi, servicesApi } from '../../api/client';
import { ActionDialog } from '../common/ActionDialog';
import { Button } from '../common/Button';
import { Card } from '../common/Card';
import { Badge } from '../common/Badge';
import { Header } from '../common/Header';
import {
  BarberCardSkeletons,
  HomeAppointmentSkeleton,
  ServiceSkeletons,
  Skeleton,
} from '../common/Skeleton';
import { CONFIG } from '../../constants/config';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useAuthStore } from '../../store/authStore';
import { useToastStore } from '../../store/toastStore';
import { Appointment, Barber, Service } from '../../types';

export function CustomerHomeView() {
  const router = useRouter();
  const { user, isAuthenticated } = useAuthStore();
  const showToast = useToastStore((state) => state.show);
  const [activeVerificationCode, setActiveVerificationCode] = useState<string | null>(null);

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

  const {
    data: appointments = [],
    isLoading: loadingAppointments,
    refetch: refetchAppointments,
  } = useQuery<Appointment[]>({
    queryKey: ['customer-home-appointments'],
    queryFn: () => bookingsApi.getAll(),
    enabled: isAuthenticated,
  });

  // Calculate the next active upcoming appointment
  const nextAppointment = useMemo(() => {
    if (!isAuthenticated || !appointments.length) return null;
    const today = new Date().toISOString().split('T')[0];
    const active = appointments
      .filter((a) => {
        const status = (a.status || '').toLowerCase();
        return (status === 'confirmed' || status === 'pending') && a.appointment_date >= today;
      })
      .sort((a, b) => {
        const timeA = a.start_time || (a as any).appointment_time || '';
        const timeB = b.start_time || (b as any).appointment_time || '';
        const dateA = `${a.appointment_date} ${timeA}`;
        const dateB = `${b.appointment_date} ${timeB}`;
        return dateA.localeCompare(dateB);
      });
    return active[0] || null;
  }, [isAuthenticated, appointments]);

  // Dynamic time-of-day greeting
  const greeting = useMemo(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
  }, []);

  const displayName = useMemo(() => {
    if (!isAuthenticated || !user) return 'Gentleman';
    return user.real_name || user.name?.split(' ')[0] || 'Member';
  }, [isAuthenticated, user]);

  const onRefresh = () => {
    refetchServices();
    refetchBarbers();
    if (isAuthenticated) {
      refetchAppointments();
    }
  };

  const handleOpenMaps = () => {
    Linking.openURL(CONFIG.BRANCH.MAPS_URL);
  };

  const handleCopyCode = (code: string) => {
    setActiveVerificationCode(code);
  };

  const featuredServices = services.slice(0, 4);

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      {/* Clean top navigation header */}
      <Header showLocationBadge={false} />

      <ScrollView
        style={styles.container}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        refreshControl={
          <RefreshControl
            refreshing={loadingServices || loadingBarbers || loadingAppointments}
            onRefresh={onRefresh}
            tintColor={COLORS.primary}
          />
        }
      >
        {/* Personalized Greeting & Status Bar */}
        <View style={styles.greetingBar}>
          <View style={styles.greetingCopy}>
            <View style={styles.eyebrowRow}>
              <Sparkles size={11} color={COLORS.primary} />
              <Text style={styles.eyebrowText}>
                {isAuthenticated ? 'VIP CLIENT' : 'WELCOME TO CANDYCUTZ'}
              </Text>
            </View>
            <Text style={styles.greetingTitle}>
              {greeting}, <Text style={styles.goldText}>{displayName}</Text>
            </Text>
            <Text style={styles.greetingSubtitle}>Ready for your signature grooming?</Text>
          </View>

          <View style={styles.branchStatusPill}>
            <View style={styles.statusDotLive} />
            <View>
              <Text style={styles.statusTitle}>Open Daily</Text>
              <Text style={styles.statusSubtitle}>Keffi Lounge</Text>
            </View>
          </View>
        </View>

        {/* Next Appointment Spotlight OR Luxury Hero Booking Banner */}
        {isAuthenticated && loadingAppointments ? (
          <HomeAppointmentSkeleton />
        ) : nextAppointment ? (
          <Card style={styles.spotlightCard} elevated>
            <LinearGradient
              colors={['#242016', '#141419', '#0E0E12']}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={styles.spotlightGradient}
            >
              <View style={styles.spotlightHeader}>
                <View style={styles.spotlightTagRow}>
                  <Sparkles size={12} color={COLORS.primary} />
                  <Text style={styles.spotlightTag}>UPCOMING APPOINTMENT</Text>
                </View>
                <Badge status={nextAppointment.status} />
              </View>

              <Text style={styles.spotlightServiceName}>
                {nextAppointment.service?.name || 'Signature Haircut'}
              </Text>

              <View style={styles.spotlightDetailsRow}>
                <View style={styles.spotlightDetail}>
                  <Calendar size={13} color={COLORS.primary} />
                  <Text style={styles.spotlightDetailText}>
                    {nextAppointment.appointment_date}
                  </Text>
                </View>
                <View style={styles.spotlightDetail}>
                  <Clock size={13} color={COLORS.primary} />
                  <Text style={styles.spotlightDetailText}>
                    {nextAppointment.start_time || (nextAppointment as any).appointment_time}
                  </Text>
                </View>
                <View style={styles.spotlightDetail}>
                  <UserIcon size={13} color={COLORS.primary} />
                  <Text style={styles.spotlightDetailText} numberOfLines={1}>
                    {nextAppointment.barber?.name || 'Stylist'}
                  </Text>
                </View>
              </View>

              {((nextAppointment as any).verification_code || nextAppointment.booking_reference) ? (
                <TouchableOpacity
                  activeOpacity={0.85}
                  onPress={() => handleCopyCode((nextAppointment as any).verification_code || nextAppointment.booking_reference)}
                  style={styles.codeContainer}
                >
                  <View style={styles.codeLeft}>
                    <ShieldCheck size={16} color={COLORS.success} />
                    <View>
                      <Text style={styles.codeLabel}>CHAIR VERIFICATION CODE</Text>
                      <Text style={styles.codeValue}>
                        #{(nextAppointment as any).verification_code || nextAppointment.booking_reference}
                      </Text>
                    </View>
                  </View>
                  <View style={styles.codeActionPill}>
                    <Text style={styles.codeActionText}>View</Text>
                  </View>
                </TouchableOpacity>
              ) : null}

              <View style={styles.spotlightActions}>
                <Button
                  title="View Bookings"
                  size="sm"
                  variant="secondary"
                  onPress={() => router.push('/(tabs)/bookings')}
                  style={styles.spotlightBtn}
                />
                <Button
                  title="Get Directions"
                  size="sm"
                  variant="outline"
                  onPress={handleOpenMaps}
                  style={styles.spotlightBtn}
                  icon={<Navigation size={13} color={COLORS.primary} />}
                />
              </View>
            </LinearGradient>
          </Card>
        ) : (
          <Card style={styles.heroCard} elevated>
            <LinearGradient
              colors={['#242017', '#17161D', '#0F0F13']}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
              style={styles.heroGradient}
            >
              <View style={styles.heroBadgeRow}>
                <Sparkles size={11} color={COLORS.primary} />
                <Text style={styles.heroBadgeText}>FLAGSHIP GROOMING EXPERIENCE</Text>
              </View>

              <Text style={styles.heroTitle}>
                Precision Cuts,{'\n'}
                <Text style={styles.goldText}>Zero Wait Time.</Text>
              </Text>

              <Text style={styles.heroSubtitle}>
                Reserve your master barber chair at CandyCutz Keffi Lounge in seconds.
              </Text>

              <Button
                title="Book an Appointment"
                size="md"
                variant="primary"
                onPress={() => router.push('/(tabs)/services')}
                style={styles.heroCtaBtn}
                icon={<Calendar size={16} color="#0A0A0C" />}
              />
            </LinearGradient>
          </Card>
        )}

        {/* Quick Action Navigation Tiles */}
        <View style={styles.quickGrid}>
          <TouchableOpacity
            style={styles.quickTile}
            activeOpacity={0.8}
            onPress={() => router.push('/(tabs)/services')}
          >
            <View style={styles.quickIconCircle}>
              <Scissors size={18} color={COLORS.primary} />
            </View>
            <Text style={styles.quickTileTitle}>Services</Text>
            <Text style={styles.quickTileHint}>Browse Menu</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.quickTile}
            activeOpacity={0.8}
            onPress={() => router.push('/(tabs)/bookings')}
          >
            <View style={styles.quickIconCircle}>
              <Calendar size={18} color={COLORS.primary} />
            </View>
            <Text style={styles.quickTileTitle}>Bookings</Text>
            <Text style={styles.quickTileHint}>My Visits</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.quickTile}
            activeOpacity={0.8}
            onPress={() => router.push('/(tabs)/services')}
          >
            <View style={styles.quickIconCircle}>
              <Sparkles size={18} color={COLORS.primary} />
            </View>
            <Text style={styles.quickTileTitle}>Concierge</Text>
            <Text style={styles.quickTileHint}>Home Service</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.quickTile}
            activeOpacity={0.8}
            onPress={handleOpenMaps}
          >
            <View style={styles.quickIconCircle}>
              <MapPin size={18} color={COLORS.primary} />
            </View>
            <Text style={styles.quickTileTitle}>Location</Text>
            <Text style={styles.quickTileHint}>Directions</Text>
          </TouchableOpacity>
        </View>

        {/* Popular Services Section */}
        <View style={styles.sectionHeader}>
          <View>
            <Text style={styles.sectionTitle}>Popular Services</Text>
            <Text style={styles.sectionSubtitle}>Handcrafted for the modern gentleman</Text>
          </View>
          <TouchableOpacity
            activeOpacity={0.7}
            onPress={() => router.push('/(tabs)/services')}
            style={styles.seeAllButton}
          >
            <Text style={styles.seeAllText}>View All</Text>
            <ChevronRight size={14} color={COLORS.primary} />
          </TouchableOpacity>
        </View>

        {loadingServices ? (
          <ServiceSkeletons />
        ) : (
          <View style={styles.servicesGrid}>
            {featuredServices.map((service: Service) => (
              <Card
                key={service.id}
                style={styles.serviceCard}
                onPress={() => router.push(`/book/${service.id}`)}
              >
                <View style={styles.serviceCardTop}>
                  <Text style={styles.serviceName}>{service.name}</Text>
                  <View style={styles.pricePill}>
                    <Text style={styles.priceText}>
                      ₦{Number(service.price).toLocaleString()}
                    </Text>
                  </View>
                </View>

                <Text style={styles.serviceDesc} numberOfLines={2}>
                  {service.description || 'Premium grooming tailored to perfection.'}
                </Text>

                <View style={styles.serviceCardBottom}>
                  <View style={styles.durationChip}>
                    <Clock size={12} color={COLORS.textSecondary} />
                    <Text style={styles.durationText}>{service.duration_minutes} mins</Text>
                  </View>

                  <View style={styles.bookActionRow}>
                    <Text style={styles.bookActionText}>Book</Text>
                    <ArrowRight size={12} color={COLORS.primary} />
                  </View>
                </View>
              </Card>
            ))}
          </View>
        )}

        {/* Master Stylists Carousel */}
        <View style={styles.sectionHeader}>
          <View>
            <Text style={styles.sectionTitle}>Master Stylists</Text>
            <Text style={styles.sectionSubtitle}>Skilled hands, precision execution</Text>
          </View>
        </View>

        {loadingBarbers ? (
          <BarberCardSkeletons count={3} />
        ) : (
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            style={styles.barbersScroll}
            contentContainerStyle={styles.barbersScrollContent}
          >
            {barbers.map((barber: Barber) => (
              <Card key={barber.id} style={styles.barberCard}>
                <View style={styles.barberAvatarWrapper}>
                  <View style={styles.barberAvatar}>
                    <Text style={styles.barberInitials}>
                      {(barber.name || 'B').substring(0, 2).toUpperCase()}
                    </Text>
                  </View>
                  <View style={styles.verifiedBadge}>
                    <CheckCircle2 size={12} color="#0A0A0C" />
                  </View>
                </View>

                <Text style={styles.barberName} numberOfLines={1}>
                  {barber.name}
                </Text>
                <Text style={styles.specialtyText} numberOfLines={1}>
                  Precision Fade & Beard
                </Text>

                <View style={styles.ratingRow}>
                  <Star size={12} color={COLORS.primary} fill={COLORS.primary} />
                  <Text style={styles.ratingText}>
                    {Number(barber.rating || 5.0).toFixed(1)}
                  </Text>
                  <Text style={styles.reviewsCount}>
                    ({barber.total_reviews || 48})
                  </Text>
                </View>

                <Button
                  title="Select Chair"
                  size="sm"
                  variant="outline"
                  onPress={() => router.push('/(tabs)/services')}
                  style={styles.barberBtn}
                />
              </Card>
            ))}
          </ScrollView>
        )}

        {/* VIP Concierge Home Service Card */}
        <Card style={styles.conciergeCard} elevated>
          <LinearGradient
            colors={['#1F1B12', '#14131A', '#0D0C10']}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={styles.conciergeGradient}
          >
            <View style={styles.conciergeBadge}>
              <Sparkles size={11} color={COLORS.primary} />
              <Text style={styles.conciergeBadgeText}>VIP CONCIERGE</Text>
            </View>

            <Text style={styles.conciergeTitle}>
              Luxury Barbing at Your Doorstep
            </Text>

            <Text style={styles.conciergeDesc}>
              Certified master barbers travel directly to your home or office across Keffi:
              Angwan Kare, High Court, Total, Gidan Zakara, and NSUK campuses.
            </Text>

            <Button
              title="Book Home Service"
              size="md"
              variant="primary"
              onPress={() => router.push('/(tabs)/services')}
              style={styles.conciergeBtn}
            />
          </LinearGradient>
        </Card>

        {/* Keffi Lounge Information & Directions */}
        <Card style={styles.loungeCard}>
          <View style={styles.loungeHeader}>
            <View style={styles.loungeIconCircle}>
              <MapPin size={18} color={COLORS.primary} />
            </View>
            <View style={styles.loungeInfo}>
              <Text style={styles.loungeTitle}>{CONFIG.BRANCH.NAME}</Text>
              <Text style={styles.loungeAddress}>
                Angwan Kare, BCG Road, Keffi, Nasarawa State
              </Text>
              <Text style={styles.loungeHours}>
                ⏱ Open {CONFIG.OPENING_TIME} – {CONFIG.CLOSING_TIME} Daily
              </Text>
            </View>
          </View>

          <TouchableOpacity
            activeOpacity={0.85}
            onPress={handleOpenMaps}
            style={styles.loungeActionBtn}
          >
            <Navigation size={14} color={COLORS.primary} />
            <Text style={styles.loungeActionText}>Open in Google Maps</Text>
          </TouchableOpacity>
        </Card>
      </ScrollView>

      {/* Verification Code Action Dialog */}
      <ActionDialog
        visible={!!activeVerificationCode}
        title="Verification Code"
        message={`Your code is:\n\n${activeVerificationCode || ''}\n\nPresent this code to your barber when you arrive at the chair.`}
        variant="primary"
        actions={[
          {
            label: 'Got it / Present to Barber',
            onPress: () => {
              showToast({
                variant: 'info',
                title: 'Code Ready',
                message: `Code ${activeVerificationCode} ready to present.`,
              });
              setActiveVerificationCode(null);
            },
          },
        ]}
        dismissLabel="Close"
        onDismiss={() => setActiveVerificationCode(null)}
      />
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
    paddingHorizontal: SPACING.md,
    paddingTop: SPACING.sm,
    paddingBottom: 48,
    gap: SPACING.lg,
  },
  goldText: {
    color: COLORS.primary,
  },

  // Greeting Bar
  greetingBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: SPACING.xs,
  },
  greetingCopy: {
    flex: 1,
    paddingRight: SPACING.sm,
  },
  eyebrowRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    marginBottom: 4,
  },
  eyebrowText: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1.2,
  },
  greetingTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
    lineHeight: 28,
  },
  greetingSubtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    marginTop: 2,
  },
  branchStatusPill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    paddingHorizontal: 12,
    paddingVertical: 7,
    borderRadius: RADIUS.full,
    backgroundColor: 'rgba(16, 185, 129, 0.08)',
    borderWidth: 1,
    borderColor: 'rgba(16, 185, 129, 0.25)',
  },
  statusDotLive: {
    width: 7,
    height: 7,
    borderRadius: 4,
    backgroundColor: COLORS.success,
  },
  statusTitle: {
    color: COLORS.success,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.3,
  },
  statusSubtitle: {
    color: COLORS.textMuted,
    fontSize: 9,
    fontWeight: '600',
  },

  // Spotlight Next Appointment Card
  spotlightCard: {
    padding: 0,
    borderRadius: RADIUS.lg,
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.3)',
    overflow: 'hidden',
  },
  spotlightGradient: {
    padding: SPACING.md,
    gap: 12,
  },
  spotlightHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  spotlightTagRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  spotlightTag: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
  },
  spotlightServiceName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
  },
  spotlightDetailsRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
    backgroundColor: 'rgba(0, 0, 0, 0.25)',
    padding: 10,
    borderRadius: RADIUS.md,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.04)',
  },
  spotlightDetail: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
  },
  spotlightDetailText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
  },
  codeContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: 'rgba(16, 185, 129, 0.08)',
    borderWidth: 1,
    borderColor: 'rgba(16, 185, 129, 0.25)',
    borderRadius: RADIUS.md,
    paddingHorizontal: 12,
    paddingVertical: 9,
  },
  codeLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  codeLabel: {
    color: COLORS.textMuted,
    fontSize: 9,
    fontWeight: '800',
    letterSpacing: 0.8,
  },
  codeValue: {
    color: COLORS.success,
    fontSize: FONTS.sizes.sm,
    fontWeight: '800',
    letterSpacing: 1.5,
    fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace',
  },
  codeActionPill: {
    backgroundColor: 'rgba(16, 185, 129, 0.15)',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: RADIUS.full,
  },
  codeActionText: {
    color: COLORS.success,
    fontSize: 11,
    fontWeight: '700',
  },
  spotlightActions: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 2,
  },
  spotlightBtn: {
    flex: 1,
  },

  // Luxury Hero Card (When No Booking)
  heroCard: {
    padding: 0,
    borderRadius: RADIUS.lg,
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.25)',
    overflow: 'hidden',
  },
  heroGradient: {
    padding: SPACING.lg,
    gap: 10,
  },
  heroBadgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  heroBadgeText: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1.2,
  },
  heroTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.hero,
    fontWeight: '900',
    lineHeight: 34,
  },
  heroSubtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
    marginBottom: 4,
  },
  heroCtaBtn: {
    marginTop: 4,
  },

  // Quick Action Grid
  quickGrid: {
    flexDirection: 'row',
    gap: 10,
  },
  quickTile: {
    flex: 1,
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.lg,
    borderWidth: 1,
    borderColor: COLORS.border,
    paddingVertical: 14,
    paddingHorizontal: 6,
    alignItems: 'center',
    justifyContent: 'center',
  },
  quickIconCircle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(212, 175, 55, 0.12)',
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.25)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 8,
  },
  quickTileTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
    textAlign: 'center',
  },
  quickTileHint: {
    color: COLORS.textMuted,
    fontSize: 9,
    fontWeight: '600',
    marginTop: 2,
    textAlign: 'center',
  },

  // Section Headers
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: SPACING.xs,
  },
  sectionTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
  },
  sectionSubtitle: {
    color: COLORS.textMuted,
    fontSize: 11,
    marginTop: 2,
  },
  seeAllButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 3,
    paddingVertical: 4,
  },
  seeAllText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },

  // Services Grid
  servicesGrid: {
    gap: 12,
  },
  serviceCard: {
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.lg,
    borderWidth: 1,
    borderColor: COLORS.border,
    padding: SPACING.md,
    gap: 8,
  },
  serviceCardTop: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 8,
  },
  serviceName: {
    flex: 1,
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
  },
  pricePill: {
    backgroundColor: 'rgba(212, 175, 55, 0.15)',
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.3)',
    borderRadius: RADIUS.full,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  priceText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
  },
  serviceDesc: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
  },
  serviceCardBottom: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingTop: 8,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  durationChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  durationText: {
    color: COLORS.textMuted,
    fontSize: 11,
  },
  bookActionRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  bookActionText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },

  // Barbers Carousel
  barbersScroll: {
    marginHorizontal: -SPACING.md,
  },
  barbersScrollContent: {
    paddingHorizontal: SPACING.md,
    gap: 12,
  },
  barberSkeletonRow: {
    flexDirection: 'row',
    gap: 12,
  },
  barberSkeleton: {
    width: 165,
    height: 195,
    borderRadius: RADIUS.lg,
  },
  barberCard: {
    width: 165,
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.lg,
    borderWidth: 1,
    borderColor: COLORS.border,
    padding: SPACING.md,
    alignItems: 'center',
    gap: 6,
  },
  barberAvatarWrapper: {
    position: 'relative',
    marginBottom: 4,
  },
  barberAvatar: {
    width: 58,
    height: 58,
    borderRadius: 29,
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 2,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  barberInitials: {
    color: COLORS.primary,
    fontSize: 20,
    fontWeight: '800',
  },
  verifiedBadge: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    width: 18,
    height: 18,
    borderRadius: 9,
    backgroundColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  barberName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
    textAlign: 'center',
  },
  specialtyText: {
    color: COLORS.textMuted,
    fontSize: 10,
    textAlign: 'center',
  },
  ratingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginVertical: 2,
  },
  ratingText: {
    color: COLORS.textPrimary,
    fontSize: 11,
    fontWeight: '700',
  },
  reviewsCount: {
    color: COLORS.textMuted,
    fontSize: 10,
  },
  barberBtn: {
    width: '100%',
    marginTop: 4,
  },

  // VIP Concierge Home Service Card
  conciergeCard: {
    padding: 0,
    borderRadius: RADIUS.lg,
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.25)',
    overflow: 'hidden',
  },
  conciergeGradient: {
    padding: SPACING.lg,
    gap: 10,
  },
  conciergeBadge: {
    alignSelf: 'flex-start',
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    backgroundColor: 'rgba(212, 175, 55, 0.15)',
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.3)',
    borderRadius: RADIUS.full,
    paddingHorizontal: 9,
    paddingVertical: 4,
  },
  conciergeBadgeText: {
    color: COLORS.primary,
    fontSize: 9,
    fontWeight: '800',
    letterSpacing: 1,
  },
  conciergeTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
    lineHeight: 24,
  },
  conciergeDesc: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
  },
  conciergeBtn: {
    marginTop: 4,
  },

  // Lounge Info & Hours
  loungeCard: {
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.lg,
    borderWidth: 1,
    borderColor: COLORS.border,
    padding: SPACING.md,
    gap: 12,
  },
  loungeHeader: {
    flexDirection: 'row',
    gap: 12,
  },
  loungeIconCircle: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: 'rgba(212, 175, 55, 0.1)',
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.25)',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 2,
  },
  loungeInfo: {
    flex: 1,
    gap: 3,
  },
  loungeTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  loungeAddress: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 16,
  },
  loungeHours: {
    color: COLORS.primary,
    fontSize: 11,
    fontWeight: '600',
    marginTop: 2,
  },
  loungeActionBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: COLORS.surfaceElevated,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    paddingVertical: 10,
  },
  loungeActionText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
});
