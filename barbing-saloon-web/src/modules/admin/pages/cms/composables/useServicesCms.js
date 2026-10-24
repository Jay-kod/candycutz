import { ref, computed } from 'vue';
import { useToast } from '@/core/composables/useToast';
import api from '@/shared/api/client';

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

  const isDirty = computed(() => {
    if (!initialSnapshot.value) return false;
    return initialSnapshot.value !== JSON.stringify(content.value);
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
        initialSnapshot.value = JSON.stringify(content.value);
        lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
      }
    } catch (error) {
      console.error('Failed to load services CMS settings:', error);
    } finally {
      loading.value = false;
    }
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

      await api.post('/v1/admin/settings', formData);
      toast.success('Services page content saved successfully!');
      
      initialSnapshot.value = JSON.stringify(content.value);
      lastSaved.value = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } catch (error) {
      console.error('Error saving services content:', error);
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
    catalogServices,
    catalogLoading,
    categories,
    fetchContent,
    fetchCatalog,
    saveContent
  };
}
