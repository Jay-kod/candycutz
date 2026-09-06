import React, { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Linking,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { SafeAreaView } from 'react-native-safe-area-context';
import { bookingsApi } from '../../src/api/client';
import { Badge } from '../../src/components/common/Badge';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { CONFIG } from '../../src/constants/config';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAuthStore } from '../../src/store/authStore';
import { Appointment } from '../../src/types';

export default function BookingsScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const { isAuthenticated } = useAuthStore();
  const [tab, setTab] = useState<'upcoming' | 'past'>('upcoming');

  const {
    data: appointments = [],
    isLoading,
    refetch,
  } = useQuery({
    queryKey: ['appointments'],
    queryFn: bookingsApi.getAll,
    enabled: isAuthenticated,
  });

  const cancelMutation = useMutation({
    mutationFn: (id: number) => bookingsApi.cancel(id, 'Cancelled via customer mobile app'),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['appointments'] });
      Alert.alert('Booking Cancelled', 'Your appointment has been successfully cancelled.');
    },
    onError: (e: any) => {
      Alert.alert('Cancellation Failed', e.response?.data?.message || 'Could not cancel booking.');
    },
  });

  const handleCancelPrompt = (appointment: Appointment) => {
    Alert.alert(
      'Cancel Appointment',
      `Are you sure you want to cancel booking ${appointment.booking_reference}?`,
      [
        { text: 'No, Keep It', style: 'cancel' },
        {
          text: 'Yes, Cancel',
          style: 'destructive',
          onPress: () => cancelMutation.mutate(appointment.id),
        },
      ]
    );
  };

  if (!isAuthenticated) {
    return (
      <SafeAreaView style={styles.safeArea} edges={['top']}>
        <View style={styles.unauthContainer}>
          <Text style={styles.unauthIcon}>📅</Text>
          <Text style={styles.unauthTitle}>Track Your Appointments</Text>
          <Text style={styles.unauthDesc}>
            Sign in to view active appointments, real-time barber queue progress, and booking history.
          </Text>
          <Button
            title="Sign In with Account"
            onPress={() => router.push('/auth/login')}
            style={styles.unauthBtn}
          />
        </View>
      </SafeAreaView>
    );
  }

  const today = new Date().toISOString().split('T')[0];

  const upcomingBookings = appointments.filter(
    (item) => item.status !== 'cancelled' && item.status !== 'completed' && item.appointment_date >= today
  );

  const pastBookings = appointments.filter(
    (item) => item.status === 'cancelled' || item.status === 'completed' || item.appointment_date < today
  );

  const currentList = tab === 'upcoming' ? upcomingBookings : pastBookings;

  const renderBookingCard = ({ item }: { item: Appointment }) => {
    const canCancel = item.status === 'pending' || item.status === 'confirmed';

    return (
      <Card style={styles.bookingCard} elevated>
        <View style={styles.cardHeader}>
          <View>
            <Text style={styles.refText}>{item.booking_reference}</Text>
            <Text style={styles.serviceName}>{item.service?.name || 'Haircut & Styling'}</Text>
          </View>
          <Badge status={item.status} />
        </View>

        <View style={styles.divider} />

        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Date & Time:</Text>
          <Text style={styles.detailValue}>
            {item.appointment_date} at {item.start_time.substring(0, 5)}
          </Text>
        </View>

        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Service Type:</Text>
          <Text style={styles.detailValue}>
            {item.appointment_type === 'home_service' ? '🏠 Home Service' : '✂ In-Shop'}
          </Text>
        </View>

        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Stylist:</Text>
          <Text style={styles.detailValue}>
            {item.barber?.name || 'Master Stylist'}
          </Text>
        </View>

        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Total Paid / Due:</Text>
          <Text style={[styles.detailValue, { color: COLORS.primary, fontWeight: '800' }]}>
            ₦{Number(item.grand_total).toLocaleString()}
          </Text>
        </View>

        {item.appointment_type === 'in_shop' && (
          <TouchableOpacity
            style={styles.locationLink}
            onPress={() => Linking.openURL(CONFIG.BRANCH.MAPS_URL)}
          >
            <Text style={styles.locationLinkText}>
              📍 Keffi Branch: {CONFIG.BRANCH.SHORT_ADDRESS} (Open Maps)
            </Text>
          </TouchableOpacity>
        )}

        {canCancel && (
          <View style={styles.cardFooter}>
            <Button
              title="Cancel Booking"
              variant="outline"
              size="sm"
              onPress={() => handleCancelPrompt(item)}
              loading={cancelMutation.isPending}
              style={styles.cancelBtn}
            />
          </View>
        )}
      </Card>
    );
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>My Appointments</Text>
      </View>

      {/* Tabs */}
      <View style={styles.tabBar}>
        <TouchableOpacity
          onPress={() => setTab('upcoming')}
          style={[styles.tabItem, tab === 'upcoming' && styles.tabItemActive]}
        >
          <Text style={[styles.tabText, tab === 'upcoming' && styles.tabTextActive]}>
            Upcoming ({upcomingBookings.length})
          </Text>
        </TouchableOpacity>
        <TouchableOpacity
          onPress={() => setTab('past')}
          style={[styles.tabItem, tab === 'past' && styles.tabItemActive]}
        >
          <Text style={[styles.tabText, tab === 'past' && styles.tabTextActive]}>
            Past & Cancelled
          </Text>
        </TouchableOpacity>
      </View>

      {isLoading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
        </View>
      ) : (
        <FlatList
          data={currentList}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderBookingCard}
          contentContainerStyle={styles.listContent}
          refreshControl={
            <RefreshControl
              refreshing={isLoading}
              onRefresh={refetch}
              tintColor={COLORS.primary}
            />
          }
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyTitle}>No Appointments Found</Text>
              <Text style={styles.emptyDesc}>
                {tab === 'upcoming'
                  ? "You don't have any upcoming reservations."
                  : 'You have no past appointment history.'}
              </Text>
              <Button
                title="Book New Appointment"
                onPress={() => router.push('/(tabs)/services')}
                style={styles.emptyBtn}
              />
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
  tabBar: {
    flexDirection: 'row',
    marginHorizontal: SPACING.md,
    backgroundColor: COLORS.surfaceElevated,
    borderRadius: RADIUS.md,
    padding: 4,
    marginBottom: SPACING.md,
  },
  tabItem: {
    flex: 1,
    paddingVertical: 10,
    alignItems: 'center',
    borderRadius: RADIUS.sm,
  },
  tabItemActive: {
    backgroundColor: COLORS.primary,
  },
  tabText: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  tabTextActive: {
    color: '#0A0A0C',
  },
  listContent: {
    padding: SPACING.md,
    gap: 14,
    paddingBottom: 32,
  },
  bookingCard: {
    padding: SPACING.md,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  refText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '800',
    letterSpacing: 1,
  },
  serviceName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '700',
    marginTop: 2,
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginVertical: 12,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  detailLabel: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.sm,
  },
  detailValue: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
  },
  locationLink: {
    backgroundColor: COLORS.surfaceHighlight,
    padding: 10,
    borderRadius: RADIUS.sm,
    marginTop: 8,
  },
  locationLinkText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
  },
  cardFooter: {
    marginTop: 14,
    borderTopWidth: 1,
    borderTopColor: COLORS.border,
    paddingTop: 12,
    alignItems: 'flex-end',
  },
  cancelBtn: {
    borderColor: COLORS.error,
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
  emptyTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '700',
    marginBottom: 6,
  },
  emptyDesc: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.sm,
    textAlign: 'center',
    marginBottom: 20,
  },
  emptyBtn: {
    minWidth: 200,
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
  unauthBtn: {
    width: '100%',
  },
});
