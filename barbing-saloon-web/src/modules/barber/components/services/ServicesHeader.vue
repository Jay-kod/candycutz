<template>
  <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 md:p-10 shadow-sm dark:shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
    <div class="absolute -top-36 -right-36 w-80 h-80 bg-gold/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-gold/15 transition-colors duration-700"></div>
    <div class="absolute -bottom-36 -left-36 w-80 h-80 bg-amber-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-amber-500/15 transition-colors duration-700"></div>

    <div class="relative z-10">
      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gold/10 border border-gold/20 mb-4">
        <ScissorsIcon class="h-3.5 w-3.5 text-gold" />
        <span class="text-[10px] font-bold text-gold tracking-widest uppercase">Grooming Menu</span>
      </div>
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-slate-900 dark:text-white tracking-tight mb-2">
        Service <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-400 to-amber-200">Catalogue</span>
        <span v-if="!loading" class="ml-3 inline-flex items-center justify-center h-8 px-3 rounded-full bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-sm text-slate-700 dark:text-slate-300 font-normal align-middle">
          {{ totalCount }}
        </span>
      </h1>
      <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm max-w-lg leading-relaxed">
        Browse official salon services added by the administrator, or submit your own custom grooming services for admin approval.
      </p>

      <!-- Quick Metrics Pills -->
      <div v-if="!loading" class="flex flex-wrap items-center gap-3 mt-4 text-xs">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-700 dark:text-slate-300">
          <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
          Salon Official: <strong class="text-slate-900 dark:text-white">{{ adminCount }}</strong>
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-700 dark:text-slate-300">
          <span class="h-2 w-2 rounded-full bg-amber-500"></span>
          My Custom: <strong class="text-slate-900 dark:text-white">{{ mineCount }}</strong>
        </span>
        <span v-if="pendingCount > 0" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-400 font-medium">
          <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
          Pending Approval: <strong class="text-amber-800 dark:text-amber-300">{{ pendingCount }}</strong>
        </span>
      </div>
    </div>

    <div class="relative z-10 flex items-center gap-3 shrink-0">
      <router-link
        to="/barber/services/new"
        class="flex items-center gap-2 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 px-6 py-3.5 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-gold/20 hover:shadow-[0_8px_30px_rgba(255,153,0,0.35)] transition-all hover:-translate-y-0.5 active:translate-y-0"
        data-test="add-service-btn"
      >
        <PlusIcon class="h-4 w-4" />
        Add Service
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ScissorsIcon, PlusIcon } from '@heroicons/vue/24/outline';

defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  totalCount: {
    type: Number,
    default: 0,
  },
  adminCount: {
    type: Number,
    default: 0,
  },
  mineCount: {
    type: Number,
    default: 0,
  },
  pendingCount: {
    type: Number,
    default: 0,
  },
});
</script>
