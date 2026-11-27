<template>
  <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:to-[#12161C] p-4 xs:p-6 sm:p-8 md:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
    <div class="absolute -top-36 -right-36 w-80 h-80 bg-gold/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-gold/15 transition-colors duration-700"></div>
    <div class="absolute -bottom-36 -left-36 w-80 h-80 bg-amber-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-amber-500/15 transition-colors duration-700"></div>

    <div class="relative z-10">
      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gold/10 border border-gold/20 mb-4">
        <SparklesIcon class="h-3.5 w-3.5 text-gold" />
        <span class="text-[10px] font-bold text-gold tracking-widest uppercase">Command Center</span>
      </div>
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-slate-900 dark:text-white tracking-tight mb-2">
        Today's <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-500 to-amber-600 dark:via-amber-400 dark:to-amber-200">Chair View</span>
      </h1>
      <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm max-w-lg leading-relaxed">
        Monitor your active bookings, verify guest payments, adjust your real-time chair availability, and close out haircuts.
      </p>
    </div>

    <div class="relative z-10 flex flex-wrap items-center gap-5 shrink-0">
      <!-- Current Time & Date -->
      <div class="hidden sm:flex flex-col items-end">
        <span class="text-2xl font-display text-slate-900 dark:text-white font-bold tabular-nums">{{ currentTime }}</span>
        <span class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold mt-0.5">{{ currentDate }}</span>
      </div>

      <div class="h-10 w-px bg-slate-200 dark:bg-white/[0.08] hidden sm:block"></div>

      <!-- Chair / Work Status Toggle -->
      <div class="flex items-center gap-3 bg-slate-100 dark:bg-[#0B0F13] px-4 py-2.5 rounded-2xl border border-slate-200 dark:border-white/[0.08]">
        <div class="flex flex-col items-end mr-1">
          <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Chair Status</span>
          <span
            class="text-[11px] font-bold uppercase"
            :class="myStatus === 'active' ? 'text-emerald-600 dark:text-emerald-400' : myStatus === 'suspended' ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400'"
          >
            {{ formatStatus(myStatus) }}
          </span>
        </div>

        <template v-if="myStatus === 'active' || myStatus === 'on_leave'">
          <button
            @click="$emit('toggle-status', 'active')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all"
            :class="
              myStatus === 'active'
                ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-transparent'
            "
          >
            Active
          </button>
          <button
            @click="$emit('toggle-status', 'on_leave')"
            class="px-3 py-1.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all"
            :class="
              myStatus === 'on_leave'
                ? 'bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/30 shadow-sm'
                : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-transparent'
            "
          >
            Break / Leave
          </button>
        </template>
        <template v-else>
          <div
            class="px-3 py-1 rounded-xl text-[10px] font-bold uppercase border"
            :class="myStatus === 'suspended' ? 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'"
          >
            {{ myStatus === 'suspended' ? 'Suspended' : 'Pending Approval' }}
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { SparklesIcon } from '@heroicons/vue/24/outline';

defineProps({
  currentTime: { type: String, required: true },
  currentDate: { type: String, required: true },
  myStatus: { type: String, required: true },
  formatStatus: { type: Function, required: true },
});

defineEmits(['toggle-status']);
</script>
