<template>
  <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- Top Barbers Panel -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-display text-lg sm:text-xl font-bold text-white flex items-center gap-2">
              Top Performing <span class="bg-gradient-to-r from-sky-400 to-indigo-400 bg-clip-text text-transparent">Barbers</span>
            </h2>
            <p class="text-xs text-white/50">Staff members ranked by fulfilled client appointments.</p>
          </div>
          <span class="text-xs font-semibold text-white/40">
            {{ topBarbers.length }} barbers
          </span>
        </div>

        <div class="space-y-3" v-if="topBarbers.length > 0">
          <div
            v-for="(barber, idx) in topBarbers"
            :key="barber.name || idx"
            class="group relative overflow-hidden rounded-xl border border-white/[0.06] bg-[#0B0F13]/60 p-3.5 transition-all duration-200 hover:border-white/20 hover:bg-[#0B0F13]/90"
          >
            <div class="flex items-center justify-between gap-3">
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
                  <div class="flex items-center gap-2">
                    <p class="truncate text-xs sm:text-sm font-semibold text-white group-hover:text-sky-400 transition-colors">
                      {{ barber.name }}
                    </p>
                    <span v-if="barber.rating" class="text-[11px] font-bold text-amber-400">
                      ★ {{ Number(barber.rating).toFixed(1) }}
                    </span>
                  </div>
                  <p class="text-[11px] text-white/40">
                    {{ barber.bookings || barber.count || 0 }} completed appointments
                  </p>
                </div>
              </div>

              <div class="text-right shrink-0">
                <p class="font-display text-xs sm:text-sm font-bold text-sky-400 tabular-nums">
                  ₦{{ formatCurrency(barber.revenue) }}
                </p>
                <p class="text-[10px] text-white/40">turnover</p>
              </div>
            </div>

            <!-- Capacity Progress Bar -->
            <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-white/[0.04]">
              <div
                class="h-full rounded-full bg-gradient-to-r from-sky-400 to-indigo-500 transition-all duration-700"
                :style="{ width: `${computeRelativeBarWidth(barber.bookings || barber.count, maxBarberBookings)}%` }"
              ></div>
            </div>
          </div>
        </div>

        <div v-else class="py-10 text-center text-xs text-white/40">
          No barber performance records available for this period.
        </div>
      </div>
    </div>

    <!-- Top Services Panel -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-display text-lg sm:text-xl font-bold text-white flex items-center gap-2">
              Most Popular <span class="bg-gradient-to-r from-emerald-400 to-[#FF6700] bg-clip-text text-transparent">Services</span>
            </h2>
            <p class="text-xs text-white/50">Services with highest booking requests and realized value.</p>
          </div>
          <span class="text-xs font-semibold text-white/40">
            {{ topServices.length }} services
          </span>
        </div>

        <div class="space-y-3" v-if="topServices.length > 0">
          <div
            v-for="(service, idx) in topServices"
            :key="service.name || idx"
            class="group relative overflow-hidden rounded-xl border border-white/[0.06] bg-[#0B0F13]/60 p-3.5 transition-all duration-200 hover:border-white/20 hover:bg-[#0B0F13]/90"
          >
            <div class="flex items-center justify-between gap-3">
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
                  <p class="truncate text-xs sm:text-sm font-semibold text-white group-hover:text-emerald-400 transition-colors">
                    {{ service.name }}
                  </p>
                  <p class="text-[11px] text-white/40">
                    {{ service.count }} times booked
                  </p>
                </div>
              </div>

              <div class="text-right shrink-0">
                <p class="font-display text-xs sm:text-sm font-bold text-emerald-400 tabular-nums">
                  ₦{{ formatCurrency(service.revenue) }}
                </p>
                <p class="text-[10px] text-white/40">generated</p>
              </div>
            </div>

            <!-- Popularity Progress Bar -->
            <div class="mt-2.5 h-1.5 w-full overflow-hidden rounded-full bg-white/[0.04]">
              <div
                class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-[#FF6700] transition-all duration-700"
                :style="{ width: `${computeRelativeBarWidth(service.count, maxServiceCount)}%` }"
              ></div>
            </div>
          </div>
        </div>

        <div v-else class="py-10 text-center text-xs text-white/40">
          No service popularity data reported for this period.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  topBarbers: { type: Array, required: true },
  topServices: { type: Array, required: true }
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};

const maxBarberBookings = computed(() => {
  if (!props.topBarbers || props.topBarbers.length === 0) return 1;
  const counts = props.topBarbers.map(b => Number(b.bookings || b.count || 0));
  return Math.max(...counts, 1);
});

const maxServiceCount = computed(() => {
  if (!props.topServices || props.topServices.length === 0) return 1;
  const counts = props.topServices.map(s => Number(s.count || 0));
  return Math.max(...counts, 1);
});

const computeRelativeBarWidth = (count, max) => {
  const c = Number(count || 0);
  const m = Number(max || 1);
  return Math.min(Math.round((c / m) * 100), 100);
};
</script>
