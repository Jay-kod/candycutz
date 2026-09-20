import React, { useEffect, useMemo, useState } from 'react';
import {
  Alert,
  FlatList,
  Linking,
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
  AlertTriangle,
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
  Sparkles,
  TrendingUp,
  User,
  X,
} from 'lucide-react-native';
import { staffQueueApi } from '../../api/client';
import { ConfirmDialog } from '../common/ConfirmDialog';
import { CONFIG } from '../../constants/config';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useAuthStore } from '../../store/authStore';
import { useChairStore } from '../../store/chairStore';
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
  const queryClient = useQueryClient();
  const { barber, isAuthenticated, setChairStatus } = useAuthStore();
  const { activeClient, elapsedSeconds, setActiveClient, tickTimer } = useChairStore();
  const { unreadCount } = useNotifications(isAuthenticated);

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

  // Dedicated Pending Bookings Query for this specific barber (e.g. In-Shop & Home Service requests)
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
        Alert.alert(
          'Booking Accepted',
          `You have accepted the booking for ${updated.customer?.name || 'the customer'}. They have been notified to prepare for the service!`
        );
      } else if (updated.status === 'cancelled') {
        Alert.alert('Booking Declined', 'The booking request has been declined.');
      }
    },
    onError: (err: any) => {
      Alert.alert('Error', err.response?.data?.message || 'Failed to update appointment status.');
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
      icon: <Check size={14} color="#10B981" strokeWidth={2.5} />,
      activeColor: '#10B981',
    },
    {
      label: 'In Chair',
      value: 'busy',
      icon: <Scissors size={14} color="#E5BA73" strokeWidth={2.5} />,
      activeColor: '#E5BA73',
    },
    {
      label: 'Break',
      value: 'break',
      icon: <Coffee size={14} color="#F59E0B" strokeWidth={2.5} />,
      activeColor: '#F59E0B',
    },
    {
      label: 'Offline',
      value: 'offline',
      icon: <Power size={14} color="#9CA3AF" strokeWidth={2.5} />,
      activeColor: '#6B7280',
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
          <Text style={styles.unauthTitle}>Barber Workspace</Text>
          <Text style={styles.unauthDesc}>
            Sign in to access your live chair queue, manage walk-ins, and track today's appointments.
          </Text>
          <Pressable
            style={({ pressed }) => [
              styles.primaryGoldBtn,
              pressed && styles.btnPressed,
            ]}
            onPress={() => router.push('/auth/login')}
          >
            <Text style={styles.primaryGoldBtnText}>Sign In to Workspace</Text>
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
        <View style={styles.barberProfileBlock}>
          <View style={styles.avatarRing}>
            <Text style={styles.avatarText}>{getInitials(barber?.name)}</Text>
          </View>
          <View>
            <Text style={styles.greetingText}>Welcome back,</Text>
            <Text style={styles.barberName} numberOfLines={1}>
              {barber?.name || 'Master Barber'}
            </Text>
          </View>
        </View>

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
            <Bell size={18} color="#FFFFFF" />
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
            <Plus size={16} color="#0A0A0C" strokeWidth={3} />
            <Text style={styles.walkInBtnText}>Walk-In</Text>
          </Pressable>
        </View>
      </View>

      {/* 2. Sleek Chair Status Segmented Hub */}
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
                    backgroundColor: 'rgba(255, 255, 255, 0.08)',
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
                    isSelected && { color: '#FFFFFF', fontWeight: '700' },
                  ]}
                >
                  {opt.label}
                </Text>
              </Pressable>
            );
          })}
        </View>
      </View>

      {/* 3. Main Scrollable Content */}
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
            {/* Shift Snapshot Bar (3 Glanceable KPIs) */}
            <View style={styles.kpiRow}>
              <View style={styles.kpiCard}>
                <View style={styles.kpiIconBox}>
                  <Clock size={16} color="#E5BA73" />
                </View>
                <View>
                  <Text style={styles.kpiValue}>{stats.waiting}</Text>
                  <Text style={styles.kpiLabel}>In Queue</Text>
                </View>
              </View>

              <View style={styles.kpiCard}>
                <View style={[styles.kpiIconBox, { backgroundColor: 'rgba(16, 185, 129, 0.12)' }]}>
                  <CheckCircle2 size={16} color="#10B981" />
                </View>
                <View>
                  <Text style={styles.kpiValue}>{stats.completed}</Text>
                  <Text style={styles.kpiLabel}>Finished</Text>
                </View>
              </View>

              <View style={styles.kpiCard}>
                <View style={[styles.kpiIconBox, { backgroundColor: 'rgba(59, 130, 246, 0.12)' }]}>
                  <TrendingUp size={16} color="#3B82F6" />
                </View>
                <View>
                  <Text style={styles.kpiValue}>{formatMoney(stats.earnedKobo)}</Text>
                  <Text style={styles.kpiLabel}>Today's Cuts</Text>
                </View>
              </View>
            </View>

            {/* 3B. High-Priority Pending Booking Requests Stage */}
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
                {/* Glowing Top Amber Accent Rim */}
                <View style={styles.pendingCardRim} />

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

                {/* Latest Pending Booking Info */}
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
                      📅 {primaryPendingBooking.appointment_date} at {primaryPendingBooking.start_time.substring(0, 5)}
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
                        <Home size={13} color="#E5BA73" />
                        <Text style={styles.homeServicePillText}>
                          🏠 Home Service Request
                        </Text>
                      </>
                    ) : (
                      <>
                        <Scissors size={13} color="#D1D5DB" />
                        <Text style={styles.inShopPillText}>
                          ✂ In-Shop Appointment
                        </Text>
                      </>
                    )}
                  </View>

                  {/* Redirection indicator */}
                  <View style={styles.viewDetailsPrompt}>
                    <Text style={styles.viewDetailsText}>View & Decide</Text>
                    <ArrowRight size={13} color="#E5BA73" />
                  </View>
                </View>

                {/* Destination Landmark if Home Service */}
                {primaryPendingBooking.appointment_type === 'home_service' &&
                  primaryPendingBooking.destination_address && (
                    <View style={styles.landmarkBox}>
                      <MapPin size={13} color="#E5BA73" />
                      <Text style={styles.landmarkText} numberOfLines={1}>
                        Location: {primaryPendingBooking.destination_address.area_landmark}, {primaryPendingBooking.destination_address.street_address}
                      </Text>
                    </View>
                  )}

                {/* Direct Action Buttons on Card */}
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
                    <X size={15} color="#EF4444" strokeWidth={2.5} />
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
                    <Check size={16} color="#0A0A0C" strokeWidth={3} />
                    <Text style={styles.pendingAcceptBtnText}>Accept Booking</Text>
                  </Pressable>
                </View>
              </Pressable>
            )}

            {/* 4. "Now In Chair" Spotlight Stage */}
            {activeClient ? (
              <View style={styles.activeSpotlightCard}>
                <View style={styles.activeCardRim} />

                <View style={styles.activeCardTopRow}>
                  <View style={styles.livePulsePill}>
                    <View style={styles.livePulseDot} />
                    <Text style={styles.livePulseText}>NOW IN CHAIR</Text>
                  </View>
                  <View style={styles.timerBadge}>
                    <Clock size={14} color="#E5BA73" />
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
                  <CheckCircle2 size={18} color="#0A0A0C" strokeWidth={2.5} />
                  <Text style={styles.finishServiceBtnText}>
                    Finish Cut & Check Out
                  </Text>
                </Pressable>
              </View>
            ) : (
              /* Chair Available Card */
              <View style={styles.chairAvailableCard}>
                <View style={styles.chairAvailableHeader}>
                  <View style={styles.chairReadyBadge}>
                    <Sparkles size={14} color="#10B981" />
                    <Text style={styles.chairReadyText}>CHAIR IS READY</Text>
                  </View>
                  <Text style={styles.dateLabel}>{getTodayFormattedDate()}</Text>
                </View>

                {nextUpClient ? (
                  <View style={styles.nextUpPromptBox}>
                    <View style={{ flex: 1 }}>
                      <Text style={styles.nextUpLead}>Next Client in Queue:</Text>
                      <Text style={styles.nextUpName} numberOfLines={1}>
                        {nextUpClient.customer?.name || 'Guest'} ({nextUpClient.start_time.substring(0, 5)})
                      </Text>
                      <Text style={styles.nextUpService}>
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
                      <Scissors size={15} color="#0A0A0C" strokeWidth={2.5} />
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

            {/* 5. Queue Segment Filter Tabs */}
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
                  <Clock size={12} color="#E5BA73" />
                  <Text style={styles.timeBadgeText}>
                    {item.start_time.substring(0, 5)}
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
                      isCompleted && { color: '#10B981' },
                      isConfirmed && { color: '#E5BA73' },
                      isPending && { color: '#F59E0B' },
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
                    <Phone size={16} color="#9CA3AF" />
                  </Pressable>
                )}
              </View>

              {item.destination_address && (
                <View style={styles.homeDeliveryBox}>
                  <MapPin size={13} color="#E5BA73" />
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
                      <Scissors size={15} color="#0A0A0C" strokeWidth={2.5} />
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
              <CheckCircle2 size={30} color="#E5BA73" />
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
                <Plus size={16} color="#0A0A0C" strokeWidth={3} />
                <Text style={styles.emptyStateAddWalkInText}>Add Walk-In Client</Text>
              </Pressable>
            )}
          </View>
        }
      />

      {/* 6. High-End Redesigned Confirmation Dialog */}
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

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#0A0A0C',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 12,
    paddingBottom: 12,
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
    backgroundColor: '#1C1C26',
    borderWidth: 1.5,
    borderColor: '#E5BA73',
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {
    color: '#E5BA73',
    fontSize: 16,
    fontWeight: '800',
  },
  greetingText: {
    color: '#9CA3AF',
    fontSize: 12,
    fontWeight: '500',
  },
  barberName: {
    color: '#FFFFFF',
    fontSize: 18,
    fontWeight: '800',
    letterSpacing: 0.2,
  },
  headerRightActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  notifBellBtn: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: '#14141B',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.08)',
    alignItems: 'center',
    justifyContent: 'center',
    position: 'relative',
  },
  notifBadge: {
    position: 'absolute',
    top: -3,
    right: -3,
    backgroundColor: '#E5BA73',
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
    color: '#0A0A0C',
  },
  walkInQuickBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: '#E5BA73',
    paddingVertical: 8,
    paddingHorizontal: 14,
    borderRadius: 20,
    shadowColor: '#E5BA73',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 3,
  },
  walkInBtnText: {
    color: '#0A0A0C',
    fontSize: 13,
    fontWeight: '800',
  },
  btnPressed: {
    transform: [{ scale: 0.96 }],
  },
  cardPressed: {
    opacity: 0.9,
    transform: [{ scale: 0.99 }],
  },
  chairStatusHub: {
    paddingHorizontal: 20,
    paddingBottom: 14,
  },
  statusSegmentContainer: {
    flexDirection: 'row',
    backgroundColor: '#14141B',
    borderRadius: 14,
    padding: 4,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.06)',
  },
  statusSegmentItem: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 8,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: 'transparent',
  },
  statusDot: {
    width: 7,
    height: 7,
    borderRadius: 3.5,
  },
  statusDotActive: {
    transform: [{ scale: 1.2 }],
  },
  statusSegmentText: {
    color: '#9CA3AF',
    fontSize: 12,
    fontWeight: '600',
  },
  scrollList: {
    paddingHorizontal: 20,
    paddingBottom: 40,
  },
  headerComponentWrapper: {
    marginBottom: 16,
  },
  kpiRow: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 16,
  },
  kpiCard: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    backgroundColor: '#14141B',
    paddingVertical: 12,
    paddingHorizontal: 10,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.06)',
  },
  kpiIconBox: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: 'rgba(212, 175, 55, 0.12)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  kpiValue: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '800',
  },
  kpiLabel: {
    color: '#9CA3AF',
    fontSize: 10,
    fontWeight: '500',
    marginTop: 1,
  },
  pendingBannerCard: {
    backgroundColor: '#181611',
    borderRadius: 18,
    padding: 16,
    borderWidth: 1.5,
    borderColor: 'rgba(229, 186, 115, 0.45)',
    marginBottom: 16,
    overflow: 'hidden',
    shadowColor: '#E5BA73',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.15,
    shadowRadius: 10,
    elevation: 4,
  },
  pendingCardRim: {
    position: 'absolute',
    top: 0,
    left: '15%',
    right: '15%',
    height: 2,
    backgroundColor: '#E5BA73',
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
    backgroundColor: 'rgba(229, 186, 115, 0.15)',
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(229, 186, 115, 0.3)',
  },
  pendingPulseDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: '#E5BA73',
  },
  pendingNoticeText: {
    color: '#E5BA73',
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.8,
  },
  pendingCountBadge: {
    backgroundColor: '#262217',
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 8,
  },
  pendingCountBadgeText: {
    color: '#E5BA73',
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
    backgroundColor: '#2A2416',
    borderWidth: 1,
    borderColor: 'rgba(229, 186, 115, 0.4)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  pendingClientAvatarText: {
    color: '#E5BA73',
    fontSize: 14,
    fontWeight: '800',
  },
  pendingClientName: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '800',
  },
  pendingScheduleText: {
    color: '#D1D5DB',
    fontSize: 12,
    marginTop: 2,
    fontWeight: '500',
  },
  pendingServiceText: {
    color: '#9CA3AF',
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
    borderRadius: 8,
  },
  homeServicePill: {
    backgroundColor: 'rgba(229, 186, 115, 0.15)',
    borderWidth: 1,
    borderColor: 'rgba(229, 186, 115, 0.3)',
  },
  inShopPill: {
    backgroundColor: 'rgba(255, 255, 255, 0.06)',
  },
  homeServicePillText: {
    color: '#E5BA73',
    fontSize: 11,
    fontWeight: '700',
  },
  inShopPillText: {
    color: '#D1D5DB',
    fontSize: 11,
    fontWeight: '700',
  },
  viewDetailsPrompt: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  viewDetailsText: {
    color: '#E5BA73',
    fontSize: 12,
    fontWeight: '700',
  },
  landmarkBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: 'rgba(229, 186, 115, 0.08)',
    paddingVertical: 6,
    paddingHorizontal: 10,
    borderRadius: 8,
    marginVertical: 4,
  },
  landmarkText: {
    color: '#E5BA73',
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
    borderTopColor: 'rgba(255, 255, 255, 0.07)',
  },
  pendingDeclineBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 9,
    paddingHorizontal: 16,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: 'rgba(239, 68, 68, 0.35)',
    backgroundColor: 'rgba(239, 68, 68, 0.08)',
  },
  pendingDeclineBtnText: {
    color: '#EF4444',
    fontSize: 13,
    fontWeight: '700',
  },
  pendingAcceptBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: '#E5BA73',
    paddingVertical: 9,
    paddingHorizontal: 16,
    borderRadius: 10,
    shadowColor: '#E5BA73',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 6,
    elevation: 3,
  },
  pendingAcceptBtnText: {
    color: '#0A0A0C',
    fontSize: 13,
    fontWeight: '800',
  },
  activeSpotlightCard: {
    backgroundColor: '#161512',
    borderRadius: 20,
    padding: 18,
    borderWidth: 1.5,
    borderColor: '#E5BA73',
    marginBottom: 20,
    overflow: 'hidden',
    shadowColor: '#E5BA73',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.15,
    shadowRadius: 14,
    elevation: 6,
  },
  activeCardRim: {
    position: 'absolute',
    top: 0,
    left: '20%',
    right: '20%',
    height: 2,
    backgroundColor: '#E5BA73',
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
    backgroundColor: 'rgba(229, 186, 115, 0.12)',
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(229, 186, 115, 0.3)',
  },
  livePulseDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: '#E5BA73',
  },
  livePulseText: {
    color: '#E5BA73',
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
  },
  timerBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: 'rgba(255, 255, 255, 0.05)',
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderRadius: 12,
  },
  timerDigits: {
    color: '#E5BA73',
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
    backgroundColor: '#26241D',
    borderWidth: 1,
    borderColor: '#E5BA73',
    alignItems: 'center',
    justifyContent: 'center',
  },
  clientAvatarText: {
    color: '#E5BA73',
    fontSize: 16,
    fontWeight: '800',
  },
  activeClientName: {
    color: '#FFFFFF',
    fontSize: 17,
    fontWeight: '800',
  },
  activeServiceName: {
    color: '#D1D5DB',
    fontSize: 13,
    marginTop: 2,
  },
  activeRefNumber: {
    color: '#6B7280',
    fontSize: 11,
    marginTop: 2,
  },
  finishServiceBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    backgroundColor: '#E5BA73',
    paddingVertical: 13,
    borderRadius: 14,
    shadowColor: '#E5BA73',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  finishServiceBtnText: {
    color: '#0A0A0C',
    fontSize: 15,
    fontWeight: '800',
  },
  chairAvailableCard: {
    backgroundColor: '#14141B',
    borderRadius: 18,
    padding: 16,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.07)',
    marginBottom: 20,
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
    backgroundColor: 'rgba(16, 185, 129, 0.12)',
    paddingVertical: 4,
    paddingHorizontal: 10,
    borderRadius: 12,
  },
  chairReadyText: {
    color: '#10B981',
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 0.8,
  },
  dateLabel: {
    color: '#9CA3AF',
    fontSize: 12,
    fontWeight: '500',
  },
  nextUpPromptBox: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: 'rgba(255, 255, 255, 0.03)',
    borderRadius: 14,
    padding: 12,
    marginTop: 4,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.05)',
  },
  nextUpLead: {
    color: '#9CA3AF',
    fontSize: 11,
    fontWeight: '600',
  },
  nextUpName: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '700',
    marginTop: 2,
  },
  nextUpService: {
    color: '#E5BA73',
    fontSize: 12,
    marginTop: 1,
  },
  seatNextBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: '#E5BA73',
    paddingVertical: 9,
    paddingHorizontal: 14,
    borderRadius: 12,
  },
  seatNextBtnText: {
    color: '#0A0A0C',
    fontSize: 13,
    fontWeight: '800',
  },
  emptyChairSub: {
    color: '#9CA3AF',
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
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '800',
  },
  filterPillsContainer: {
    flexDirection: 'row',
    backgroundColor: '#14141B',
    padding: 3,
    borderRadius: 12,
    gap: 4,
  },
  filterPill: {
    paddingVertical: 5,
    paddingHorizontal: 10,
    borderRadius: 8,
  },
  filterPillActive: {
    backgroundColor: '#232330',
  },
  filterPillText: {
    color: '#9CA3AF',
    fontSize: 11,
    fontWeight: '600',
  },
  filterPillTextActive: {
    color: '#E5BA73',
    fontWeight: '700',
  },
  queueItemCard: {
    backgroundColor: '#14141B',
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.06)',
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
    backgroundColor: 'rgba(212, 175, 55, 0.1)',
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 8,
  },
  timeBadgeText: {
    color: '#E5BA73',
    fontSize: 12,
    fontWeight: '700',
  },
  itemStatusPill: {
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 8,
    backgroundColor: 'rgba(255, 255, 255, 0.05)',
  },
  statusPillCompleted: {
    backgroundColor: 'rgba(16, 185, 129, 0.12)',
  },
  statusPillConfirmed: {
    backgroundColor: 'rgba(212, 175, 55, 0.12)',
  },
  statusPillPending: {
    backgroundColor: 'rgba(245, 158, 11, 0.12)',
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
    backgroundColor: '#1F1F2C',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.08)',
  },
  clientAvatarSmallText: {
    color: '#D1D5DB',
    fontSize: 13,
    fontWeight: '700',
  },
  queueClientName: {
    color: '#FFFFFF',
    fontSize: 15,
    fontWeight: '700',
  },
  queueServiceDetails: {
    color: '#9CA3AF',
    fontSize: 13,
    marginTop: 2,
  },
  queueRefText: {
    color: '#6B7280',
    fontSize: 11,
    marginTop: 1,
  },
  callIconBtn: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: 'rgba(255, 255, 255, 0.05)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  homeDeliveryBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: 'rgba(212, 175, 55, 0.06)',
    paddingVertical: 6,
    paddingHorizontal: 10,
    borderRadius: 10,
    marginTop: 10,
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.15)',
  },
  homeDeliveryText: {
    color: '#E5BA73',
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
    borderTopColor: 'rgba(255, 255, 255, 0.05)',
  },
  startCutBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: '#E5BA73',
    paddingVertical: 10,
    borderRadius: 12,
  },
  startCutBtnText: {
    color: '#0A0A0C',
    fontSize: 13,
    fontWeight: '800',
  },
  checkInBtn: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#232330',
    paddingVertical: 10,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.1)',
  },
  checkInBtnText: {
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '700',
  },
  noShowBtn: {
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(239, 68, 68, 0.35)',
    backgroundColor: 'rgba(239, 68, 68, 0.06)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  noShowBtnText: {
    color: '#EF4444',
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
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: 'rgba(212, 175, 55, 0.1)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  emptyQueueTitle: {
    color: '#FFFFFF',
    fontSize: 17,
    fontWeight: '700',
    marginBottom: 6,
  },
  emptyQueueSubtitle: {
    color: '#9CA3AF',
    fontSize: 13,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 18,
  },
  emptyStateAddWalkInBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: '#E5BA73',
    paddingVertical: 10,
    paddingHorizontal: 18,
    borderRadius: 14,
  },
  emptyStateAddWalkInText: {
    color: '#0A0A0C',
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
    backgroundColor: 'rgba(212, 175, 55, 0.12)',
    borderWidth: 1,
    borderColor: COLORS.primary,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
  },
  unauthTitle: {
    color: '#FFFFFF',
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 10,
  },
  unauthDesc: {
    color: '#9CA3AF',
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 24,
  },
  primaryGoldBtn: {
    width: '100%',
    backgroundColor: '#E5BA73',
    paddingVertical: 14,
    borderRadius: 16,
    alignItems: 'center',
  },
  primaryGoldBtnText: {
    color: '#0A0A0C',
    fontSize: 15,
    fontWeight: '800',
  },
});
