<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 shadow-[0_0_15px_rgba(245,158,11,0.15)]">
          <UserGroupIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Barber Team Showcase Teaser</h2>
          <p class="text-xs text-white/50 mt-0.5">The introduction section displayed above the public master barber roster.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 02
      </span>
    </div>

    <div class="grid lg:grid-cols-2 gap-8">
      <!-- Left: Form Inputs -->
      <div class="space-y-5">
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Section Title
          </label>
          <input 
            v-model="content.about_team_title" 
            type="text" 
            placeholder="e.g. Meet the team" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-xs font-semibold text-white placeholder-white/20 focus:border-amber-500/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-amber-500/15 transition-all outline-none" 
          />
        </div>

        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
              Subtitle / Team Description
            </label>
            <span class="text-[10px] font-mono text-white/40">
              {{ (content.about_team_subtitle || '').length }} chars
            </span>
          </div>
          <textarea 
            v-model="content.about_team_subtitle" 
            rows="4" 
            placeholder="Our barbers are masters of their craft, bringing years of experience..." 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white/80 placeholder-white/20 focus:border-amber-500/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-amber-500/15 transition-all outline-none resize-none leading-relaxed"
          ></textarea>
        </div>

        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 flex items-center justify-between gap-4">
          <div class="space-y-0.5">
            <p class="text-xs font-bold text-white">Need to update stylists or chairs?</p>
            <p class="text-[11px] text-white/50">Manage individual barber profiles, specialties, and schedules.</p>
          </div>
          <router-link 
            to="/admin/barbers" 
            class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/10 text-admin hover:bg-admin/10 transition-all"
          >
            Barber Roster →
          </router-link>
        </div>
      </div>

      <!-- Right: Real-time Public Preview Card -->
      <div class="space-y-3">
        <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/70">
          <SparklesIcon class="h-3.5 w-3.5 text-amber-400" />
          Live Team Header Mockup
        </label>

        <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] p-6 sm:p-8 space-y-4 shadow-2xl text-center">
          <div class="absolute -right-10 -bottom-10 h-36 w-36 rounded-full bg-amber-500/10 blur-2xl pointer-events-none"></div>

          <div class="space-y-2 relative z-10">
            <h3 class="font-display text-2xl sm:text-3xl text-gold font-bold tracking-tight">
              {{ content.about_team_title || 'Meet the team' }}
            </h3>
            <p class="text-xs sm:text-sm text-white/60 leading-relaxed max-w-md mx-auto">
              {{ content.about_team_subtitle || 'Our barbers are masters of their craft, bringing years of experience and a passion for perfection to every cut.' }}
            </p>
          </div>

          <!-- Stylist Mockup Grid Strip -->
          <div class="pt-4 grid grid-cols-3 gap-2 relative z-10 border-t border-white/[0.06]">
            <div v-for="i in 3" :key="i" class="rounded-xl border border-white/10 bg-white/[0.03] p-3 text-center space-y-1.5">
              <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-300 font-bold mx-auto flex items-center justify-center text-xs">
                ★
              </div>
              <p class="text-[10px] font-bold text-white truncate">Stylist {{ i }}</p>
              <p class="text-[9px] text-emerald-400 font-mono">Available</p>
            </div>
          </div>
        </div>

        <p class="text-xs text-white/40 leading-relaxed">
          The public About Us page automatically populates the master barber cards directly beneath this text from your active staff database.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  UserGroupIcon, 
  SparklesIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  content: {
    type: Object,
    required: true
  }
});
</script>
