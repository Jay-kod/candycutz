import { ref, computed, onMounted, watch } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useAdminLegalEditor(props) {
  const toast = useToast();

  const loading = ref(true);
  const saving = ref(false);
  const error = ref(null);
  const activeView = ref('editor'); // 'editor' | 'preview'

  const title = ref(props.pageTitle || 'Legal Documentation');
  const sections = ref((props.defaultSections || []).map(s => ({ ...s })));

  const titleKey = computed(() => `${props.settingPrefix}_title`);
  const sectionsKey = computed(() => `${props.settingPrefix}_sections`);

  const originalSnapshot = ref('');

  const computeSnapshot = () => {
    return JSON.stringify({
      title: title.value,
      sections: sections.value
    });
  };

  const hasChanges = computed(() => {
    return originalSnapshot.value !== '' && originalSnapshot.value !== computeSnapshot();
  });

  const addSection = () => {
    sections.value.push({
      heading: '',
      body: ''
    });
  };

  const removeSection = (index) => {
    if (sections.value.length > 1) {
      sections.value.splice(index, 1);
    } else {
      toast.error('The document must retain at least one legal section.');
    }
  };

  const resetToDefaults = () => {
    title.value = props.pageTitle || 'Legal Documentation';
    sections.value = (props.defaultSections || []).map(s => ({ ...s }));
    toast.info('Reset to default document template. Click "Save Changes" to publish.');
  };

  const fetchContent = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await client.get('/admin/settings');
      const data = response.data?.data || {};

      if (typeof data[titleKey.value] === 'string' && data[titleKey.value].trim()) {
        title.value = data[titleKey.value];
      }

      if (data[sectionsKey.value]) {
        try {
          const parsed = typeof data[sectionsKey.value] === 'string'
            ? JSON.parse(data[sectionsKey.value])
            : data[sectionsKey.value];
          if (Array.isArray(parsed) && parsed.length > 0) {
            sections.value = parsed.map(s => ({
              heading: s.heading || '',
              body: s.body || ''
            }));
          }
        } catch (e) {
          console.warn(`Could not parse ${sectionsKey.value}:`, e);
        }
      }

      originalSnapshot.value = computeSnapshot();
    } catch (err) {
      console.error(`Failed to load ${props.pageTitle} content:`, err);
      error.value = `Failed to load ${props.pageTitle} settings from server.`;
      toast.error(`Could not load ${props.pageTitle} content.`);
    } finally {
      loading.value = false;
    }
  };

  const saveContent = async () => {
    if (!title.value.trim()) {
      toast.error('A document title is required before saving.');
      return false;
    }

    const invalidSection = sections.value.find(s => !s.heading.trim());
    if (invalidSection) {
      toast.error('Each legal article requires a section heading.');
      return false;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      formData.append(`settings[${titleKey.value}]`, title.value.trim());
      formData.append(`settings[${sectionsKey.value}]`, JSON.stringify(sections.value));

      await client.post('/admin/settings', formData);

      originalSnapshot.value = computeSnapshot();
      toast.success(`${props.pageTitle} document saved.`);
      return true;
    } catch (err) {
      console.error(`Failed to save ${props.pageTitle} content:`, err);
      toast.error(`Could not save ${props.pageTitle} changes.`);
      return false;
    } finally {
      saving.value = false;
    }
  };

  watch(() => props.settingPrefix, () => {
    title.value = props.pageTitle || 'Legal Documentation';
    sections.value = (props.defaultSections || []).map(s => ({ ...s }));
    fetchContent();
  });

  onMounted(() => {
    fetchContent();
  });

  return {
    loading,
    saving,
    error,
    activeView,
    title,
    sections,
    hasChanges,
    addSection,
    removeSection,
    resetToDefaults,
    fetchContent,
    saveContent
  };
}

