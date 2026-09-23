import React, { useCallback, useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Image,
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
import * as ImagePicker from 'expo-image-picker';
import { galleryApi } from '../../src/api/client';
import { GalleryItem } from '../../src/api/types';
import { Card } from '../../src/components/common/Card';
import { FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAppTheme } from '../../src/hooks/useAppTheme';
import { useAuthStore } from '../../src/store/authStore';

const CATEGORIES = [
  { key: 'all', label: 'All Cuts' },
  { key: 'haircut', label: 'Haircuts' },
  { key: 'beard', label: 'Beards' },
  { key: 'combo', label: 'Combos' },
  { key: 'before_after', label: 'Transformations' },
];

export default function GalleryScreen() {
  const router = useRouter();
  const { colors } = useAppTheme();
  const styles = createStyles(colors);

  const { isBarber, user, barber } = useAuthStore();
  const [items, setItems] = useState<GalleryItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [activeCategory, setActiveCategory] = useState('all');

  // Upload modal state
  const [showModal, setShowModal] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [formTitle, setFormTitle] = useState('');
  const [formCategory, setFormCategory] = useState('haircut');
  const [formDesc, setFormDesc] = useState('');
  const [selectedImageUri, setSelectedImageUri] = useState<string | null>(null);

  const fetchGallery = useCallback(async () => {
    try {
      setLoading(true);
      const data = await galleryApi.getAll();
      setItems(Array.isArray(data) ? data : []);
    } catch {
      // Keep existing list on failure
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    fetchGallery();
  }, [fetchGallery]);

  const handleRefresh = () => {
    setRefreshing(true);
    fetchGallery();
  };

  const handlePickImage = async () => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission Denied', 'Camera roll access is needed to upload cut photos.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'],
      allowsEditing: true,
      aspect: [4, 3],
      quality: 0.85,
    });

    if (!result.canceled && result.assets && result.assets[0]?.uri) {
      setSelectedImageUri(result.assets[0].uri);
    }
  };

  const handleSubmitPhoto = async () => {
    if (!selectedImageUri) {
      Alert.alert('Image Required', 'Please select a cut photo from your gallery.');
      return;
    }
    if (!formTitle.trim()) {
      Alert.alert('Title Required', 'Please enter a title for this haircut/style.');
      return;
    }

    try {
      setSubmitting(true);
      await galleryApi.upload({
        title: formTitle.trim(),
        category: formCategory,
        description: formDesc.trim() || undefined,
        imageUri: selectedImageUri,
      });

      Alert.alert('Success', 'Cut photo published to the portfolio gallery!');
      setShowModal(false);
      setFormTitle('');
      setFormCategory('haircut');
      setFormDesc('');
      setSelectedImageUri(null);
      fetchGallery();
    } catch (err: any) {
      const msg = err?.response?.data?.message || err?.message || 'Failed to upload photo.';
      Alert.alert('Error', msg);
    } finally {
      setSubmitting(false);
    }
  };

  const handleDeleteItem = (item: GalleryItem) => {
    Alert.alert(
      'Delete Photo',
      `Are you sure you want to remove "${item.title}" from your portfolio?`,
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Delete',
          style: 'destructive',
          onPress: async () => {
            try {
              await galleryApi.delete(item.id);
              setItems((prev) => prev.filter((i) => i.id !== item.id));
            } catch {
              Alert.alert('Error', 'Failed to delete gallery item.');
            }
          },
        },
      ]
    );
  };

  const filteredItems = items.filter((item) => {
    if (activeCategory === 'all') return true;
    return item.category?.toLowerCase() === activeCategory.toLowerCase();
  });

  return (
    <SafeAreaView style={styles.safeArea}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Portfolio Gallery</Text>
        {isBarber || user?.role === 'admin' ? (
          <TouchableOpacity onPress={() => setShowModal(true)} style={styles.addBtn}>
            <Text style={styles.addBtnText}>+ Add Cut</Text>
          </TouchableOpacity>
        ) : (
          <View style={styles.headerRight} />
        )}
      </View>

      {/* Category Filter Pills */}
      <View style={styles.filterContainer}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.filterScroll}>
          {CATEGORIES.map((cat) => (
            <TouchableOpacity
              key={cat.key}
              style={[styles.filterPill, activeCategory === cat.key && styles.filterPillActive]}
              onPress={() => setActiveCategory(cat.key)}
            >
              <Text style={[styles.filterPillText, activeCategory === cat.key && styles.filterPillTextActive]}>
                {cat.label}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>

      {loading && !refreshing ? (
        <View style={styles.centered}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading portfolio cuts…</Text>
        </View>
      ) : (
        <FlatList
          data={filteredItems}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.content}
          refreshing={refreshing}
          onRefresh={handleRefresh}
          ListHeaderComponent={
            <Card style={styles.headerCard} elevated>
              <Text style={styles.headerCardTitle}>Showcase Gallery</Text>
              <Text style={styles.headerCardDesc}>
                High-resolution client cuts and grooming styles synchronized in real time between web and mobile apps.
              </Text>
            </Card>
          }
          ListEmptyComponent={
            <Card style={styles.emptyCard} elevated>
              <Text style={styles.emptyIcon}>✂️</Text>
              <Text style={styles.emptyTitle}>No Cuts Found</Text>
              <Text style={styles.emptyDesc}>
                {isBarber
                  ? 'Tap "+ Add Cut" to upload your latest work to the gallery.'
                  : 'Check back soon for fresh cut styles from our master barbers.'}
              </Text>
            </Card>
          }
          renderItem={({ item }) => {
            const isOwner = barber?.id && item.barber?.id === barber.id;
            return (
              <Card style={styles.card} elevated>
                {item.image_url ? (
                  <View style={styles.imageContainer}>
                    <Image source={{ uri: item.image_url }} style={styles.image} resizeMode="cover" />
                    <View style={styles.tagBadge}>
                      <Text style={styles.tagText}>{(item.category || 'Cut').toUpperCase()}</Text>
                    </View>
                  </View>
                ) : (
                  <View style={styles.imagePlaceholder}>
                    <Text style={styles.imageIcon}>✂️</Text>
                    <View style={styles.tagBadge}>
                      <Text style={styles.tagText}>{(item.category || 'Cut').toUpperCase()}</Text>
                    </View>
                  </View>
                )}

                <View style={styles.cardInfo}>
                  <View style={styles.titleRow}>
                    <Text style={styles.itemTitle}>{item.title}</Text>
                    {isOwner && (
                      <TouchableOpacity onPress={() => handleDeleteItem(item)} style={styles.deleteBtn}>
                        <Text style={styles.deleteBtnText}>✕</Text>
                      </TouchableOpacity>
                    )}
                  </View>

                  {item.description ? (
                    <Text style={styles.itemDesc} numberOfLines={2}>
                      {item.description}
                    </Text>
                  ) : null}

                  <View style={styles.metaRow}>
                    <Text style={styles.barberName}>
                      By {item.barber?.name || 'CandyCutz Barber'}
                    </Text>
                    {item.is_featured && (
                      <View style={styles.featuredBadge}>
                        <Text style={styles.featuredText}>★ Featured</Text>
                      </View>
                    )}
                  </View>
                </View>
              </Card>
            );
          }}
        />
      )}

      {/* Upload Modal */}
      <Modal visible={showModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContent, { backgroundColor: colors.surface }]}>
            <ScrollView showsVerticalScrollIndicator={false}>
              <Text style={styles.modalTitle}>Add Portfolio Cut</Text>
              <Text style={styles.modalSubtitle}>
                Showcase your cut on both the customer mobile app and website gallery.
              </Text>

              {/* Image Picker */}
              <TouchableOpacity style={styles.imagePickerBtn} onPress={handlePickImage}>
                {selectedImageUri ? (
                  <Image source={{ uri: selectedImageUri }} style={styles.pickerPreview} />
                ) : (
                  <View style={styles.pickerPlaceholder}>
                    <Text style={styles.pickerIcon}>📷</Text>
                    <Text style={styles.pickerLabel}>Tap to select cut photo</Text>
                    <Text style={styles.pickerSub}>JPG, PNG or WEBP (Max 5MB)</Text>
                  </View>
                )}
              </TouchableOpacity>

              {/* Title */}
              <Text style={styles.inputLabel}>Cut Title *</Text>
              <TextInput
                style={styles.input}
                placeholderTextColor={colors.textMuted}
                placeholder="e.g. Crisp Taper Fade & Texture"
                value={formTitle}
                onChangeText={setFormTitle}
              />

              {/* Category */}
              <Text style={styles.inputLabel}>Style Category</Text>
              <View style={styles.catRow}>
                {CATEGORIES.filter((c) => c.key !== 'all').map((c) => (
                  <TouchableOpacity
                    key={c.key}
                    style={[styles.catChip, formCategory === c.key && styles.catChipActive]}
                    onPress={() => setFormCategory(c.key)}
                  >
                    <Text style={[styles.catChipText, formCategory === c.key && styles.catChipTextActive]}>
                      {c.label}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>

              {/* Description */}
              <Text style={styles.inputLabel}>Description (Optional)</Text>
              <TextInput
                style={[styles.input, styles.inputMulti]}
                placeholderTextColor={colors.textMuted}
                placeholder="Details on the fade, styling products used, or notes…"
                value={formDesc}
                onChangeText={setFormDesc}
                multiline
              />

              {/* Submit */}
              <TouchableOpacity
                style={[styles.submitBtn, submitting && styles.submitBtnDisabled]}
                onPress={handleSubmitPhoto}
                disabled={submitting}
              >
                {submitting ? (
                  <ActivityIndicator color="#fff" size="small" />
                ) : (
                  <Text style={styles.submitBtnText}>Publish to Portfolio</Text>
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
    content: {
      padding: SPACING.md,
      gap: SPACING.md,
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
      overflow: 'hidden',
      padding: 0,
    },
    imageContainer: {
      height: 200,
      position: 'relative',
      backgroundColor: colors.surfaceHighlight,
    },
    image: {
      width: '100%',
      height: '100%',
    },
    imagePlaceholder: {
      height: 160,
      backgroundColor: colors.surfaceHighlight,
      alignItems: 'center',
      justifyContent: 'center',
      borderBottomWidth: 1,
      borderBottomColor: colors.border,
      position: 'relative',
    },
    imageIcon: {
      fontSize: 36,
    },
    tagBadge: {
      position: 'absolute',
      top: 10,
      right: 10,
      backgroundColor: 'rgba(0,0,0,0.7)',
      paddingHorizontal: 8,
      paddingVertical: 4,
      borderRadius: RADIUS.sm,
    },
    tagText: {
      color: colors.primary,
      fontSize: 10,
      fontWeight: '800',
    },
    cardInfo: {
      padding: SPACING.md,
    },
    titleRow: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: 4,
    },
    itemTitle: {
      color: colors.textPrimary,
      fontSize: FONTS.sizes.md,
      fontWeight: '700',
      flex: 1,
    },
    deleteBtn: {
      padding: 4,
      marginLeft: 8,
    },
    deleteBtnText: {
      color: colors.error ?? '#EF4444',
      fontSize: 14,
      fontWeight: '700',
    },
    itemDesc: {
      color: colors.textSecondary,
      fontSize: FONTS.sizes.xs,
      lineHeight: 16,
      marginBottom: 8,
    },
    metaRow: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginTop: 4,
    },
    barberName: {
      color: colors.primary,
      fontSize: FONTS.sizes.xs,
      fontWeight: '700',
    },
    featuredBadge: {
      backgroundColor: colors.primaryLight,
      paddingHorizontal: 6,
      paddingVertical: 2,
      borderRadius: RADIUS.sm,
    },
    featuredText: {
      color: colors.primary,
      fontSize: 10,
      fontWeight: '700',
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
    imagePickerBtn: {
      height: 160,
      borderRadius: RADIUS.md,
      overflow: 'hidden',
      borderWidth: 1.5,
      borderColor: colors.border,
      borderStyle: 'dashed',
      marginBottom: SPACING.md,
      backgroundColor: colors.surfaceHighlight,
    },
    pickerPreview: {
      width: '100%',
      height: '100%',
    },
    pickerPlaceholder: {
      flex: 1,
      alignItems: 'center',
      justifyContent: 'center',
      gap: 4,
    },
    pickerIcon: {
      fontSize: 32,
    },
    pickerLabel: {
      color: colors.textPrimary,
      fontSize: FONTS.sizes.sm,
      fontWeight: '700',
    },
    pickerSub: {
      color: colors.textMuted,
      fontSize: 10,
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
      minHeight: 70,
      textAlignVertical: 'top',
    },
    catRow: {
      flexDirection: 'row',
      flexWrap: 'wrap',
      gap: 8,
      marginBottom: 6,
    },
    catChip: {
      paddingHorizontal: 12,
      paddingVertical: 6,
      borderRadius: RADIUS.full,
      backgroundColor: colors.surfaceHighlight,
      borderWidth: 1,
      borderColor: colors.border,
    },
    catChipActive: {
      backgroundColor: colors.primary,
      borderColor: colors.primary,
    },
    catChipText: {
      color: colors.textSecondary,
      fontSize: 11,
      fontWeight: '600',
    },
    catChipTextActive: {
      color: '#fff',
      fontWeight: '700',
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
