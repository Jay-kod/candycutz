<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Shop Narrative -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Shop Narrative</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <BookOpenIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-2xl font-bold text-white tracking-tight truncate">
          {{ content?.about_shop_subtitle || 'A Sharper Standard' }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ content?.about_shop_title || 'About Us' }}</span>
        <span 
          class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-mono"
          :class="hasCustomImage ? 'bg-emerald-500/10 text-emerald-400' : 'bg-white/5 text-white/40'"
        >
          {{ hasCustomImage ? 'Custom Asset' : 'Default Asset' }}
        </span>
      </div>
    </div>

    <!-- Card 2: Editorial Volume -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Editorial Volume</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <DocumentTextIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-emerald-400 tracking-tight">{{ totalWords }}</span>
        <span class="text-xs text-white/40">words</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>2 Editorial Paragraphs</span>
        <span class="text-[10px] font-mono text-white/40">{{ totalChars }} chars</span>
      </div>
    </div>

    <!-- Card 3: Team Showcase -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-amber-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-500/5 blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Team Showcase</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <UserGroupIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-2xl font-bold text-amber-400 tracking-tight truncate">
          {{ content?.about_team_title || 'Meet the team' }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">Master Barber Roster</span>
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
        <span class="font-display text-2xl font-bold text-white tracking-tight truncate">Our Story Hero</span>
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
  BookOpenIcon, 
  DocumentTextIcon, 
  UserGroupIcon, 
  PhotoIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  content: {
    type: Object,
    default: () => ({})
  },
  currentShopImage: {
    type: String,
    default: null
  }
});

const hasCustomImage = computed(() => !!props.currentShopImage);

const totalWords = computed(() => {
  const p1 = props.content?.about_shop_text_1 || '';
  const p2 = props.content?.about_shop_text_2 || '';
  const combined = `${p1} ${p2}`.trim();
  if (!combined) return 0;
  return combined.split(/\s+/).filter(Boolean).length;
});

const totalChars = computed(() => {
  const p1 = props.content?.about_shop_text_1 || '';
  const p2 = props.content?.about_shop_text_2 || '';
  return (p1 + p2).length;
});
</script>

