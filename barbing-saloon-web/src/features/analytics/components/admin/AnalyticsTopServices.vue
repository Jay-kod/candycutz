<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-display text-lg sm:text-xl font-bold text-white flex items-center gap-2">
          Top Performing <span class="bg-gradient-to-r from-amber-400 to-[#FF6700] bg-clip-text text-transparent">Services</span>
        </h2>
        <p class="text-xs text-white/50">Services ranked by customer volume and generated turnover.</p>
      </div>

      <span class="text-xs font-semibold text-white/40">
        {{ services.length }} tracked
      </span>
    </div>

    <!-- Services List -->
    <div class="space-y-3">
      <div
        v-for="(svc, idx) in services"
        :key="svc.name || idx"
        class="group relative overflow-hidden rounded-xl border border-white/[0.06] bg-[#0B0F13]/60 p-3.5 transition-all duration-200 hover:border-white/20 hover:bg-[#0B0F13]/90"
      >
        <div class="flex items-center justify-between gap-3">
          <!-- Rank & Service Info -->
          <div class="flex items-center gap-3 min-w-0">
            <span
              class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs font-bold font-mono"
              :class="[
                idx === 0 ? 'bg-amber-400/20 text-amber-400 border border-amber-400/30' :
                idx === 1 ? 'bg-slate-300/20 text-slate-300 border border-slate-300/30' :
                idx === 2 ? 'bg-amber-700/20 text-amber-600 border border-amber-700/30' :
                'bg-white/5 text-white/40 border border-white/5'
              ]"
            >
              #{{ idx + 1 }}
            </span>

            <div class="min-w-0">
              <p class="truncate text-xs sm:text-sm font-semibold text-white group-hover:text-[#FF6700] transition-colors">
                {{ svc.name }}
              </p>
              <p class="text-[11px] text-white/40">
                {{ Number(svc.count || 0) }} completed bookings
              </p>
            </div>
          </div>

          <!-- Revenue -->
          <div class="text-right shrink-0">
            <p class="font-display text-xs sm:text-sm font-bold text-emerald-400 tabular-nums">
              ₦{{ formatCurrency(svc.revenue) }}
            </p>
            <p class="text-[10px] text-white/40 tabular-nums">
              {{ computeRevenueShare(svc.revenue) }}% share
            </p>
          </div>
        </div>

        <!-- Relative Progress Bar -->
        <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-white/[0.04]">
          <div
            class="h-full rounded-full bg-gradient-to-r from-amber-400 to-[#FF6700] transition-all duration-700"
            :style="{ width: `${computeRelativeBarWidth(svc.count)}%` }"
          ></div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="services.length === 0" class="py-8 text-center text-xs text-white/40">
        No service booking records found for the selected range.
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  services: {
    type: Array,
    default: () => []
  },
  totalRevenue: {
    type: [Number, String],
    default: 0
  },
  formatCurrency: {
    type: Function,
    default: (val) => Number(val || 0).toLocaleString()
  }
});

const maxCount = computed(() => {
  if (!props.services || props.services.length === 0) return 1;
  const counts = props.services.map(s => Number(s.count || 0));
  return Math.max(...counts, 1);
});

const computeRelativeBarWidth = (count) => {
  const c = Number(count || 0);
  return Math.min(Math.round((c / maxCount.value) * 100), 100);
};

const computeRevenueShare = (revenue) => {
  const tot = Number(props.totalRevenue || 0);
  const rev = Number(revenue || 0);
  if (tot <= 0) return 0;
  return Math.round((rev / tot) * 100);
};
</script>
