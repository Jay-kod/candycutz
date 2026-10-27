<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="API Platform Metrics">
    <!-- Card 1: Total Endpoints -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Total Routes</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <CodeBracketSquareIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.totalEndpoints }}</span>
        <span class="text-xs text-white/40">endpoints</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Resource modules</span>
        <span class="font-mono text-white/80 font-medium">{{ stats.groupCount }} groups</span>
      </div>
    </div>

    <!-- Card 2: Gateway Health & Latency -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.healthState === 'online' 
        ? 'border-emerald-500/30 bg-[#12161C] hover:border-emerald-400' 
        : stats.healthState === 'checking'
          ? 'border-amber-500/30 bg-[#12161C] hover:border-amber-400'
          : 'border-rose-500/30 bg-[#12161C] hover:border-rose-400'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.healthState === 'online' ? 'bg-emerald-500/15 group-hover:bg-emerald-500/25' : 'bg-amber-500/15'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Probe Latency</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.healthState === 'online' 
            ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40' 
            : 'bg-amber-500/20 text-amber-400 border-amber-500/40'"
        >
          <ServerStackIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight font-mono"
          :class="stats.healthState === 'online' ? 'text-emerald-400' : 'text-amber-400'"
        >
          {{ stats.latencyMs ? `${stats.latencyMs}ms` : (stats.healthState === 'online' ? 'Live' : 'Checking') }}
        </span>
        <span class="text-xs text-white/40">response time</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="h-1.5 w-1.5 rounded-full" :class="stats.healthState === 'online' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"></span>
        <span>/api/v1/health probe</span>
      </div>
    </div>

    <!-- Card 3: Authoritative Surface -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-cyan-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-cyan-500/5 blur-2xl group-hover:bg-cyan-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Architecture §2</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-500/15 text-cyan-400 border border-cyan-500/30">
          <ShieldCheckIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">100%</span>
        <span class="text-xs text-cyan-400/80 font-medium">/api/v1/*</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Surface policy</span>
        <span class="text-white/60">Strictly enforced</span>
      </div>
    </div>

    <!-- Card 4: Protected Endpoints -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-violet-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-violet-500/5 blur-2xl group-hover:bg-violet-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Protected Auth</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-500/15 text-violet-400 border border-violet-500/30">
          <LockClosedIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.protectedCount }}</span>
        <span class="text-xs text-violet-400/80 font-medium">secured routes</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Public access</span>
        <span class="font-mono text-white/80 font-medium">{{ stats.publicCount }} unauthenticated</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { 
  CodeBracketSquareIcon, 
  ServerStackIcon, 
  ShieldCheckIcon, 
  LockClosedIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      totalEndpoints: 0,
      publicCount: 0,
      protectedCount: 0,
      groupCount: 0,
      healthState: 'checking',
      latencyMs: null,
    })
  }
});
</script>
