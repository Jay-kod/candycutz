import React, { useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Linking,
  RefreshControl,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { SafeAreaView } from 'react-native-safe-area-context';
import { staffQueueApi } from '../../src/api/client';
import { Badge } from '../../src/components/common/Badge';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { Appointment } from '../../src/types';

const STATUS_FILTERS = ['All', 'confirmed', 'in_progress', 'completed', 'cancelled'];

export default function BarberAppointmentsScreen() {
  const [selectedStatus, setSelectedStatus] = useState('All');

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

  const renderAppointmentItem = ({ item }: { item: Appointment }) => (
    <Card style={styles.card} elevated>
      <View style={styles.cardTop}>
        <View>
          <Text style={styles.refText}>{item.booking_reference}</Text>
          <Text style={styles.customerName}>{item.customer?.name || 'Walk-In Guest'}</Text>
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
        <Text style={styles.detailLabel}>Service:</Text>
        <Text style={styles.detailValue}>{item.service?.name}</Text>
      </View>

      <View style={styles.detailRow}>
        <Text style={styles.detailLabel}>Type:</Text>
        <Text style={styles.detailValue}>
          {item.appointment_type === 'home_service' ? '🏠 Home Service' : '✂ In-Shop'}
        </Text>
      </View>

      <View style={styles.detailRow}>
        <Text style={styles.detailLabel}>Total Revenue:</Text>
        <Text style={[styles.detailValue, { color: COLORS.primary, fontWeight: '800' }]}>
          ₦{Number(item.grand_total).toLocaleString()}
        </Text>
      </View>

      {item.customer?.phone && (
        <TouchableOpacity
          style={styles.phoneButton}
          onPress={() => Linking.openURL(`tel:${item.customer?.phone}`)}
        >
          <Text style={styles.phoneText}>📞 Call Customer ({item.customer.phone})</Text>
        </TouchableOpacity>
      )}
    </Card>
  );

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <View style={styles.header}>
        <Text style={styles.title}>All Bookings</Text>
        <Text style={styles.subtitle}>Comprehensive schedule and client log</Text>
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
            return (
              <TouchableOpacity
                style={[styles.filterChip, isSelected && styles.filterChipActive]}
                onPress={() => setSelectedStatus(item)}
              >
                <Text style={[styles.filterText, isSelected && styles.filterTextActive]}>
                  {item.toUpperCase()}
                </Text>
              </TouchableOpacity>
            );
          }}
        />
      </View>

      {isLoading ? (
        <View style={styles.centerContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
        </View>
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
              <Text style={styles.emptyText}>No appointments matching this filter.</Text>
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
    fontSize: FONTS.sizes.xl,
    fontWeight: '800',
  },
  subtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    marginTop: 2,
  },
  filterRow: {
    marginVertical: SPACING.xs,
  },
  filterList: {
    paddingHorizontal: SPACING.md,
    gap: 8,
  },
  filterChip: {
    paddingVertical: 6,
    paddingHorizontal: 12,
    borderRadius: RADIUS.full,
    backgroundColor: COLORS.surfaceElevated,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  filterChipActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  filterText: {
    color: COLORS.textSecondary,
    fontSize: 10,
    fontWeight: '700',
  },
  filterTextActive: {
    color: '#0A0A0C',
  },
  listContent: {
    padding: SPACING.md,
    gap: 12,
    paddingBottom: 40,
  },
  card: {
    padding: SPACING.md,
  },
  cardTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
  },
  refText: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '800',
    letterSpacing: 1,
  },
  customerName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginTop: 2,
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.border,
    marginVertical: 10,
  },
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
  detailLabel: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
  },
  detailValue: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '600',
  },
  phoneButton: {
    marginTop: 10,
    backgroundColor: COLORS.surfaceHighlight,
    padding: 8,
    borderRadius: RADIUS.sm,
    alignItems: 'center',
  },
  phoneText: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
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
