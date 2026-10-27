<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Services Metrics">
    <!-- Card 1: Total Offerings -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Service Menu</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <SparklesIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-white/40">treatments</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
        <span>{{ stats.activeCount }} active & bookable</span>
      </div>
    </div>

    <!-- Card 2: Pending Barber Approvals -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.pendingCount > 0 
        ? 'border-amber-500/40 bg-[#12161C] hover:border-amber-400' 
        : 'border-white/[0.08] bg-[#12161C] hover:border-white/20'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.pendingCount > 0 ? 'bg-amber-500/15 group-hover:bg-amber-500/25' : 'bg-white/5'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Pending Review</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.pendingCount > 0 
            ? 'bg-amber-500/20 text-amber-400 border-amber-500/40' 
            : 'bg-white/5 text-white/40 border-white/10'"
        >
          <ExclamationTriangleIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.pendingCount > 0 ? 'text-amber-400' : 'text-white/60'"
        >
          {{ stats.pendingCount }}
        </span>
        <span class="text-xs text-white/40">submissions</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] relative z-10 pt-2 border-t border-white/[0.04]">
        <span 
          v-if="stats.pendingCount > 0" 
          class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"
        ></span>
        <span :class="stats.pendingCount > 0 ? 'text-amber-400/90 font-medium' : 'text-white/40'">
          {{ stats.pendingCount > 0 ? 'Barber services require review' : 'All barber creations reviewed' }}
        </span>
      </div>
    </div>

    <!-- Card 3: Average Treatment Price -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Average Price</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <BanknotesIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-1 relative z-10">
        <span class="text-sm font-semibold text-emerald-400/80">₦</span>
        <span class="font-display text-3xl font-bold text-emerald-400 tracking-tight">{{ stats.avgPrice.toLocaleString() }}</span>
        <span class="text-xs text-white/40 ml-1">avg</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Range:</span>
        <span class="font-mono text-white/70">₦{{ stats.minPrice.toLocaleString() }} – ₦{{ stats.maxPrice.toLocaleString() }}</span>
      </div>
    </div>

    <!-- Card 4: Average Duration -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Mean Duration</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 border border-sky-500/30">
          <ClockIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-sky-400 tracking-tight">{{ stats.avgDuration }}</span>
        <span class="text-xs text-white/40">minutes</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Pacing:</span>
        <span class="text-white/70">Signature salon pace</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { 
  SparklesIcon, 
  ExclamationTriangleIcon, 
  BanknotesIcon, 
  ClockIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      totalCount: 0,
      activeCount: 0,
      pendingCount: 0,
      avgPrice: 0,
      minPrice: 0,
      maxPrice: 0,
      avgDuration: 0
    })
  }
});
</script>

