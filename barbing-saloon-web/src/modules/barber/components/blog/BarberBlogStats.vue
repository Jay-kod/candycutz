<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Editorial Performance Metrics">
    <!-- Card 1: Total Articles -->
    <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-5 shadow-sm dark:shadow-xl group hover:border-gold/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-gold/5 blur-2xl group-hover:bg-gold/15 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Total Articles</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-gold/15 text-gold border border-gold/30">
          <DocumentTextIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-slate-500">articles</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 relative z-10 pt-2 border-t border-slate-100 dark:border-white/[0.04]">
        <span>Publish rate</span>
        <span class="font-medium text-amber-800 dark:text-gold font-mono">{{ stats.publishRate }}% live</span>
      </div>
    </div>

    <!-- Card 2: Published Live -->
    <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-5 shadow-sm dark:shadow-xl group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/15 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Published Live</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
          <GlobeAltIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ stats.publishedCount }}</span>
        <span class="text-xs text-emerald-700 dark:text-emerald-400/80 font-medium">visible online</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 relative z-10 pt-2 border-t border-slate-100 dark:border-white/[0.04]">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
        <span>Publicly accessible to clients</span>
      </div>
    </div>

    <!-- Card 3: Drafts Queue -->
    <div
      class="relative overflow-hidden rounded-3xl border p-5 shadow-sm dark:shadow-xl group transition-all duration-300 bg-white dark:bg-[#12161C]"
      :class="stats.draftsCount > 0 ? 'border-amber-500/30 hover:border-amber-400' : 'border-slate-200 dark:border-white/[0.08] hover:border-slate-300 dark:hover:border-white/20'"
    >
      <div
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.draftsCount > 0 ? 'bg-amber-500/15 group-hover:bg-amber-500/25' : 'bg-slate-200/50 dark:bg-white/5'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Drafts Queue</span>
        <span
          class="flex h-8 w-8 items-center justify-center rounded-xl border"
          :class="stats.draftsCount > 0 ? 'bg-amber-500/20 text-amber-700 dark:text-amber-400 border-amber-500/40' : 'bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-white/10'"
        >
          <ClockIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.draftsCount > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-900 dark:text-white'"
        >
          {{ stats.draftsCount }}
        </span>
        <span class="text-xs text-slate-500">in studio draft</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 relative z-10 pt-2 border-t border-slate-100 dark:border-white/[0.04]">
        <span
          class="h-1.5 w-1.5 rounded-full"
          :class="stats.draftsCount > 0 ? 'bg-amber-500 animate-pulse' : 'bg-slate-400 dark:bg-slate-600'"
        ></span>
        <span>{{ stats.draftsCount > 0 ? 'Private drafts pending launch' : 'All articles published live' }}</span>
      </div>
    </div>

    <!-- Card 4: Reader Loves / Engagement -->
    <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-5 shadow-sm dark:shadow-xl group hover:border-rose-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-rose-500/5 blur-2xl group-hover:bg-rose-500/15 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Reader Loves</span>
        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30">
          <HeartIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-slate-900 dark:text-white tracking-tight">{{ stats.totalLoves }}</span>
        <span class="text-xs text-rose-600 dark:text-rose-400/80">reactions</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 relative z-10 pt-2 border-t border-slate-100 dark:border-white/[0.04]">
        <span>Client appreciation</span>
        <span class="font-medium text-rose-600 dark:text-rose-400 font-mono">100% genuine</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import {
  DocumentTextIcon,
  GlobeAltIcon,
  ClockIcon,
  HeartIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
  },
});
</script>
