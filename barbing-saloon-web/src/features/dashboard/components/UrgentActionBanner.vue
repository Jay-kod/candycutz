<template>
  <div
    v-if="hasPending"
    class="relative overflow-hidden rounded-2xl border border-amber-500/30 bg-amber-500/10 dark:bg-amber-500/5 p-4 sm:p-5 shadow-sm transition-all"
  >
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-start gap-3 min-w-0">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400">
          <ExclamationTriangleIcon class="h-5 w-5 animate-pulse" />
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200">
              Operational Attention Required
            </h4>
            <span class="inline-flex items-center rounded-full bg-amber-500/20 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider">
              {{ pendingCount }} Pending
            </span>
          </div>
          <p class="text-xs text-amber-800/80 dark:text-amber-300/80 mt-0.5 truncate">
            {{ pendingCount === 1 ? '1 appointment is' : `${pendingCount} appointments are` }} currently awaiting verification, barber assignment, or check-in.
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
        <RouterLink
          to="/admin/appointments"
          class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white dark:text-slate-950 font-semibold text-xs px-3.5 py-2 shadow-sm transition-all hover:scale-[1.02] active:scale-95"
        >
          <span>Review Queue</span>
          <ArrowRightIcon class="h-3.5 w-3.5" />
        </RouterLink>
      </div>
    </div>
  </div>

  <div
    v-else
    class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 px-4 py-2.5 flex items-center justify-between gap-2 text-xs text-emerald-700 dark:text-emerald-400"
  >
    <div class="flex items-center gap-2">
      <CheckCircleIcon class="h-4 w-4 shrink-0 text-emerald-500" />
      <span class="font-medium">All appointments are up to date &bull; No pending approvals in queue</span>
    </div>
    <RouterLink
      to="/admin/walk-in"
      class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 shrink-0"
    >
      <span>+ Fast Walk-In</span>
    </RouterLink>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { ExclamationTriangleIcon, CheckCircleIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({}),
  },
});

const pendingCount = computed(() => {
  return Number(props.stats?.pending_appointments ?? props.stats?.pending ?? 0);
});

const hasPending = computed(() => pendingCount.value > 0);
</script>

