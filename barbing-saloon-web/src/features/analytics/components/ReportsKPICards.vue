<template>
  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 sm:gap-5">
    <!-- Card 1: Total Revenue -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Total Revenue</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-400 group-hover:scale-105 transition-transform">
          <BanknotesIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
          ₦{{ formatCurrency(businessStats.total_revenue) }}
        </span>
        <p class="mt-1 text-xs text-white/40 flex items-center gap-1.5">
          <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
          <span>₦{{ formatCurrency(businessStats.realized_revenue) }} realized</span>
        </p>
      </div>
    </div>

    <!-- Card 2: Completed Appointments -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-sky-500/30 hover:shadow-xl hover:shadow-sky-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Appointments</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-sky-500/20 bg-sky-500/10 text-sky-400 group-hover:scale-105 transition-transform">
          <CalendarDaysIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-1">
          <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
            {{ Number(businessStats.total_appointments || 0).toLocaleString() }}
          </span>
          <span class="text-xs text-white/40">appts</span>
        </div>
        <p class="mt-1 text-xs text-white/40 flex items-center gap-1.5">
          <span class="inline-block h-1.5 w-1.5 rounded-full bg-sky-400"></span>
          <span>₦{{ formatCurrency(businessStats.average_ticket) }} avg ticket</span>
        </p>
      </div>
    </div>

    <!-- Card 3: New Customers -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-violet-500/30 hover:shadow-xl hover:shadow-violet-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">New Clients</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-violet-500/20 bg-violet-500/10 text-violet-400 group-hover:scale-105 transition-transform">
          <UserPlusIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-1">
          <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
            {{ Number(businessStats.new_customers || 0).toLocaleString() }}
          </span>
          <span class="text-xs text-white/40">registered</span>
        </div>
        <p class="mt-1 text-xs text-white/40 flex items-center gap-1.5">
          <span class="inline-block h-1.5 w-1.5 rounded-full bg-violet-400"></span>
          <span>In selected timeframe</span>
        </p>
      </div>
    </div>

    <!-- Card 4: Total Customers -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-amber-500/30 hover:shadow-xl hover:shadow-amber-500/5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Total Client Base</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-400 group-hover:scale-105 transition-transform">
          <UsersIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-1">
          <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
            {{ Number(businessStats.total_customers || 0).toLocaleString() }}
          </span>
          <span class="text-xs text-white/40">clients</span>
        </div>
        <p class="mt-1 text-xs text-white/40 flex items-center gap-1.5">
          <span class="inline-block h-1.5 w-1.5 rounded-full bg-amber-400"></span>
          <span>{{ platformStats.active_barbers || 0 }} active barbers</span>
        </p>
      </div>
    </div>

    <!-- Card 5: Service Catalogue & Rating -->
    <div class="group relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 backdrop-blur-xl transition-all duration-300 hover:border-rose-500/30 hover:shadow-xl hover:shadow-rose-500/5 sm:col-span-2 lg:col-span-1">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold uppercase tracking-wider text-white/50">Platform Scale</span>
        <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-400 group-hover:scale-105 transition-transform">
          <SparklesIcon class="h-5 w-5" />
        </div>
      </div>
      <div class="mt-4">
        <div class="flex items-baseline gap-1">
          <span class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-white tabular-nums">
            {{ platformStats.total_services || 0 }}
          </span>
          <span class="text-xs text-white/40">services</span>
        </div>
        <p class="mt-1 text-xs text-white/40 flex items-center gap-1.5">
          <span class="text-amber-400 font-bold">★ {{ Number(platformStats.avg_rating || 5).toFixed(1) }}</span>
          <span>avg client rating</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  BanknotesIcon, 
  CalendarDaysIcon, 
  UserPlusIcon, 
  UsersIcon, 
  SparklesIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  businessStats: { type: Object, default: () => ({}) },
  platformStats: { type: Object, default: () => ({}) }
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(value || 0));
};
</script>
