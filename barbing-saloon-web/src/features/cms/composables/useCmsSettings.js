import { ref, computed } from 'vue';
import { getActivePinia } from 'pinia';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';
import { getStorageUrl as resolveStorageUrl } from '@/core/utils/url';
import { DEFAULT_OVERLAY_PRESETS } from '@/core/utils/heroOverlay';
import { usePublicContentStore } from '@/core/stores/publicContent.store';

export function useCmsSettings() {
  const toast = useToast();
  const saving = ref(false);
  const loading = ref(false);
  const lastSaved = ref(null);
  const initialSnapshot = ref(null);
  const activeTab = ref('all');

  const splitHeadline = (title) => {
    const normalizedTitle = String(title || '').replace(/\s*\n\s*/g, ' ').trim();
    const sentenceBreak = normalizedTitle.match(/^(.+?[.!?])\s+(.+)$/);
    if (sentenceBreak) return [sentenceBreak[1], sentenceBreak[2]];

    const words = normalizedTitle.split(/\s+/);
    if (words.length < 2) return [normalizedTitle, ''];
    const midpoint = Math.ceil(words.length / 2);
    return [words.slice(0, midpoint).join(' '), words.slice(midpoint).join(' ')];
  };

  const settings = ref({
    // Hero Headline & Dual-Color Phrasing
    cta_title: 'Fresh Cut. Zero Stress.',
    cta_title_part_one: 'Fresh Cut.',
    cta_title_part_two: 'Zero Stress.',
    cta_title_color_order: 'first-white',
    cta_subtitle: 'Pick your barber, choose your style, lock your time.',
    cta_caption: 'Expert barbers. Fresh fades. A better kind of barbershop experience.',
    cta_primary_text: 'Book your cut',
    // Legacy / Generic Hero Keys (mirrored & synchronized)
    hero_title: 'Fresh Cut. Zero Stress.',
    hero_subtitle: 'Pick your barber, choose your style, lock your time.',
    hero_btn1_text: 'Book your cut',
    hero_btn2_text: 'About Us',
    hero_image_opacity: 22,
    hero_overlay_opacity: 45,
    hero_overlay_color: '#D4AF37',
    hero_overlay_presets: JSON.stringify(DEFAULT_OVERLAY_PRESETS),
    // Proof Stats
    stats_years: '10+',
    stats_barbers: '5+',
    stats_services: '20+',
    stats_clients: '5k+',
    // About Teaser
    about_teaser_title: 'Our Philosophy',
    about_teaser_subtitle: 'A Sharper Standard',
    about_teaser_text: "Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut. Our shop combines traditional barbering techniques with modern trends in a relaxing, luxurious environment.",
    // Portal Teaser
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

  const isDirty = computed(() => {
    if (heroImageFile.value || aboutImageFile.value) return true;
    if (!initialSnapshot.value) return false;
    return initialSnapshot.value !== JSON.stringify(settings.value);
  });

  const getStorageUrl = (path) => {
    return resolveStorageUrl(path);
  };

  const fetchSettings = async () => {
    loading.value = true;
    try {
      const response = await api.get('/v1/admin/settings');
      const data = response.data.data;
      if (data) {
        Object.keys(settings.value).forEach(key => {
          if (data[key] !== undefined && data[key] !== null) {
            settings.value[key] = key === 'hero_image_opacity'
              ? Number(data[key])
              : data[key];
          }
        });

        // Headline & CTA Phrasing Resolution
        const resolvedTitle = data.cta_title || data.hero_title || settings.value.cta_title;
        settings.value.cta_title = resolvedTitle;
        const titleParts = splitHeadline(resolvedTitle);
        settings.value.cta_title_part_one = data.cta_title_part_one ?? titleParts[0];
        settings.value.cta_title_part_two = data.cta_title_part_two ?? titleParts[1];
        settings.value.cta_title_color_order = data.cta_title_color_order === 'first-gold'
          ? 'first-gold'
          : 'first-white';
        settings.value.cta_subtitle = data.cta_subtitle || data.hero_subtitle || settings.value.cta_subtitle;
        settings.value.cta_caption = data.cta_caption ?? settings.value.cta_caption ?? '';
        settings.value.cta_primary_text = data.cta_primary_text || data.hero_btn1_text || settings.value.cta_primary_text;
        
        // Synchronize legacy keys
        settings.value.hero_title = settings.value.cta_title;
        settings.value.hero_subtitle = settings.value.cta_subtitle;
        settings.value.hero_btn1_text = settings.value.cta_primary_text;
        settings.value.hero_btn2_text = data.hero_btn2_text || settings.value.hero_btn2_text || 'About Us';

        // Opacity & Color Resolution
        if (data.hero_image_opacity !== undefined && data.hero_image_opacity !== null) {
          settings.value.hero_image_opacity = Number(data.hero_image_opacity);
        } else if (data.hero_opacity !== undefined && data.hero_opacity !== null) {
          settings.value.hero_image_opacity = Number(data.hero_opacity);
        }
        if (data.hero_overlay_opacity !== undefined && data.hero_overlay_opacity !== null) {
          settings.value.hero_overlay_opacity = Number(data.hero_overlay_opacity);
        } else if (data.page_hero_home_overlay_opacity !== undefined && data.page_hero_home_overlay_opacity !== null) {
          settings.value.hero_overlay_opacity = Number(data.page_hero_home_overlay_opacity);
        } else {
          settings.value.hero_overlay_opacity = 45;
        }
        settings.value.hero_overlay_color = data.hero_overlay_color || data.page_hero_home_overlay_color || '#D4AF37';
        if (data.hero_overlay_presets !== undefined && data.hero_overlay_presets !== null) {
          settings.value.hero_overlay_presets = typeof data.hero_overlay_presets === 'string'
            ? data.hero_overlay_presets
            : JSON.stringify(data.hero_overlay_presets);
        } else {
          settings.value.hero_overlay_presets = JSON.stringify(DEFAULT_OVERLAY_PRESETS);
        }

        currentHeroImage.value = data.hero_image || null;
        currentAboutImage.value = data.about_teaser_image || null;
        initialSnapshot.value = JSON.stringify(settings.value);
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
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
    // Validate required fields
    const p1 = (settings.value.cta_title_part_one || '').trim();
    const p2 = (settings.value.cta_title_part_two || '').trim();
    const sub = (settings.value.cta_subtitle || settings.value.hero_subtitle || '').trim();
    const btn1 = (settings.value.cta_primary_text || settings.value.hero_btn1_text || '').trim();

    if ((!p1 && !settings.value.hero_title) || !sub || !btn1) {
      toast.error('Headline, supporting line, and primary button label are required.');
      return;
    }

    saving.value = true;
    try {
      // Reconcile and synchronize headline
      if (p1 && p2) {
        settings.value.cta_title = `${p1} ${p2}`;
        settings.value.hero_title = settings.value.cta_title;
      } else if (settings.value.hero_title) {
        settings.value.cta_title = settings.value.hero_title;
      }
      settings.value.hero_subtitle = sub;
      settings.value.cta_subtitle = sub;
      settings.value.hero_btn1_text = btn1;
      settings.value.cta_primary_text = btn1;

      const formData = new FormData();
      Object.keys(settings.value).forEach(key => {
        formData.append(`settings[${key}]`, settings.value[key]);
      });

      // Explicit synchronization to satisfy both cta_* and hero_* consumers
      formData.append('settings[cta_title]', settings.value.cta_title);
      formData.append('settings[cta_title_part_one]', settings.value.cta_title_part_one);
      formData.append('settings[cta_title_part_two]', settings.value.cta_title_part_two);
      formData.append('settings[cta_title_color_order]', settings.value.cta_title_color_order || 'first-white');
      formData.append('settings[cta_subtitle]', settings.value.cta_subtitle);
      formData.append('settings[cta_caption]', settings.value.cta_caption || '');
      formData.append('settings[cta_primary_text]', settings.value.cta_primary_text);
      formData.append('settings[hero_title]', settings.value.hero_title);
      formData.append('settings[hero_subtitle]', settings.value.hero_subtitle);
      formData.append('settings[hero_btn1_text]', settings.value.hero_btn1_text);
      formData.append('settings[hero_btn2_text]', settings.value.hero_btn2_text || 'About Us');

      formData.append('settings[hero_opacity]', settings.value.hero_image_opacity);
      formData.append('settings[hero_overlay_opacity]', settings.value.hero_overlay_opacity);
      formData.append('settings[page_hero_home_overlay_opacity]', settings.value.hero_overlay_opacity);
      formData.append('settings[hero_overlay_color]', settings.value.hero_overlay_color || '#D4AF37');
      formData.append('settings[page_hero_home_overlay_color]', settings.value.hero_overlay_color || '#D4AF37');
      
      const presetsVal = typeof settings.value.hero_overlay_presets === 'string'
        ? settings.value.hero_overlay_presets
        : JSON.stringify(settings.value.hero_overlay_presets || DEFAULT_OVERLAY_PRESETS);
      formData.append('settings[hero_overlay_presets]', presetsVal);
      
      if (heroImageFile.value) {
        formData.append('hero_image', heroImageFile.value);
      }
      
      if (aboutImageFile.value) {
        formData.append('about_teaser_image', aboutImageFile.value);
      }

      const response = await api.post('/v1/admin/settings', formData);
      toast.success('Settings updated successfully');

      if (getActivePinia()) {
        usePublicContentStore().clearSettingsCache();
      }
      
      const updatedData = response.data.data;
      if (updatedData) {
        currentHeroImage.value = updatedData.hero_image || currentHeroImage.value;
        heroImageFile.value = null; 
        if (fileInputRef) fileInputRef.value = '';
        
        currentAboutImage.value = updatedData.about_teaser_image || currentAboutImage.value;
        aboutImageFile.value = null;
        if (aboutFileInputRef) aboutFileInputRef.value = '';

        initialSnapshot.value = JSON.stringify(settings.value);
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
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
    lastSaved,
    isDirty,
    activeTab,
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
