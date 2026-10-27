<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl flex flex-col justify-between">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="font-display text-lg sm:text-xl font-bold text-white flex items-center gap-2">
          Appointment <span class="bg-gradient-to-r from-emerald-400 to-sky-400 bg-clip-text text-transparent">Status</span>
        </h2>
        <p class="text-xs text-white/50">Breakdown of bookings by fulfillment state.</p>
      </div>

      <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-400">
        {{ completionRate }}% Fulfilled
      </span>
    </div>

    <!-- Donut SVG & Center Metrics -->
    <div class="relative my-3 mx-auto h-48 w-48 flex items-center justify-center">
      <svg viewBox="0 0 100 100" class="h-full w-full transform -rotate-90">
        <!-- Background Track -->
        <circle
          cx="50"
          cy="50"
          r="40"
          fill="transparent"
          stroke="rgba(255,255,255,0.06)"
          stroke-width="14"
        />

        <!-- Animated Segments -->
        <circle
          v-for="(seg, idx) in donutSegments"
          :key="idx"
          cx="50"
          cy="50"
          r="40"
          fill="transparent"
          :stroke="seg.color"
          stroke-width="14"
          stroke-linecap="round"
          :stroke-dasharray="`${seg.length} ${seg.gap}`"
          :stroke-dashoffset="seg.offset"
          class="transition-all duration-1000 ease-out"
        />
      </svg>

      <!-- Center Text -->
      <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
        <span class="font-display text-3xl font-bold text-white tabular-nums tracking-tight">
          {{ totalCount }}
        </span>
        <span class="text-[10px] uppercase tracking-widest text-white/40">Total Appts</span>
      </div>
    </div>

    <!-- Status Legend -->
    <div class="mt-4 space-y-2 border-t border-white/[0.06] pt-4">
      <div
        v-for="item in legendItems"
        :key="item.label"
        class="flex items-center justify-between text-xs transition-colors hover:text-white"
      >
        <div class="flex items-center gap-2">
          <span class="h-2.5 w-2.5 rounded-full" :class="item.bgClass"></span>
          <span class="text-white/70 capitalize">{{ item.label.replace('_', ' ') }}</span>
        </div>
        <div class="flex items-center gap-2">
          <span class="font-semibold text-white tabular-nums">{{ item.count }}</span>
          <span class="text-[10px] text-white/40 tabular-nums">
            ({{ totalCount > 0 ? Math.round((item.count / totalCount) * 100) : 0 }}%)
          </span>
        </div>
      </div>

      <div v-if="legendItems.length === 0" class="text-center py-2 text-xs text-white/40">
        No status records available for this period.
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  donutSegments: {
    type: Array,
    required: true
  },
  legendItems: {
    type: Array,
    required: true
  },
  totalCount: {
    type: Number,
    required: true
  },
  completionRate: {
    type: Number,
    default: 0
  }
});
</script>

