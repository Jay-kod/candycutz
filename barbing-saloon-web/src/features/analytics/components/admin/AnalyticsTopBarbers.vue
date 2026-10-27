<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-display text-lg sm:text-xl font-bold text-white flex items-center gap-2">
          Staff <span class="bg-gradient-to-r from-sky-400 to-indigo-400 bg-clip-text text-transparent">Performance</span>
        </h2>
        <p class="text-xs text-white/50">Top barbers ordered by appointment throughput.</p>
      </div>

      <span class="text-xs font-semibold text-white/40">
        {{ barbers.length }} barbers
      </span>
    </div>

    <!-- Barbers List -->
    <div class="space-y-3">
      <div
        v-for="(barber, idx) in barbers"
        :key="barber.name || idx"
        class="group relative overflow-hidden rounded-xl border border-white/[0.06] bg-[#0B0F13]/60 p-3.5 transition-all duration-200 hover:border-white/20 hover:bg-[#0B0F13]/90"
      >
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <!-- Avatar Initials -->
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-white/[0.1] bg-white/[0.05] font-display text-xs font-bold text-white">
              {{ getInitials(barber.name) }}
            </div>

            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <p class="truncate text-xs sm:text-sm font-semibold text-white group-hover:text-sky-400 transition-colors">
                  {{ barber.name }}
                </p>
                <span v-if="barber.rating" class="inline-flex items-center gap-0.5 text-[11px] font-bold text-amber-400">
                  ★ {{ Number(barber.rating).toFixed(1) }}
                </span>
              </div>
              <p class="text-[11px] text-white/40">
                {{ Number(barber.count || 0) }} completed appointments
              </p>
            </div>
          </div>

          <div class="text-right shrink-0">
            <p class="font-display text-xs sm:text-sm font-bold text-sky-400 tabular-nums">
              ₦{{ formatCurrency(barber.revenue) }}
            </p>
            <p class="text-[10px] text-white/40 tabular-nums">
              turnover
            </p>
          </div>
        </div>

        <!-- Relative Capacity Bar -->
        <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-white/[0.04]">
          <div
            class="h-full rounded-full bg-gradient-to-r from-sky-400 to-indigo-500 transition-all duration-700"
            :style="{ width: `${computeRelativeBarWidth(barber.count)}%` }"
          ></div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="barbers.length === 0" class="py-8 text-center text-xs text-white/40">
        No barber throughput recorded for the selected date range.
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  barbers: {
    type: Array,
    default: () => []
  },
  formatCurrency: {
    type: Function,
    default: (val) => Number(val || 0).toLocaleString()
  }
});

const maxBarberCount = computed(() => {
  if (!props.barbers || props.barbers.length === 0) return 1;
  const counts = props.barbers.map(b => Number(b.count || 0));
  return Math.max(...counts, 1);
});

const computeRelativeBarWidth = (count) => {
  const c = Number(count || 0);
  return Math.min(Math.round((c / maxBarberCount.value) * 100), 100);
};

const getInitials = (name) => {
  if (!name) return 'CC';
  const parts = String(name).trim().split(/\s+/);
  if (parts.length >= 2) {
    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
  return String(name).slice(0, 2).toUpperCase();
};
</script>
