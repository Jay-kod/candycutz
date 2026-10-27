<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Gallery Portfolio Metrics">
    <!-- Card 1: Total Portfolio Photos -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Masterpieces</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <PhotoIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-theme-text tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-theme-text-muted">photos</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
        <span>{{ stats.hairAndBeardCount }} signature cuts & beard styles</span>
      </div>
    </div>

    <!-- Card 2: Styling Taxonomies / Categories -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-amber-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-500/5 blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Taxonomies</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-500 dark:text-amber-400 border border-amber-500/30">
          <TagIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-amber-500 dark:text-amber-400 tracking-tight">{{ stats.distinctCats }}</span>
        <span class="text-xs text-theme-text-muted">categories</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span>Diverse cut & style spectrum</span>
      </div>
    </div>

    <!-- Card 3: Featured Stylists -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Stylist Portfolios</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-500 dark:text-emerald-400 border border-emerald-500/30">
          <ScissorsIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-emerald-600 dark:text-emerald-400 tracking-tight">{{ stats.distinctBarbers }}</span>
        <span class="text-xs text-theme-text-muted">stylists</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span>Barbers with featured work</span>
      </div>
    </div>

    <!-- Card 4: Shop Ambiance & Interior -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Shop Ambiance</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-500 dark:text-sky-400 border border-sky-500/30">
          <SparklesIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-sky-600 dark:text-sky-400 tracking-tight">{{ stats.shopPhotosCount }}</span>
        <span class="text-xs text-theme-text-muted">captures</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span>Salon interior, equipment & vibe</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { 
  PhotoIcon, 
  TagIcon, 
  ScissorsIcon, 
  SparklesIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      totalCount: 0,
      distinctCats: 0,
      distinctBarbers: 0,
      shopPhotosCount: 0,
      hairAndBeardCount: 0
    })
  }
});
</script>
