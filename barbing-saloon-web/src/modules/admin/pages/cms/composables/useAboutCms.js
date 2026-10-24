import { ref, computed, onBeforeUnmount } from 'vue';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';
import { getStorageUrl as resolveStorageUrl } from '@/core/utils/url';

export function useAboutCms() {
  const toast = useToast();
  const saving = ref(false);
  const loading = ref(false);
  const lastSaved = ref(null);
  const initialSnapshot = ref(null);
  const activeTab = ref('all');

  const content = ref({
    about_shop_title: 'About Us',
    about_shop_subtitle: 'A Sharper Standard',
    about_shop_text_1: "Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut. Our shop combines traditional barbering techniques with modern trends in a relaxing, luxurious environment.",
    about_shop_text_2: "Whether you're here for a quick shape-up or a complete restyle, our expert barbers are dedicated to making you look and feel your best. Experience the art of grooming at its finest.",
    about_team_title: 'Meet the team',
    about_team_subtitle: 'Our barbers are masters of their craft, bringing years of experience and a passion for perfection to every cut.'
  });

  const currentShopImage = ref(null);
  const shopImageFile = ref(null);
  const shopImageLoadError = ref(false);
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

  const previewShopUrl = computed(() => {
    if (shopImageFile.value) {
      return URL.createObjectURL(shopImageFile.value);
    }
    return null;
  });

  const isDirty = computed(() => {
    if (shopImageFile.value || heroImageFile.value || removeHeroImage.value) return true;
    if (!initialSnapshot.value) return false;
    return initialSnapshot.value !== JSON.stringify({
      content: content.value,
      heroImage: currentHeroImage.value,
      heroOpacity: heroOpacity.value,
    });
  });
  const previewHeroImage = computed(() => {
    if (heroPreviewUrl.value) return heroPreviewUrl.value;
    if (removeHeroImage.value || !currentHeroImage.value) return '';
    return getStorageUrl(currentHeroImage.value);
  });

  const getStorageUrl = (path) => {
    return resolveStorageUrl(path);
  };

  const fetchContent = async () => {
    loading.value = true;
    try {
      const response = await api.get('/v1/admin/settings');
      const data = response.data?.data;
      if (data) {
        Object.keys(content.value).forEach(key => {
          if (data[key] !== undefined && data[key] !== null) {
            content.value[key] = data[key];
          }
        });
        currentShopImage.value = data.about_shop_image || null;
        currentHeroImage.value = data.page_hero_about_image || '';
        heroOpacity.value = Number(data.page_hero_about_overlay_opacity ?? 76);
        initialSnapshot.value = JSON.stringify({
          content: content.value,
          heroImage: currentHeroImage.value,
          heroOpacity: heroOpacity.value,
        });
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }
    } catch (error) {
      console.error('Failed to load about content:', error);
    } finally {
      loading.value = false;
    }
  };

  const handleShopImageUpload = (file) => {
    if (file) {
      if (file.size > 2 * 1024 * 1024) {
        toast.error('Image must be less than 2MB');
        return false;
      }
      shopImageFile.value = file;
      return true;
    }
    return false;
  };

  const handleHeroImageUpload = (file) => {
    if (file) {
      heroImageFile.value = file;
      removeHeroImage.value = false;
      clearHeroPreviewUrl();
      heroPreviewUrl.value = URL.createObjectURL(file);
      return true;
    }
    return false;
  };

  const clearHeroImage = () => {
    heroImageFile.value = null;
    if (heroImageInput.value) heroImageInput.value.value = '';
    removeHeroImage.value = true;
    clearHeroPreviewUrl();
  };

  const saveContent = async (fileInputRef) => {
    if (!content.value.about_shop_title || !content.value.about_shop_subtitle) {
      toast.error('Please enter the shop section title and subtitle.');
      return;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      Object.keys(content.value).forEach(key => {
        formData.append(`settings[${key}]`, content.value[key]);
      });
      
      if (shopImageFile.value) {
        formData.append('about_shop_image', shopImageFile.value);
      }
      formData.append('settings[page_hero_about_overlay_opacity]', String(heroOpacity.value));
      formData.append('page_hero_page', 'about');
      if (heroImageFile.value) {
        formData.append('page_hero_image', heroImageFile.value);
      } else if (removeHeroImage.value) {
        formData.append('settings[page_hero_about_image]', '');
      }
      
      const response = await api.post('/v1/admin/settings', formData);
      toast.success('About page content saved successfully!');
      
      const updatedData = response.data?.data;
      if (updatedData) {
        currentShopImage.value = updatedData.about_shop_image || currentShopImage.value;
        shopImageFile.value = null;
        currentHeroImage.value = updatedData.page_hero_about_image || '';
        heroImageFile.value = null;
        removeHeroImage.value = false;
        if (heroImageInput.value) heroImageInput.value.value = '';
        clearHeroPreviewUrl();
        if (fileInputRef && fileInputRef.value) {
          fileInputRef.value.value = '';
        }
        initialSnapshot.value = JSON.stringify({
          content: content.value,
          heroImage: currentHeroImage.value,
          heroOpacity: heroOpacity.value,
        });
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }
    } catch (error) {
      console.error('Error saving about content:', error);
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
    currentShopImage,
    currentHeroImage,
    heroImageInput,
    heroOpacity,
    previewHeroImage,
    shopImageFile,
    shopImageLoadError,
    previewShopUrl,
    getStorageUrl,
    fetchContent,
    handleShopImageUpload,
    handleHeroImageUpload,
    clearHeroImage,
    saveContent
  };
}
