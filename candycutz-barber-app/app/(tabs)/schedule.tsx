import React, { useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  Modal,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Switch,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { SafeAreaView } from 'react-native-safe-area-context';
import { staffScheduleApi } from '../../src/api/client';
import { Button } from '../../src/components/common/Button';
import { Card } from '../../src/components/common/Card';
import { COLORS, FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { BlockedPeriod, WeeklyScheduleDay } from '../../src/types';

const DEFAULT_DAYS: WeeklyScheduleDay[] = [
  { day_of_week: 1, day_name: 'Monday', is_working: true, start_time: '08:00', end_time: '20:00' },
  { day_of_week: 2, day_name: 'Tuesday', is_working: true, start_time: '08:00', end_time: '20:00' },
  { day_of_week: 3, day_name: 'Wednesday', is_working: true, start_time: '08:00', end_time: '20:00' },
  { day_of_week: 4, day_name: 'Thursday', is_working: true, start_time: '08:00', end_time: '20:00' },
  { day_of_week: 5, day_name: 'Friday', is_working: true, start_time: '08:00', end_time: '20:00' },
  { day_of_week: 6, day_name: 'Saturday', is_working: true, start_time: '08:00', end_time: '21:00' },
  { day_of_week: 0, day_name: 'Sunday', is_working: true, start_time: '10:00', end_time: '18:00' },
];

export default function BarberScheduleScreen() {
  const queryClient = useQueryClient();
  const [modalVisible, setModalVisible] = useState(false);

  // Block period form states
  const [blockDate, setBlockDate] = useState(new Date().toISOString().split('T')[0]);
  const [blockStart, setBlockStart] = useState('13:00');
  const [blockEnd, setBlockEnd] = useState('14:00');
  const [blockReason, setBlockReason] = useState('Lunch / Prayer Break');

  const {
    data: weeklyDays = DEFAULT_DAYS,
    isLoading: loadingSchedule,
    refetch: refetchSchedule,
  } = useQuery({
    queryKey: ['weeklySchedule'],
    queryFn: staffScheduleApi.getSchedule,
  });

  const {
    data: blockedPeriods = [],
    isLoading: loadingBlocked,
    refetch: refetchBlocked,
  } = useQuery({
    queryKey: ['blockedPeriods'],
    queryFn: staffScheduleApi.getBlockedPeriods,
  });

  const addBlockMutation = useMutation({
    mutationFn: staffScheduleApi.addBlockedPeriod,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['blockedPeriods'] });
      setModalVisible(false);
      Alert.alert('Time Blocked', 'Your schedule has been successfully updated.');
    },
    onError: (e: any) => {
      Alert.alert('Error', e.response?.data?.message || 'Failed to block time.');
    },
  });

  const deleteBlockMutation = useMutation({
    mutationFn: staffScheduleApi.deleteBlockedPeriod,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['blockedPeriods'] });
    },
  });

  const handleAddBlock = () => {
    if (!blockDate || !blockStart || !blockEnd) return;
    addBlockMutation.mutate({
      start_datetime: `${blockDate} ${blockStart}:00`,
      end_datetime: `${blockDate} ${blockEnd}:00`,
      reason: blockReason,
    });
  };

  return (
    <SafeAreaView style={styles.safeArea} edges={['top']}>
      <ScrollView
        contentContainerStyle={styles.container}
        refreshControl={
          <RefreshControl
            refreshing={loadingSchedule || loadingBlocked}
            onRefresh={() => {
              refetchSchedule();
              refetchBlocked();
            }}
            tintColor={COLORS.primary}
          />
        }
      >
        <View style={styles.header}>
          <View>
            <Text style={styles.title}>Weekly Working Hours</Text>
            <Text style={styles.subtitle}>Manage your regular shift and availability</Text>
          </View>
          <Button
            title="+ Block Time"
            size="sm"
            variant="outline"
            onPress={() => setModalVisible(true)}
          />
        </View>

        {/* Weekly Shifts */}
        <Card style={styles.daysCard} elevated>
          {(weeklyDays.length ? weeklyDays : DEFAULT_DAYS).map((day) => (
            <View key={day.day_of_week} style={styles.dayRow}>
              <View style={styles.dayInfo}>
                <Text style={styles.dayName}>{day.day_name}</Text>
                <Text style={styles.dayHours}>
                  {day.is_working ? `${day.start_time} - ${day.end_time}` : 'Day Off'}
                </Text>
              </View>
              <Switch
                value={day.is_working}
                trackColor={{ false: COLORS.border, true: COLORS.primary }}
                thumbColor={day.is_working ? '#0A0A0C' : '#9CA3AF'}
              />
            </View>
          ))}
        </Card>

        {/* Blocked Periods Section */}
        <Text style={styles.sectionTitle}>Active Blocked Hours / Time-Off</Text>
        <Text style={styles.sectionDesc}>
          Time blocks prevent customers from booking slots during breaks, prayers, or off-site visits.
        </Text>

        {blockedPeriods.length === 0 ? (
          <Card style={styles.emptyCard}>
            <Text style={styles.emptyText}>No blocked hours currently set.</Text>
          </Card>
        ) : (
          blockedPeriods.map((item: BlockedPeriod) => (
            <Card key={item.id} style={styles.blockedCard} elevated>
              <View style={styles.blockedHeader}>
                <Text style={styles.blockedReason}>{item.reason || 'Blocked Out of Shop'}</Text>
                <TouchableOpacity
                  onPress={() => deleteBlockMutation.mutate(item.id)}
                  style={styles.deleteBtn}
                >
                  <Text style={styles.deleteText}>Delete</Text>
                </TouchableOpacity>
              </View>
              <Text style={styles.blockedTime}>
                {item.start_datetime} &rarr; {item.end_datetime}
              </Text>
            </Card>
          ))
        )}

        {/* Add Block Modal */}
        <Modal
          visible={modalVisible}
          animationType="slide"
          transparent
          onRequestClose={() => setModalVisible(false)}
        >
          <View style={styles.modalOverlay}>
            <View style={styles.modalContent}>
              <Text style={styles.modalTitle}>Block Time Out</Text>
              <Text style={styles.modalSubtitle}>
                Add time during your shift when you are unavailable for appointments.
              </Text>

              <Text style={styles.inputLabel}>Date (YYYY-MM-DD)</Text>
              <TextInput
                style={styles.modalInput}
                value={blockDate}
                onChangeText={setBlockDate}
                placeholder="2026-09-06"
                placeholderTextColor={COLORS.textMuted}
              />

              <View style={styles.timeRow}>
                <View style={{ flex: 1, marginRight: 8 }}>
                  <Text style={styles.inputLabel}>From (HH:MM)</Text>
                  <TextInput
                    style={styles.modalInput}
                    value={blockStart}
                    onChangeText={setBlockStart}
                    placeholder="13:00"
                    placeholderTextColor={COLORS.textMuted}
                  />
                </View>
                <View style={{ flex: 1 }}>
                  <Text style={styles.inputLabel}>To (HH:MM)</Text>
                  <TextInput
                    style={styles.modalInput}
                    value={blockEnd}
                    onChangeText={setBlockEnd}
                    placeholder="14:00"
                    placeholderTextColor={COLORS.textMuted}
                  />
                </View>
              </View>

              <Text style={styles.inputLabel}>Reason</Text>
              <TextInput
                style={styles.modalInput}
                value={blockReason}
                onChangeText={setBlockReason}
                placeholder="Lunch, Prayer, Emergency"
                placeholderTextColor={COLORS.textMuted}
              />

              <View style={styles.modalActions}>
                <Button
                  title="Cancel"
                  variant="ghost"
                  onPress={() => setModalVisible(false)}
                  style={{ flex: 1 }}
                />
                <Button
                  title="Confirm Block"
                  onPress={handleAddBlock}
                  loading={addBlockMutation.isPending}
                  style={{ flex: 1 }}
                />
              </View>
            </View>
          </View>
        </Modal>
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
    padding: SPACING.md,
    paddingBottom: 40,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: SPACING.md,
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
  daysCard: {
    padding: 0,
    marginBottom: SPACING.lg,
  },
  dayRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 12,
    paddingHorizontal: SPACING.md,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border,
  },
  dayInfo: {
    flex: 1,
  },
  dayName: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  dayHours: {
    color: COLORS.primary,
    fontSize: FONTS.sizes.xs,
    marginTop: 2,
  },
  sectionTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '800',
    marginTop: SPACING.sm,
  },
  sectionDesc: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 16,
    marginTop: 4,
    marginBottom: SPACING.md,
  },
  emptyCard: {
    padding: 20,
    alignItems: 'center',
    borderStyle: 'dashed',
  },
  emptyText: {
    color: COLORS.textMuted,
    fontSize: FONTS.sizes.xs,
  },
  blockedCard: {
    padding: SPACING.md,
    marginBottom: 10,
    borderLeftWidth: 4,
    borderLeftColor: COLORS.warning,
  },
  blockedHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  blockedReason: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '700',
  },
  deleteBtn: {
    padding: 4,
  },
  deleteText: {
    color: COLORS.error,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  blockedTime: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    marginTop: 4,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.75)',
    justifyContent: 'center',
    padding: SPACING.lg,
  },
  modalContent: {
    backgroundColor: COLORS.surface,
    borderRadius: RADIUS.lg,
    padding: SPACING.lg,
    borderWidth: 1,
    borderColor: COLORS.border,
  },
  modalTitle: {
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
  },
  modalSubtitle: {
    color: COLORS.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 18,
    marginTop: 4,
    marginBottom: SPACING.md,
  },
  inputLabel: {
    color: COLORS.textSecondary,
    fontSize: 10,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  modalInput: {
    backgroundColor: COLORS.surfaceHighlight,
    borderWidth: 1,
    borderColor: COLORS.border,
    borderRadius: RADIUS.md,
    padding: 12,
    color: COLORS.textPrimary,
    fontSize: FONTS.sizes.sm,
    marginBottom: 12,
  },
  timeRow: {
    flexDirection: 'row',
  },
  modalActions: {
    flexDirection: 'row',
    gap: 12,
    marginTop: SPACING.md,
  },
});
