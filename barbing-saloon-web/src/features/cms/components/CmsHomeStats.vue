<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Hero Banner & Opacity -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Hero Showcase</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <PhotoIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ heroOpacity }}%</span>
        <span class="text-xs text-white/40">image opacity</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ ctaLabels }}</span>
        <span 
          class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-mono"
          :class="hasCustomHeroImage ? 'bg-emerald-500/10 text-emerald-400' : 'bg-white/5 text-white/40'"
        >
          {{ hasCustomHeroImage ? 'Custom Asset' : 'Default Asset' }}
        </span>
      </div>
    </div>

    <!-- Card 2: Social Proof Counters -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Proof Metrics</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <SparklesIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-emerald-400 tracking-tight">4 Counters</span>
        <span class="text-xs text-white/40">active</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ metricsSummary }}</span>
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
      </div>
    </div>

    <!-- Card 3: Philosophy Teaser -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-amber-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-500/5 blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Brand Philosophy</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <BookmarkSquareIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-2xl font-bold text-amber-400 tracking-tight truncate">
          {{ settings?.about_teaser_subtitle || 'Philosophy' }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ settings?.about_teaser_title || 'About Section' }}</span>
        <span 
          class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-mono"
          :class="hasCustomAboutImage ? 'bg-amber-500/15 text-amber-300' : 'bg-white/5 text-white/40'"
        >
          {{ hasCustomAboutImage ? 'Custom Asset' : 'Default Asset' }}
        </span>
      </div>
    </div>

    <!-- Card 4: Customer Portal Gateway -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Portal Gateway</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 border border-sky-500/30">
          <UserGroupIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-2xl font-bold text-white tracking-tight truncate">
          {{ settings?.portal_teaser_title || 'Customer Portal' }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">Onboarding & Booking Gateway</span>
        <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  PhotoIcon, 
  SparklesIcon, 
  BookmarkSquareIcon, 
  UserGroupIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({})
  },
  currentHeroImage: {
    type: String,
    default: null
  },
  currentAboutImage: {
    type: String,
    default: null
  }
});

const heroOpacity = computed(() => {
  const val = props.settings?.hero_image_opacity;
  return (val !== undefined && val !== null) ? val : 22;
});

const ctaLabels = computed(() => {
  const btn1 = props.settings?.hero_btn1_text || 'Book';
  const btn2 = props.settings?.hero_btn2_text || 'About';
  return `${btn1} • ${btn2}`;
});

const hasCustomHeroImage = computed(() => !!props.currentHeroImage);
const hasCustomAboutImage = computed(() => !!props.currentAboutImage);

const metricsSummary = computed(() => {
  const years = props.settings?.stats_years || '10+';
  const barbers = props.settings?.stats_barbers || '5+';
  const clients = props.settings?.stats_clients || '5k+';
  return `${years} yrs • ${barbers} barbers • ${clients} clients`;
});
</script>
