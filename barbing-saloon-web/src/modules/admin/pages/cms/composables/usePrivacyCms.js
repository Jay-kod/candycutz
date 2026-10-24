import { ref, computed } from 'vue';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';
import { getStorageUrl } from '@/core/utils/url';
import { DEFAULT_PRIVACY_CONTENT, DEFAULT_PRIVACY_SECTIONS } from './privacyConstants';

export function usePrivacyCms() {
  const toast = useToast();
  const saving = ref(false);
  const loading = ref(false);
  const lastSaved = ref(null);
  const initialSnapshot = ref(null);
  const activeTab = ref('all');

  const content = ref({ ...DEFAULT_PRIVACY_CONTENT });
  const sections = ref(JSON.parse(JSON.stringify(DEFAULT_PRIVACY_SECTIONS)));

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

  const totalWords = computed(() => {
    let words = 0;
    const titleWords = (content.value.privacy_title || '').trim().split(/\s+/).filter(Boolean).length;
    words += titleWords;
    sections.value.forEach(sec => {
      const hWords = (sec.heading || '').trim().split(/\s+/).filter(Boolean).length;
      const bWords = (sec.body || '').trim().split(/\s+/).filter(Boolean).length;
      words += hWords + bWords;
    });
    return words;
  });

  const estimatedReadTime = computed(() => {
    return Math.max(1, Math.ceil(totalWords.value / 200));
  });

  const hasDeactivationClause = computed(() => {
    return sections.value.some(s => 
      (s.heading || '').toLowerCase().includes('deactivation') || 
      (s.heading || '').toLowerCase().includes('erasure') || 
      (s.body || '').toLowerCase().includes('apple guideline') ||
      (s.body || '').toLowerCase().includes('deactivate account')
    );
  });

  const isDirty = computed(() => {
    if (selectedHeroImage.value || removeHeroImage.value) return true;
    if (!initialSnapshot.value) return false;
    const current = JSON.stringify({
      content: content.value,
      sections: sections.value,
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

  const addSection = () => {
    const nextIndex = sections.value.length + 1;
    sections.value.push({
      heading: `${nextIndex}. New Clause`,
      body: ''
    });
  };

  const removeSection = (index) => {
    if (sections.value.length <= 1) {
      toast.error('You must keep at least one policy clause.');
      return;
    }
    sections.value.splice(index, 1);
  };

  const moveSection = (index, direction) => {
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= sections.value.length) return;
    const item = sections.value.splice(index, 1)[0];
    sections.value.splice(targetIndex, 0, item);
  };

  const fetchContent = async () => {
    loading.value = true;
    try {
      const response = await api.get('/v1/admin/settings');
      const data = response.data?.data || {};
      if (data) {
        if (data.privacy_title) content.value.privacy_title = String(data.privacy_title);
        if (data.privacy_subtitle) content.value.privacy_subtitle = String(data.privacy_subtitle);

        if (data.privacy_sections) {
          try {
            const parsed = typeof data.privacy_sections === 'string'
              ? JSON.parse(data.privacy_sections)
              : data.privacy_sections;
            if (Array.isArray(parsed) && parsed.length > 0) {
              sections.value = parsed;
            }
          } catch (e) {
            console.warn('Could not parse privacy_sections, using default clauses:', e);
          }
        }

        heroImage.value = data.page_hero_privacy_image || '';
        heroOpacity.value = Number(data.page_hero_privacy_overlay_opacity ?? 76);

        initialSnapshot.value = JSON.stringify({
          content: content.value,
          sections: sections.value,
          heroOpacity: heroOpacity.value
        });
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }
    } catch (error) {
      console.error('Failed to load privacy CMS settings:', error);
    } finally {
      loading.value = false;
    }
  };

  const saveContent = async (imageInputRef) => {
    if (!content.value.privacy_title?.trim()) {
      toast.error('Please enter a privacy policy title.');
      return;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      formData.append('settings[privacy_title]', content.value.privacy_title);
      formData.append('settings[privacy_subtitle]', content.value.privacy_subtitle || '');
      formData.append('settings[privacy_sections]', JSON.stringify(sections.value));
      formData.append('settings[page_hero_privacy_overlay_opacity]', String(heroOpacity.value));
      formData.append('page_hero_page', 'privacy');

      if (selectedHeroImage.value) {
        formData.append('page_hero_image', selectedHeroImage.value);
      } else if (removeHeroImage.value) {
        formData.append('settings[page_hero_privacy_image]', '');
      }

      const response = await api.post('/v1/admin/settings', formData);
      toast.success('Privacy policy content saved successfully!');

      const data = response.data?.data;
      if (data) {
        heroImage.value = data.page_hero_privacy_image || '';
      }
      selectedHeroImage.value = null;
      removeHeroImage.value = false;
      if (imageInputRef && imageInputRef.value) {
        imageInputRef.value.value = '';
      }
      clearPreviewUrl();

      initialSnapshot.value = JSON.stringify({
        content: content.value,
        sections: sections.value,
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
    sections,
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
    totalWords,
    estimatedReadTime,
    hasDeactivationClause,
    selectHeroImage,
    clearHeroImage,
    addSection,
    removeSection,
    moveSection,
    fetchContent,
    saveContent
  };
}

