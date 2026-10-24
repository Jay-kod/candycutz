<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Top Barbers Card -->
    <div class="rounded-2xl border border-white/[0.07] bg-[#0A0F12]/80 backdrop-blur-xl shadow-xl overflow-hidden flex flex-col justify-between">
      <div>
        <div class="px-5 lg:px-6 py-4 border-b border-white/[0.05] flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 shrink-0">
              <TrophyIcon class="h-5 w-5" />
            </div>
            <div>
              <h2 class="text-sm font-bold text-white tracking-wide">Top Barbers</h2>
              <p class="text-[11px] text-white/40">Performance leaderboard</p>
            </div>
          </div>
          <RouterLink to="/admin/barbers" class="text-xs font-semibold text-admin hover:text-admin-light transition-colors">
            View All
          </RouterLink>
        </div>

        <div class="divide-y divide-white/[0.04]">
          <div 
            v-for="(barber, idx) in displayedBarbers" 
            :key="barber.id" 
            class="px-5 lg:px-6 py-3 flex items-center gap-3.5 hover:bg-white/[0.02] transition-colors"
          >
            <!-- Rank Badge -->
            <span 
              class="h-6 w-6 rounded-lg flex items-center justify-center text-[11px] font-bold shrink-0 border"
              :class="rankBadgeClass(idx)"
            >
              {{ idx + 1 }}
            </span>

            <!-- Barber Avatar -->
            <div class="h-9 w-9 rounded-xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center text-xs font-bold text-white shrink-0">
              {{ getInitials(barber.name) }}
            </div>

            <!-- Details -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-white truncate">{{ barber.name }}</p>
              <div class="flex items-center gap-2 text-xs text-white/40 mt-0.5">
                <span>{{ barber.bookings || 0 }} bookings</span>
                <span class="text-white/20">&bull;</span>
                <span class="text-amber-400/90 flex items-center gap-0.5">
                  ★ {{ Number(barber.rating || 5.0).toFixed(1) }}
                </span>
              </div>
            </div>

            <!-- Revenue -->
            <div class="text-right shrink-0">
              <span class="text-xs font-bold text-emerald-400 tabular-nums">
                ₦{{ formatCurrency(barber.revenue || 0) }}
              </span>
            </div>
          </div>

          <div v-if="!topBarbers?.length" class="flex flex-col items-center justify-center py-10 text-white/30">
            <UserGroupIcon class="h-8 w-8 mb-2 opacity-40 text-amber-400" />
            <p class="text-xs font-medium">No barber performance data</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Popular Services Card -->
    <div class="rounded-2xl border border-white/[0.07] bg-[#0A0F12]/80 backdrop-blur-xl shadow-xl overflow-hidden flex flex-col justify-between">
      <div>
        <div class="px-5 lg:px-6 py-4 border-b border-white/[0.05] flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 shrink-0">
              <SparklesIcon class="h-5 w-5" />
            </div>
            <div>
              <h2 class="text-sm font-bold text-white tracking-wide">Popular Services</h2>
              <p class="text-[11px] text-white/40">Most booked haircuts & treatments</p>
            </div>
          </div>
        </div>

        <div class="p-5 lg:p-6 space-y-4">
          <div v-for="service in displayedServices" :key="service.name" class="group">
            <div class="flex items-center justify-between mb-1.5 text-xs">
              <span class="font-medium text-white/90 group-hover:text-admin-light transition-colors truncate max-w-[200px]">
                {{ service.name }}
              </span>
              <span class="font-bold text-white/60 tabular-nums">
                {{ service.bookings || 0 }} booked
              </span>
            </div>

            <!-- Progress Bar -->
            <div class="h-2 w-full rounded-full bg-white/[0.05] overflow-hidden p-0.5">
              <div 
                class="h-full rounded-full bg-gradient-to-r from-admin to-amber-500 transition-all duration-700 group-hover:brightness-110"
                :style="{ width: `${serviceBarWidth(service)}%` }"
              ></div>
            </div>
          </div>

          <div v-if="!topServices?.length" class="flex flex-col items-center justify-center py-10 text-white/30">
            <SparklesIcon class="h-8 w-8 mb-2 opacity-40 text-purple-400" />
            <p class="text-xs font-medium">No services activity recorded</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { TrophyIcon, SparklesIcon, UserGroupIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  topBarbers: { type: Array, default: () => [] },
  topServices: { type: Array, default: () => [] }
});

const displayedBarbers = computed(() => {
  return (props.topBarbers || []).slice(0, 4);
});

const displayedServices = computed(() => {
  return (props.topServices || []).slice(0, 5);
});

const getInitials = (name) => {
  if (!name) return 'MB';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};

const rankBadgeClass = (idx) => {
  if (idx === 0) return 'bg-amber-500/15 text-amber-400 border-amber-500/30';
  if (idx === 1) return 'bg-slate-400/15 text-slate-300 border-slate-400/30';
  if (idx === 2) return 'bg-amber-700/15 text-amber-600 border-amber-700/30';
  return 'bg-white/5 text-white/40 border-white/10';
};

const serviceBarWidth = (service) => {
  if (!props.topServices?.length) return 0;
  const max = Math.max(...props.topServices.map(s => Number(s.bookings || 0)), 1);
  return Math.max(Math.round(((service.bookings || 0) / max) * 100), 5);
};
</script>
