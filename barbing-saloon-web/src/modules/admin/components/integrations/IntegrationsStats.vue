<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Integration Status Metrics">
    <!-- Card 1: Total Gateways -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Gateways Ready</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <CheckBadgeIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.configuredCount }}/{{ stats.totalServices }}</span>
        <span class="text-xs text-white/40">connected</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Operational health</span>
        <span class="font-medium text-admin font-mono">{{ stats.percentageConfigured }}% ready</span>
      </div>
    </div>

    <!-- Card 2: Brevo SMTP -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.mailConfigured 
        ? 'border-emerald-500/30 bg-[#12161C] hover:border-emerald-400' 
        : 'border-amber-500/30 bg-[#12161C] hover:border-amber-400'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.mailConfigured ? 'bg-emerald-500/15 group-hover:bg-emerald-500/25' : 'bg-amber-500/15'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Email Gateway</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.mailConfigured 
            ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40' 
            : 'bg-amber-500/20 text-amber-400 border-amber-500/40'"
        >
          <EnvelopeIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.mailConfigured ? 'text-emerald-400' : 'text-amber-400'"
        >
          {{ stats.mailConfigured ? 'Connected' : 'Missing' }}
        </span>
        <span class="text-xs text-white/40">Brevo SMTP</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span 
          class="h-1.5 w-1.5 rounded-full" 
          :class="stats.mailConfigured ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"
        ></span>
        <span>{{ stats.mailConfigured ? 'Transactional delivery active' : 'Credentials required' }}</span>
      </div>
    </div>

    <!-- Card 3: Google OAuth -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.googleConfigured 
        ? 'border-cyan-500/30 bg-[#12161C] hover:border-cyan-400' 
        : 'border-white/[0.08] bg-[#12161C] hover:border-white/20'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.googleConfigured ? 'bg-cyan-500/15 group-hover:bg-cyan-500/25' : 'bg-white/5'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Google Sign-In</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.googleConfigured 
            ? 'bg-cyan-500/20 text-cyan-400 border-cyan-500/40' 
            : 'bg-white/5 text-white/40 border-white/10'"
        >
          <GlobeAltIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.googleConfigured ? 'text-cyan-400' : 'text-white/60'"
        >
          {{ stats.googleConfigured ? 'Configured' : 'Inactive' }}
        </span>
        <span class="text-xs text-white/40">OAuth 2.0</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Android client</span>
        <span class="font-mono text-white/80 font-medium">{{ stats.googleAndroidConfigured ? 'Active' : 'Unset' }}</span>
      </div>
    </div>

    <!-- Card 4: Apple Sign-In -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.appleConfigured 
        ? 'border-violet-500/30 bg-[#12161C] hover:border-violet-400' 
        : 'border-white/[0.08] bg-[#12161C] hover:border-white/20'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.appleConfigured ? 'bg-violet-500/15 group-hover:bg-violet-500/25' : 'bg-white/5'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Apple Sign-In</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.appleConfigured 
            ? 'bg-violet-500/20 text-violet-400 border-violet-500/40' 
            : 'bg-white/5 text-white/40 border-white/10'"
        >
          <DevicePhoneMobileIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.appleConfigured ? 'text-violet-400' : 'text-white/60'"
        >
          {{ stats.appleConfigured ? 'Configured' : 'Inactive' }}
        </span>
        <span class="text-xs text-white/40">Services ID</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>iOS & Web sign-in</span>
        <span class="text-white/60">Cross-client</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { 
  CheckBadgeIcon, 
  EnvelopeIcon, 
  GlobeAltIcon, 
  DevicePhoneMobileIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      configuredCount: 0,
      totalServices: 3,
      percentageConfigured: 0,
      mailConfigured: false,
      googleConfigured: false,
      appleConfigured: false,
      googleAndroidConfigured: false,
    })
  }
});
</script>

