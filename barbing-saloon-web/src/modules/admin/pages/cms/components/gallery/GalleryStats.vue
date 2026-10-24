<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Portfolio Headline -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Portfolio Headline</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <SparklesIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-2xl font-bold text-white tracking-tight truncate">
          {{ content?.gallery_title || 'Our Gallery' }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">Showcase Hook Active</span>
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
      </div>
    </div>

    <!-- Card 2: Hero Cover Asset -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Hero Cover Asset</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <PhotoIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-2xl font-bold text-emerald-400 tracking-tight truncate">
          {{ hasCover ? 'Custom Cover' : 'Default Cover' }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Overlay Dim: {{ heroOpacity }}%</span>
        <span 
          class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-mono"
          :class="hasCover ? 'bg-emerald-500/10 text-emerald-400' : 'bg-white/5 text-white/40'"
        >
          {{ hasCover ? 'Active Asset' : 'Fallback' }}
        </span>
      </div>
    </div>

    <!-- Card 3: Portfolio Photos -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-amber-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-500/5 blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Portfolio Media</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <FilmIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-amber-400 tracking-tight">{{ itemCount }}</span>
        <span class="text-xs text-white/40">photos</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ categorySnippet }}</span>
        <router-link to="/admin/gallery" class="text-amber-400 hover:underline">Manage →</router-link>
      </div>
    </div>

    <!-- Card 4: Hero Presentation Link -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Page Hero</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 border border-sky-500/30">
          <EyeIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-2xl font-bold text-white tracking-tight truncate">Gallery Hero</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <router-link to="/admin/websites/heroes" class="text-sky-400 hover:underline">Page Heroes Studio →</router-link>
        <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  SparklesIcon, 
  PhotoIcon, 
  FilmIcon, 
  EyeIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  content: {
    type: Object,
    default: () => ({})
  },
  heroImage: {
    type: String,
    default: ''
  },
  heroOpacity: {
    type: Number,
    default: 76
  },
  items: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  }
});

const hasCover = computed(() => !!props.heroImage);
const itemCount = computed(() => props.items?.length || 0);

const categorySnippet = computed(() => {
  if (props.categories && props.categories.length > 0) {
    return props.categories.slice(0, 2).join(' · ');
  }
  return 'Haircuts · Transformations';
});
</script>
