<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Feature Flag Metrics">
    <!-- Card 1: Total Runtime Flags -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Total Flags</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <FlagIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-white/40">registered</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Active deployment</span>
        <span class="font-medium text-admin font-mono">{{ stats.activeRate }}% enabled</span>
      </div>
    </div>

    <!-- Card 2: Active in Production -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.activeCount > 0 
        ? 'border-emerald-500/30 bg-[#12161C] hover:border-emerald-400' 
        : 'border-white/[0.08] bg-[#12161C] hover:border-white/20'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.activeCount > 0 ? 'bg-emerald-500/15 group-hover:bg-emerald-500/25' : 'bg-white/5'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Active Flags</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.activeCount > 0 
            ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40' 
            : 'bg-white/5 text-white/40 border-white/10'"
        >
          <CheckCircleIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.activeCount > 0 ? 'text-emerald-400' : 'text-white/60'"
        >
          {{ stats.activeCount }}
        </span>
        <span class="text-xs text-emerald-400/70 font-medium">live rollout</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="h-1.5 w-1.5 rounded-full" :class="stats.activeCount > 0 ? 'bg-emerald-400 animate-pulse' : 'bg-white/20'"></span>
        <span>Enforcing client behavior</span>
      </div>
    </div>

    <!-- Card 3: Inactive / Gated -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.inactiveCount > 0 
        ? 'border-amber-500/30 bg-[#12161C] hover:border-amber-400' 
        : 'border-white/[0.08] bg-[#12161C] hover:border-white/20'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.inactiveCount > 0 ? 'bg-amber-500/15 group-hover:bg-amber-500/25' : 'bg-white/5'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Inactive Flags</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.inactiveCount > 0 
            ? 'bg-amber-500/20 text-amber-400 border-amber-500/40' 
            : 'bg-white/5 text-white/40 border-white/10'"
        >
          <PauseCircleIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.inactiveCount > 0 ? 'text-amber-400' : 'text-white/60'"
        >
          {{ stats.inactiveCount }}
        </span>
        <span class="text-xs text-white/40">dormant / staging</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="h-1.5 w-1.5 rounded-full" :class="stats.inactiveCount > 0 ? 'bg-amber-400' : 'bg-white/20'"></span>
        <span>Disabled from client traffic</span>
      </div>
    </div>

    <!-- Card 4: Platform Reach -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-violet-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-violet-500/5 blur-2xl group-hover:bg-violet-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Cross-Platform</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-500/15 text-violet-400 border border-violet-500/30">
          <DevicePhoneMobileIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.crossPlatformCount }}</span>
        <span class="text-xs text-violet-400/80 font-medium">web & app</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Coverage breakdown</span>
        <span class="font-mono text-white/80 font-medium">{{ stats.webCount }} web • {{ stats.appCount }} app</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { 
  FlagIcon, 
  CheckCircleIcon, 
  PauseCircleIcon, 
  DevicePhoneMobileIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      totalCount: 0,
      activeCount: 0,
      inactiveCount: 0,
      webCount: 0,
      appCount: 0,
      crossPlatformCount: 0,
      activeRate: 0,
    })
  }
});
</script>
