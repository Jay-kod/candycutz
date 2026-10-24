import { ref, computed } from 'vue';
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

  const previewShopUrl = computed(() => {
    if (shopImageFile.value) {
      return URL.createObjectURL(shopImageFile.value);
    }
    return null;
  });

  const isDirty = computed(() => {
    if (shopImageFile.value) return true;
    if (!initialSnapshot.value) return false;
    return initialSnapshot.value !== JSON.stringify(content.value);
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
        initialSnapshot.value = JSON.stringify(content.value);
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
      
      const response = await api.post('/v1/admin/settings', formData);
      toast.success('About page content saved successfully!');
      
      const updatedData = response.data?.data;
      if (updatedData) {
        currentShopImage.value = updatedData.about_shop_image || currentShopImage.value;
        shopImageFile.value = null;
        if (fileInputRef && fileInputRef.value) {
          fileInputRef.value.value = '';
        }
        initialSnapshot.value = JSON.stringify(content.value);
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }
    } catch (error) {
      console.error('Error saving about content:', error);
      toast.error('Failed to save changes.');
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
    currentShopImage,
    shopImageFile,
    shopImageLoadError,
    previewShopUrl,
    getStorageUrl,
    fetchContent,
    handleShopImageUpload,
    saveContent
  };
}

