<template>
  <div class="space-y-8 animate-fade-in pb-12">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 lg:p-12 shadow-2xl">
      <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/10 blur-3xl"></div>
      <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div>
          <p class="text-xs uppercase tracking-[0.3em] text-admin/80 font-bold flex items-center gap-2">
            <SparklesIcon class="w-4 h-4" /> Homepage Component
          </p>
          <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text drop-shadow-lg">
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Features Block</span>
          </h1>
          <p class="mt-3 text-sm lg:text-base text-ivory/60 max-w-xl">
            Manage the @efferd/features-1 block displayed on your website homepage. Customise badges, headlines, and value propositions.
          </p>
        </div>
        
        <div class="flex items-center gap-3">
          <button 
            @click="resetDefaults"
            type="button"
            class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-ivory/70 hover:bg-white/10 hover:text-theme-text transition-all"
          >
            <ArrowPathIcon class="w-4 h-4" />
            Reset Defaults
          </button>
          
          <button 
            @click="saveContent" 
            :disabled="saving" 
            class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-6 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
            <CheckIcon v-else class="h-5 w-5" />
            {{ saving ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Live Preview Card -->
    <div class="rounded-2xl border border-admin/10 bg-black/40 backdrop-blur-md overflow-hidden shadow-xl">
      <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-5 flex items-center justify-between">
        <div>
          <h2 class="font-display text-xl text-theme-text flex items-center gap-2">
            <EyeIcon class="w-5 h-5 text-admin" /> Live Preview
          </h2>
          <p class="mt-0.5 text-xs text-ivory/40">Real-time simulation of the features section as it appears on your public website.</p>
        </div>
        <span class="px-2.5 py-1 text-[10px] uppercase font-bold tracking-widest rounded-full bg-admin/10 text-admin border border-admin/20">
          @efferd/features-1
        </span>
      </div>
      
      <div class="p-6 sm:p-8 bg-obsidian/70">
        <FeaturesSection 
          :badge="content.features_badge"
          :title="content.features_title"
          :subtitle="content.features_subtitle"
          :features="items"
        />
      </div>
    </div>

    <!-- Section Heading Configuration -->
    <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
      <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6">
        <h2 class="font-display text-xl text-theme-text">Section Heading & Copy</h2>
        <p class="mt-1 text-xs text-ivory/40">Controls the badge, main heading, and narrative lead for the section.</p>
      </div>
      
      <div class="p-8 space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Badge Text</label>
            <input 
              v-model="content.features_badge" 
              type="text" 
              placeholder="e.g. WHY CHOOSE CANDYCUTZ"
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" 
            />
          </div>
          <div>
            <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Main Heading</label>
            <input 
              v-model="content.features_title" 
              type="text" 
              placeholder="e.g. Engineered for Perfection"
              class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" 
            />
          </div>
        </div>

        <div>
          <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Subtitle / Supporting Description</label>
          <textarea 
            v-model="content.features_subtitle" 
            rows="3" 
            placeholder="e.g. Every detail of the CandyCutz experience is calibrated to deliver the pinnacle of men’s grooming."
            class="w-full rounded-xl border border-white/10 bg-white/5 px-5 py-3.5 text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-none"
          ></textarea>
        </div>
      </div>
    </div>

    <!-- Feature Cards Manager -->
    <div class="rounded-2xl border border-admin/10 bg-black/30 backdrop-blur-md overflow-hidden shadow-xl">
      <div class="border-b border-white/5 bg-gradient-to-r from-admin/10 to-transparent px-8 py-6 flex items-center justify-between">
        <div>
          <h2 class="font-display text-xl text-theme-text">Feature Columns ({{ items.length }})</h2>
          <p class="mt-1 text-xs text-ivory/40">Customise individual feature cards, their icons, titles, and descriptions.</p>
        </div>
        
        <button 
          @click="addFeature"
          type="button"
          class="flex items-center gap-2 rounded-xl bg-admin/10 border border-admin/30 px-4 py-2 text-xs font-bold uppercase tracking-wider text-admin hover:bg-admin/20 transition-all"
        >
          <PlusIcon class="w-4 h-4" /> Add Feature
        </button>
      </div>

      <div class="p-8 space-y-6">
        <div 
          v-for="(item, idx) in items" 
          :key="idx"
          class="rounded-xl border border-white/10 bg-white/[0.02] p-6 relative transition-all hover:border-admin/30 group"
        >
          <!-- Item Card Header -->
          <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/5">
            <div class="flex items-center gap-3">
              <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-admin/20 border border-admin/40 text-admin font-display text-xs font-bold">
                {{ idx + 1 }}
              </span>
              <span class="text-sm font-semibold text-theme-text">Feature #{{ idx + 1 }}</span>
            </div>
            
            <button 
              v-if="items.length > 1"
              @click="removeFeature(idx)"
              type="button"
              class="text-ivory/40 hover:text-red-400 p-1.5 rounded-lg hover:bg-red-500/10 transition-colors"
              title="Remove this feature"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>

          <div class="grid md:grid-cols-12 gap-6">
            <!-- Icon Picker -->
            <div class="md:col-span-4 space-y-3">
              <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold">Select Icon</label>
              
              <!-- Current Selected Icon Display -->
              <div class="flex items-center gap-3 p-3 rounded-xl border border-white/10 bg-black/40">
                <div class="w-10 h-10 rounded-lg bg-gold/10 border border-gold/30 flex items-center justify-center text-gold">
                  <component :is="resolveIconComponent(item.icon)" class="w-5 h-5" />
                </div>
                <div>
                  <p class="text-xs font-bold text-theme-text capitalize">{{ item.icon || 'scissors' }}</p>
                  <p class="text-[10px] text-ivory/40">Active Icon</p>
                </div>
              </div>

              <!-- Icon Options Grid -->
              <div class="grid grid-cols-5 gap-2 pt-1">
                <button
                  v-for="opt in availableIcons"
                  :key="opt.name"
                  type="button"
                  @click="item.icon = opt.name"
                  :title="opt.name"
                  :class="[
                    'flex items-center justify-center p-2 rounded-lg border transition-all',
                    item.icon === opt.name 
                      ? 'border-admin bg-admin/20 text-admin shadow-sm shadow-admin/30' 
                      : 'border-white/5 bg-white/5 text-ivory/50 hover:bg-white/10 hover:text-theme-text'
                  ]"
                >
                  <component :is="opt.comp" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Content Inputs -->
            <div class="md:col-span-8 space-y-4">
              <div>
                <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Feature Title</label>
                <input 
                  v-model="item.title" 
                  type="text" 
                  placeholder="e.g. Precision Craft"
                  class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all" 
                />
              </div>

              <div>
                <label class="block text-xs uppercase tracking-widest text-ivory/50 font-bold mb-2">Description</label>
                <textarea 
                  v-model="item.description" 
                  rows="2" 
                  placeholder="e.g. Master barbers dedicated to flawless fades, lines, and custom contours."
                  class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-theme-text placeholder-ivory/30 focus:border-admin/50 focus:ring-2 focus:ring-admin/20 focus:outline-none transition-all resize-none"
                ></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom Action Bar -->
      <div class="border-t border-white/5 bg-black/20 px-8 py-5 flex items-center justify-between">
        <button 
          @click="addFeature"
          type="button"
          class="flex items-center gap-2 text-xs font-bold text-admin hover:text-admin-light transition-colors uppercase tracking-wider"
        >
          <PlusIcon class="w-4 h-4" /> Add Another Feature
        </button>
        
        <button 
          @click="saveContent" 
          :disabled="saving" 
          class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-8 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.4)] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
          <CheckIcon v-else class="h-5 w-5" />
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from '@/core/composables/useToast';
import { adminApi } from '@/shared/api/old_adminApi';
import FeaturesSection from '@/components/FeaturesSection.vue';
import {
  SparklesIcon,
  CheckIcon,
  PlusIcon,
  TrashIcon,
  ArrowPathIcon,
  EyeIcon,
} from '@heroicons/vue/24/outline';
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

const toast = useToast();
const saving = ref(false);

const availableIcons = [
  { name: 'scissors', comp: Scissors },
  { name: 'crown', comp: Crown },
  { name: 'zap', comp: Zap },
  { name: 'shield-check', comp: ShieldCheck },
  { name: 'star', comp: Star },
  { name: 'award', comp: Award },
  { name: 'clock', comp: Clock },
  { name: 'sparkles', comp: Sparkles },
  { name: 'heart', comp: Heart },
  { name: 'flame', comp: Flame },
  { name: 'globe', comp: Globe },
  { name: 'activity', comp: Activity },
  { name: 'layers', comp: Layers },
  { name: 'calendar', comp: Calendar },
  { name: 'smartphone', comp: Smartphone },
];

const iconMap = {
  zap: Zap,
  shield: ShieldCheck,
  'shield-check': ShieldCheck,
  activity: Activity,
  globe: Globe,
  scissors: Scissors,
  sparkles: Sparkles,
  clock: Clock,
  award: Award,
  star: Star,
  crown: Crown,
  heart: Heart,
  flame: Flame,
  layers: Layers,
  calendar: Calendar,
  smartphone: Smartphone,
  check: CheckCircle,
};

const resolveIconComponent = (name) => {
  if (!name) return Sparkles;
  const key = String(name).toLowerCase().trim();
  return iconMap[key] || Sparkles;
};

const defaultFeatures = [
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

const content = ref({
  features_badge: 'WHY CHOOSE CANDYCUTZ',
  features_title: 'Engineered for Perfection',
  features_subtitle: 'Every detail of the CandyCutz experience is calibrated to deliver the pinnacle of men’s grooming.',
});

const items = ref([...defaultFeatures.map(item => ({ ...item }))]);

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
  }
};

const resetDefaults = () => {
  content.value = {
    features_badge: 'WHY CHOOSE CANDYCUTZ',
    features_title: 'Engineered for Perfection',
    features_subtitle: 'Every detail of the CandyCutz experience is calibrated to deliver the pinnacle of men’s grooming.',
  };
  items.value = [...defaultFeatures.map(item => ({ ...item }))];
  toast.info('Features reset to default configuration. Click "Save Changes" to apply.');
};

const fetchContent = async () => {
  try {
    const response = await adminApi.settings();
    const data = response.data?.data;
    if (data) {
      if (data.features_badge) content.value.features_badge = data.features_badge;
      if (data.features_title) content.value.features_title = data.features_title;
      if (data.features_subtitle) content.value.features_subtitle = data.features_subtitle;

      if (data.features_items) {
        try {
          const parsed = typeof data.features_items === 'string' 
            ? JSON.parse(data.features_items) 
            : data.features_items;
          if (Array.isArray(parsed) && parsed.length > 0) {
            items.value = parsed;
          }
        } catch (e) {
          console.warn('Could not parse features_items:', e);
        }
      }
    }
  } catch (error) {
    console.error('Failed to load features content:', error);
  }
};

const saveContent = async () => {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('settings[features_badge]', content.value.features_badge || '');
    formData.append('settings[features_title]', content.value.features_title || '');
    formData.append('settings[features_subtitle]', content.value.features_subtitle || '');
    formData.append('settings[features_items]', JSON.stringify(items.value));

    await adminApi.updateSettings(formData);
    toast.success('Features block content saved successfully!');
  } catch (error) {
    console.error('Error saving features content:', error);
    toast.error('Failed to save changes.');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchContent();
});
</script>
