<template>
  <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 md:p-10 shadow-sm dark:shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
    <!-- Ambient ambient glow spheres -->
    <div class="absolute -top-36 -right-36 w-80 h-80 bg-gold/15 rounded-full blur-[100px] pointer-events-none group-hover:bg-gold/20 transition-colors duration-700"></div>
    <div class="absolute -bottom-36 -left-36 w-80 h-80 bg-amber-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-amber-500/15 transition-colors duration-700"></div>

    <div class="relative z-10 max-w-2xl">
      <!-- Tag Pill -->
      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gold/10 border border-gold/20 mb-4">
        <PencilSquareIcon class="h-3.5 w-3.5 text-gold" />
        <span class="text-[10px] font-bold text-gold tracking-widest uppercase">Editorial Hub</span>
      </div>

      <!-- Headline -->
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-slate-900 dark:text-white tracking-tight mb-2">
        Barber <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-300 to-amber-500">Blog</span>
        <span
          v-if="!loading"
          class="ml-3 inline-flex items-center justify-center h-8 px-3 rounded-full bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-sm text-slate-700 dark:text-slate-300 font-normal align-middle"
        >
          {{ totalCount }}
        </span>
      </h1>

      <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed">
        Publish expert grooming insights, haircut maintenance advice, and trend highlights for your salon clientele.
      </p>
    </div>

    <!-- Header Actions -->
    <div class="relative z-10 flex items-center gap-3 shrink-0">
      <button
        type="button"
        @click="$emit('refresh')"
        :disabled="loading"
        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.04] px-4 py-3.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white dark:hover:bg-white/[0.08] hover:border-gold/30 transition-all disabled:opacity-50"
        title="Refresh articles from server"
      >
        <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': loading }" />
        <span class="hidden sm:inline">Refresh</span>
      </button>

      <router-link
        to="/barber/blog/new"
        class="flex items-center gap-2 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 px-6 py-3.5 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-gold/20 hover:shadow-[0_8px_30px_rgba(255,153,0,0.35)] transition-all hover:-translate-y-0.5 active:translate-y-0"
        data-test="new-post-btn"
      >
        <PlusIcon class="h-4 w-4 stroke-[2.5]" />
        <span>New Article</span>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import {
  PencilSquareIcon,
  PlusIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  totalCount: {
    type: Number,
    default: 0,
  },
});

defineEmits(['refresh']);
</script>
