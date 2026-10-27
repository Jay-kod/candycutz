import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { applyBrandSettings, splitBrandName } from '@/shared/composables/useBrandIdentity';

export function useAdminBrandIdentity() {
  const route = useRoute();
  const toast = useToast();

  const loading = ref(true);
  const saving = ref(false);
  const error = ref(null);

  const form = reactive({
    first: 'Candy',
    second: 'Cutz',
    separator: 'none',
    goldPart: 'second',
    tagline: ''
  });

  const originalForm = reactive({
    first: 'Candy',
    second: 'Cutz',
    separator: 'none',
    goldPart: 'second',
    tagline: ''
  });

  const isAdmin = computed(() => {
    return !route?.path || route.path.startsWith('/admin');
  });

  const settingsEndpoint = computed(() => {
    return isAdmin.value ? '/admin/settings' : '/super-admin/settings';
  });

  const fullName = computed(() => {
    const f = form.first.trim();
    const s = form.second.trim();
    const sep = form.separator === 'space' ? ' ' : '';
    return `${f}${sep}${s}`.trim();
  });

  const hasChanges = computed(() => {
    return form.first !== originalForm.first ||
      form.second !== originalForm.second ||
      form.separator !== originalForm.separator ||
      form.goldPart !== originalForm.goldPart ||
      form.tagline !== originalForm.tagline;
  });

  const load = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await client.get(settingsEndpoint.value);
      const settings = response.data?.data || {};
      const name = settings.brand_name || settings.site_name || 'CandyCutz';
      const [first, second, separator] = splitBrandName(name);

      form.first = settings.brand_name_part_one ?? first;
      form.second = settings.brand_name_part_two ?? second;
      form.separator = settings.brand_name_separator ?? separator;
      form.goldPart = settings.brand_gold_part === 'first' ? 'first' : 'second';
      form.tagline = settings.brand_tagline || settings.site_description || '';

      // Cache snapshot for dirty-state detection
      originalForm.first = form.first;
      originalForm.second = form.second;
      originalForm.separator = form.separator;
      originalForm.goldPart = form.goldPart;
      originalForm.tagline = form.tagline;

      applyBrandSettings(settings);
    } catch (err) {
      console.error('Failed to load shop identity settings:', err);
      error.value = 'Could not load shop identity settings from server.';
      toast.error('Could not load shop identity settings.');
    } finally {
      loading.value = false;
    }
  };

  const save = async () => {
    const first = form.first.trim();
    const second = form.second.trim();

    if (!first || !second) {
      toast.error('Enter both parts of the shop name before saving.');
      return false;
    }

    saving.value = true;
    try {
      const name = `${first}${form.separator === 'space' ? ' ' : ''}${second}`;
      const formData = new FormData();
      formData.append('settings[brand_name]', name);
      formData.append('settings[site_name]', name);
      formData.append('settings[brand_name_part_one]', first);
      formData.append('settings[brand_name_part_two]', second);
      formData.append('settings[brand_name_separator]', form.separator);
      formData.append('settings[brand_gold_part]', form.goldPart);
      formData.append('settings[brand_tagline]', form.tagline.trim());
      formData.append('settings[site_description]', form.tagline.trim());

      await client.post(settingsEndpoint.value, formData);

      // Update baseline dirty-check state
      originalForm.first = form.first;
      originalForm.second = form.second;
      originalForm.separator = form.separator;
      originalForm.goldPart = form.goldPart;
      originalForm.tagline = form.tagline;

      // Sync active global brand identity across portals
      applyBrandSettings({
        brand_name: name,
        brand_name_part_one: first,
        brand_name_part_two: second,
        brand_name_separator: form.separator,
        brand_gold_part: form.goldPart,
        brand_tagline: form.tagline.trim(),
      });

      toast.success('Shop name and branding saved.');
      return true;
    } catch (err) {
      console.error('Failed to save shop identity settings:', err);
      toast.error('Could not save shop identity settings.');
      return false;
    } finally {
      saving.value = false;
    }
  };

  const reset = () => {
    form.first = originalForm.first;
    form.second = originalForm.second;
    form.separator = originalForm.separator;
    form.goldPart = originalForm.goldPart;
    form.tagline = originalForm.tagline;
  };

  onMounted(() => {
    load();
  });

  return {
    form,
    originalForm,
    loading,
    saving,
    error,
    hasChanges,
    fullName,
    isAdmin,
    load,
    save,
    reset
  };
}
