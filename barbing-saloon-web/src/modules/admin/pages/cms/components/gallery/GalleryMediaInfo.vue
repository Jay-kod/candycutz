<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 shadow-[0_0_15px_rgba(245,158,11,0.15)]">
          <FilmIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Portfolio Media Integration Bridge</h2>
          <p class="text-xs text-white/50 mt-0.5">Individual haircut transformations, customer showcases, and barber credits are managed in the Media Manager.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 02
      </span>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 items-center">
      <!-- Left 2 Cols: Explanatory & Feature Cards -->
      <div class="lg:col-span-2 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 space-y-1">
            <span class="text-[10px] uppercase font-bold text-white/40 tracking-wider">Total Portfolio</span>
            <p class="font-display text-2xl font-bold text-white">{{ items.length || '18+' }}</p>
            <p class="text-[11px] text-amber-400">Published Photos</p>
          </div>
          <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 space-y-1">
            <span class="text-[10px] uppercase font-bold text-white/40 tracking-wider">Categories</span>
            <p class="font-display text-2xl font-bold text-white">{{ categories.length || '4' }}</p>
            <p class="text-[11px] text-emerald-400">Filter Taxonomies</p>
          </div>
          <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 space-y-1">
            <span class="text-[10px] uppercase font-bold text-white/40 tracking-wider">Barber Credits</span>
            <p class="font-display text-2xl font-bold text-white">Linked</p>
            <p class="text-[11px] text-sky-400">Master Stylist Tagging</p>
          </div>
        </div>

        <p class="text-xs text-white/50 leading-relaxed">
          The public Gallery page presents these images in a masonry grid layout with lightbox full-screen modal zoom. You can upload new cuts, tag the barber who crafted the style, and organize them into categories anytime.
        </p>
      </div>

      <!-- Right 1 Col: Big Action Box -->
      <div class="rounded-2xl border border-admin/30 bg-admin/5 p-6 text-center space-y-3">
        <div class="w-12 h-12 rounded-xl bg-admin/20 text-admin flex items-center justify-center mx-auto">
          <PhotoIcon class="w-6 h-6" />
        </div>
        <div>
          <h4 class="font-display text-base font-bold text-white">Upload new haircut photos?</h4>
          <p class="text-[11px] text-white/50 mt-1">Jump to the full Gallery Media Manager.</p>
        </div>
        <router-link
          to="/admin/gallery"
          class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-gradient-to-r from-admin to-admin-light px-4 py-2.5 text-xs font-bold text-obsidian shadow-lg hover:shadow-[0_4px_20px_rgba(255,103,0,0.4)] transition-all"
        >
          <span>Open Media Manager</span>
          <ArrowTopRightOnSquareIcon class="h-4 w-4" />
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  FilmIcon, 
  PhotoIcon, 
  ArrowTopRightOnSquareIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  items: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  }
});
</script>
