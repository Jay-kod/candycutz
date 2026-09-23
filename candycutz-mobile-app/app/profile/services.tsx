import React, { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Modal,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Card } from '../../src/components/common/Card';
import { FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAppTheme } from '../../src/hooks/useAppTheme';
import { useAuthStore } from '../../src/store/authStore';
import { servicesApi } from '../../src/api/client';
import type { Service } from '../../src/api/types';

export default function ServicesScreen() {
  const router = useRouter();
  const { user } = useAuthStore();
  const { colors } = useAppTheme();
  const styles = createStyles(colors);

  const isBarber = user?.role === 'barber';

  const [services, setServices] = useState<Service[]>([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  // Form state for new service request
  const [formName, setFormName] = useState('');
  const [formDesc, setFormDesc] = useState('');
  const [formPrice, setFormPrice] = useState('');
  const [formDuration, setFormDuration] = useState('');

  const fetchServices = useCallback(async () => {
    try {
      setLoading(true);
      const data = await servicesApi.getAll();
      setServices(Array.isArray(data) ? data : []);
    } catch {
      // Silent — keep empty
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchServices();
  }, [fetchServices]);

  const handleSubmitService = async () => {
    if (!formName.trim() || !formPrice.trim() || !formDuration.trim()) {
      Alert.alert('Missing Fields', 'Please fill in service name, price, and duration.');
      return;
    }

    try {
      setSubmitting(true);
      await servicesApi.create({
        name: formName.trim(),
        description: formDesc.trim() || undefined,
        price: parseFloat(formPrice),
        duration_minutes: parseInt(formDuration, 10),
      });
      Alert.alert('Request Submitted', 'Your service request has been sent for admin approval.');
      setShowModal(false);
      setFormName('');
      setFormDesc('');
      setFormPrice('');
      setFormDuration('');
      fetchServices();
    } catch (err: any) {
      const msg = err?.response?.data?.message || 'Failed to submit service request.';
      Alert.alert('Error', msg);
    } finally {
      setSubmitting(false);
    }
  };

  const getStatusLabel = (item: Service & { approval_status?: string }) => {
    if (item.approval_status === 'pending') return { label: 'Pending Approval', color: colors.warning ?? '#F59E0B' };
    if (item.approval_status === 'rejected') return { label: 'Rejected', color: colors.error ?? '#EF4444' };
    if (item.is_active) return { label: 'Active', color: colors.success };
    return { label: 'Inactive', color: colors.textMuted };
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>My Services</Text>
        {isBarber ? (
          <TouchableOpacity onPress={() => setShowModal(true)} style={styles.addBtn}>
            <Text style={styles.addBtnText}>+ New</Text>
          </TouchableOpacity>
        ) : (
          <View style={styles.headerRight} />
        )}
      </View>

      {loading ? (
        <View style={styles.centered}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading services…</Text>
        </View>
      ) : (
        <FlatList
          data={services}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.content}
          ListEmptyComponent={
            <Card style={styles.emptyCard} elevated>
              <Text style={styles.emptyIcon}>📋</Text>
              <Text style={styles.emptyTitle}>No Services Found</Text>
              <Text style={styles.emptyDesc}>
                {isBarber ? 'Tap "+ New" to request a service.' : 'Services will appear here once available.'}
              </Text>
            </Card>
          }
          ListHeaderComponent={
            <Card style={styles.headerCard} elevated>
              <Text style={styles.headerCardTitle}>
                {isBarber ? 'Active Barber Menu' : 'Available Services'}
              </Text>
              <Text style={styles.headerCardDesc}>
                {isBarber
                  ? 'Services assigned to your chair. Commission is computed based on your verified ticket completions.'
                  : 'Browse the services currently offered at CandyCutz.'}
              </Text>
            </Card>
          }
          renderItem={({ item }) => {
            const status = getStatusLabel(item as any);
            return (
              <Card style={styles.card} elevated>
                <View style={styles.cardTop}>
                  <View style={styles.tag}>
                    <Text style={styles.tagText}>{(item.category || 'SERVICE').toUpperCase()}</Text>
                  </View>
                  <View style={[styles.statusBadge, { backgroundColor: status.color + '20' }]}>
                    <Text style={[styles.statusText, { color: status.color }]}>{status.label}</Text>
                  </View>
                </View>

                <Text style={styles.serviceName}>{item.name}</Text>

                <View style={styles.cardFooter}>
                  <View>
                    <Text style={styles.footerLabel}>SERVICE FEE</Text>
                    <Text style={styles.footerVal}>₦{Number(item.price).toLocaleString()}</Text>
                  </View>
                  <View>
                    <Text style={styles.footerLabel}>DURATION</Text>
                    <Text style={styles.footerValSecondary}>{item.duration_minutes} mins</Text>
                  </View>
                </View>
              </Card>
            );
          }}
        />
      )}

      {/* Request New Service Modal (Barber only) */}
      <Modal visible={showModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContent, { backgroundColor: colors.surfaceElevated }]}>
            <ScrollView showsVerticalScrollIndicator={false}>
              <Text style={styles.modalTitle}>Request New Service</Text>
              <Text style={styles.modalSubtitle}>This will be submitted for admin approval.</Text>

              <Text style={styles.inputLabel}>Service Name *</Text>
              <TextInput
                style={styles.input}
                placeholderTextColor={colors.textMuted}
                placeholder="e.g. Luxury Beard Sculpt"
                value={formName}
                onChangeText={setFormName}
              />

              <Text style={styles.inputLabel}>Description</Text>
              <TextInput
                style={[styles.input, styles.inputMulti]}
                placeholderTextColor={colors.textMuted}
                placeholder="Describe the service…"
                value={formDesc}
                onChangeText={setFormDesc}
                multiline
              />

              <View style={styles.formRow}>
                <View style={styles.formHalf}>
                  <Text style={styles.inputLabel}>Price (₦) *</Text>
                  <TextInput
                    style={styles.input}
                    placeholderTextColor={colors.textMuted}
                    placeholder="5000"
                    value={formPrice}
                    onChangeText={setFormPrice}
                    keyboardType="numeric"
                  />
                </View>
                <View style={styles.formHalf}>
                  <Text style={styles.inputLabel}>Duration (mins) *</Text>
                  <TextInput
                    style={styles.input}
                    placeholderTextColor={colors.textMuted}
                    placeholder="30"
                    value={formDuration}
                    onChangeText={setFormDuration}
                    keyboardType="numeric"
                  />
                </View>
              </View>

              <TouchableOpacity
                style={[styles.submitBtn, submitting && styles.submitBtnDisabled]}
                onPress={handleSubmitService}
                disabled={submitting}
              >
                {submitting ? (
                  <ActivityIndicator color="#fff" size="small" />
                ) : (
                  <Text style={styles.submitBtnText}>Submit Request</Text>
                )}
              </TouchableOpacity>

              <TouchableOpacity style={styles.cancelBtn} onPress={() => setShowModal(false)}>
                <Text style={styles.cancelBtnText}>Cancel</Text>
              </TouchableOpacity>
            </ScrollView>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

function createStyles(colors: ReturnType<typeof useAppTheme>['colors']) {
  return StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: colors.background,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.sm,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  backBtn: {
    padding: SPACING.xs,
  },
  backArrow: {
    color: colors.textPrimary,
    fontSize: 22,
    fontWeight: '700',
  },
  headerTitle: {
    color: colors.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '700',
  },
  headerRight: {
    width: 32,
  },
  addBtn: {
    backgroundColor: colors.primary,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: RADIUS.sm,
  },
  addBtnText: {
    color: '#fff',
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
  },
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 12,
  },
  loadingText: {
    color: colors.textMuted,
    fontSize: FONTS.sizes.sm,
  },
  content: {
    padding: SPACING.md,
    gap: SPACING.md,
  },
  emptyCard: {
    padding: SPACING.xl,
    alignItems: 'center',
  },
  emptyIcon: {
    fontSize: 36,
    marginBottom: 8,
  },
  emptyTitle: {
    color: colors.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginBottom: 4,
  },
  emptyDesc: {
    color: colors.textSecondary,
    fontSize: FONTS.sizes.xs,
    textAlign: 'center',
  },
  headerCard: {
    padding: SPACING.md,
    backgroundColor: colors.surfaceElevated,
    borderWidth: 1.5,
    borderColor: colors.primary,
    marginBottom: SPACING.sm,
  },
  headerCardTitle: {
    color: colors.primary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginBottom: 4,
  },
  headerCardDesc: {
    color: colors.textSecondary,
    fontSize: FONTS.sizes.xs,
    lineHeight: 16,
  },
  card: {
    padding: SPACING.md,
  },
  cardTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  tag: {
    backgroundColor: colors.primaryLight,
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: RADIUS.sm,
  },
  tagText: {
    color: colors.primary,
    fontSize: 9,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
  statusBadge: {
    paddingHorizontal: 8,
    paddingVertical: 3,
    borderRadius: RADIUS.sm,
  },
  statusText: {
    fontSize: 10,
    fontWeight: '700',
  },
  serviceName: {
    color: colors.textPrimary,
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
    marginBottom: 12,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: colors.border,
    paddingTop: 10,
  },
  footerLabel: {
    color: colors.textMuted,
    fontSize: 9,
    fontWeight: '700',
  },
  footerVal: {
    color: colors.primary,
    fontSize: FONTS.sizes.md,
    fontWeight: '800',
    marginTop: 2,
  },
  footerValSecondary: {
    color: colors.textSecondary,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
    marginTop: 2,
  },
  // Modal styles
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    padding: SPACING.lg,
    maxHeight: '85%',
  },
  modalTitle: {
    color: colors.textPrimary,
    fontSize: FONTS.sizes.lg,
    fontWeight: '800',
    marginBottom: 4,
  },
  modalSubtitle: {
    color: colors.textSecondary,
    fontSize: FONTS.sizes.xs,
    marginBottom: SPACING.md,
  },
  inputLabel: {
    color: colors.textSecondary,
    fontSize: FONTS.sizes.xs,
    fontWeight: '700',
    marginBottom: 4,
    marginTop: 8,
  },
  input: {
    backgroundColor: colors.surfaceHighlight,
    color: colors.textPrimary,
    borderRadius: RADIUS.md,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: FONTS.sizes.sm,
    borderWidth: 1,
    borderColor: colors.border,
  },
  inputMulti: {
    minHeight: 80,
    textAlignVertical: 'top',
  },
  formRow: {
    flexDirection: 'row',
    gap: 12,
  },
  formHalf: {
    flex: 1,
  },
  submitBtn: {
    backgroundColor: colors.primary,
    borderRadius: RADIUS.md,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: SPACING.lg,
  },
  submitBtnDisabled: {
    opacity: 0.6,
  },
  submitBtnText: {
    color: '#fff',
    fontSize: FONTS.sizes.md,
    fontWeight: '700',
  },
  cancelBtn: {
    alignItems: 'center',
    paddingVertical: 12,
    marginTop: 8,
  },
  cancelBtnText: {
    color: colors.textMuted,
    fontSize: FONTS.sizes.sm,
    fontWeight: '600',
  },
  });
}
