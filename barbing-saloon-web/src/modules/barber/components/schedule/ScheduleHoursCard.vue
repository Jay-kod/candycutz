<template>
  <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] overflow-hidden shadow-sm dark:shadow-2xl relative">
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-white/[0.08] px-6 py-5 bg-slate-50 dark:bg-[#0B0F13]/40">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gold/10 border border-gold/20">
          <ClockIcon class="h-4 w-4 text-gold" />
        </div>
        <div>
          <h2 class="font-display text-lg text-slate-900 dark:text-white font-bold">Weekly Roster</h2>
          <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest font-semibold">Standard shift availability</p>
        </div>
      </div>
      <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
        <PencilSquareIcon class="h-3.5 w-3.5 text-gold" />
        Toggle switch to close chair
      </p>
    </div>

    <div class="p-6 space-y-3">
      <div
        v-for="hour in workingHours"
        :key="hour.day_of_week"
        class="group relative flex flex-col sm:flex-row sm:items-center justify-between rounded-2xl border p-4 transition-all duration-300"
        :class="
          hour.is_closed
            ? 'border-red-500/15 bg-gradient-to-r from-red-500/[0.04] to-transparent'
            : 'border-slate-200 dark:border-white/[0.08] bg-slate-50/50 dark:bg-white/[0.02] hover:border-gold/30'
        "
        data-test="day-row"
      >
        <!-- Day Label & Toggle -->
        <div class="flex items-center gap-4 mb-3 sm:mb-0">
          <button
            @click="hour.is_closed = !hour.is_closed"
            type="button"
            class="relative h-7 w-12 rounded-full transition-all duration-300 focus:outline-none"
            :class="
              hour.is_closed
                ? 'bg-red-950/40 border border-red-500/30'
                : 'bg-gold/20 border border-gold/40 shadow-[0_0_12px_rgba(255,153,0,0.25)]'
            "
            data-test="toggle-day-closed"
          >
            <span
              class="absolute top-0.5 inline-block h-5 w-5 rounded-full transition-all duration-300"
              :class="
                hour.is_closed
                  ? 'left-1 bg-red-400 shadow-sm'
                  : 'left-[1.375rem] bg-gold shadow-sm'
              "
            />
          </button>

          <div class="flex flex-col">
            <span class="text-sm text-slate-900 dark:text-white font-semibold tracking-wide">
              {{ dayName(hour.day_of_week) }}
            </span>
            <span
              class="text-[10px] uppercase font-bold tracking-widest mt-0.5"
              :class="hour.is_closed ? 'text-red-600 dark:text-red-400/70' : 'text-amber-700 dark:text-gold/90'"
            >
              {{ hour.is_closed ? 'Day Off / Closed' : 'Chair Open' }}
            </span>
          </div>
        </div>

        <!-- Shift Times -->
        <div class="flex items-center gap-2.5">
          <template v-if="!hour.is_closed">
            <div class="relative flex items-center">
              <div class="absolute left-3 pointer-events-none">
                <div class="h-2 w-2 rounded-full bg-gold shadow-[0_0_6px_rgba(255,153,0,0.6)]"></div>
              </div>
              <input
                v-model="hour.open_time"
                type="time"
                class="w-[120px] rounded-xl border border-slate-300 dark:border-white/[0.08] bg-slate-100 dark:bg-[#0B0F13] pl-7 pr-3 py-2 text-xs text-slate-900 dark:text-white font-medium outline-none transition-all focus:border-gold/50"
              />
            </div>

            <span class="text-[10px] uppercase tracking-widest text-slate-500 font-bold px-1">to</span>

            <div class="relative flex items-center">
              <div class="absolute left-3 pointer-events-none">
                <div class="h-2 w-2 rounded-full bg-amber-400 shadow-[0_0_6px_rgba(251,191,36,0.6)]"></div>
              </div>
              <input
                v-model="hour.close_time"
                type="time"
                class="w-[120px] rounded-xl border border-slate-300 dark:border-white/[0.08] bg-slate-100 dark:bg-[#0B0F13] pl-7 pr-3 py-2 text-xs text-slate-900 dark:text-white font-medium outline-none transition-all focus:border-gold/50"
              />
            </div>
          </template>

          <div
            v-else
            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-red-500/[0.06] border border-red-500/15"
          >
            <div class="h-1.5 w-1.5 rounded-full bg-red-400/60"></div>
            <span class="text-red-600 dark:text-red-400/80 text-[10px] font-bold uppercase tracking-widest">Closed</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ClockIcon,
  PencilSquareIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  workingHours: {
    type: Array,
    required: true,
  },
  dayName: {
    type: Function,
    required: true,
  },
});
</script>
