import React, { useEffect, useState } from 'react';
import {
  Alert,
  FlatList,
  Linking,
  Pressable,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useLocalSearchParams } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { SafeAreaView } from 'react-native-safe-area-context';
import {
  AlertTriangle,
  Calendar,
  Check,
  CheckCircle2,
  Clock,
  Home,
  MapPin,
  Phone,
  Scissors,
  X,
} from 'lucide-react-native';
import { staffQueueApi } from '../../src/api/client';
import { Badge } from '../../src/components/common/Badge';
import { Card } from '../../src/components/common/Card';
import { ConfirmDialog } from '../../src/components/common/ConfirmDialog';
import { LoadingState } from '../../src/components/common/LoadingState';
import { CONFIG } from '../../src/constants/config';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { Appointment } from '../../src/types';

const STATUS_FILTERS = ['All', 'pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];

export default function BarberAppointmentsScreen() {
  const params = useLocalSearchParams<{ status?: string }>();
  const queryClient = useQueryClient();

  const [selectedStatus, setSelectedStatus] = useState<string>(
    params.status && STATUS_FILTERS.includes(params.status) ? params.status : 'All'
  );

  const [confirmAction, setConfirmAction] = useState<{
    appointment: Appointment;
    action: 'accept' | 'decline';
  } | null>(null);

  // Sync selected status if route param changes
  useEffect(() => {
    if (params.status && STATUS_FILTERS.includes(params.status)) {
      setSelectedStatus(params.status);
    }
  }, [params.status]);

  const {
    data: appointments = [],
    isLoading,
    refetch,
  } = useQuery({
    queryKey: ['allAppointments', selectedStatus],
    queryFn: () =>
      staffQueueApi.getAllAppointments({
        status: selectedStatus === 'All' ? undefined : selectedStatus,
      }),
  });

  // Mutation to accept or decline/cancel appointment
  const transitionMutation = useMutation({
    mutationFn: ({ id, status }: { id: number; status: any }) =>
      staffQueueApi.transitionStatus(id, status),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['allAppointments'] });
      queryClient.invalidateQueries({ queryKey: ['todayQueue'] });
      queryClient.invalidateQueries({ queryKey: ['barberPendingBookings'] });

      if (variables.status === 'confirmed') {
        Alert.alert(
          'Booking Accepted',
          'The customer has been notified that you accepted the booking. They can now prepare for the service!'
        );
      } else if (variables.status === 'cancelled') {
        Alert.alert('Booking Declined', 'The appointment request has been declined.');
      }
    },
    onError: (err: any) => {
      Alert.alert('Error', err.response?.data?.message || 'Failed to update appointment status.');
    },
  });

  const handleAccept = (item: Appointment) => {
    transitionMutation.mutate({ id: item.id, status: 'confirmed' });
  };

  const handleDecline = (item: Appointment) => {
    setConfirmAction({ appointment: item, action: 'decline' });
  };

  const formatMoney = (amount: number) => {
    return `${CONFIG.CURRENCY_SYMBOL}${amount.toLocaleString()}`;
  };

  const renderAppointmentItem = ({ item }: { item: Appointment }) => {
    const isPending = item.status === 'pending';
    const isHomeService = item.appointment_type === 'home_service';

    return (
      <Card style={[styles.card, isPending && styles.pendingCardHighlight]} elevated>
        {/* Pending Action Banner */}
        {isPending && (
          <View style={styles.actionRequiredBanner}>
            <AlertTriangle size={14} color="#E5BA73" />
            <Text style={styles.actionRequiredText}>
              ACTION REQUIRED: Accept or decline this customer's booking request
            </Text>
          </View>
        )}

        <View style={styles.cardTop}>
          <View style={{ flex: 1 }}>
            <Text style={styles.refText}>{item.booking_reference}</Text>
            <Text style={styles.customerName}>{item.customer?.name || 'Walk-In Guest'}</Text>
          </View>
          <Badge status={item.status} />
        </View>

        <View style={styles.divider} />

        {/* Date & Time */}
        <View style={styles.detailRow}>
          <View style={styles.rowLabelGroup}>
            <Clock size={13} color="#9CA3AF" />
            <Text style={styles.detailLabel}>Schedule:</Text>
          </View>
          <Text style={styles.detailValue}>
            {item.appointment_date} at {item.start_time.substring(0, 5)}
          </Text>
        </View>

        {/* Service */}
        <View style={styles.detailRow}>
          <View style={styles.rowLabelGroup}>
            <Scissors size={13} color="#9CA3AF" />
            <Text style={styles.detailLabel}>Service:</Text>
          </View>
          <Text style={styles.detailValue}>{item.service?.name || 'Standard Cut'}</Text>
        </View>

        {/* Service Type Tag (In-Shop vs Home Service) */}
        <View style={styles.detailRow}>
          <View style={styles.rowLabelGroup}>
            {isHomeService ? (
              <Home size={13} color="#E5BA73" />
            ) : (
              <Scissors size={13} color="#9CA3AF" />
            )}
            <Text style={styles.detailLabel}>Service Mode:</Text>
          </View>
          <View
            style={[
              styles.modePill,
              isHomeService ? styles.homeModePill : styles.shopModePill,
            ]}
          >
            <Text
              style={[
                styles.modePillText,
                isHomeService ? { color: '#E5BA73' } : { color: '#D1D5DB' },
              ]}
            >
              {isHomeService ? '🏠 Home Service' : '✂ In-Shop'}
            </Text>
          </View>
        </View>

        {/* Home Service Delivery Address & Landmark */}
        {isHomeService && item.destination_address && (
          <View style={styles.homeAddressCard}>
            <MapPin size={14} color="#E5BA73" />
            <View style={{ flex: 1 }}>
              <Text style={styles.homeAddressHeader}>Customer Location:</Text>
              <Text style={styles.homeAddressBody}>
                {item.destination_address.street_address}, {item.destination_address.area_landmark}
              </Text>
            </View>
          </View>
        )}

        {/* Total Price */}
        <View style={styles.detailRow}>
          <Text style={styles.detailLabel}>Total Revenue:</Text>
          <Text style={[styles.detailValue, { color: COLORS.primary, fontWeight: '800' }]}>
            {formatMoney(item.grand_total || item.service?.price || 0)}
          </Text>
        </View>

        {/* Phone Contact */}
        {item.customer?.phone && (
          <TouchableOpacity
            style={styles.phoneButton}
            onPress={() => Linking.openURL(`tel:${item.customer?.phone}`)}
          >
            <Phone size={13} color={COLORS.primary} />
            <Text style={styles.phoneText}>Call Customer ({item.customer.phone})</Text>
          </TouchableOpacity>
        )}

        {/* Pending Booking Accept / Decline Buttons */}
        {isPending && (
          <View style={styles.pendingActionButtonsRow}>
            <Pressable
              onPress={() => handleDecline(item)}
              style={({ pressed }) => [
                styles.declineBtn,
                pressed && styles.btnPressed,
              ]}
              accessibilityRole="button"
              accessibilityLabel="Decline booking request"
            >
              <X size={15} color="#EF4444" strokeWidth={2.5} />
              <Text style={styles.declineBtnText}>Decline</Text>
            </Pressable>

            <Pressable
              onPress={() => handleAccept(item)}
              style={({ pressed }) => [
                styles.acceptBtn,
                pressed && styles.btnPressed,
              ]}
              accessibilityRole="button"
              accessibilityLabel="Accept booking request"
            >
              <Check size={16} color="#0A0A0C" strokeWidth={3} />
              <Text style={styles.acceptBtnText}>Accept Booking</Text>
            </Pressable>
          </View>
        )}
      </Card>
    );
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>All Bookings</Text>
        <Text style={styles.subtitle}>
          Review customer requests, confirm appointments, and manage services
        </Text>
      </View>

      {/* Filter Tabs */}
      <View style={styles.filterRow}>
        <FlatList
          horizontal
          showsHorizontalScrollIndicator={false}
          data={STATUS_FILTERS}
          keyExtractor={(item) => item}
          contentContainerStyle={styles.filterList}
          renderItem={({ item }) => {
            const isSelected = selectedStatus === item;
            const isPendingTab = item === 'pending';

            return (
              <TouchableOpacity
                style={[
                  styles.filterChip,
                  isSelected && styles.filterChipActive,
                  isPendingTab && !isSelected && styles.filterChipPendingAlert,
                ]}
                onPress={() => setSelectedStatus(item)}
              >
                <Text
                  style={[
                    styles.filterText,
                    isSelected && styles.filterTextActive,
                    isPendingTab && !isSelected && { color: '#E5BA73' },
                  ]}
                >
                  {item === 'pending' ? 'PENDING REQUESTS' : item.toUpperCase()}
                </Text>
              </TouchableOpacity>
            );
          }}
        />
      </View>

      {isLoading ? (
        <LoadingState message="Loading your appointment book" />
      ) : (
        <FlatList
          data={appointments}
          keyExtractor={(item) => String(item.id)}
          renderItem={renderAppointmentItem}
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
              <Text style={styles.emptyText}>
                {selectedStatus === 'pending'
                  ? 'No pending booking requests right now. Great job!'
                  : 'No appointments matching this filter.'}
              </Text>
            </View>
          }
        />
      )}

      {/* Confirm Dialog for Declining Booking */}
      <ConfirmDialog
        visible={Boolean(confirmAction)}
        title="Decline Booking Request?"
        message={`Are you sure you want to decline this request for ${
          confirmAction?.appointment.customer?.name || 'this customer'
        }? The slot will be released.`}
        confirmLabel="Decline Booking"
        destructive
        variant="danger"
        onCancel={() => setConfirmAction(null)}
        onConfirm={() => {
          if (confirmAction) {
            transitionMutation.mutate({
              id: confirmAction.appointment.id,
              status: 'cancelled',
            });
          }
          setConfirmAction(null);
        }}
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
    paddingHorizontal: 20,
    paddingTop: 12,
    paddingBottom: 10,
  },
  title: {
    color: COLORS.textPrimary,
    fontSize: 22,
    fontWeight: '800',
    letterSpacing: 0.2,
  },
  subtitle: {
    color: COLORS.textSecondary,
    fontSize: 13,
    marginTop: 2,
    lineHeight: 18,
  },
  filterRow: {
    marginVertical: 8,
  },
  filterList: {
    paddingHorizontal: 20,
    gap: 8,
  },
  filterChip: {
    paddingVertical: 7,
    paddingHorizontal: 14,
    borderRadius: RADIUS.full,
    backgroundColor: '#14141B',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.08)',
  },
  filterChipActive: {
    backgroundColor: '#E5BA73',
    borderColor: '#E5BA73',
  },
  filterChipPendingAlert: {
    borderColor: 'rgba(229, 186, 115, 0.4)',
    backgroundColor: 'rgba(229, 186, 115, 0.1)',
  },
  filterText: {
    color: COLORS.textSecondary,
    fontSize: 11,
    fontWeight: '700',
    letterSpacing: 0.5,
  },
  filterTextActive: {
    color: '#0A0A0C',
    fontWeight: '800',
  },
  listContent: {
    padding: 20,
    gap: 14,
    paddingBottom: 40,
  },
  card: {
    padding: 16,
    borderRadius: 18,
    backgroundColor: '#14141B',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.07)',
  },
  pendingCardHighlight: {
    borderColor: 'rgba(229, 186, 115, 0.35)',
    backgroundColor: '#161513',
  },
  actionRequiredBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: 'rgba(229, 186, 115, 0.12)',
    paddingVertical: 6,
    paddingHorizontal: 10,
    borderRadius: 8,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: 'rgba(229, 186, 115, 0.25)',
  },
  actionRequiredText: {
    color: '#E5BA73',
    fontSize: 11,
    fontWeight: '700',
    flex: 1,
  },
  cardTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  refText: {
    color: '#E5BA73',
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 1,
  },
  customerName: {
    color: COLORS.textPrimary,
    fontSize: 16,
    fontWeight: '800',
    marginTop: 2,
  },
  divider: {
    height: 1,
    backgroundColor: 'rgba(255, 255, 255, 0.06)',
    marginVertical: 12,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  rowLabelGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  detailLabel: {
    color: COLORS.textSecondary,
    fontSize: 13,
  },
  detailValue: {
    color: COLORS.textPrimary,
    fontSize: 13,
    fontWeight: '600',
  },
  modePill: {
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 8,
  },
  homeModePill: {
    backgroundColor: 'rgba(229, 186, 115, 0.15)',
    borderWidth: 1,
    borderColor: 'rgba(229, 186, 115, 0.3)',
  },
  shopModePill: {
    backgroundColor: 'rgba(255, 255, 255, 0.06)',
  },
  modePillText: {
    fontSize: 11,
    fontWeight: '700',
  },
  homeAddressCard: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 8,
    backgroundColor: 'rgba(229, 186, 115, 0.07)',
    padding: 10,
    borderRadius: 10,
    marginVertical: 8,
    borderWidth: 1,
    borderColor: 'rgba(229, 186, 115, 0.18)',
  },
  homeAddressHeader: {
    color: '#E5BA73',
    fontSize: 11,
    fontWeight: '700',
    marginBottom: 2,
  },
  homeAddressBody: {
    color: '#D1D5DB',
    fontSize: 12,
    lineHeight: 17,
  },
  phoneButton: {
    marginTop: 6,
    marginBottom: 4,
    backgroundColor: '#1E1E28',
    paddingVertical: 9,
    paddingHorizontal: 12,
    borderRadius: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
  },
  phoneText: {
    color: '#E5BA73',
    fontSize: 12,
    fontWeight: '700',
  },
  pendingActionButtonsRow: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 14,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255, 255, 255, 0.07)',
  },
  declineBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: 'rgba(239, 68, 68, 0.35)',
    backgroundColor: 'rgba(239, 68, 68, 0.08)',
  },
  declineBtnText: {
    color: '#EF4444',
    fontSize: 13,
    fontWeight: '700',
  },
  acceptBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    backgroundColor: '#E5BA73',
    paddingVertical: 10,
    paddingHorizontal: 16,
    borderRadius: 12,
    shadowColor: '#E5BA73',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 3,
  },
  acceptBtnText: {
    color: '#0A0A0C',
    fontSize: 14,
    fontWeight: '800',
  },
  btnPressed: {
    transform: [{ scale: 0.97 }],
  },
  emptyContainer: {
    padding: 40,
    alignItems: 'center',
  },
  emptyText: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.sm,
    textAlign: 'center',
    lineHeight: 20,
  },
});
