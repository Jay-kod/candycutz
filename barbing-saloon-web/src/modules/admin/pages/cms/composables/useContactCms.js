import { ref, computed, watch } from 'vue';
import { getActivePinia } from 'pinia';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';
import { getStorageUrl } from '@/core/utils/url';
import { usePublicContentStore } from '@/core/stores/publicContent.store';
import { extractGoogleMapsCoordinates, extractGoogleMapsLocation } from '@/features/directions/utils/googleMaps';
import { DEFAULT_CONTACT_CONTENT, PLATFORMS_LIST } from './contactConstants';

export function useContactCms() {
  const toast = useToast();
  const saving = ref(false);
  const loading = ref(false);
  const lastSaved = ref(null);
  const initialSnapshot = ref(null);
  const activeTab = ref('all');
  const resolvedShop = ref(null);

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

  const isTrustedGoogleEmbedUrl = (url) => {
    if (!url || !url.startsWith('https://')) return false;
    return (
      url.startsWith('https://www.google.com/maps/embed') ||
      (url.startsWith('https://maps.google.com/maps') &&
        (url.includes('output=embed') || url.includes('pb=')))
    );
  };

  const extractCoordinatesFromMapInput = (rawInput) => extractGoogleMapsCoordinates(rawInput);

  const extractedMapSrc = computed(() => {
    const raw = (content.value.contact_map_iframe || '').trim();
    if (!raw) return '';
    if (isTrustedGoogleEmbedUrl(raw)) {
      return raw;
    }
    const match = raw.match(/src\s*=\s*["']([^"']+)["']/i);
    if (match) {
      const extracted = match[1].replace(/&amp;/g, '&').trim();
      if (isTrustedGoogleEmbedUrl(extracted)) {
        return extracted;
      }
    }
    return '';
  });

  watch(
    () => [content.value.contact_map_iframe, content.value.contact_map_link],
    ([iframeVal, linkVal], [prevIframe, prevLink]) => {
      if (loading.value) return;
      const linkLoc = extractGoogleMapsLocation(linkVal);
      const iframeLoc = extractGoogleMapsLocation(iframeVal);
      const preferred = (linkLoc?.precise ? linkLoc : null) || (iframeLoc?.precise ? iframeLoc : null) || linkLoc || iframeLoc;
      if (!preferred) return;

      const hasCoords = Boolean(String(content.value.latitude || '').trim() && String(content.value.longitude || '').trim());
      const mapChanged = iframeVal !== prevIframe || linkVal !== prevLink;
      if (!hasCoords || preferred.precise || (mapChanged && !resolvedShop.value?.location_lock?.verified)) {
        content.value.latitude = preferred.latitude;
        content.value.longitude = preferred.longitude;
      }
    }
  );

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

  const refreshResolvedShop = async () => {
    try {
      const shopRes = await api.get('/v1/shop/location');
      const shopData = shopRes?.data?.data || shopRes?.data || null;
      if (shopData?.latitude && shopData?.longitude) {
        resolvedShop.value = shopData;
        if (!isDirty.value) {
          content.value.latitude = String(shopData.latitude);
          content.value.longitude = String(shopData.longitude);
        }
      }
    } catch {
      // Safe fallback when shop/location endpoint is unavailable in isolated unit tests
    }
  };

  const syncResolvedSnapshot = async () => {
    initialSnapshot.value = JSON.stringify({
      content: content.value,
      heroOpacity: heroOpacity.value,
    });
    await refreshResolvedShop();
    initialSnapshot.value = JSON.stringify({
      content: content.value,
      heroOpacity: heroOpacity.value,
    });
    lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
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
        if (!data.contact_address && data.address) {
          content.value.contact_address = String(data.address);
        }
        if (!data.contact_map_iframe && data.map_iframe) {
          content.value.contact_map_iframe = String(data.map_iframe);
        }
        if (!data.contact_map_link && data.map_link) {
          content.value.contact_map_link = String(data.map_link);
        }
        if ((!content.value.latitude || !content.value.longitude) && (content.value.contact_map_iframe || content.value.contact_map_link)) {
          const coords = extractCoordinatesFromMapInput(content.value.contact_map_iframe || content.value.contact_map_link);
          if (coords) {
            content.value.latitude = coords.latitude;
            content.value.longitude = coords.longitude;
          }
        }
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

        await syncResolvedSnapshot();
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
        if (key === 'contact_map_iframe' && extractedMapSrc.value) {
          formData.append('settings[contact_map_iframe]', extractedMapSrc.value);
          return;
        }
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

      const hadRawIframe = Boolean((content.value.contact_map_iframe || '').trim());
      const response = await api.post('/v1/admin/settings', formData);

      if (getActivePinia()) {
        usePublicContentStore().clearSettingsCache();
      }

      const data = response.data?.data;
      if (data) {
        heroImage.value = data.page_hero_contact_image || '';
        if (data.contact_map_iframe !== undefined && data.contact_map_iframe !== null) {
          content.value.contact_map_iframe = String(data.contact_map_iframe);
        }
        if (data.latitude !== undefined && data.latitude !== null) {
          content.value.latitude = String(data.latitude);
        }
        if (data.longitude !== undefined && data.longitude !== null) {
          content.value.longitude = String(data.longitude);
        }
      }

      if (hadRawIframe && !extractedMapSrc.value) {
        toast.error('Map embed URL was invalid and could not be saved. Please paste a valid Google Maps embed snippet or URL.');
      } else {
        toast.success('Contact settings and shop location updated successfully!');
      }
      selectedFile.value = null;
      removeImage.value = false;
      if (imageInputRef && imageInputRef.value) {
        imageInputRef.value.value = '';
      }
      clearPreviewUrl();

      await syncResolvedSnapshot();
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
    resolvedShop,
    clearPreviewUrl,
    selectImage,
    clearImage,
    fetchContent,
    saveContent
  };
}

