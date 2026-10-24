import { ref, computed, onBeforeUnmount } from 'vue';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';
import { getStorageUrl } from '@/core/utils/url';

export function useServicesCms() {
  const toast = useToast();
  const saving = ref(false);
  const loading = ref(false);
  const lastSaved = ref(null);
  const initialSnapshot = ref(null);
  const activeTab = ref('all');

  const content = ref({
    services_title: 'Our Premium Services',
    services_subtitle: 'Explore our full range of grooming services designed to keep you looking sharp and feeling confident.'
  });

  const catalogServices = ref([]);
  const catalogLoading = ref(false);
  const currentHeroImage = ref('');
  const heroImageFile = ref(null);
  const heroImageInput = ref(null);
  const heroOpacity = ref(76);
  const removeHeroImage = ref(false);
  const heroPreviewUrl = ref('');

  const clearHeroPreviewUrl = () => {
    if (heroPreviewUrl.value) URL.revokeObjectURL(heroPreviewUrl.value);
    heroPreviewUrl.value = '';
  };

  const previewHeroImage = computed(() => {
    if (heroPreviewUrl.value) return heroPreviewUrl.value;
    if (removeHeroImage.value || !currentHeroImage.value) return '';
    return getStorageUrl(currentHeroImage.value);
  });

  const isDirty = computed(() => {
    if (heroImageFile.value || removeHeroImage.value) return true;
    if (!initialSnapshot.value) return false;
    return initialSnapshot.value !== JSON.stringify({
      content: content.value,
      heroImage: currentHeroImage.value,
      heroOpacity: heroOpacity.value,
    });
  });

  const categories = computed(() => {
    const set = new Set();
    catalogServices.value.forEach(s => {
      const name = s.category?.name || s.category;
      if (name) set.add(name);
    });
    return Array.from(set);
  });

  const fetchContent = async () => {
    loading.value = true;
    try {
      const response = await api.get('/v1/admin/settings');
      const data = response.data?.data;
      if (data) {
        if (data.services_title) content.value.services_title = data.services_title;
        if (data.services_subtitle) content.value.services_subtitle = data.services_subtitle;
        currentHeroImage.value = data.page_hero_services_image || '';
        heroOpacity.value = Number(data.page_hero_services_overlay_opacity ?? 76);
        initialSnapshot.value = JSON.stringify({
          content: content.value,
          heroImage: currentHeroImage.value,
          heroOpacity: heroOpacity.value,
        });
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }
    } catch (error) {
      console.error('Failed to load services CMS settings:', error);
    } finally {
      loading.value = false;
    }
  };

  const handleHeroImageUpload = (file) => {
    if (!file) return;
    heroImageFile.value = file;
    removeHeroImage.value = false;
    clearHeroPreviewUrl();
    heroPreviewUrl.value = URL.createObjectURL(file);
  };

  const clearHeroImage = () => {
    heroImageFile.value = null;
    if (heroImageInput.value) heroImageInput.value.value = '';
    removeHeroImage.value = true;
    clearHeroPreviewUrl();
  };

  const fetchCatalog = async () => {
    catalogLoading.value = true;
    try {
      const response = await api.get('/v1/services');
      const data = response.data?.data || response.data;
      if (Array.isArray(data)) {
        catalogServices.value = data;
      }
    } catch (error) {
      // Non-critical fallback for catalog preview
      console.warn('Could not load services catalog preview:', error);
    } finally {
      catalogLoading.value = false;
    }
  };

  const saveContent = async () => {
    if (!content.value.services_title) {
      toast.error('Please enter a services page title.');
      return;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      formData.append('settings[services_title]', content.value.services_title);
      formData.append('settings[services_subtitle]', content.value.services_subtitle);
      formData.append('settings[page_hero_services_overlay_opacity]', String(heroOpacity.value));
      formData.append('page_hero_page', 'services');
      if (heroImageFile.value) {
        formData.append('page_hero_image', heroImageFile.value);
      } else if (removeHeroImage.value) {
        formData.append('settings[page_hero_services_image]', '');
      }

      const response = await api.post('/v1/admin/settings', formData);
      toast.success('Services page content saved successfully!');
      
      const data = response.data?.data;
      if (data) currentHeroImage.value = data.page_hero_services_image || '';
      heroImageFile.value = null;
      removeHeroImage.value = false;
      if (heroImageInput.value) heroImageInput.value.value = '';
      clearHeroPreviewUrl();
      initialSnapshot.value = JSON.stringify({
        content: content.value,
        heroImage: currentHeroImage.value,
        heroOpacity: heroOpacity.value,
      });
      lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } catch (error) {
      console.error('Error saving services content:', error);
      toast.error('Failed to save changes.');
    } finally {
      saving.value = false;
    }
  };

  onBeforeUnmount(clearHeroPreviewUrl);

  return {
    content,
    saving,
    loading,
    lastSaved,
    isDirty,
    activeTab,
    catalogServices,
    catalogLoading,
    currentHeroImage,
    heroImageInput,
    heroOpacity,
    previewHeroImage,
    categories,
    fetchContent,
    fetchCatalog,
    handleHeroImageUpload,
    clearHeroImage,
    saveContent
  };
}
