<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Menu Headline Hook -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Menu Headline</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <SparklesIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-2xl font-bold text-white tracking-tight truncate">
          {{ content?.services_title || 'Our Premium Services' }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">Grooming Menu Hook</span>
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
      </div>
    </div>

    <!-- Card 2: Catalog Items -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Catalog Items</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <ScissorsIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-emerald-400 tracking-tight">{{ serviceCount }}</span>
        <span class="text-xs text-white/40">treatments</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">Bookable on Public Menu</span>
        <router-link to="/admin/services" class="text-emerald-400 hover:underline">Manage →</router-link>
      </div>
    </div>

    <!-- Card 3: Category Taxonomy -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-amber-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-500/5 blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Filter Categories</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <TagIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-amber-400 tracking-tight">{{ categoryCount }}</span>
        <span class="text-xs text-white/40">taxonomies</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ categorySnippet }}</span>
        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
      </div>
    </div>

    <!-- Card 4: Hero Presentation Link -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Page Hero</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 border border-sky-500/30">
          <PhotoIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-2xl font-bold text-white tracking-tight truncate">Grooming Hero</span>
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
  ScissorsIcon, 
  TagIcon, 
  PhotoIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  content: {
    type: Object,
    default: () => ({})
  },
  services: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  }
});

const serviceCount = computed(() => {
  return props.services?.length || 0;
});

const categoryCount = computed(() => {
  if (props.categories && props.categories.length > 0) {
    return props.categories.length;
  }
  return 3;
});

const categorySnippet = computed(() => {
  if (props.categories && props.categories.length > 0) {
    return props.categories.slice(0, 2).join(' · ');
  }
  return 'Haircuts · Beard Styling';
});
</script>
