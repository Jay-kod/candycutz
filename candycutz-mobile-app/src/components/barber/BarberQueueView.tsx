import React, { useEffect, useMemo, useState } from 'react';
import {
  FlatList,
  Linking,
  Platform,
  Pressable,
  RefreshControl,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { SafeAreaView } from 'react-native-safe-area-context';
import {
  ArrowRight,
  Bell,
  Calendar,
  Check,
  CheckCircle2,
  Clock,
  Coffee,
  Home,
  MapPin,
  Phone,
  Plus,
  Power,
  Scissors,
  ShieldCheck,
  Sparkles,
  TrendingUp,
  User,
  X,
} from 'lucide-react-native';

import { staffQueueApi } from '../../api/client';
import { ConfirmDialog } from '../common/ConfirmDialog';
import { QueueSkeletons } from '../common/Skeleton';
import { CONFIG } from '../../constants/config';
import { FONTS, RADIUS, SPACING, ThemeColors } from '../../constants/theme';
import { useAppTheme } from '../../hooks/useAppTheme';
import { useAuthStore } from '../../store/authStore';
import { useChairStore } from '../../store/chairStore';
import { useToastStore } from '../../store/toastStore';
import { useNotifications } from '../../hooks/useNotifications';
import { Appointment, ChairStatus } from '../../types';

type QueueFilterTab = 'upcoming' | 'completed' | 'all';

interface ChairStatusOption {
  label: string;
  value: ChairStatus;
  icon: React.ReactNode;
  activeColor: string;
}

export function BarberQueueView() {
  const router = useRouter();
  const { colors } = useAppTheme();
  const COLORS = colors;
  const styles = createStyles(colors);
  const queryClient = useQueryClient();
  const { barber, isAuthenticated, setChairStatus } = useAuthStore();
  const { activeClient, elapsedSeconds, setActiveClient, tickTimer } = useChairStore();
  const { unreadCount } = useNotifications(isAuthenticated);
  const showToast = useToastStore((s) => s.show);

  const [activeTab, setActiveTab] = useState<QueueFilterTab>('upcoming');
  const [pendingApproval, setPendingApproval] = useState<{
    appointment: Appointment;
    status: 'in_progress' | 'completed' | 'no_show' | 'confirmed' | 'cancelled';
  } | null>(null);

  // Today's Queue Query with 15s auto-polling
  const {
    data: queue = [],
    isLoading: isQueueLoading,
    refetch: refetchQueue,
  } = useQuery({
    queryKey: ['todayQueue'],
    queryFn: staffQueueApi.getTodayQueue,
    enabled: isAuthenticated,
    refetchInterval: 15000,
  });

  // Dedicated Pending Bookings Query for this specific barber
  const {
    data: pendingBookings = [],
    isLoading: isPendingLoading,
    refetch: refetchPending,
  } = useQuery({
    queryKey: ['barberPendingBookings'],
    queryFn: () => staffQueueApi.getAllAppointments({ status: 'pending' }),
    enabled: isAuthenticated,
    refetchInterval: 12000,
  });

  // Combined refresh
  const isRefreshing = isQueueLoading || isPendingLoading;
  const handleRefresh = async () => {
    await Promise.all([refetchQueue(), refetchPending()]);
  };

  // 1-second interval timer for active haircut
  useEffect(() => {
    const timer = setInterval(() => {
      tickTimer();
    }, 1000);
    return () => clearInterval(timer);
  }, [tickTimer]);

  // Appointment status mutation
  const transitionMutation = useMutation({
    mutationFn: ({ id, status }: { id: number; status: any }) =>
      staffQueueApi.transitionStatus(id, status),
    onSuccess: (updated) => {
      queryClient.invalidateQueries({ queryKey: ['todayQueue'] });
      queryClient.invalidateQueries({ queryKey: ['barberPendingBookings'] });
      queryClient.invalidateQueries({ queryKey: ['allAppointments'] });

      if (updated.status === 'in_progress') {
        setActiveClient(updated);
        setChairStatus('busy');
      } else if (updated.status === 'completed') {
        setActiveClient(null);
        setChairStatus('free');
      } else if (updated.status === 'confirmed') {
        showToast({
          variant: 'success',
          title: 'Booking Accepted',
          message: `You have accepted the booking for ${updated.customer?.name || 'the customer'}. They have been notified to prepare!`,
        });
      } else if (updated.status === 'cancelled') {
        showToast({
          variant: 'info',
          title: 'Booking Declined',
          message: 'The booking request has been declined.',
        });
      }
    },
    onError: (err: any) => {
      showToast({
        variant: 'error',
        title: 'Error',
        message: err.response?.data?.message || 'Failed to update appointment status.',
      });
    },
  });

  // Calculate high-level day stats
  const stats = useMemo(() => {
    const total = queue.length;
    const completed = queue.filter((item) => item.status === 'completed').length;
    const waiting = queue.filter(
      (item) =>
        item.status === 'pending' ||
        item.status === 'confirmed' ||
        item.status === 'in_progress'
    ).length;

    const earnedKobo = queue
      .filter((item) => item.status === 'completed' || item.status === 'in_progress')
      .reduce((sum, item) => sum + (item.grand_total || item.service?.price || 0), 0);

    return { total, completed, waiting, earnedKobo };
  }, [queue]);

  // Next client in line
  const nextUpClient = useMemo(() => {
    return queue.find(
      (item) =>
        (item.status === 'confirmed' || item.status === 'pending') &&
        item.id !== activeClient?.id
    );
  }, [queue, activeClient]);

  // Filtered queue items based on active tab
  const filteredQueue = useMemo(() => {
    switch (activeTab) {
      case 'upcoming':
        return queue.filter(
          (item) =>
            (item.status === 'pending' ||
              item.status === 'confirmed' ||
              item.status === 'in_progress') &&
            item.id !== activeClient?.id
        );
      case 'completed':
        return queue.filter((item) => item.status === 'completed');
      case 'all':
      default:
        return queue.filter((item) => item.id !== activeClient?.id);
    }
  }, [queue, activeTab, activeClient]);

  // Chair status configuration
  const CHAIR_OPTIONS: ChairStatusOption[] = [
    {
      label: 'Ready',
      value: 'free',
      icon: <Check size={13} color={COLORS.success} strokeWidth={2.5} />,
      activeColor: COLORS.success,
    },
    {
      label: 'In Chair',
      value: 'busy',
      icon: <Scissors size={13} color={COLORS.primary} strokeWidth={2.5} />,
      activeColor: COLORS.primary,
    },
    {
      label: 'Break',
      value: 'break',
      icon: <Coffee size={13} color={COLORS.warning} strokeWidth={2.5} />,
      activeColor: COLORS.warning,
    },
    {
      label: 'Offline',
      value: 'offline',
      icon: <Power size={13} color={COLORS.textSecondary} strokeWidth={2.5} />,
      activeColor: COLORS.textMuted,
    },
  ];

  // Helper formatters
  const formatTimer = (totalSecs: number) => {
    const mins = Math.floor(totalSecs / 60);
    const secs = totalSecs % 60;
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
  };

  const formatMoney = (amount: number) => {
    return `${CONFIG.CURRENCY_SYMBOL}${amount.toLocaleString()}`;
  };

  const getInitials = (name?: string) => {
    if (!name) return 'CL';
    return name
      .split(' ')
      .slice(0, 2)
      .map((part) => part[0])
      .join('')
      .toUpperCase();
  };

  const getTodayFormattedDate = () => {
    const now = new Date();
    return now.toLocaleDateString('en-US', {
      weekday: 'short',
      month: 'short',
      day: 'numeric',
    });
  };

  const navigateToPendingBookings = () => {
    router.push('/(tabs)/appointments?status=pending');
  };

  if (!isAuthenticated) {
    return (
      <SafeAreaView style={styles.safeArea} edges={['top']}>
        <View style={styles.unauthCard}>
          <View style={styles.unauthIconHalo}>
            <Scissors size={32} color={COLORS.primary} />
          </View>
          <Text style={styles.unauthTitle}>Barber Workstation</Text>
          <Text style={styles.unauthDesc}>
            Sign in to manage your live chair queue, record walk-ins, and oversee today's appointments.
          </Text>
          <Pressable
            style={({ pressed }) => [
              styles.primaryGoldBtn,
              pressed && styles.btnPressed,
            ]}
            onPress={() => router.push('/auth/login')}
          >
            <Text style={styles.primaryGoldBtnText}>Sign In to Workstation</Text>
          </Pressable>
        </View>
      </SafeAreaView>
    );
  }

  // Get first pending booking for home preview
  const primaryPendingBooking = pendingBookings[0] as Appointment | undefined;

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      {/* 1. Executive Luxury Header */}
      <View style={styles.header}>
        <Pressable
          onPress={() => {
            useAuthStore.getState().setViewMode('barber');
            router.push('/(tabs)/profile');
          }}
          style={({ pressed }) => [styles.barberProfileBlock, pressed && styles.cardPressed]}
          accessibilityRole="button"
          accessibilityLabel="Open barber staff desk"
        >
          <View style={styles.avatarRing}>
            <Text style={styles.avatarText}>{getInitials(barber?.name)}</Text>
          </View>
          <View>
            <View style={styles.greetingEyebrowRow}>
              <Sparkles size={11} color={COLORS.primary} />
              <Text style={styles.greetingEyebrow}>MASTER STYLIST</Text>
            </View>
            <Text style={styles.barberName} numberOfLines={1}>
              {barber?.name || 'Master Barber'}
            </Text>
          </View>
        </Pressable>

        <View style={styles.headerRightActions}>
          <Pressable
            onPress={() => router.push('/notifications')}
            style={({ pressed }) => [
              styles.notifBellBtn,
              pressed && styles.btnPressed,
            ]}
            accessibilityRole="button"
            accessibilityLabel="Notifications"
          >
            <Bell size={18} color={COLORS.textPrimary} />
            {unreadCount > 0 && (
              <View style={styles.notifBadge}>
                <Text style={styles.notifBadgeText}>
                  {unreadCount > 99 ? '99+' : unreadCount}
                </Text>
              </View>
            )}
          </Pressable>

          <Pressable
            onPress={() => router.push('/walkin')}
            style={({ pressed }) => [
              styles.walkInQuickBtn,
              pressed && styles.btnPressed,
            ]}
            accessibilityRole="button"
            accessibilityLabel="Add walk in client"
          >
            <Plus size={15} color={colors.onPrimary} strokeWidth={3} />
            <Text style={styles.walkInBtnText}>Walk-In</Text>
          </Pressable>
        </View>
      </View>

      {/* 2. Modern Chair Status Segmented Hub */}
      <View style={styles.chairStatusHub}>
        <View style={styles.statusSegmentContainer}>
          {CHAIR_OPTIONS.map((opt) => {
            const isSelected = (barber?.chair_status || 'free') === opt.value;
            return (
              <Pressable
                key={opt.value}
                onPress={() => setChairStatus(opt.value)}
                style={[
                  styles.statusSegmentItem,
                  isSelected && {
                    backgroundColor: colors.surfaceHighlight,
                    borderColor: opt.activeColor,
                  },
                ]}
              >
                <View
                  style={[
                    styles.statusDot,
                    { backgroundColor: opt.activeColor },
                    isSelected && styles.statusDotActive,
                  ]}
                />
                <Text
                  style={[
                    styles.statusSegmentText,
                    isSelected && { color: COLORS.textPrimary, fontWeight: '700' },
                  ]}
                >
                  {opt.label}
                </Text>
              </Pressable>
            );
          })}
        </View>
      </View>

      {/* 3. Main Scrollable Queue Content */}
      {isQueueLoading && queue.length === 0 ? (
        <QueueSkeletons count={3} />
      ) : (
        <FlatList
          data={filteredQueue}
        keyExtractor={(item) => String(item.id)}
        refreshControl={
          <RefreshControl
            refreshing={isRefreshing}
            onRefresh={handleRefresh}
            tintColor={COLORS.primary}
          />
        }
        contentContainerStyle={styles.scrollList}
        showsVerticalScrollIndicator={false}
        ListHeaderComponent={
          <View style={styles.headerComponentWrapper}>
            {/* Shift Snapshot KPIs (3 Live KPI Cards) */}
            <View style={styles.kpiRow}>
              <View style={styles.kpiCard}>
                <View style={styles.kpiIconBox}>
                  <Clock size={16} color={COLORS.primary} />
                </View>
                <View>
                  <Text style={styles.kpiValue}>{stats.waiting}</Text>
                  <Text style={styles.kpiLabel}>In Queue</Text>
                </View>
              </View>

              <View style={styles.kpiCard}>
                <View style={[styles.kpiIconBox, { backgroundColor: COLORS.successLight }]}>
                  <CheckCircle2 size={16} color={COLORS.success} />
                </View>
                <View>
                  <Text style={styles.kpiValue}>{stats.completed}</Text>
                  <Text style={styles.kpiLabel}>Finished</Text>
                </View>
              </View>

              <View style={styles.kpiCard}>
                <View style={[styles.kpiIconBox, { backgroundColor: COLORS.infoLight }]}>
                  <TrendingUp size={16} color={COLORS.info} />
                </View>
                <View>
                  <Text style={styles.kpiValue}>{formatMoney(stats.earnedKobo)}</Text>
                  <Text style={styles.kpiLabel}>Today's Cuts</Text>
                </View>
              </View>
            </View>

            {/* High-Priority Pending Booking Requests Stage */}
            {pendingBookings.length > 0 && primaryPendingBooking && (
              <Pressable
                onPress={navigateToPendingBookings}
                style={({ pressed }) => [
                  styles.pendingBannerCard,
                  pressed && styles.cardPressed,
                ]}
                accessibilityRole="button"
                accessibilityLabel="View pending booking requests"
              >
                <View
                  style={[styles.pendingGradientContent, { backgroundColor: colors.surfaceElevated }]}
                >
                  <View style={styles.pendingCardHeader}>
                    <View style={styles.pendingNoticePill}>
                      <View style={styles.pendingPulseDot} />
                      <Text style={styles.pendingNoticeText}>ACTION REQUIRED</Text>
                    </View>
                    <View style={styles.pendingCountBadge}>
                      <Text style={styles.pendingCountBadgeText}>
                        {pendingBookings.length} {pendingBookings.length === 1 ? 'Request' : 'Requests'}
                      </Text>
                    </View>
                  </View>

                  {/* Latest Pending Booking Details */}
                  <View style={styles.pendingBookingDetails}>
                    <View style={styles.pendingClientAvatar}>
                      <Text style={styles.pendingClientAvatarText}>
                        {getInitials(primaryPendingBooking.customer?.name)}
                      </Text>
                    </View>
                    <View style={{ flex: 1 }}>
                      <Text style={styles.pendingClientName} numberOfLines={1}>
                        {primaryPendingBooking.customer?.name || 'New Customer'}
                      </Text>
                      <Text style={styles.pendingScheduleText}>
                        📅 {primaryPendingBooking.appointment_date} at {(primaryPendingBooking.start_time || '').substring(0, 5)}
                      </Text>
                      <Text style={styles.pendingServiceText}>
                        {primaryPendingBooking.service?.name || 'Haircut Service'} • {formatMoney(primaryPendingBooking.grand_total || primaryPendingBooking.service?.price || 0)}
                      </Text>
                    </View>
                  </View>

                  {/* Service Mode Badge (In-Shop vs Home Service) */}
                  <View style={styles.serviceModeBadgeRow}>
                    <View
                      style={[
                        styles.modeBadgePill,
                        primaryPendingBooking.appointment_type === 'home_service'
                          ? styles.homeServicePill
                          : styles.inShopPill,
                      ]}
                    >
                      {primaryPendingBooking.appointment_type === 'home_service' ? (
                        <>
                          <Home size={13} color={COLORS.primary} />
                          <Text style={styles.homeServicePillText}>
                            🏠 Home Service Request
                          </Text>
                        </>
                      ) : (
                        <>
                          <Scissors size={13} color={COLORS.textSecondary} />
                          <Text style={styles.inShopPillText}>
                            ✂ In-Shop Appointment
                          </Text>
                        </>
                      )}
                    </View>

                    <View style={styles.viewDetailsPrompt}>
                      <Text style={styles.viewDetailsText}>View & Decide</Text>
                      <ArrowRight size={13} color={COLORS.primary} />
                    </View>
                  </View>

                  {/* Destination Landmark if Home Service */}
                  {primaryPendingBooking.appointment_type === 'home_service' &&
                    primaryPendingBooking.destination_address && (
                      <View style={styles.landmarkBox}>
                        <MapPin size={13} color={COLORS.primary} />
                        <Text style={styles.landmarkText} numberOfLines={1}>
                          Location: {primaryPendingBooking.destination_address.area_landmark}, {primaryPendingBooking.destination_address.street_address}
                        </Text>
                      </View>
                    )}

                  {/* Direct Action Buttons on Pending Card */}
                  <View style={styles.pendingCardActionButtons}>
                    <Pressable
                      onPress={() =>
                        setPendingApproval({
                          appointment: primaryPendingBooking,
                          status: 'cancelled',
                        })
                      }
                      style={({ pressed }) => [
                        styles.pendingDeclineBtn,
                        pressed && styles.btnPressed,
                      ]}
                    >
                      <X size={15} color={COLORS.error} strokeWidth={2.5} />
                      <Text style={styles.pendingDeclineBtnText}>Decline</Text>
                    </Pressable>

                    <Pressable
                      onPress={() =>
                        setPendingApproval({
                          appointment: primaryPendingBooking,
                          status: 'confirmed',
                        })
                      }
                      style={({ pressed }) => [
                        styles.pendingAcceptBtn,
                        pressed && styles.btnPressed,
                      ]}
                    >
                      <Check size={16} color={colors.onPrimary} strokeWidth={3} />
                      <Text style={styles.pendingAcceptBtnText}>Accept Booking</Text>
                    </Pressable>
                  </View>
                </View>
              </Pressable>
            )}

            {/* "Now In Chair" Spotlight Stage */}
            {activeClient ? (
              <View style={styles.activeSpotlightCard}>
                <View
                  style={[styles.activeGradientContent, { backgroundColor: colors.surfaceElevated }]}
                >
                  <View style={styles.activeCardTopRow}>
                    <View style={styles.livePulsePill}>
                      <View style={styles.livePulseDot} />
                      <Text style={styles.livePulseText}>NOW IN CHAIR</Text>
                    </View>
                    <View style={styles.timerBadge}>
                      <Clock size={14} color={COLORS.primary} />
                      <Text style={styles.timerDigits}>{formatTimer(elapsedSeconds)}</Text>
                    </View>
                  </View>

                  <View style={styles.activeClientDetails}>
                    <View style={styles.clientAvatarLarge}>
                      <Text style={styles.clientAvatarText}>
                        {getInitials(activeClient.customer?.name)}
                      </Text>
                    </View>
                    <View style={{ flex: 1 }}>
                      <Text style={styles.activeClientName} numberOfLines={1}>
                        {activeClient.customer?.name || 'Walk-In Guest'}
                      </Text>
                      <Text style={styles.activeServiceName}>
                        {activeClient.service?.name || 'Standard Cut'} • {formatMoney(activeClient.grand_total || activeClient.service?.price || 0)}
                      </Text>
                      <Text style={styles.activeRefNumber}>
                        Ref: {activeClient.booking_reference}
                      </Text>
                    </View>
                  </View>

                  {/* Primary Finish CTA */}
                  <Pressable
                    onPress={() => setPendingApproval({ appointment: activeClient, status: 'completed' })}
                    style={({ pressed }) => [
                      styles.finishServiceBtn,
                      pressed && styles.btnPressed,
                    ]}
                  >
                    <CheckCircle2 size={18} color={colors.onPrimary} strokeWidth={2.5} />
                    <Text style={styles.finishServiceBtnText}>
                      Finish Cut & Check Out
                    </Text>
                  </Pressable>
                </View>
              </View>
            ) : (
              /* Chair Available Card */
              <View style={styles.chairAvailableCard}>
                <View style={styles.chairAvailableHeader}>
                  <View style={styles.chairReadyBadge}>
                    <Sparkles size={14} color={COLORS.success} />
                    <Text style={styles.chairReadyText}>CHAIR IS READY</Text>
                  </View>
                  <Text style={styles.dateLabel}>{getTodayFormattedDate()}</Text>
                </View>

                {nextUpClient ? (
                  <View style={styles.nextUpPromptBox}>
                    <View style={{ flex: 1, paddingRight: 8 }}>
                      <Text style={styles.nextUpLead}>Next Client in Queue:</Text>
                      <Text style={styles.nextUpName} numberOfLines={1}>
                        {nextUpClient.customer?.name || 'Guest'} ({(nextUpClient.start_time || '').substring(0, 5)})
                      </Text>
                      <Text style={styles.nextUpService} numberOfLines={1}>
                        {nextUpClient.service?.name || 'Haircut Service'}
                      </Text>
                    </View>
                    <Pressable
                      onPress={() => setPendingApproval({ appointment: nextUpClient, status: 'in_progress' })}
                      style={({ pressed }) => [
                        styles.seatNextBtn,
                        pressed && styles.btnPressed,
                      ]}
                    >
                      <Scissors size={15} color={colors.onPrimary} strokeWidth={2.5} />
                      <Text style={styles.seatNextBtnText}>Seat Now</Text>
                    </Pressable>
                  </View>
                ) : (
                  <Text style={styles.emptyChairSub}>
                    Ready for walk-in guests or your next scheduled appointment.
                  </Text>
                )}
              </View>
            )}

            {/* Queue Segment Filter Tabs */}
            <View style={styles.sectionHeaderRow}>
              <Text style={styles.sectionHeading}>Today's Lineup</Text>
              <View style={styles.filterPillsContainer}>
                <Pressable
                  onPress={() => setActiveTab('upcoming')}
                  style={[
                    styles.filterPill,
                    activeTab === 'upcoming' && styles.filterPillActive,
                  ]}
                >
                  <Text
                    style={[
                      styles.filterPillText,
                      activeTab === 'upcoming' && styles.filterPillTextActive,
                    ]}
                  >
                    Up Next ({stats.waiting})
                  </Text>
                </Pressable>

                <Pressable
                  onPress={() => setActiveTab('completed')}
                  style={[
                    styles.filterPill,
                    activeTab === 'completed' && styles.filterPillActive,
                  ]}
                >
                  <Text
                    style={[
                      styles.filterPillText,
                      activeTab === 'completed' && styles.filterPillTextActive,
                    ]}
                  >
                    Done ({stats.completed})
                  </Text>
                </Pressable>

                <Pressable
                  onPress={() => setActiveTab('all')}
                  style={[
                    styles.filterPill,
                    activeTab === 'all' && styles.filterPillActive,
                  ]}
                >
                  <Text
                    style={[
                      styles.filterPillText,
                      activeTab === 'all' && styles.filterPillTextActive,
                    ]}
                  >
                    All ({stats.total})
                  </Text>
                </Pressable>
              </View>
            </View>
          </View>
        }
        renderItem={({ item }) => {
          const isCompleted = item.status === 'completed';
          const isPending = item.status === 'pending';
          const isConfirmed = item.status === 'confirmed';

          return (
            <View style={styles.queueItemCard}>
              <View style={styles.queueCardHeader}>
                <View style={styles.timeBadge}>
                  <Clock size={12} color={COLORS.primary} />
                  <Text style={styles.timeBadgeText}>
                    {(item.start_time || '').substring(0, 5)}
                  </Text>
                </View>

                <View
                  style={[
                    styles.itemStatusPill,
                    isCompleted && styles.statusPillCompleted,
                    isConfirmed && styles.statusPillConfirmed,
                    isPending && styles.statusPillPending,
                  ]}
                >
                  <Text
                    style={[
                      styles.itemStatusText,
                      isCompleted && { color: COLORS.success },
                      isConfirmed && { color: COLORS.primary },
                      isPending && { color: COLORS.warning },
                    ]}
                  >
                    {item.status.replace('_', ' ').toUpperCase()}
                  </Text>
                </View>
              </View>

              <View style={styles.queueBodyRow}>
                <View style={styles.clientAvatarSmall}>
                  <Text style={styles.clientAvatarSmallText}>
                    {getInitials(item.customer?.name)}
                  </Text>
                </View>
                <View style={{ flex: 1 }}>
                  <Text style={styles.queueClientName}>
                    {item.customer?.name || 'Walk-In Guest'}
                  </Text>
                  <Text style={styles.queueServiceDetails}>
                    {item.service?.name || 'Haircut'} • {formatMoney(item.grand_total || item.service?.price || 0)}
                  </Text>
                  <Text style={styles.queueRefText}>Ref: {item.booking_reference}</Text>
                </View>

                {item.customer?.phone && (
                  <Pressable
                    onPress={() => Linking.openURL(`tel:${item.customer?.phone}`)}
                    style={styles.callIconBtn}
                    accessibilityRole="button"
                    accessibilityLabel="Call client"
                  >
                    <Phone size={16} color={COLORS.textSecondary} />
                  </Pressable>
                )}
              </View>

              {item.destination_address && (
                <View style={styles.homeDeliveryBox}>
                  <MapPin size={13} color={COLORS.primary} />
                  <Text style={styles.homeDeliveryText} numberOfLines={1}>
                    VIP Home: {item.destination_address.street_address}, {item.destination_address.area_landmark}
                  </Text>
                </View>
              )}

              {!isCompleted && item.status !== 'cancelled' && item.status !== 'no_show' && (
                <View style={styles.cardActionsRow}>
                  {isPending && (
                    <Pressable
                      onPress={() => transitionMutation.mutate({ id: item.id, status: 'confirmed' })}
                      style={({ pressed }) => [
                        styles.checkInBtn,
                        pressed && styles.btnPressed,
                      ]}
                    >
                      <Text style={styles.checkInBtnText}>Check-In Client</Text>
                    </Pressable>
                  )}

                  {(isConfirmed || isPending) && (
                    <Pressable
                      onPress={() => setPendingApproval({ appointment: item, status: 'in_progress' })}
                      style={({ pressed }) => [
                        styles.startCutBtn,
                        pressed && styles.btnPressed,
                      ]}
                    >
                      <Scissors size={15} color={colors.onPrimary} strokeWidth={2.5} />
                      <Text style={styles.startCutBtnText}>Seat in Chair</Text>
                    </Pressable>
                  )}

                  <Pressable
                    onPress={() => setPendingApproval({ appointment: item, status: 'no_show' })}
                    style={({ pressed }) => [
                      styles.noShowBtn,
                      pressed && styles.btnPressed,
                    ]}
                  >
                    <Text style={styles.noShowBtnText}>No-Show</Text>
                  </Pressable>
                </View>
              )}
            </View>
          );
        }}
        ListEmptyComponent={
          <View style={styles.emptyQueueContainer}>
            <View style={styles.emptyIconHalo}>
              <CheckCircle2 size={28} color={COLORS.primary} />
            </View>
            <Text style={styles.emptyQueueTitle}>
              {activeTab === 'upcoming'
                ? 'Queue is Clear'
                : activeTab === 'completed'
                ? 'No Completed Cuts Yet'
                : 'No Appointments Today'}
            </Text>
            <Text style={styles.emptyQueueSubtitle}>
              {activeTab === 'upcoming'
                ? 'All clients have been attended to. Ready for new walk-in guests!'
                : 'Your schedule for today will appear here as appointments are booked.'}
            </Text>
            {activeTab === 'upcoming' && (
              <Pressable
                onPress={() => router.push('/walkin')}
                style={({ pressed }) => [
                  styles.emptyStateAddWalkInBtn,
                  pressed && styles.btnPressed,
                ]}
              >
                <Plus size={16} color={colors.onPrimary} strokeWidth={3} />
                <Text style={styles.emptyStateAddWalkInText}>Add Walk-In Client</Text>
              </Pressable>
            )}
          </View>
        }
      />
      )}

      {/* 4. Confirmation Dialog */}
      <ConfirmDialog
        visible={Boolean(pendingApproval)}
        title={
          pendingApproval?.status === 'no_show'
            ? 'Mark Client as No-Show?'
            : pendingApproval?.status === 'completed'
            ? 'Complete Haircut Service?'
            : pendingApproval?.status === 'confirmed'
            ? 'Accept Booking Request?'
            : pendingApproval?.status === 'cancelled'
            ? 'Decline Booking Request?'
            : 'Seat Client in Chair?'
        }
        message={
          pendingApproval?.status === 'no_show'
            ? `Are you sure ${pendingApproval.appointment.customer?.name || 'this client'} did not arrive? The slot will be freed.`
            : pendingApproval?.status === 'completed'
            ? `Finish appointment for ${pendingApproval?.appointment.customer?.name || 'this client'}. Chair status will return to ready.`
            : pendingApproval?.status === 'confirmed'
            ? `Accept booking for ${pendingApproval?.appointment.customer?.name || 'this client'} (${pendingApproval?.appointment.appointment_type === 'home_service' ? 'Home Service' : 'In-Shop'}). The customer will be notified to prepare.`
            : pendingApproval?.status === 'cancelled'
            ? `Decline booking request for ${pendingApproval?.appointment.customer?.name || 'this client'}? The customer will be informed.`
            : `Seat ${pendingApproval?.appointment.customer?.name || 'this client'} and begin the timer for ${pendingApproval?.appointment.service?.name || 'this cut'}.`
        }
        confirmLabel={
          pendingApproval?.status === 'no_show'
            ? 'Confirm No-Show'
            : pendingApproval?.status === 'completed'
            ? 'Finish & Check Out'
            : pendingApproval?.status === 'confirmed'
            ? 'Accept Booking'
            : pendingApproval?.status === 'cancelled'
            ? 'Decline Request'
            : 'Start Service'
        }
        destructive={
          pendingApproval?.status === 'no_show' ||
          pendingApproval?.status === 'cancelled'
        }
        variant={
          pendingApproval?.status === 'no_show' ||
          pendingApproval?.status === 'cancelled'
            ? 'danger'
            : 'primary'
        }
        onCancel={() => setPendingApproval(null)}
        onConfirm={() => {
          if (pendingApproval) {
            transitionMutation.mutate({
              id: pendingApproval.appointment.id,
              status: pendingApproval.status,
            });
          }
          setPendingApproval(null);
        }}
      />
    </SafeAreaView>
  );
}

const createStyles = (colors: ThemeColors) => {
  const COLORS = colors;

  return StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: SPACING.md,
    paddingTop: SPACING.sm,
    paddingBottom: SPACING.sm,
  },
  barberProfileBlock: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  avatarRing: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: COLORS.surfaceElevated,
    borderWidth: 2,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    color: COLORS.primary,
    fontSize: 16,
    fontWeight: '800',
  },
  greetingEyebrowRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginBottom: 2,
  },
  greetingEyebrow: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
  },
  barberName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
    letterSpacing: 0.2,
  },
  headerRightActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  notifBellBtn: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border,
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  notifBadge: {
    position: 'absolute',
    top: -3,
    right: -3,
    backgroundColor: COLORS.primary,
    borderRadius: 8,
    minWidth: 16,
    height: 16,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
  },
  notifBadgeText: {
    fontSize: 9,
    fontWeight: '800',
    color: COLORS.onPrimary,
  },
  walkInQuickBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.primary,
    paddingVertical: 8,
    paddingHorizontal: 14,
    borderRadius: RADIUS.full,
    shadowColor: COLORS.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 3,
  },
  walkInBtnText: {
    color: COLORS.onPrimary,
    fontSize: 13,
    fontWeight: '800',
  },
  btnPressed: {
    transform: [{ scale: 0.96 }],
  },
  cardPressed: {
    opacity: 0.92,
    transform: [{ scale: 0.99 }],
  },
  chairStatusHub: {
    paddingHorizontal: SPACING.md,
    paddingBottom: SPACING.sm,
  },
  statusSegmentContainer: {
    flexDirection: 'row',
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.lg,
    padding: 4,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  statusSegmentItem: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 8,
    borderRadius: RADIUS.md,
    borderWidth: 1,
    borderColor: 'transparent',
  },
  statusDot: {
    width: 7,
    height: 7,
    borderRadius: 3.5,
  },
  statusDotActive: {
    transform: [{ scale: 1.25 }],
  },
  statusSegmentText: {
    color: COLORS.textMuted,
    fontSize: 12,
    fontWeight: '600',
  },
  scrollList: {
    paddingHorizontal: SPACING.md,
    paddingBottom: 40,
  },
  headerComponentWrapper: {
    marginBottom: SPACING.md,
  },
  kpiRow: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: SPACING.md,
  },
  kpiCard: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: COLORS.surface,
    paddingVertical: 12,
    paddingHorizontal: 10,
    borderRadius: RADIUS.lg,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  kpiIconBox: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: COLORS.primaryLight,
    alignItems: 'center',
    justifyContent: 'center',
  },
  kpiValue: {
    color: COLORS.textPrimary,
    fontSize: 15,
    fontWeight: '800',
  },
  kpiLabel: {
    color: COLORS.textMuted,
    fontSize: 10,
    fontWeight: '500',
    marginTop: 1,
  },
  pendingBannerCard: {
    borderRadius: RADIUS.md,
    marginBottom: SPACING.md,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  pendingGradientContent: {
    padding: SPACING.md,
  },
  pendingCardRim: {
    position: 'absolute',
    top: 0,
    left: '15%',
    right: '15%',
    height: 2,
    backgroundColor: COLORS.primary,
  },
  pendingCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  pendingNoticePill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.primaryLight,
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderRadius: RADIUS.full,
    borderWidth: 1,
    borderColor: COLORS.primaryGlow,
  },
  pendingPulseDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: COLORS.primary,
  },
  pendingNoticeText: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.8,
  },
  pendingCountBadge: {
    backgroundColor: COLORS.primaryLight,
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: RADIUS.sm,
  },
  pendingCountBadgeText: {
    color: COLORS.primary,
    fontSize: 11,
    fontWeight: '700',
  },
  pendingBookingDetails: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 10,
  },
  pendingClientAvatar: {
    width: 42,
    height: 42,
    borderRadius: 21,
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.primaryGlow,
    alignItems: 'center',
    justifyContent: 'center',
  },
  pendingClientAvatarText: {
    color: COLORS.primary,
    fontSize: 14,
    fontWeight: '800',
  },
  pendingClientName: {
    color: COLORS.textPrimary,
    fontSize: 16,
    fontWeight: '800',
  },
  pendingScheduleText: {
    color: COLORS.textSecondary,
    fontSize: 12,
    marginTop: 2,
    fontWeight: '500',
  },
  pendingServiceText: {
    color: COLORS.textMuted,
    fontSize: 12,
    marginTop: 1,
  },
  serviceModeBadgeRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginVertical: 6,
  },
  modeBadgePill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderRadius: RADIUS.sm,
  },
  homeServicePill: {
    backgroundColor: COLORS.primaryLight,
    borderWidth: 1,
    borderColor: COLORS.primaryGlow,
  },
  inShopPill: {
    backgroundColor: COLORS.surfaceHighlight,
  },
  homeServicePillText: {
    color: COLORS.primary,
    fontSize: 11,
    fontWeight: '700',
  },
  inShopPillText: {
    color: COLORS.textSecondary,
    fontSize: 11,
    fontWeight: '700',
  },
  viewDetailsPrompt: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  viewDetailsText: {
    color: COLORS.primary,
    fontSize: 12,
    fontWeight: '700',
  },
  landmarkBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.primaryLight,
    paddingVertical: 6,
    paddingHorizontal: 10,
    borderRadius: RADIUS.sm,
    marginVertical: 4,
  },
  landmarkText: {
    color: COLORS.primary,
    fontSize: 11,
    fontWeight: '500',
    flex: 1,
  },
  pendingCardActionButtons: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 10,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  pendingDeclineBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 9,
    paddingHorizontal: 16,
    borderRadius: RADIUS.md,
    borderWidth: 1,
    borderColor: 'rgba(239, 68, 68, 0.35)',
    backgroundColor: COLORS.errorLight,
  },
  pendingDeclineBtnText: {
    color: COLORS.error,
    fontSize: 13,
    fontWeight: '700',
  },
  pendingAcceptBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: COLORS.primary,
    paddingVertical: 9,
    paddingHorizontal: 16,
    borderRadius: RADIUS.md,
    shadowColor: COLORS.primary,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 6,
    elevation: 3,
  },
  pendingAcceptBtnText: {
    color: COLORS.onPrimary,
    fontSize: 13,
    fontWeight: '800',
  },
  activeSpotlightCard: {
    borderRadius: RADIUS.md,
    borderWidth: 1,
    borderColor: COLORS.primary,
    marginBottom: SPACING.lg,
    overflow: 'hidden',
  },
  activeGradientContent: {
    padding: SPACING.md,
  },
  activeCardRim: {
    position: 'absolute',
    top: 0,
    left: '20%',
    right: '20%',
    height: 2,
    backgroundColor: COLORS.primary,
  },
  activeCardTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 14,
  },
  livePulsePill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.primaryLight,
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderRadius: RADIUS.full,
    borderWidth: 1,
    borderColor: COLORS.primaryGlow,
  },
  livePulseDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: COLORS.primary,
  },
  livePulseText: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
  },
  timerBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.surfaceHighlight,
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderRadius: RADIUS.full,
  },
  timerDigits: {
    color: COLORS.primary,
    fontSize: 15,
    fontWeight: '800',
    fontVariant: ['tabular-nums'],
  },
  activeClientDetails: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 16,
  },
  clientAvatarLarge: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  clientAvatarText: {
    color: COLORS.primary,
    fontSize: 16,
    fontWeight: '800',
  },
  activeClientName: {
    color: COLORS.textPrimary,
    fontSize: 17,
    fontWeight: '800',
  },
  activeServiceName: {
    color: COLORS.textSecondary,
    fontSize: 13,
    marginTop: 2,
  },
  activeRefNumber: {
    color: COLORS.textMuted,
    fontSize: 11,
    marginTop: 2,
  },
  finishServiceBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: COLORS.primary,
    paddingVertical: 13,
    borderRadius: RADIUS.md,
    shadowColor: COLORS.primary,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  finishServiceBtnText: {
    color: COLORS.onPrimary,
    fontSize: 15,
    fontWeight: '800',
  },
  chairAvailableCard: {
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.lg,
    padding: SPACING.md,
    borderWidth: 1,
    borderColor: COLORS.border,
    marginBottom: SPACING.lg,
  },
  chairAvailableHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  chairReadyBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.successLight,
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderRadius: RADIUS.full,
  },
  chairReadyText: {
    color: COLORS.success,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.8,
  },
  dateLabel: {
    color: COLORS.textMuted,
    fontSize: 12,
    fontWeight: '500',
  },
  nextUpPromptBox: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: COLORS.surfaceHighlight,
    borderRadius: RADIUS.md,
    padding: 12,
    marginTop: 4,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  nextUpLead: {
    color: COLORS.textMuted,
    fontSize: 11,
    fontWeight: '600',
  },
  nextUpName: {
    color: COLORS.textPrimary,
    fontSize: 14,
    fontWeight: '700',
    marginTop: 2,
  },
  nextUpService: {
    color: COLORS.primary,
    fontSize: 12,
    marginTop: 1,
  },
  seatNextBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.primary,
    paddingVertical: 9,
    paddingHorizontal: 14,
    borderRadius: RADIUS.md,
  },
  seatNextBtnText: {
    color: COLORS.onPrimary,
    fontSize: 13,
    fontWeight: '800',
  },
  emptyChairSub: {
    color: COLORS.textMuted,
    fontSize: 13,
    lineHeight: 18,
  },
  sectionHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  sectionHeading: {
    color: COLORS.textPrimary,
    fontSize: 16,
    fontWeight: '800',
  },
  filterPillsContainer: {
    flexDirection: 'row',
    backgroundColor: COLORS.surface,
    padding: 3,
    borderRadius: RADIUS.md,
    gap: 4,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  filterPill: {
    paddingVertical: 5,
    paddingHorizontal: 10,
    borderRadius: RADIUS.sm,
  },
  filterPillActive: {
    backgroundColor: COLORS.surfaceHighlight,
  },
  filterPillText: {
    color: COLORS.textMuted,
    fontSize: 11,
    fontWeight: '600',
  },
  filterPillTextActive: {
    color: COLORS.primary,
    fontWeight: '700',
  },
  queueItemCard: {
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.lg,
    padding: SPACING.md,
    borderWidth: 1,
    borderColor: COLORS.border,
    marginBottom: 12,
  },
  queueCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  timeBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.primaryLight,
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: RADIUS.sm,
  },
  timeBadgeText: {
    color: COLORS.primary,
    fontSize: 12,
    fontWeight: '700',
  },
  itemStatusPill: {
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: RADIUS.sm,
    backgroundColor: COLORS.surfaceHighlight,
  },
  statusPillCompleted: {
    backgroundColor: COLORS.successLight,
  },
  statusPillConfirmed: {
    backgroundColor: COLORS.primaryLight,
  },
  statusPillPending: {
    backgroundColor: COLORS.warningLight,
  },
  itemStatusText: {
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  queueBodyRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  clientAvatarSmall: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: COLORS.surfaceHighlight,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  clientAvatarSmallText: {
    color: COLORS.textSecondary,
    fontSize: 13,
    fontWeight: '700',
  },
  queueClientName: {
    color: COLORS.textPrimary,
    fontSize: 15,
    fontWeight: '700',
  },
  queueServiceDetails: {
    color: COLORS.textSecondary,
    fontSize: 13,
    marginTop: 2,
  },
  queueRefText: {
    color: COLORS.textMuted,
    fontSize: 11,
    marginTop: 1,
  },
  callIconBtn: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  homeDeliveryBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.primaryLight,
    paddingVertical: 6,
    paddingHorizontal: 10,
    borderRadius: RADIUS.sm,
    marginTop: 10,
    borderWidth: 1,
    borderColor: COLORS.primaryLight,
  },
  homeDeliveryText: {
    color: COLORS.primary,
    fontSize: 12,
    fontWeight: '500',
    flex: 1,
  },
  cardActionsRow: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 14,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
  },
  startCutBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: COLORS.primary,
    paddingVertical: 10,
    borderRadius: RADIUS.md,
  },
  startCutBtnText: {
    color: COLORS.onPrimary,
    fontSize: 13,
    fontWeight: '800',
  },
  checkInBtn: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.surfaceHighlight,
    paddingVertical: 10,
    borderRadius: RADIUS.md,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  checkInBtnText: {
    color: COLORS.textPrimary,
    fontSize: 13,
    fontWeight: '700',
  },
  noShowBtn: {
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: RADIUS.md,
    borderWidth: 1,
    borderColor: 'rgba(239, 68, 68, 0.35)',
    backgroundColor: COLORS.errorLight,
    alignItems: 'center',
    justifyContent: 'center',
  },
  noShowBtnText: {
    color: COLORS.error,
    fontSize: 12,
    fontWeight: '700',
  },
  emptyQueueContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 48,
    paddingHorizontal: 24,
  },
  emptyIconHalo: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: COLORS.primaryLight,
    borderWidth: 1,
    borderColor: COLORS.primaryLight,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  emptyQueueTitle: {
    color: COLORS.textPrimary,
    fontSize: 17,
    fontWeight: '700',
    marginBottom: 6,
  },
  emptyQueueSubtitle: {
    color: COLORS.textMuted,
    fontSize: 13,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 18,
  },
  emptyStateAddWalkInBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: COLORS.primary,
    paddingVertical: 10,
    paddingHorizontal: 18,
    borderRadius: RADIUS.full,
  },
  emptyStateAddWalkInText: {
    color: COLORS.onPrimary,
    fontSize: 14,
    fontWeight: '800',
  },
  unauthCard: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 32,
  },
  unauthIconHalo: {
    width: 72,
    height: 72,
    borderRadius: 36,
    backgroundColor: COLORS.primaryLight,
    borderWidth: 1,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
  },
  unauthTitle: {
    color: COLORS.textPrimary,
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 10,
  },
  unauthDesc: {
    color: COLORS.textSecondary,
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 24,
  },
  primaryGoldBtn: {
    width: '100%',
    backgroundColor: COLORS.primary,
    paddingVertical: 14,
    borderRadius: RADIUS.lg,
    alignItems: 'center',
  },
  primaryGoldBtnText: {
    color: COLORS.onPrimary,
    fontSize: 15,
    fontWeight: '800',
  },
  });
};
