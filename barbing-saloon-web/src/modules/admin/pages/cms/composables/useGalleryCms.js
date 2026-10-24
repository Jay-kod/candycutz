import { ref, computed } from 'vue';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';
import { getStorageUrl } from '@/core/utils/url';

export function useGalleryCms() {
  const toast = useToast();
  const saving = ref(false);
  const loading = ref(false);
  const lastSaved = ref(null);
  const initialSnapshot = ref(null);
  const activeTab = ref('all');

  const content = ref({
    gallery_title: 'Our Gallery',
    gallery_subtitle: 'Browse our portfolio of premium cuts, styles, and grooming transformations.'
  });

  const heroImage = ref('');
  const heroOpacity = ref(76);
  const selectedFile = ref(null);
  const removeImage = ref(false);
  const localPreviewUrl = ref('');

  const galleryItems = ref([]);
  const galleryLoading = ref(false);

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

  const categories = computed(() => {
    const set = new Set();
    galleryItems.value.forEach(item => {
      if (item.category) set.add(item.category);
    });
    return Array.from(set);
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
      const data = response.data?.data;
      if (data) {
        if (data.gallery_title) content.value.gallery_title = data.gallery_title;
        if (data.gallery_subtitle) content.value.gallery_subtitle = data.gallery_subtitle;
        heroImage.value = data.page_hero_gallery_image || '';
        heroOpacity.value = Number(data.page_hero_gallery_overlay_opacity ?? 76);
        
        initialSnapshot.value = JSON.stringify({
          content: content.value,
          heroOpacity: heroOpacity.value
        });
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }
    } catch (error) {
      console.error('Failed to load gallery CMS settings:', error);
    } finally {
      loading.value = false;
    }
  };

  const fetchGallery = async () => {
    galleryLoading.value = true;
    try {
      const response = await api.get('/v1/gallery');
      const data = response.data?.data || response.data;
      if (Array.isArray(data)) {
        galleryItems.value = data;
      }
    } catch (error) {
      console.warn('Could not load gallery portfolio preview:', error);
    } finally {
      galleryLoading.value = false;
    }
  };

  const saveContent = async (imageInputRef) => {
    if (!content.value.gallery_title) {
      toast.error('Please enter a gallery page title.');
      return;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      formData.append('settings[gallery_title]', content.value.gallery_title);
      formData.append('settings[gallery_subtitle]', content.value.gallery_subtitle);
      formData.append('settings[page_hero_gallery_overlay_opacity]', String(heroOpacity.value));
      formData.append('page_hero_page', 'gallery');

      if (selectedFile.value) {
        formData.append('page_hero_image', selectedFile.value);
      } else if (removeImage.value) {
        formData.append('settings[page_hero_gallery_image]', '');
      }

      const response = await api.post('/v1/admin/settings', formData);
      toast.success('Gallery page content saved successfully!');

      const data = response.data?.data;
      if (data) {
        heroImage.value = data.page_hero_gallery_image || '';
      }
      selectedFile.value = null;
      if (imageInputRef && imageInputRef.value) {
        imageInputRef.value.value = '';
      }
      removeImage.value = false;
      clearPreviewUrl();

      initialSnapshot.value = JSON.stringify({
        content: content.value,
        heroOpacity: heroOpacity.value
      });
      lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } catch (error) {
      console.error('Failed to save gallery content:', error);
      toast.error('Failed to save Gallery page settings.');
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
    previewImage,
    galleryItems,
    galleryLoading,
    categories,
    selectImage,
    clearImage,
    clearPreviewUrl,
    fetchContent,
    fetchGallery,
    saveContent
  };
}

