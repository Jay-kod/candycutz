import { ref, computed, onMounted, markRaw } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import {
  Zap,
  ShieldCheck,
  Activity,
  Globe,
  Scissors,
  Sparkles,
  Clock,
  Award,
  Star,
  Crown,
  Heart,
  Flame,
  Layers,
  Calendar,
  Smartphone,
  CheckCircle,
} from 'lucide-vue-next';

export const availableIcons = [
  { name: 'scissors', comp: markRaw(Scissors), label: 'Scissors' },
  { name: 'crown', comp: markRaw(Crown), label: 'Crown' },
  { name: 'zap', comp: markRaw(Zap), label: 'Lightning' },
  { name: 'shield-check', comp: markRaw(ShieldCheck), label: 'Shield' },
  { name: 'star', comp: markRaw(Star), label: 'Star' },
  { name: 'award', comp: markRaw(Award), label: 'Award' },
  { name: 'clock', comp: markRaw(Clock), label: 'Clock' },
  { name: 'sparkles', comp: markRaw(Sparkles), label: 'Sparkles' },
  { name: 'heart', comp: markRaw(Heart), label: 'Heart' },
  { name: 'flame', comp: markRaw(Flame), label: 'Flame' },
  { name: 'globe', comp: markRaw(Globe), label: 'Globe' },
  { name: 'activity', comp: markRaw(Activity), label: 'Pulse' },
  { name: 'layers', comp: markRaw(Layers), label: 'Layers' },
  { name: 'calendar', comp: markRaw(Calendar), label: 'Calendar' },
  { name: 'smartphone', comp: markRaw(Smartphone), label: 'Mobile' },
];

export const iconMap = {
  zap: markRaw(Zap),
  shield: markRaw(ShieldCheck),
  'shield-check': markRaw(ShieldCheck),
  activity: markRaw(Activity),
  globe: markRaw(Globe),
  scissors: markRaw(Scissors),
  sparkles: markRaw(Sparkles),
  clock: markRaw(Clock),
  award: markRaw(Award),
  star: markRaw(Star),
  crown: markRaw(Crown),
  heart: markRaw(Heart),
  flame: markRaw(Flame),
  layers: markRaw(Layers),
  calendar: markRaw(Calendar),
  smartphone: markRaw(Smartphone),
  check: markRaw(CheckCircle),
};

export const defaultFeatures = [
  {
    title: 'Precision Craft',
    icon: 'scissors',
    description: 'Master barbers dedicated to flawless fades, lines, and custom contours.',
  },
  {
    title: 'Luxury Lounge',
    icon: 'crown',
    description: 'Exclusive ambiance with bespoke amenities and complimentary refreshments.',
  },
  {
    title: 'Instant Booking',
    icon: 'zap',
    description: 'Real-time schedule availability and effortless queue coordination.',
  },
  {
    title: 'Sanitized & Safe',
    icon: 'shield-check',
    description: 'Hospital-grade tool sterilization and highest hygienic standards.',
  },
];

export const defaultContent = {
  features_badge: 'WHY CHOOSE CANDYCUTZ',
  features_title: 'Engineered for Perfection',
  features_subtitle: 'Every detail of the CandyCutz experience is calibrated to deliver the pinnacle of men’s grooming.',
};

export function useAdminFeaturesCms() {
  const toast = useToast();

  const loading = ref(true);
  const saving = ref(false);
  const error = ref(null);

  const content = ref({ ...defaultContent });
  const items = ref(defaultFeatures.map(item => ({ ...item })));

  const originalSnapshot = ref('');

  const computeSnapshot = () => {
    return JSON.stringify({
      content: content.value,
      items: items.value
    });
  };

  const hasChanges = computed(() => {
    return originalSnapshot.value !== '' && originalSnapshot.value !== computeSnapshot();
  });

  const resolveIconComponent = (name) => {
    if (!name) return Sparkles;
    const key = String(name).toLowerCase().trim();
    return iconMap[key] || Sparkles;
  };

  const addFeature = () => {
    items.value.push({
      title: 'New Feature',
      icon: 'star',
      description: 'Describe the feature and its unique benefit for your clients.',
    });
  };

  const removeFeature = (index) => {
    if (items.value.length > 1) {
      items.value.splice(index, 1);
    } else {
      toast.error('The features block must have at least one card.');
    }
  };

  const resetDefaults = () => {
    content.value = { ...defaultContent };
    items.value = defaultFeatures.map(item => ({ ...item }));
    toast.info('Features reset to default configuration. Click "Save Changes" to publish.');
  };

  const fetchContent = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await client.get('/admin/settings');
      const data = response.data?.data || {};

      if (data.features_badge) content.value.features_badge = data.features_badge;
      if (data.features_title) content.value.features_title = data.features_title;
      if (data.features_subtitle) content.value.features_subtitle = data.features_subtitle;

      if (data.features_items) {
        try {
          const parsed = typeof data.features_items === 'string'
            ? JSON.parse(data.features_items)
            : data.features_items;
          if (Array.isArray(parsed) && parsed.length > 0) {
            items.value = parsed.map(p => ({
              title: p.title || 'Feature',
              icon: p.icon || 'scissors',
              description: p.description || ''
            }));
          }
        } catch (e) {
          console.warn('Could not parse features_items:', e);
        }
      }

      originalSnapshot.value = computeSnapshot();
    } catch (err) {
      console.error('Failed to load features content:', err);
      error.value = 'Failed to load features content from server.';
      toast.error('Could not load features configuration.');
    } finally {
      loading.value = false;
    }
  };

  const saveContent = async () => {
    if (!content.value.features_title?.trim()) {
      toast.error('Main heading is required before saving.');
      return false;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      formData.append('settings[features_badge]', content.value.features_badge || '');
      formData.append('settings[features_title]', content.value.features_title || '');
      formData.append('settings[features_subtitle]', content.value.features_subtitle || '');
      formData.append('settings[features_items]', JSON.stringify(items.value));

      await client.post('/admin/settings', formData);

      originalSnapshot.value = computeSnapshot();
      toast.success('Features block content saved successfully!');
      return true;
    } catch (err) {
      console.error('Error saving features content:', err);
      toast.error('Failed to save changes.');
      return false;
    } finally {
      saving.value = false;
    }
  };

  onMounted(() => {
    fetchContent();
  });

  return {
    loading,
    saving,
    error,
    hasChanges,
    content,
    items,
    availableIcons,
    resolveIconComponent,
    addFeature,
    removeFeature,
    resetDefaults,
    fetchContent,
    saveContent
  };
}

