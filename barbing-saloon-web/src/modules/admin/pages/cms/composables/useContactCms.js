import { ref, computed } from 'vue';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';
import { getStorageUrl } from '@/core/utils/url';
import { DEFAULT_CONTACT_CONTENT, PLATFORMS_LIST } from './contactConstants';

export function useContactCms() {
  const toast = useToast();
  const saving = ref(false);
  const loading = ref(false);
  const lastSaved = ref(null);
  const initialSnapshot = ref(null);
  const activeTab = ref('all');

  const content = ref({ ...DEFAULT_CONTACT_CONTENT });
  const platformsList = PLATFORMS_LIST;

  const heroImage = ref('');
  const heroOpacity = ref(76);
  const selectedFile = ref(null);
  const removeImage = ref(false);
  const localPreviewUrl = ref('');

  const previewImage = computed(() => {
    if (localPreviewUrl.value) return localPreviewUrl.value;
    if (removeImage.value || !heroImage.value) return '';
    return getStorageUrl(heroImage.value);
  });

  const isDirty = computed(() => {
    if (selectedFile.value || removeImage.value) return true;
    if (!initialSnapshot.value) return false;
    const current = JSON.stringify({
      content: content.value,
      heroOpacity: heroOpacity.value
    });
    return initialSnapshot.value !== current;
  });

  const isOptionChecked = (key) => {
    const val = content.value[key];
    if (val === undefined || val === null || val === '') return true;
    return val === true || val === 'true' || val === 1 || val === '1';
  };

  const toggleOption = (key, eventOrChecked) => {
    const checked = typeof eventOrChecked === 'boolean' 
      ? eventOrChecked 
      : Boolean(eventOrChecked?.target?.checked);
    content.value[key] = checked ? 'true' : 'false';
  };

  const getPlatformStatusText = (platform) => {
    const link = (content.value[platform.linkKey] || '').trim();
    if (!link) return 'Not Set';
    const showFooter = isOptionChecked(platform.footerKey);
    const showContact = isOptionChecked(platform.contactKey);
    if (showFooter && showContact) return 'Footer & Contact';
    if (showFooter) return 'Footer Only';
    if (showContact) return 'Contact Only';
    return 'Hidden';
  };

  const getPlatformStatusBadge = (platform) => {
    const link = (content.value[platform.linkKey] || '').trim();
    if (!link) return 'border-white/10 text-white/40 bg-white/5';
    const showFooter = isOptionChecked(platform.footerKey);
    const showContact = isOptionChecked(platform.contactKey);
    if (showFooter || showContact) return 'border-emerald-500/30 text-emerald-400 bg-emerald-500/10';
    return 'border-amber-500/30 text-amber-400 bg-amber-500/10';
  };

  const activeSocialCount = computed(() => {
    return platformsList.filter((p) => {
      const link = (content.value[p.linkKey] || '').trim();
      const active = isOptionChecked(p.footerKey) || isOptionChecked(p.contactKey);
      return Boolean(link && active);
    }).length;
  });

  const activeFooterPreviewList = computed(() => {
    return platformsList.filter((p) => {
      const link = (content.value[p.linkKey] || '').trim();
      return Boolean(link && isOptionChecked(p.footerKey));
    });
  });
  const activeFooterPreviewCount = computed(() => activeFooterPreviewList.value.length);

  const activeContactPreviewList = computed(() => {
    return platformsList.filter((p) => {
      const link = (content.value[p.linkKey] || '').trim();
      return Boolean(link && isOptionChecked(p.contactKey));
    });
  });
  const activeContactPreviewCount = computed(() => activeContactPreviewList.value.length);

  const extractedMapSrc = computed(() => {
    const raw = (content.value.contact_map_iframe || '').trim();
    if (!raw) return '';
    if (raw.startsWith('https://www.google.com/maps/embed')) {
      return raw;
    }
    const match = raw.match(/src\s*=\s*["']([^"']+)["']/i);
    if (match) {
      const extracted = match[1].replace(/&amp;/g, '&');
      if (extracted.startsWith('https://www.google.com/maps/embed')) {
        return extracted;
      }
    }
    return '';
  });

  const clearPreviewUrl = () => {
    if (localPreviewUrl.value) {
      URL.revokeObjectURL(localPreviewUrl.value);
    }
    localPreviewUrl.value = '';
  };

  const selectImage = (file) => {
    if (!file) return false;
    if (file.size > 5 * 1024 * 1024) {
      toast.error('Image must be less than 5MB');
      return false;
    }
    selectedFile.value = file;
    removeImage.value = false;
    clearPreviewUrl();
    localPreviewUrl.value = URL.createObjectURL(file);
    return true;
  };

  const clearImage = (imageInputRef) => {
    selectedFile.value = null;
    if (imageInputRef && imageInputRef.value) {
      imageInputRef.value.value = '';
    }
    removeImage.value = true;
    clearPreviewUrl();
  };

  const fetchContent = async () => {
    loading.value = true;
    try {
      const response = await api.get('/v1/admin/settings');
      const data = response.data?.data || {};
      if (data) {
        const keys = Object.keys(content.value);
        keys.forEach((key) => {
          if (data[key] !== undefined && data[key] !== null) {
            content.value[key] = String(data[key]);
          }
        });
        const legacyMap = {
          social_instagram: 'instagram',
          social_facebook: 'facebook',
          social_twitter: 'twitter',
          social_whatsapp: 'whatsapp',
          social_tiktok: 'tiktok',
          social_youtube: 'youtube',
          social_linkedin: 'linkedin',
        };
        Object.entries(legacyMap).forEach(([newKey, oldKey]) => {
          if (!content.value[newKey] && (data[oldKey] || data.social?.[oldKey])) {
            content.value[newKey] = String(data[oldKey] || data.social?.[oldKey]);
          }
        });
        heroImage.value = data.page_hero_contact_image || '';
        heroOpacity.value = Number(data.page_hero_contact_overlay_opacity ?? 76);

        initialSnapshot.value = JSON.stringify({
          content: content.value,
          heroOpacity: heroOpacity.value
        });
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }
    } catch (error) {
      console.error('Failed to load contact CMS content:', error);
    } finally {
      loading.value = false;
    }
  };

  const saveContent = async (imageInputRef) => {
    saving.value = true;
    try {
      const formData = new FormData();
      Object.entries(content.value).forEach(([key, value]) => {
        formData.append(`settings[${key}]`, value ?? '');
      });
      // Save legacy keys for backwards compatibility
      formData.append('settings[instagram]', content.value.social_instagram ?? '');
      formData.append('settings[facebook]', content.value.social_facebook ?? '');
      formData.append('settings[whatsapp]', content.value.social_whatsapp ?? '');
      formData.append('settings[twitter]', content.value.social_twitter ?? '');
      formData.append('settings[page_hero_contact_overlay_opacity]', String(heroOpacity.value));
      formData.append('page_hero_page', 'contact');

      if (selectedFile.value) {
        formData.append('page_hero_image', selectedFile.value);
      } else if (removeImage.value) {
        formData.append('settings[page_hero_contact_image]', '');
      }

      const response = await api.post('/v1/admin/settings', formData);
      toast.success('Social links and contact content updated successfully!');

      const data = response.data?.data;
      if (data) {
        heroImage.value = data.page_hero_contact_image || '';
      }
      selectedFile.value = null;
      removeImage.value = false;
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
    selectedFile,
    removeImage,
    localPreviewUrl,
    previewImage,
    platformsList,
    isOptionChecked,
    toggleOption,
    getPlatformStatusText,
    getPlatformStatusBadge,
    activeSocialCount,
    activeFooterPreviewList,
    activeFooterPreviewCount,
    activeContactPreviewList,
    activeContactPreviewCount,
    extractedMapSrc,
    clearPreviewUrl,
    selectImage,
    clearImage,
    fetchContent,
    saveContent
  };
}
