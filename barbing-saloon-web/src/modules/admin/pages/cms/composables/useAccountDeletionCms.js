import { ref, computed } from 'vue';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';
import { getStorageUrl } from '@/core/utils/url';
import { DEFAULT_ACCOUNT_DELETION_CONTENT } from './accountDeletionConstants';

export function useAccountDeletionCms() {
  const toast = useToast();
  const saving = ref(false);
  const loading = ref(false);
  const lastSaved = ref(null);
  const initialSnapshot = ref(null);
  const activeTab = ref('all');

  const content = ref({ ...DEFAULT_ACCOUNT_DELETION_CONTENT });

  const heroImage = ref('');
  const heroOpacity = ref(76);
  const selectedHeroImage = ref(null);
  const removeHeroImage = ref(false);
  const localPreviewUrl = ref('');

  const previewHeroImage = computed(() => {
    if (localPreviewUrl.value) return localPreviewUrl.value;
    if (removeHeroImage.value || !heroImage.value) return '';
    return getStorageUrl(heroImage.value);
  });

  const retentionNoticeWordCount = computed(() => {
    return (content.value.account_deletion_body || '').trim().split(/\s+/).filter(Boolean).length;
  });

  const hasPlaceholdersConfigured = computed(() => {
    return Boolean(
      content.value.account_deletion_email_placeholder?.trim() &&
      content.value.account_deletion_identifier_placeholder?.trim() &&
      content.value.account_deletion_reason_placeholder?.trim()
    );
  });

  const isCompliantWithAppStore = computed(() => {
    const text = ((content.value.account_deletion_title || '') + ' ' + (content.value.account_deletion_body || '')).toLowerCase();
    return text.includes('delete') || text.includes('deactivat') || text.includes('eras');
  });

  const isDirty = computed(() => {
    if (selectedHeroImage.value || removeHeroImage.value) return true;
    if (!initialSnapshot.value) return false;
    const current = JSON.stringify({
      content: content.value,
      heroOpacity: heroOpacity.value
    });
    return initialSnapshot.value !== current;
  });

  const clearPreviewUrl = () => {
    if (localPreviewUrl.value) {
      URL.revokeObjectURL(localPreviewUrl.value);
    }
    localPreviewUrl.value = '';
  };

  const selectHeroImage = (file) => {
    if (!file) return false;
    if (file.size > 5 * 1024 * 1024) {
      toast.error('Image must be less than 5MB');
      return false;
    }
    selectedHeroImage.value = file;
    removeHeroImage.value = false;
    clearPreviewUrl();
    localPreviewUrl.value = URL.createObjectURL(file);
    return true;
  };

  const clearHeroImage = (imageInputRef) => {
    selectedHeroImage.value = null;
    if (imageInputRef && imageInputRef.value) {
      imageInputRef.value.value = '';
    }
    removeHeroImage.value = true;
    clearPreviewUrl();
  };

  const fetchContent = async () => {
    loading.value = true;
    try {
      const response = await api.get('/v1/admin/settings');
      const data = response.data?.data || {};
      if (data) {
        if (data.account_deletion_title) content.value.account_deletion_title = String(data.account_deletion_title);
        if (data.account_deletion_subtitle) content.value.account_deletion_subtitle = String(data.account_deletion_subtitle);
        if (data.account_deletion_body) content.value.account_deletion_body = String(data.account_deletion_body);

        for (const key of [
          'account_deletion_email_placeholder',
          'account_deletion_identifier_placeholder',
          'account_deletion_reason_placeholder',
        ]) {
          if (data[key]) content.value[key] = String(data[key]);
        }

        heroImage.value = data.page_hero_account_deletion_image || '';
        heroOpacity.value = Number(data.page_hero_account_deletion_overlay_opacity ?? 76);

        initialSnapshot.value = JSON.stringify({
          content: content.value,
          heroOpacity: heroOpacity.value
        });
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }
    } catch (error) {
      console.error('Failed to load account deletion CMS settings:', error);
    } finally {
      loading.value = false;
    }
  };

  const saveContent = async (imageInputRef) => {
    if (!content.value.account_deletion_title?.trim()) {
      toast.error('Please enter an account deletion title.');
      return;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      formData.append('settings[account_deletion_title]', content.value.account_deletion_title);
      formData.append('settings[account_deletion_subtitle]', content.value.account_deletion_subtitle || '');
      formData.append('settings[account_deletion_body]', content.value.account_deletion_body || '');
      formData.append('settings[account_deletion_email_placeholder]', content.value.account_deletion_email_placeholder || '');
      formData.append('settings[account_deletion_identifier_placeholder]', content.value.account_deletion_identifier_placeholder || '');
      formData.append('settings[account_deletion_reason_placeholder]', content.value.account_deletion_reason_placeholder || '');
      formData.append('settings[page_hero_account_deletion_overlay_opacity]', String(heroOpacity.value));
      formData.append('page_hero_page', 'account_deletion');

      if (selectedHeroImage.value) {
        formData.append('page_hero_image', selectedHeroImage.value);
      } else if (removeHeroImage.value) {
        formData.append('settings[page_hero_account_deletion_image]', '');
      }

      const response = await api.post('/v1/admin/settings', formData);
      toast.success('Account deletion content saved successfully!');

      const data = response.data?.data;
      if (data) {
        heroImage.value = data.page_hero_account_deletion_image || '';
      }
      selectedHeroImage.value = null;
      removeHeroImage.value = false;
      if (imageInputRef && imageInputRef.value) {
        imageInputRef.value.value = '';
      }
      clearPreviewUrl();

      initialSnapshot.value = JSON.stringify({
        content: content.value,
        heroOpacity: heroOpacity.value
      });
      lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } catch (error) {
      toast.error('Failed to save changes. Please try again.');
    } finally {
      saving.value = false;
    }
  };

  return {
    content,
    saving,
    loading,
    lastSaved,
    isDirty,
    activeTab,
    heroImage,
    heroOpacity,
    selectedHeroImage,
    removeHeroImage,
    previewHeroImage,
    retentionNoticeWordCount,
    hasPlaceholdersConfigured,
    isCompliantWithAppStore,
    selectHeroImage,
    clearHeroImage,
    fetchContent,
    saveContent
  };
}

