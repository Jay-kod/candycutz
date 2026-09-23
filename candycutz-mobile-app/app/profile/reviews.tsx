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
import { servicesApi, testimonialsApi } from '../../src/api/client';
import { Service, Testimonial } from '../../src/api/types';
import { Card } from '../../src/components/common/Card';
import { FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAppTheme } from '../../src/hooks/useAppTheme';
import { useAuthStore } from '../../src/store/authStore';

export default function ReviewsScreen() {
  const router = useRouter();
  const { colors } = useAppTheme();
  const styles = createStyles(colors);

  const { isAuthenticated } = useAuthStore();
  const [reviews, setReviews] = useState<Testimonial[]>([]);
  const [services, setServices] = useState<Service[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [selectedServiceId, setSelectedServiceId] = useState<number | 'all'>('all');

  // Submit review modal
  const [showModal, setShowModal] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [formServiceId, setFormServiceId] = useState<number | null>(null);
  const [formRating, setFormRating] = useState(5);
  const [formReview, setFormReview] = useState('');

  const loadData = useCallback(async () => {
    try {
      setLoading(true);
      const [reviewsData, servicesData] = await Promise.all([
        isAuthenticated ? testimonialsApi.getMyReviews() : testimonialsApi.getAll(),
        servicesApi.getAll(),
      ]);
      setReviews(Array.isArray(reviewsData) ? reviewsData : []);
      setServices(Array.isArray(servicesData) ? servicesData : []);
    } catch {
      // Keep existing list on failure
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [isAuthenticated]);

  useEffect(() => {
    loadData();
  }, [loadData]);

  const handleRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const handleSubmitReview = async () => {
    if (!formReview.trim()) {
      Alert.alert('Review Required', 'Please share details of your grooming experience.');
      return;
    }

    try {
      setSubmitting(true);
      await testimonialsApi.submit({
        rating: formRating,
        review: formReview.trim(),
        service_id: formServiceId || undefined,
      });

      Alert.alert(
        'Review Submitted',
        'Thank you! Your feedback has been submitted for moderation and will appear once approved.'
      );
      setShowModal(false);
      setFormReview('');
      setFormRating(5);
      setFormServiceId(null);
      loadData();
    } catch (err: any) {
      const msg = err?.response?.data?.message || err?.message || 'Failed to submit review.';
      Alert.alert('Error', msg);
    } finally {
      setSubmitting(false);
    }
  };

  const filteredReviews = reviews.filter((item) => {
    if (selectedServiceId === 'all') return true;
    return item.service_id === selectedServiceId || item.service?.id === selectedServiceId;
  });

  return (
    <SafeAreaView style={styles.safeArea}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Client Reviews</Text>
        <TouchableOpacity onPress={() => setShowModal(true)} style={styles.addBtn}>
          <Text style={styles.addBtnText}>+ Write</Text>
        </TouchableOpacity>
      </View>

      {/* Service Filter Pills */}
      {services.length > 0 && (
        <View style={styles.filterContainer}>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.filterScroll}>
            <TouchableOpacity
              style={[styles.filterPill, selectedServiceId === 'all' && styles.filterPillActive]}
              onPress={() => setSelectedServiceId('all')}
            >
              <Text style={[styles.filterPillText, selectedServiceId === 'all' && styles.filterPillTextActive]}>
                All Services
              </Text>
            </TouchableOpacity>
            {services.map((s) => (
              <TouchableOpacity
                key={s.id}
                style={[styles.filterPill, selectedServiceId === s.id && styles.filterPillActive]}
                onPress={() => setSelectedServiceId(s.id)}
              >
                <Text style={[styles.filterPillText, selectedServiceId === s.id && styles.filterPillTextActive]}>
                  {s.name}
                </Text>
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
      )}

      {loading && !refreshing ? (
        <View style={styles.centered}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading reviews…</Text>
        </View>
      ) : (
        <FlatList
          data={filteredReviews}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.listContent}
          refreshing={refreshing}
          onRefresh={handleRefresh}
          ListHeaderComponent={
            <Card style={styles.summaryCard} elevated>
              <Text style={styles.summaryTitle}>Verified Client Feedback</Text>
              <Text style={styles.summarySubtitle}>
                Reviews are tagged to specific services to maintain world-class grooming excellence.
              </Text>
            </Card>
          }
          ListEmptyComponent={
            <Card style={styles.emptyCard} elevated>
              <Text style={styles.emptyIcon}>⭐</Text>
              <Text style={styles.emptyTitle}>No Reviews Found</Text>
              <Text style={styles.emptyDesc}>
                Tap "+ Write" to share your haircut or grooming experience.
              </Text>
            </Card>
          }
          renderItem={({ item }) => (
            <Card style={styles.card} elevated>
              <View style={styles.cardTop}>
                <View style={styles.customerInfo}>
                  <Text style={styles.customerName}>{item.customer_name || 'Anonymous Client'}</Text>
                  <View style={styles.badgesRow}>
                    {item.service?.name ? (
                      <View style={styles.serviceTag}>
                        <Text style={styles.serviceTagText}>✂️ {item.service.name}</Text>
                      </View>
                    ) : null}

                    {item.barber?.name ? (
                      <Text style={styles.barberTag}>with {item.barber.name}</Text>
                    ) : null}
                  </View>
                </View>

                {/* Star rating */}
                <View style={styles.starsRow}>
                  {Array.from({ length: 5 }).map((_, i) => (
                    <Text
                      key={i}
                      style={[
                        styles.star,
                        { color: i < item.rating ? colors.primary : colors.border },
                      ]}
                    >
                      ★
                    </Text>
                  ))}
                </View>
              </View>

              <Text style={styles.comment}>"{item.review || item.comment}"</Text>

              <View style={styles.cardFooter}>
                <View
                  style={[
                    styles.statusBadge,
                    item.is_approved ? styles.statusApproved : styles.statusPending,
                  ]}
                >
                  <Text
                    style={[
                      styles.statusText,
                      { color: item.is_approved ? colors.success : (colors.warning ?? '#F59E0B') },
                    ]}
                  >
                    {item.is_approved ? 'Verified' : 'Under Review'}
                  </Text>
                </View>

                {item.created_at ? (
                  <Text style={styles.date}>
                    {new Date(item.created_at).toLocaleDateString(undefined, {
                      month: 'short',
                      day: 'numeric',
                      year: 'numeric',
                    })}
                  </Text>
                ) : null}
              </View>
            </Card>
          )}
        />
      )}

      {/* Write Review Modal */}
      <Modal visible={showModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContent, { backgroundColor: colors.surface }]}>
            <ScrollView showsVerticalScrollIndicator={false}>
              <Text style={styles.modalTitle}>Leave Feedback</Text>
              <Text style={styles.modalSubtitle}>
                Rate your service and help other clients choose their next cut.
              </Text>

              {/* Service Selection */}
              <Text style={styles.inputLabel}>Tagged Service</Text>
              <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.serviceSelectScroll}>
                {services.map((s) => (
                  <TouchableOpacity
                    key={s.id}
                    style={[
                      styles.serviceSelectChip,
                      formServiceId === s.id && styles.serviceSelectChipActive,
                    ]}
                    onPress={() => setFormServiceId(formServiceId === s.id ? null : s.id)}
                  >
                    <Text
                      style={[
                        styles.serviceSelectText,
                        formServiceId === s.id && styles.serviceSelectTextActive,
                      ]}
                    >
                      {s.name}
                    </Text>
                  </TouchableOpacity>
                ))}
              </ScrollView>

              {/* Star Rating Selection */}
              <Text style={styles.inputLabel}>Rating</Text>
              <View style={styles.starPickerRow}>
                {[1, 2, 3, 4, 5].map((star) => (
                  <TouchableOpacity key={star} onPress={() => setFormRating(star)} style={styles.starBtn}>
                    <Text
                      style={[
                        styles.pickerStar,
                        { color: star <= formRating ? colors.primary : colors.border },
                      ]}
                    >
                      ★
                    </Text>
                  </TouchableOpacity>
                ))}
                <Text style={styles.ratingWord}>
                  {['Poor', 'Fair', 'Good', 'Great', 'Exceptional'][formRating - 1]}
                </Text>
              </View>

              {/* Review Text */}
              <Text style={styles.inputLabel}>Your Feedback *</Text>
              <TextInput
                style={[styles.input, styles.reviewInput]}
                placeholderTextColor={colors.textMuted}
                placeholder="How was the precision, wait time, and styling quality?"
                value={formReview}
                onChangeText={setFormReview}
                multiline
              />

              {/* Submit */}
              <TouchableOpacity
                style={[styles.submitBtn, submitting && styles.submitBtnDisabled]}
                onPress={handleSubmitReview}
                disabled={submitting}
              >
                {submitting ? (
                  <ActivityIndicator color="#fff" size="small" />
                ) : (
                  <Text style={styles.submitBtnText}>Submit Review</Text>
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
    filterContainer: {
      paddingVertical: 10,
      borderBottomWidth: 1,
      borderBottomColor: colors.border,
    },
    filterScroll: {
      paddingHorizontal: SPACING.md,
      gap: 8,
    },
    filterPill: {
      paddingHorizontal: 14,
      paddingVertical: 6,
      borderRadius: RADIUS.full,
      backgroundColor: colors.surfaceHighlight,
      borderWidth: 1,
      borderColor: colors.border,
    },
    filterPillActive: {
      backgroundColor: colors.primary,
      borderColor: colors.primary,
    },
    filterPillText: {
      color: colors.textSecondary,
      fontSize: FONTS.sizes.xs,
      fontWeight: '600',
    },
    filterPillTextActive: {
      color: '#fff',
      fontWeight: '800',
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
    listContent: {
      padding: SPACING.md,
      gap: SPACING.md,
    },
    summaryCard: {
      padding: SPACING.md,
      backgroundColor: colors.surfaceElevated,
      borderWidth: 1.5,
      borderColor: colors.primary,
      marginBottom: SPACING.sm,
    },
    summaryTitle: {
      color: colors.primary,
      fontSize: FONTS.sizes.md,
      fontWeight: '700',
      marginBottom: 4,
    },
    summarySubtitle: {
      color: colors.textSecondary,
      fontSize: FONTS.sizes.xs,
      lineHeight: 16,
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
    card: {
      padding: SPACING.md,
    },
    cardTop: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'flex-start',
      marginBottom: 10,
    },
    customerInfo: {
      flex: 1,
      marginRight: 8,
    },
    customerName: {
      color: colors.textPrimary,
      fontSize: FONTS.sizes.sm,
      fontWeight: '700',
      marginBottom: 4,
    },
    badgesRow: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 6,
      flexWrap: 'wrap',
    },
    serviceTag: {
      backgroundColor: colors.primaryLight,
      paddingHorizontal: 7,
      paddingVertical: 2,
      borderRadius: RADIUS.sm,
    },
    serviceTagText: {
      color: colors.primary,
      fontSize: 10,
      fontWeight: '800',
    },
    barberTag: {
      color: colors.textMuted,
      fontSize: 11,
    },
    starsRow: {
      flexDirection: 'row',
      gap: 2,
    },
    star: {
      fontSize: 14,
    },
    comment: {
      color: colors.textSecondary,
      fontSize: FONTS.sizes.xs,
      lineHeight: 18,
      fontStyle: 'italic',
      marginBottom: 10,
    },
    cardFooter: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'center',
      borderTopWidth: 1,
      borderTopColor: colors.border,
      paddingTop: 8,
    },
    statusBadge: {
      paddingHorizontal: 8,
      paddingVertical: 2,
      borderRadius: RADIUS.sm,
    },
    statusApproved: {
      backgroundColor: 'rgba(22, 163, 74, 0.12)',
    },
    statusPending: {
      backgroundColor: 'rgba(245, 158, 11, 0.12)',
    },
    statusText: {
      fontSize: 10,
      fontWeight: '700',
    },
    date: {
      color: colors.textMuted,
      fontSize: 10,
    },
    // Modal
    modalOverlay: {
      flex: 1,
      backgroundColor: 'rgba(0,0,0,0.65)',
      justifyContent: 'flex-end',
    },
    modalContent: {
      borderTopLeftRadius: 24,
      borderTopRightRadius: 24,
      padding: SPACING.lg,
      maxHeight: '90%',
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
      marginBottom: 6,
      marginTop: 10,
    },
    serviceSelectScroll: {
      marginBottom: 4,
    },
    serviceSelectChip: {
      paddingHorizontal: 12,
      paddingVertical: 6,
      borderRadius: RADIUS.full,
      backgroundColor: colors.surfaceHighlight,
      borderWidth: 1,
      borderColor: colors.border,
      marginRight: 8,
    },
    serviceSelectChipActive: {
      backgroundColor: colors.primary,
      borderColor: colors.primary,
    },
    serviceSelectText: {
      color: colors.textSecondary,
      fontSize: 11,
      fontWeight: '600',
    },
    serviceSelectTextActive: {
      color: '#fff',
      fontWeight: '700',
    },
    starPickerRow: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 8,
      marginVertical: 4,
    },
    starBtn: {
      padding: 4,
    },
    pickerStar: {
      fontSize: 28,
    },
    ratingWord: {
      color: colors.primary,
      fontSize: FONTS.sizes.xs,
      fontWeight: '800',
      marginLeft: 8,
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
    reviewInput: {
      minHeight: 100,
      textAlignVertical: 'top',
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
      marginTop: 6,
    },
    cancelBtnText: {
      color: colors.textMuted,
      fontSize: FONTS.sizes.sm,
      fontWeight: '600',
    },
  });
}
