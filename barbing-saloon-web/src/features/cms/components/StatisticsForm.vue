<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.15)]">
          <SparklesIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Social Proof Statistics</h2>
          <p class="text-xs text-white/50 mt-0.5">Four key metric counters displayed immediately below the hero showcase banner.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 02
      </span>
    </div>

    <!-- 4 Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Stat 1: Years Experience -->
      <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 space-y-3 hover:border-emerald-500/30 transition-all">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/70">
            <ClockIcon class="h-3.5 w-3.5 text-emerald-400" />
            Years Experience
          </label>
          <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">Preview: {{ settings.stats_years || '10+' }}</span>
        </div>
        <input 
          v-model="settings.stats_years" 
          type="text" 
          placeholder="e.g. 10+" 
          class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-sm font-semibold text-white placeholder-white/20 focus:border-emerald-500/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-emerald-500/15 transition-all outline-none font-mono"
        />
        <p class="text-[11px] text-white/40">Demonstrates salon pedigree & tenure.</p>
      </div>

      <!-- Stat 2: Master Barbers -->
      <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 space-y-3 hover:border-emerald-500/30 transition-all">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/70">
            <ScissorsIcon class="h-3.5 w-3.5 text-emerald-400" />
            Master Barbers
          </label>
          <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">Preview: {{ settings.stats_barbers || '5+' }}</span>
        </div>
        <input 
          v-model="settings.stats_barbers" 
          type="text" 
          placeholder="e.g. 5+" 
          class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-sm font-semibold text-white placeholder-white/20 focus:border-emerald-500/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-emerald-500/15 transition-all outline-none font-mono"
        />
        <p class="text-[11px] text-white/40">Active certified styling specialists.</p>
      </div>

      <!-- Stat 3: Premium Services -->
      <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 space-y-3 hover:border-emerald-500/30 transition-all">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/70">
            <SparklesIcon class="h-3.5 w-3.5 text-emerald-400" />
            Premium Services
          </label>
          <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">Preview: {{ settings.stats_services || '20+' }}</span>
        </div>
        <input 
          v-model="settings.stats_services" 
          type="text" 
          placeholder="e.g. 20+" 
          class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-sm font-semibold text-white placeholder-white/20 focus:border-emerald-500/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-emerald-500/15 transition-all outline-none font-mono"
        />
        <p class="text-[11px] text-white/40">Catalog treatments & cuts available.</p>
      </div>

      <!-- Stat 4: Happy Clients -->
      <div class="rounded-xl border border-white/[0.08] bg-white/[0.02] p-4 space-y-3 hover:border-emerald-500/30 transition-all">
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/70">
            <UserGroupIcon class="h-3.5 w-3.5 text-emerald-400" />
            Happy Clients
          </label>
          <span class="text-[10px] font-mono text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">Preview: {{ settings.stats_clients || '5k+' }}</span>
        </div>
        <input 
          v-model="settings.stats_clients" 
          type="text" 
          placeholder="e.g. 5k+" 
          class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2.5 text-sm font-semibold text-white placeholder-white/20 focus:border-emerald-500/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-emerald-500/15 transition-all outline-none font-mono"
        />
        <p class="text-[11px] text-white/40">Completed cuts & verified reviews.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  SparklesIcon, 
  ClockIcon, 
  ScissorsIcon, 
  UserGroupIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  settings: {
    type: Object,
    required: true
  }
});
</script>
