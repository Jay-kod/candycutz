<template>
  <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl">
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="font-display text-lg sm:text-xl font-bold text-theme-text flex items-center gap-2">
          Appointment <span class="bg-gradient-to-r from-emerald-400 via-sky-400 to-amber-400 bg-clip-text text-transparent">Status Breakdown</span>
        </h2>
        <p class="text-xs text-theme-text-muted">Comprehensive distribution across all booking lifecycle states.</p>
      </div>

      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-400">
          {{ completionRate }}% Completed
        </span>
        <span class="text-xs font-semibold text-theme-text-muted">
          {{ totalAppointments }} total
        </span>
      </div>
    </div>

    <!-- Status Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3.5" v-if="statusBreakdown.length > 0">
      <div
        v-for="status in statusBreakdown"
        :key="status.status"
        class="group relative overflow-hidden rounded-xl border p-4 backdrop-blur-sm transition-all duration-200"
        :class="getStatusCardClass(status.status)"
      >
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold uppercase tracking-wider text-theme-text-muted capitalize">
            {{ formatStatusLabel(status.status) }}
          </span>
          <span class="h-2 w-2 rounded-full" :class="getStatusDotClass(status.status)"></span>
        </div>

        <div class="mt-3 flex items-baseline justify-between">
          <p class="font-display text-2xl font-bold text-theme-text tabular-nums">
            {{ status.count }}
          </p>
          <span class="text-[11px] font-semibold text-theme-text-muted tabular-nums">
            {{ computePercentage(status.count) }}%
          </span>
        </div>

        <!-- Progress Bar -->
        <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-theme-bg">
          <div
            class="h-full rounded-full transition-all duration-700"
            :class="getStatusBarClass(status.status)"
            :style="{ width: `${computePercentage(status.count)}%` }"
          ></div>
        </div>
      </div>
    </div>

    <div v-else class="py-10 text-center text-xs text-theme-text-muted">
      No status breakdown records available for this selected period.
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  statusBreakdown: { type: Array, required: true }
});

const totalAppointments = computed(() => {
  return props.statusBreakdown.reduce((sum, s) => sum + Number(s.count || 0), 0);
});

const completionRate = computed(() => {
  const tot = totalAppointments.value;
  if (tot === 0) return 0;
  const completedItem = props.statusBreakdown.find(s => s.status === 'completed');
  const count = Number(completedItem?.count || 0);
  return Math.round((count / tot) * 100);
});

const computePercentage = (count) => {
  const tot = totalAppointments.value;
  if (tot === 0) return 0;
  return Math.round((Number(count || 0) / tot) * 100);
};

const formatStatusLabel = (status) => {
  return String(status || '').replace(/_/g, ' ');
};

const getStatusCardClass = (status) => {
  switch (status) {
    case 'completed':
      return 'border-emerald-500/20 bg-emerald-500/5 hover:border-emerald-500/40';
    case 'confirmed':
    case 'approved':
      return 'border-sky-500/20 bg-sky-500/5 hover:border-sky-500/40';
    case 'pending':
      return 'border-amber-500/20 bg-amber-500/5 hover:border-amber-500/40';
    case 'cancelled':
      return 'border-rose-500/20 bg-rose-500/5 hover:border-rose-500/40';
    case 'no_show':
      return 'border-purple-500/20 bg-purple-500/5 hover:border-purple-500/40';
    default:
      return 'border-theme-border bg-theme-surface hover:border-gold/30';
  }
};

const getStatusDotClass = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-400';
    case 'confirmed':
    case 'approved': return 'bg-sky-400';
    case 'pending': return 'bg-amber-400';
    case 'cancelled': return 'bg-rose-400';
    case 'no_show': return 'bg-purple-400';
    default: return 'bg-theme-border';
  }
};

const getStatusBarClass = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-400';
    case 'confirmed':
    case 'approved': return 'bg-sky-400';
    case 'pending': return 'bg-amber-400';
    case 'cancelled': return 'bg-rose-400';
    case 'no_show': return 'bg-purple-400';
    default: return 'bg-theme-border';
  }
};
</script>
