import { ref, reactive, computed, onMounted } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export const COOKIE_CATEGORIES = [
  {
    key: 'necessary',
    label: 'Strictly Necessary',
    titleKey: 'cookie_necessary_title',
    descriptionKey: 'cookie_necessary_description',
    required: true,
    badgeText: 'Always Active • Core Site Security',
  },
  {
    key: 'preferences',
    label: 'User Preferences',
    titleKey: 'cookie_preferences_title',
    descriptionKey: 'cookie_preferences_description',
    required: false,
    badgeText: 'Optional • Remembers User Choices',
  },
  {
    key: 'analytics',
    label: 'Performance & Analytics',
    titleKey: 'cookie_analytics_title',
    descriptionKey: 'cookie_analytics_description',
    required: false,
    badgeText: 'Optional • Usage Telemetry',
  },
  {
    key: 'marketing',
    label: 'Marketing & Outreach',
    titleKey: 'cookie_marketing_title',
    descriptionKey: 'cookie_marketing_description',
    required: false,
    badgeText: 'Optional • Campaign Attribution',
  },
];

export const DEFAULT_COOKIE_CONTENT = {
  cookie_title: 'Manage Cookies',
  cookie_intro: 'Choose which optional cookie categories you want to allow. Necessary storage is always active so the site can provide core features and preserve booking security.',
  cookie_necessary_title: 'Necessary',
  cookie_necessary_description: 'Required for core site functions such as keeping your session secure, verifying CSRF tokens, and handling appointments. These cannot be switched off.',
  cookie_preferences_title: 'Preferences',
  cookie_preferences_description: 'Remember customized preferences such as your favorite barbers, preferred theme, and local calendar options.',
  cookie_analytics_title: 'Analytics',
  cookie_analytics_description: 'Allow anonymous telemetry to help measure site performance, response latency, and booking flow effectiveness.',
  cookie_marketing_title: 'Marketing',
  cookie_marketing_description: 'Allow promotional attribution for exclusive VIP deals, seasonal styling discounts, and loyalty events.',
};

export function useAdminCookieCms() {
  const toast = useToast();

  const loading = ref(true);
  const saving = ref(false);
  const error = ref(null);
  const activeView = ref('editor'); // 'editor' | 'preview'

  const content = reactive({ ...DEFAULT_COOKIE_CONTENT });
  const originalSnapshot = ref('');

  const computeSnapshot = () => JSON.stringify(content);

  const hasChanges = computed(() => {
    return originalSnapshot.value !== '' && originalSnapshot.value !== computeSnapshot();
  });

  const resetToDefaults = () => {
    for (const [key, value] of Object.entries(DEFAULT_COOKIE_CONTENT)) {
      content[key] = value;
    }
    toast.info('Cookie preferences reset to factory defaults. Click "Save Changes" to publish.');
  };

  const fetchContent = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await client.get('/admin/settings');
      const data = response.data?.data || {};

      for (const key of Object.keys(content)) {
        if (typeof data[key] === 'string' && data[key].trim()) {
          content[key] = data[key];
        }
      }

      originalSnapshot.value = computeSnapshot();
    } catch (err) {
      console.error('Failed to load cookie preferences content:', err);
      error.value = 'Failed to load cookie preferences from server.';
      toast.error('Could not load cookie preferences content.');
    } finally {
      loading.value = false;
    }
  };

  const saveContent = async () => {
    if (!content.cookie_title?.trim() || !content.cookie_intro?.trim()) {
      toast.error('Add a page title and introduction before saving.');
      return false;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      for (const [key, value] of Object.entries(content)) {
        formData.append(`settings[${key}]`, value.trim());
      }

      await client.post('/admin/settings', formData);

      originalSnapshot.value = computeSnapshot();
      toast.success('Cookie preferences page saved successfully.');
      return true;
    } catch (err) {
      console.error('Failed to save cookie preferences content:', err);
      toast.error('Could not save cookie preferences changes.');
      return false;
    } finally {
      saving.value = false;
    }
  };

  onMounted(() => {
    fetchContent();
  });

  return {
    categories: COOKIE_CATEGORIES,
    loading,
    saving,
    error,
    activeView,
    content,
    hasChanges,
    resetToDefaults,
    fetchContent,
    saveContent
  };
}

