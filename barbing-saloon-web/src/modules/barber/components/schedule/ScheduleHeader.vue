<template>
  <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 md:p-10 shadow-sm dark:shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
    <div class="absolute -top-36 -right-36 w-80 h-80 bg-gold/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-gold/15 transition-colors duration-700"></div>
    <div class="absolute -bottom-36 -left-36 w-80 h-80 bg-amber-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-amber-500/15 transition-colors duration-700"></div>

    <div class="relative z-10">
      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gold/10 border border-gold/20 mb-4">
        <CalendarDaysIcon class="h-3.5 w-3.5 text-gold" />
        <span class="text-[10px] font-bold text-gold tracking-widest uppercase">Roster & Shifts</span>
      </div>
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-slate-900 dark:text-white tracking-tight mb-2">
        Working <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-400 to-amber-200">Hours</span>
      </h1>
      <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm max-w-lg leading-relaxed">
        Define your weekly chair availability, toggle day-offs, and monitor upcoming queued appointments.
      </p>
    </div>

    <div class="relative z-10 flex items-center gap-3 shrink-0">
      <button
        @click="$emit('save')"
        :disabled="isSaving"
        class="flex items-center gap-2 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 px-6 py-3.5 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-gold/20 hover:shadow-[0_8px_30px_rgba(255,153,0,0.35)] transition-all disabled:opacity-50 hover:-translate-y-0.5 active:translate-y-0"
        data-test="save-schedule-btn"
      >
        <ArrowPathIcon v-if="isSaving" class="h-4 w-4 animate-spin" />
        <CheckCircleIcon v-else class="h-4 w-4" />
        {{ isSaving ? 'Saving...' : 'Save Schedule' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import {
  CalendarDaysIcon,
  CheckCircleIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  isSaving: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['save']);
</script>
