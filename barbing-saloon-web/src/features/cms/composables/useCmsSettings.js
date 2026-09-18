import { ref, computed } from 'vue';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';

export function useCmsSettings() {
  const toast = useToast();
  const saving = ref(false);
  const loading = ref(false);

  const settings = ref({
    hero_title: 'Premium grooming with a sharper standard.',
    hero_subtitle: 'Experience the CandyCutz difference. Log in to explore our full menu of premium services, meet our expert barbers, and book your next appointment seamlessly.',
    hero_btn1_text: 'Book a Service',
    hero_btn2_text: 'About Us',
    stats_years: '10+',
    stats_barbers: '5+',
    stats_services: '20+',
    stats_clients: '5k+',
    about_teaser_title: 'Our Philosophy',
    about_teaser_subtitle: 'A Sharper Standard',
    about_teaser_text: "Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut. Our shop combines traditional barbering techniques with modern trends in a relaxing, luxurious environment.",
    portal_teaser_title: 'The CandyCutz Portal',
    portal_teaser_subtitle: 'Unlock the Full Experience',
    portal_teaser_text: 'Create an account to browse our extensive list of services, check barber availability in real-time, view our exclusive gallery, and manage your appointments seamlessly from your own personalized dashboard.'
  });

  const currentHeroImage = ref(null);
  const heroImageFile = ref(null);
  const imageLoadError = ref(false);

  const currentAboutImage = ref(null);
  const aboutImageFile = ref(null);
  const aboutImageLoadError = ref(false);

  const previewUrl = computed(() => {
    if (heroImageFile.value) {
      return URL.createObjectURL(heroImageFile.value);
    }
    return null;
  });

  const previewAboutUrl = computed(() => {
    if (aboutImageFile.value) {
      return URL.createObjectURL(aboutImageFile.value);
    }
    return null;
  });

  const getStorageUrl = (path) => {
    if (!path) return '';
    return `${import.meta.env.VITE_API_BASE_URL.replace('/api', '')}/storage/${path}`;
  };

  const fetchSettings = async () => {
    loading.value = true;
    try {
      const response = await api.get('/v1/admin/settings');
      const data = response.data.data;
      if (data) {
        Object.keys(settings.value).forEach(key => {
          if (data[key]) settings.value[key] = data[key];
        });
        currentHeroImage.value = data.hero_image || null;
        currentAboutImage.value = data.about_teaser_image || null;
      }
    } catch (error) {
      console.error('Failed to load settings:', error);
    } finally {
      loading.value = false;
    }
  };

  const handleImageUpload = (file, type = 'hero') => {
    if (file) {
      if (file.size > 2 * 1024 * 1024) {
        toast.error('Image must be less than 2MB');
        return false;
      }
      if (type === 'hero') {
        heroImageFile.value = file;
      } else {
        aboutImageFile.value = file;
      }
      return true;
    }
    return false;
  };

  const saveSettings = async (fileInputRef, aboutFileInputRef) => {
    if (!settings.value.hero_title || !settings.value.hero_subtitle) {
      toast.error('Please fill in all text fields.');
      return;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      Object.keys(settings.value).forEach(key => {
        formData.append(`settings[${key}]`, settings.value[key]);
      });
      
      if (heroImageFile.value) {
        formData.append('hero_image', heroImageFile.value);
      }
      
      if (aboutImageFile.value) {
        formData.append('about_teaser_image', aboutImageFile.value);
      }

      const response = await api.post('/v1/admin/settings', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Settings updated successfully');
      
      const updatedData = response.data.data;
      if (updatedData) {
        currentHeroImage.value = updatedData.hero_image || currentHeroImage.value;
        heroImageFile.value = null; 
        if (fileInputRef) fileInputRef.value = '';
        
        currentAboutImage.value = updatedData.about_teaser_image || currentAboutImage.value;
        aboutImageFile.value = null;
        if (aboutFileInputRef) aboutFileInputRef.value = '';
      }
    } catch (error) {
      console.error('Error saving settings:', error);
      toast.error('Failed to save settings');
    } finally {
      saving.value = false;
    }
  };

  return {
    settings,
    saving,
    loading,
    currentHeroImage,
    heroImageFile,
    imageLoadError,
    currentAboutImage,
    aboutImageFile,
    aboutImageLoadError,
    previewUrl,
    previewAboutUrl,
    getStorageUrl,
    fetchSettings,
    handleImageUpload,
    saveSettings
  };
}
