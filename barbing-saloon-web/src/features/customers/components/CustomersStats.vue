<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Total Client Base -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Client Registry</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <UserGroupIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-white/40">registered</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
        <span>{{ stats.activeCount }} active with bookings</span>
      </div>
    </div>

    <!-- Card 2: Lifetime Gross Revenue -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Lifetime Volume</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <BanknotesIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-2xl font-bold text-emerald-400 tracking-tight">₦{{ stats.totalSpent.toLocaleString() }}</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Avg. ₦{{ stats.avgSpend.toLocaleString() }} / client</span>
      </div>
    </div>

    <!-- Card 3: VIP & Top Tier -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-amber-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-500/5 blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">VIP & Top Spenders</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <TrophyIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-amber-400 tracking-tight">{{ stats.vipCount }}</span>
        <span class="text-xs text-white/40">VIP accounts</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04] truncate">
        <span v-if="stats.topCustomer" class="truncate">
          Top: <span class="text-white font-medium">{{ stats.topCustomer.name }}</span> (₦{{ (stats.topCustomer.total_spent || 0).toLocaleString() }})
        </span>
        <span v-else>No spending recorded yet</span>
      </div>
    </div>

    <!-- Card 4: Total Bookings Generated -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Booking Volume</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 border border-sky-500/30">
          <ChartBarIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.totalBookings }}</span>
        <span class="text-xs text-white/40">appointments</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Engagement: {{ stats.avgBookings }} visits / client</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  UserGroupIcon, 
  BanknotesIcon, 
  TrophyIcon, 
  ChartBarIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  customers: {
    type: Array,
    default: () => []
  }
});

const stats = computed(() => {
  const list = props.customers || [];
  const totalCount = list.length;
  
  const totalSpent = list.reduce((sum, c) => sum + (Number(c.total_spent) || 0), 0);
  const totalBookings = list.reduce((sum, c) => sum + (Number(c.total_bookings) || 0), 0);
  
  const activeCount = list.filter(c => (c.total_bookings || 0) > 0).length;
  const vipCount = list.filter(c => (c.total_spent || 0) > 0).length;
  
  const avgSpend = totalCount > 0 ? Math.round(totalSpent / totalCount) : 0;
  const avgBookings = totalCount > 0 ? (totalBookings / totalCount).toFixed(1) : '0.0';

  const sortedBySpend = [...list].sort((a, b) => (b.total_spent || 0) - (a.total_spent || 0));
  const topCustomer = sortedBySpend[0] && (sortedBySpend[0].total_spent || 0) > 0 ? sortedBySpend[0] : null;

  return {
    totalCount,
    totalSpent,
    totalBookings,
    activeCount,
    vipCount,
    avgSpend,
    avgBookings,
    topCustomer
  };
});
</script>
