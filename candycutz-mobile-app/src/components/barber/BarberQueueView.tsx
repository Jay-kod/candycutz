import React, { useEffect } from 'react';
import {
  Alert,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { SafeAreaView } from 'react-native-safe-area-context';
import { staffQueueApi } from '../../api/client';
import { Badge } from '../common/Badge';
import { Button } from '../common/Button';
import { Card } from '../common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../constants/theme';
import { useAuthStore } from '../../store/authStore';
import { useChairStore } from '../../store/chairStore';
import { Appointment, ChairStatus } from '../../types';

const CHAIR_STATUSES: { label: string; value: ChairStatus }[] = [
  { label: 'Free (Ready)', value: 'free' },
  { label: 'In Service', value: 'busy' },
  { label: 'On Break', value: 'break' },
  { label: 'Offline', value: 'offline' },
];

export function BarberQueueView() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const { barber, isAuthenticated, setChairStatus } = useAuthStore();
  const { activeClient, elapsedSeconds, setActiveClient, tickTimer } = useChairStore();

  const {
    data: queue = [],
    isLoading,
    refetch,
  } = useQuery({
    queryKey: ['todayQueue'],
    queryFn: staffQueueApi.getTodayQueue,
    enabled: isAuthenticated,
    refetchInterval: 15000, // auto refresh every 15s
  });

  // Timer interval for client in chair
  useEffect(() => {
    const timer = setInterval(() => {
      tickTimer();
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  const transitionMutation = useMutation({
    mutationFn: ({ id, status }: { id: number; status: any }) =>
      staffQueueApi.transitionStatus(id, status),
    onSuccess: (updated) => {
      queryClient.invalidateQueries({ queryKey: ['todayQueue'] });
      if (updated.status === 'in_progress') {
        setActiveClient(updated);
        setChairStatus('busy');
      } else if (updated.status === 'completed') {
        setActiveClient(null);
        setChairStatus('free');
        Alert.alert('Service Completed', `Great job! Service for ${updated.customer?.name || 'client'} finished.`);
      }
    },
    onError: (e: any) => {
      Alert.alert('Status Error', e.response?.data?.message || 'Could not update appointment.');
    },
  });

  if (!isAuthenticated) {
    return (
      <SafeAreaView style={styles.safeArea} edges={['top']}>
        <View style={styles.unauthContainer}>
          <Text style={styles.unauthIcon}>✂</Text>
          <Text style={styles.unauthTitle}>CandyCutz Staff Portal</Text>
          <Text style={styles.unauthDesc}>
            Sign in with your barber staff credentials to manage chair availability, live queue, and appointments.
          </Text>
          <Button
            title="Sign In as Barber / Staff"
            onPress={() => router.push('/auth/login')}
            style={{ width: '100%' }}
          />
        </View>
      </SafeAreaView>
    );
  }

  const formatTimer = (totalSecs: number) => {
    const mins = Math.floor(totalSecs / 60);
    const secs = totalSecs % 60;
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
  };

  const handleStartService = (appointment: Appointment) => {
    transitionMutation.mutate({ id: appointment.id, status: 'in_progress' });
  };

  const handleCompleteService = (appointment: Appointment) => {
    transitionMutation.mutate({ id: appointment.id, status: 'completed' });
  };

  const handleMarkNoShow = (appointment: Appointment) => {
    Alert.alert(
      'Mark as No-Show',
      `Are you sure ${appointment.customer?.name || 'client'} did not arrive?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Confirm No-Show',
          style: 'destructive',
          onPress: () => transitionMutation.mutate({ id: appointment.id, status: 'no_show' }),
        },
      ]
    );
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      {/* Top Header & Chair Status */}
      <View style={styles.header}>
        <View>
          <Text style={styles.barberGreeting}>Stylist Desk</Text>
          <Text style={styles.barberName}>{barber?.name || 'Master Barber'}</Text>
        </View>

        <TouchableOpacity
          onPress={() => router.push('/walkin')}
          style={styles.walkInBtn}
        >
          <Text style={styles.walkInBtnText}>+ Walk-In</Text>
        </TouchableOpacity>
      </View>

      {/* Chair Status Selector Strip */}
      <View style={styles.chairStatusRow}>
        <Text style={styles.chairLabel}>Chair Status:</Text>
        <View style={styles.statusChips}>
          {CHAIR_STATUSES.map((s) => {
            const isSelected = barber?.chair_status === s.value;
            return (
              <TouchableOpacity
                key={s.value}
                style={[styles.statusChip, isSelected && styles.statusChipActive]}
                onPress={() => setChairStatus(s.value)}
              >
                <Text style={[styles.statusChipText, isSelected && styles.statusChipTextActive]}>
                  {s.label}
                </Text>
              </TouchableOpacity>
            );
          })}
        </View>
      </View>

      <FlatList
        data={queue}
        keyExtractor={(item) => String(item.id)}
        refreshControl={
          <RefreshControl
            refreshing={isLoading}
            onRefresh={refetch}
            tintColor={COLORS.primary}
          />
        }
        contentContainerStyle={styles.listContent}
        ListHeaderComponent={
          <>
            {/* Active Client In Chair Banner */}
            {activeClient ? (
              <Card style={styles.activeChairCard} elevated>
                <View style={styles.activeChairHeader}>
                  <View style={styles.inServiceBadge}>
                    <View style={styles.pulseDot} />
                    <Text style={styles.inServiceText}>NOW IN CHAIR</Text>
                  </View>
                  <Text style={styles.timerText}>{formatTimer(elapsedSeconds)}</Text>
                </View>

                <Text style={styles.clientName}>{activeClient.customer?.name || 'Client'}</Text>
                <Text style={styles.clientService}>{activeClient.service?.name}</Text>
                <Text style={styles.clientRef}>{activeClient.booking_reference}</Text>

                <View style={styles.activeChairActions}>
                  <Button
                    title="Finish Cut & Check Out"
                    onPress={() => handleCompleteService(activeClient)}
                    loading={transitionMutation.isPending}
                    style={{ flex: 1 }}
                  />
                </View>
              </Card>
            ) : (
              <Card style={styles.emptyChairCard}>
                <Text style={styles.emptyChairTitle}>Chair is Available</Text>
                <Text style={styles.emptyChairSubtitle}>
                  Ready for next booked client or walk-in guest.
                </Text>
              </Card>
            )}

            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>Today's Queue ({queue.length})</Text>
            </View>
          </>
        }
        renderItem={({ item }) => {
          const isCurrentActive = activeClient?.id === item.id;
          if (isCurrentActive) return null; // Already rendered in Active Chair banner

          return (
            <Card style={styles.queueCard} elevated>
              <View style={styles.queueCardHeader}>
                <View>
                  <Text style={styles.queueTime}>{item.start_time.substring(0, 5)}</Text>
                  <Text style={styles.queueName}>{item.customer?.name || 'Walk-In Guest'}</Text>
                </View>
                <Badge status={item.status} />
              </View>

              <Text style={styles.queueService}>{item.service?.name || 'Standard Cut'}</Text>
              <Text style={styles.queueRef}>Ref: {item.booking_reference}</Text>

              {item.destination_address && (
                <View style={styles.homeAddressBox}>
                  <Text style={styles.homeAddressText}>
                    🏠 Home Delivery: {(item.destination_address as any).street_address}, {(item.destination_address as any).area_landmark}
                  </Text>
                </View>
              )}

              {/* Status Action Buttons */}
              <View style={styles.queueActionsRow}>
                {item.status === 'pending' && (
                  <Button
                    title="Check-In"
                    size="sm"
                    onPress={() => transitionMutation.mutate({ id: item.id, status: 'confirmed' })}
                    style={styles.actionBtn}
                  />
                )}

                {(item.status === 'confirmed' || item.status === 'pending') && (
                  <Button
                    title="Start Cut"
                    size="sm"
                    variant="primary"
                    onPress={() => handleStartService(item)}
                    style={styles.actionBtn}
                  />
                )}

                {item.status !== 'cancelled' && item.status !== 'completed' && (
                  <Button
                    title="No-Show"
                    size="sm"
                    variant="outline"
                    onPress={() => handleMarkNoShow(item)}
                    style={{ ...styles.actionBtn, borderColor: COLORS.error }}
                    textStyle={{ color: COLORS.error }}
                  />
                )}
              </View>
            </Card>
          );
        }}
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No appointments in queue for today.</Text>
            <Button
              title="Add Walk-In Guest"
              onPress={() => router.push('/walkin')}
              style={{ marginTop: 14 }}
            />
          </View>
        }
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
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: SPACING.md,
    paddingTop: SPACING.sm,
    paddingBottom: SPACING.sm,
  },
  barberGreeting: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 1,
  },
  barberName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
  },
  walkInBtn: {
    backgroundColor: COLORS.primary,
    paddingVertical: 8,
    paddingHorizontal: 14,
    borderRadius: RADIUS.md,
  },
  walkInBtnText: {
    color: '#0A0A0C',
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
  },
  chairStatusRow: {
    paddingHorizontal: SPACING.md,
    paddingBottom: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  chairLabel: {
    color: COLORS.textSecondary,
    fontSize: 11,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  statusChips: {
    flexDirection: 'row',
    gap: 6,
  },
  statusChip: {
    paddingVertical: 6,
    paddingHorizontal: 10,
    backgroundColor: COLORS.surfaceElevated,
    borderRadius: RADIUS.full,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  statusChipActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  statusChipText: {
    color: COLORS.textSecondary,
    fontSize: 11,
    fontWeight: '700',
  },
  statusChipTextActive: {
    color: '#0A0A0C',
  },
  listContent: {
    padding: SPACING.md,
    paddingBottom: 40,
    gap: 12,
  },
  activeChairCard: {
    backgroundColor: '#1B180E',
    borderColor: COLORS.primary,
    borderWidth: 1.5,
    padding: SPACING.md,
    marginBottom: SPACING.sm,
  },
  activeChairHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  inServiceBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primaryLight,
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: RADIUS.full,
  },
  pulseDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: COLORS.primary,
    marginRight: 6,
  },
  inServiceText: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
  },
  timerText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '900',
    fontVariant: ['tabular-nums'],
  },
  clientName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
  },
  clientService: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    marginTop: 2,
  },
  clientRef: {
    color: COLORS.textMuted,
    fontSize: 11,
    marginTop: 2,
  },
  activeChairActions: {
    flexDirection: 'row',
    marginTop: 14,
  },
  emptyChairCard: {
    padding: SPACING.md,
    alignItems: 'center',
    borderStyle: 'dashed',
    marginBottom: SPACING.sm,
  },
  emptyChairTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
  },
  emptyChairSubtitle: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
    marginTop: 2,
  },
  sectionHeader: {
    marginTop: 6,
    marginBottom: 4,
  },
  sectionTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '800',
  },
  queueCard: {
    padding: SPACING.md,
  },
  queueCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  queueTime: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.md,
    fontWeight: '800',
  },
  queueName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginTop: 1,
  },
  queueService: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    marginTop: 4,
  },
  queueRef: {
    color: COLORS.textMuted,
    fontSize: 10,
    marginTop: 2,
  },
  homeAddressBox: {
    backgroundColor: COLORS.surfaceHighlight,
    padding: 8,
    borderRadius: RADIUS.sm,
    marginTop: 8,
  },
  homeAddressText: {
    color: COLORS.textGold,
    fontSize: 11,
  },
  queueActionsRow: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 12,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    paddingTop: 10,
  },
  actionBtn: {
    flex: 1,
  },
  centerContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  emptyContainer: {
    padding: 30,
    alignItems: 'center',
  },
  emptyText: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.sm,
  },
  unauthContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: SPACING.xl,
  },
  unauthIcon: {
    fontSize: 54,
    marginBottom: 16,
  },
  unauthTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xxl,
    fontWeight: '800',
    textAlign: 'center',
    marginBottom: 8,
  },
  unauthDesc: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    textAlign: 'center',
    lineHeight: 20,
    marginBottom: 24,
  },
});
