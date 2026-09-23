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
  Switch,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import { useRouter } from 'expo-router';
import * as ImagePicker from 'expo-image-picker';
import { blogApi } from '../../src/api/client';
import { BlogPost } from '../../src/api/types';
import { Card } from '../../src/components/common/Card';
import { FONTS, RADIUS, SPACING } from '../../src/constants/theme';
import { useAppTheme } from '../../src/hooks/useAppTheme';
import { useAuthStore } from '../../src/store/authStore';

export default function BlogScreen() {
  const router = useRouter();
  const { colors } = useAppTheme();
  const styles = createStyles(colors);

  const { isBarber, user } = useAuthStore();
  const [posts, setPosts] = useState<BlogPost[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [reactingId, setReactingId] = useState<number | null>(null);

  // Authoring modal state
  const [showModal, setShowModal] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [formTitle, setFormTitle] = useState('');
  const [formExcerpt, setFormExcerpt] = useState('');
  const [formContent, setFormContent] = useState('');
  const [isPublishImmediately, setIsPublishImmediately] = useState(true);
  const [selectedImageUri, setSelectedImageUri] = useState<string | null>(null);

  const fetchPosts = useCallback(async () => {
    try {
      setLoading(true);
      const data = await blogApi.getAll();
      setPosts(Array.isArray(data) ? data : []);
    } catch {
      // Keep existing list on failure
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    fetchPosts();
  }, [fetchPosts]);

  const handleRefresh = () => {
    setRefreshing(true);
    fetchPosts();
  };

  const handlePickCoverImage = async () => {
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission Denied', 'Camera roll access is needed to select an article cover image.');
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ['images'],
      allowsEditing: true,
      aspect: [16, 9],
      quality: 0.85,
    });

    if (!result.canceled && result.assets && result.assets[0]?.uri) {
      setSelectedImageUri(result.assets[0].uri);
    }
  };

  const handleSubmitArticle = async () => {
    if (!formTitle.trim()) {
      Alert.alert('Title Required', 'Please enter an article title.');
      return;
    }
    if (!formContent.trim()) {
      Alert.alert('Content Required', 'Please write some grooming content for the article.');
      return;
    }

    try {
      setSubmitting(true);
      await blogApi.create({
        title: formTitle.trim(),
        excerpt: formExcerpt.trim() || formContent.slice(0, 120),
        content: formContent.trim(),
        imageUri: selectedImageUri || undefined,
        status: isPublishImmediately ? 'published' : 'draft',
      });

      Alert.alert('Article Published', 'Your article is now live across web and mobile blog feeds!');
      setShowModal(false);
      setFormTitle('');
      setFormExcerpt('');
      setFormContent('');
      setSelectedImageUri(null);
      setIsPublishImmediately(true);
      fetchPosts();
    } catch (err: any) {
      const msg = err?.response?.data?.message || err?.message || 'Failed to publish article.';
      Alert.alert('Error', msg);
    } finally {
      setSubmitting(false);
    }
  };

  const handleReact = async (post: BlogPost) => {
    try {
      setReactingId(post.id);
      await blogApi.react(post.id, 'love');
      setPosts((prev) =>
        prev.map((p) => (p.id === post.id ? { ...p, loves_count: (p.loves_count || 0) + 1 } : p))
      );
    } catch {
      // Ignore reaction errors
    } finally {
      setReactingId(null);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()} style={styles.backBtn}>
          <Text style={styles.backArrow}>←</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Grooming Journal</Text>
        {isBarber || user?.role === 'admin' ? (
          <TouchableOpacity onPress={() => setShowModal(true)} style={styles.addBtn}>
            <Text style={styles.addBtnText}>+ Write</Text>
          </TouchableOpacity>
        ) : (
          <View style={styles.headerRight} />
        )}
      </View>

      {loading && !refreshing ? (
        <View style={styles.centered}>
          <ActivityIndicator size="large" color={colors.primary} />
          <Text style={styles.loadingText}>Loading articles…</Text>
        </View>
      ) : (
        <FlatList
          data={posts}
          keyExtractor={(item) => String(item.id)}
          contentContainerStyle={styles.content}
          refreshing={refreshing}
          onRefresh={handleRefresh}
          ListHeaderComponent={
            <Card style={styles.headerCard} elevated>
              <Text style={styles.headerCardTitle}>CandyCutz Journal</Text>
              <Text style={styles.headerCardDesc}>
                Expert grooming guides, styling trends, and maintenance secrets shared by our professional barbers.
              </Text>
            </Card>
          }
          ListEmptyComponent={
            <Card style={styles.emptyCard} elevated>
              <Text style={styles.emptyIcon}>📰</Text>
              <Text style={styles.emptyTitle}>No Articles Yet</Text>
              <Text style={styles.emptyDesc}>
                {isBarber
                  ? 'Tap "+ Write" to create your first grooming article!'
                  : 'Check back soon for expert grooming guides.'}
              </Text>
            </Card>
          }
          renderItem={({ item }) => (
            <Card style={styles.card} elevated>
              {item.featured_image_url ? (
                <View style={styles.coverContainer}>
                  <Image source={{ uri: item.featured_image_url }} style={styles.coverImage} resizeMode="cover" />
                </View>
              ) : null}

              <View style={styles.cardBody}>
                <Text style={styles.postTitle}>{item.title}</Text>
                <Text style={styles.postExcerpt} numberOfLines={3}>
                  {item.excerpt || item.content?.replace(/<[^>]*>?/gm, '')}
                </Text>

                <View style={styles.metaRow}>
                  <View style={styles.authorBadge}>
                    <Text style={styles.authorText}>
                      ✍️ {item.author?.name || item.author_display || 'CandyCutz'}
                    </Text>
                  </View>

                  <View style={styles.actionRow}>
                    <TouchableOpacity
                      style={styles.reactionBtn}
                      onPress={() => handleReact(item)}
                      disabled={reactingId === item.id}
                    >
                      <Text style={styles.reactionIcon}>❤️</Text>
                      <Text style={styles.reactionCount}>{item.loves_count || 0}</Text>
                    </TouchableOpacity>

                    {item.created_at ? (
                      <Text style={styles.dateText}>
                        {new Date(item.created_at).toLocaleDateString(undefined, {
                          month: 'short',
                          day: 'numeric',
                        })}
                      </Text>
                    ) : null}
                  </View>
                </View>
              </View>
            </Card>
          )}
        />
      )}

      {/* Authoring Modal */}
      <Modal visible={showModal} animationType="slide" transparent>
        <View style={styles.modalOverlay}>
          <View style={[styles.modalContent, { backgroundColor: colors.surface }]}>
            <ScrollView showsVerticalScrollIndicator={false}>
              <Text style={styles.modalTitle}>Write Grooming Article</Text>
              <Text style={styles.modalSubtitle}>
                Share your styling wisdom. Articles publish to both mobile and web feeds instantly.
              </Text>

              {/* Cover Image Picker */}
              <TouchableOpacity style={styles.coverPickerBtn} onPress={handlePickCoverImage}>
                {selectedImageUri ? (
                  <Image source={{ uri: selectedImageUri }} style={styles.coverPickerPreview} />
                ) : (
                  <View style={styles.pickerPlaceholder}>
                    <Text style={styles.pickerIcon}>🖼️</Text>
                    <Text style={styles.pickerLabel}>Select Cover Photo (Optional)</Text>
                  </View>
                )}
              </TouchableOpacity>

              {/* Title */}
              <Text style={styles.inputLabel}>Article Title *</Text>
              <TextInput
                style={styles.input}
                placeholderTextColor={colors.textMuted}
                placeholder="e.g. 5 Beard Care Tips for Dry Weather"
                value={formTitle}
                onChangeText={setFormTitle}
              />

              {/* Excerpt */}
              <Text style={styles.inputLabel}>Short Summary (Excerpt)</Text>
              <TextInput
                style={styles.input}
                placeholderTextColor={colors.textMuted}
                placeholder="Brief summary appearing on card previews…"
                value={formExcerpt}
                onChangeText={setFormExcerpt}
              />

              {/* Content */}
              <Text style={styles.inputLabel}>Article Content *</Text>
              <TextInput
                style={[styles.input, styles.contentInput]}
                placeholderTextColor={colors.textMuted}
                placeholder="Write your article content here…"
                value={formContent}
                onChangeText={setFormContent}
                multiline
              />

              {/* Publish toggle */}
              <View style={styles.toggleRow}>
                <View>
                  <Text style={styles.toggleLabel}>Publish Immediately</Text>
                  <Text style={styles.toggleSub}>Make visible to customers now</Text>
                </View>
                <Switch
                  value={isPublishImmediately}
                  onValueChange={setIsPublishImmediately}
                  trackColor={{ false: colors.border, true: colors.primary }}
                />
              </View>

              {/* Submit */}
              <TouchableOpacity
                style={[styles.submitBtn, submitting && styles.submitBtnDisabled]}
                onPress={handleSubmitArticle}
                disabled={submitting}
              >
                {submitting ? (
                  <ActivityIndicator color="#fff" size="small" />
                ) : (
                  <Text style={styles.submitBtnText}>Publish Article</Text>
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
    coverContainer: {
      height: 160,
      backgroundColor: colors.surfaceHighlight,
    },
    coverImage: {
      width: '100%',
      height: '100%',
    },
    cardBody: {
      padding: SPACING.md,
    },
    postTitle: {
      color: colors.textPrimary,
      fontSize: FONTS.sizes.md,
      fontWeight: '700',
      marginBottom: 6,
      lineHeight: 22,
    },
    postExcerpt: {
      color: colors.textSecondary,
      fontSize: FONTS.sizes.xs,
      lineHeight: 18,
      marginBottom: 12,
    },
    metaRow: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'center',
      borderTopWidth: 1,
      borderTopColor: colors.border,
      paddingTop: 10,
    },
    authorBadge: {
      backgroundColor: colors.surfaceHighlight,
      paddingHorizontal: 8,
      paddingVertical: 3,
      borderRadius: RADIUS.sm,
    },
    authorText: {
      color: colors.primary,
      fontSize: 11,
      fontWeight: '700',
    },
    actionRow: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 12,
    },
    reactionBtn: {
      flexDirection: 'row',
      alignItems: 'center',
      gap: 4,
      backgroundColor: colors.surfaceHighlight,
      paddingHorizontal: 8,
      paddingVertical: 3,
      borderRadius: RADIUS.sm,
    },
    reactionIcon: {
      fontSize: 12,
    },
    reactionCount: {
      color: colors.textPrimary,
      fontSize: 11,
      fontWeight: '700',
    },
    dateText: {
      color: colors.textMuted,
      fontSize: 11,
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
    coverPickerBtn: {
      height: 120,
      borderRadius: RADIUS.md,
      overflow: 'hidden',
      borderWidth: 1.5,
      borderColor: colors.border,
      borderStyle: 'dashed',
      marginBottom: SPACING.md,
      backgroundColor: colors.surfaceHighlight,
    },
    coverPickerPreview: {
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
      fontSize: 28,
    },
    pickerLabel: {
      color: colors.textSecondary,
      fontSize: FONTS.sizes.xs,
      fontWeight: '600',
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
    contentInput: {
      minHeight: 120,
      textAlignVertical: 'top',
    },
    toggleRow: {
      flexDirection: 'row',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginTop: SPACING.md,
      paddingVertical: 8,
    },
    toggleLabel: {
      color: colors.textPrimary,
      fontSize: FONTS.sizes.sm,
      fontWeight: '700',
    },
    toggleSub: {
      color: colors.textMuted,
      fontSize: 10,
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
