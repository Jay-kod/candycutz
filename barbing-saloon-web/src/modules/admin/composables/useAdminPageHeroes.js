import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import client from '@/shared/api/client';
import { getStorageUrl } from '@/core/utils/url';
import { useToast } from '@/core/composables/useToast';

export const HERO_PAGES = [
  { key: 'home', label: 'Home', defaultTitle: 'Precision Cuts. Made for You.', defaultSubtitle: 'Expert barbers. Fresh fades. A better kind of barbershop experience.' },
  { key: 'about', label: 'About', defaultTitle: 'About CandyCutz', defaultSubtitle: 'Our story, our craft, and the standard behind every cut.' },
  { key: 'services', label: 'Services', defaultTitle: 'Our Premium Services', defaultSubtitle: 'Explore our full range of grooming services designed to keep you looking sharp and feeling confident.', titleKey: 'services_title', subtitleKey: 'services_subtitle' },
  { key: 'gallery', label: 'Gallery', defaultTitle: 'Our Gallery', defaultSubtitle: 'Browse our portfolio of premium cuts, styles, and grooming transformations.', titleKey: 'gallery_title', subtitleKey: 'gallery_subtitle' },
  { key: 'contact', label: 'Contact', defaultTitle: 'Contact', defaultSubtitle: 'Get in touch with the CandyCutz team.', titleKey: 'contact_title', subtitleKey: 'contact_subtitle' },
  { key: 'privacy', label: 'Privacy Policy', defaultTitle: 'Privacy Policy', defaultSubtitle: 'How we collect, use, and protect your information.', titleKey: 'privacy_title' },
  { key: 'terms', label: 'Terms of Service', defaultTitle: 'Terms of Service', defaultSubtitle: 'The terms that apply when you use CandyCutz services.', titleKey: 'terms_title' },
  { key: 'legal', label: 'Legal Stuff', defaultTitle: 'Legal Stuff', defaultSubtitle: 'Important information about CandyCutz.', titleKey: 'legal_stuff_title' },
  { key: 'security', label: 'Security', defaultTitle: 'Security', defaultSubtitle: 'How CandyCutz approaches the security of your information.', titleKey: 'security_title' },
  { key: 'cookies', label: 'Manage Cookies', defaultTitle: 'Manage Cookies', defaultSubtitle: 'Manage your cookie preferences.', titleKey: 'cookie_title', subtitleKey: 'cookie_intro' },
  { key: 'account_deletion', label: 'Account Deletion', defaultTitle: 'Account Deactivation & Erasure', defaultSubtitle: 'Instructions for requesting deletion or deactivation of your account and associated personal data.', titleKey: 'account_deletion_title' },
];

export function useAdminPageHeroes() {
  const toast = useToast();

  const settings = ref({});
  const loading = ref(true);
  const saving = ref(false);
  const error = ref(null);

  const selectedPage = ref('home');
  const selectedFile = ref(null);
  const localPreviewUrl = ref('');
  const removeImage = ref(false);

  const form = reactive({
    title: '',
    subtitle: '',
    image: '',
    opacity: 76
  });

  const originalState = reactive({
    title: '',
    subtitle: '',
    image: '',
    opacity: 76
  });

  const currentPage = computed(() => {
    return HERO_PAGES.find(p => p.key === selectedPage.value) || HERO_PAGES[0];
  });

  const isHome = computed(() => selectedPage.value === 'home');
  const titleKey = computed(() => (isHome.value ? 'cta_title' : `page_hero_${selectedPage.value}_title`));
  const subtitleKey = computed(() => (isHome.value ? 'cta_subtitle' : `page_hero_${selectedPage.value}_subtitle`));
  const imageKey = computed(() => (isHome.value ? 'hero_image' : `page_hero_${selectedPage.value}_image`));
  const opacityKey = computed(() => `page_hero_${selectedPage.value}_overlay_opacity`);

  const previewImage = computed(() => {
    if (localPreviewUrl.value) return localPreviewUrl.value;
    if (removeImage.value || !form.image) return '';
    return getStorageUrl(form.image);
  });

  const hasChanges = computed(() => {
    return form.title !== originalState.title ||
      form.subtitle !== originalState.subtitle ||
      form.opacity !== originalState.opacity ||
      selectedFile.value !== null ||
      removeImage.value === true;
  });

  const clearPreviewUrl = () => {
    if (localPreviewUrl.value) {
      URL.revokeObjectURL(localPreviewUrl.value);
      localPreviewUrl.value = '';
    }
  };

  const loadSelectedPage = () => {
    const page = currentPage.value;
    const fallbackTitle = page.titleKey ? settings.value[page.titleKey] : '';
    const fallbackSubtitle = page.subtitleKey ? settings.value[page.subtitleKey] : '';

    form.title = settings.value[titleKey.value] || fallbackTitle || page.defaultTitle;
    form.subtitle = settings.value[subtitleKey.value] || fallbackSubtitle || page.defaultSubtitle;
    form.image = settings.value[imageKey.value] || '';
    form.opacity = Number(settings.value[opacityKey.value] ?? (isHome.value ? 22 : 76));

    originalState.title = form.title;
    originalState.subtitle = form.subtitle;
    originalState.image = form.image;
    originalState.opacity = form.opacity;

    selectedFile.value = null;
    removeImage.value = false;
    clearPreviewUrl();
  };

  const selectImage = (file) => {
    selectedFile.value = file;
    removeImage.value = false;
    clearPreviewUrl();
    if (file) {
      localPreviewUrl.value = URL.createObjectURL(file);
    }
  };

  const clearImage = () => {
    selectedFile.value = null;
    removeImage.value = true;
    clearPreviewUrl();
  };

  const resetText = () => {
    form.title = currentPage.value.titleKey
      ? settings.value[currentPage.value.titleKey] || currentPage.value.defaultTitle
      : currentPage.value.defaultTitle;
    form.subtitle = currentPage.value.subtitleKey
      ? settings.value[currentPage.value.subtitleKey] || currentPage.value.defaultSubtitle
      : currentPage.value.defaultSubtitle;
  };

  const loadSettings = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await client.get('/admin/settings');
      settings.value = response.data?.data || {};
      loadSelectedPage();
    } catch (err) {
      console.error('Failed to load page hero settings:', err);
      error.value = 'Failed to load page hero settings from server.';
      toast.error('Could not load page hero settings.');
    } finally {
      loading.value = false;
    }
  };

  const save = async () => {
    if (!form.title.trim() || !form.subtitle.trim()) {
      toast.error('A hero title and subtitle are required.');
      return false;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      formData.append(`settings[${titleKey.value}]`, form.title.trim());
      formData.append(`settings[${subtitleKey.value}]`, form.subtitle.trim());
      formData.append(`settings[${opacityKey.value}]`, String(form.opacity));
      formData.append('page_hero_page', selectedPage.value);

      if (selectedFile.value) {
        formData.append('page_hero_image', selectedFile.value);
      } else if (removeImage.value) {
        formData.append(`settings[${imageKey.value}]`, '');
      }

      const response = await client.post('/admin/settings', formData);
      settings.value = response.data?.data || settings.value;
      loadSelectedPage();
      toast.success(`${currentPage.value.label} hero saved.`);
      return true;
    } catch (err) {
      console.error(`Failed to save ${currentPage.value.label} hero:`, err);
      toast.error(`Could not save the ${currentPage.value.label} hero.`);
      return false;
    } finally {
      saving.value = false;
    }
  };

  onMounted(() => {
    loadSettings();
  });

  onBeforeUnmount(() => {
    clearPreviewUrl();
  });

  return {
    pages: HERO_PAGES,
    settings,
    loading,
    saving,
    error,
    selectedPage,
    currentPage,
    form,
    selectedFile,
    removeImage,
    previewImage,
    hasChanges,
    loadSelectedPage,
    selectImage,
    clearImage,
    resetText,
    loadSettings,
    save
  };
}

