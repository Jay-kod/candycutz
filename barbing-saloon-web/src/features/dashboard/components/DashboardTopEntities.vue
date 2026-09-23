<template>
  <div class="space-y-6">
    <!-- Top Barbers -->
    <div class="rounded-xl border border-theme-border bg-theme-surface overflow-hidden">
      <div class="border-b border-theme-border px-6 py-4 flex items-center justify-between">
        <h3 class="text-sm font-bold text-theme-text flex items-center gap-2">
          <TrophyIcon class="h-4 w-4 text-amber-400" />
          Top Barbers
        </h3>
        <RouterLink to="/admin/barbers" class="text-[10px] font-semibold text-admin/60 hover:text-admin transition-colors">View All</RouterLink>
      </div>
      <div class="divide-y divide-white/[0.03]">
        <div v-for="(barber, idx) in topBarbers" :key="barber.id" class="flex items-center gap-3 px-6 py-3.5 hover:bg-white/[0.02] transition-colors">
          <span class="text-xs font-bold w-5 text-center" :class="idx === 0 ? 'text-amber-400' : idx === 1 ? 'text-gray-400' : idx === 2 ? 'text-amber-700' : 'text-ivory/20'">#{{ idx + 1 }}</span>
          <div class="h-9 w-9 rounded-full bg-admin/10 flex items-center justify-center text-[11px] font-bold text-white shrink-0">
            {{ getInitials(barber.name) }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-theme-text truncate">{{ barber.name }}</p>
            <p class="text-[10px] text-ivory/35">{{ barber.bookings || 0 }} bookings · ⭐ {{ Number(barber.rating || 0).toFixed(1) }}</p>
          </div>
          <span class="text-xs font-bold text-emerald-400 tabular-nums whitespace-nowrap">₦{{ formatCurrency(barber.revenue || 0) }}</span>
        </div>
        <div v-if="!topBarbers?.length" class="flex flex-col items-center py-8 text-ivory/20">
          <UsersIcon class="h-8 w-8 mb-2" />
          <p class="text-xs">No barber data</p>
        </div>
      </div>
    </div>

    <!-- Top Services -->
    <div class="rounded-xl border border-theme-border bg-theme-surface overflow-hidden">
      <div class="border-b border-theme-border px-6 py-4">
        <h3 class="text-sm font-bold text-theme-text flex items-center gap-2">
          <SparklesIcon class="h-4 w-4 text-purple-400" />
          Popular Services
        </h3>
      </div>
      <div class="p-5 space-y-3">
        <div v-for="service in topServices" :key="service.name" class="group">
          <div class="flex items-center justify-between mb-1.5">
            <span class="text-xs text-theme-text font-medium truncate max-w-[160px]">{{ service.name }}</span>
            <span class="text-[10px] text-ivory/40 font-bold tabular-nums">{{ service.bookings || 0 }} booked</span>
          </div>
          <div class="h-1.5 w-full rounded-full bg-white/[0.04] overflow-hidden">
            <div class="h-full rounded-full bg-admin transition-all duration-700 group-hover:opacity-90"
                 :style="{ width: `${serviceBarWidth(service)}%` }"></div>
          </div>
        </div>
        <div v-if="!topServices?.length" class="flex flex-col items-center py-6 text-ivory/20">
          <SparklesIcon class="h-8 w-8 mb-2" />
          <p class="text-xs">No services data</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { TrophyIcon, SparklesIcon, UsersIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  topBarbers: { type: Array, default: () => [] },
  topServices: { type: Array, default: () => [] }
});

const getInitials = (name) => {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(value || 0));
};

const serviceBarWidth = (service) => {
  if (!props.topServices?.length) return 0;
  const max = Math.max(...props.topServices.map(s => s.bookings), 1);
  return (service.bookings / max) * 100;
};
</script>
