<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-sky-500/15 border border-sky-500/30 flex items-center justify-center text-sky-400 shadow-[0_0_15px_rgba(14,165,233,0.15)]">
          <UserGroupIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Customer Portal Teaser</h2>
          <p class="text-xs text-white/50 mt-0.5">Call-to-action block driving user account signups and mobile portal bookings.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 04
      </span>
    </div>

    <div class="grid lg:grid-cols-2 gap-8">
      <!-- Left: Copywriting Inputs -->
      <div class="space-y-5">
        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Eyebrow / Small Title
          </label>
          <input 
            v-model="settings.portal_teaser_title" 
            type="text" 
            placeholder="e.g. The CandyCutz Portal" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-xs font-semibold text-white placeholder-white/20 focus:border-sky-500/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-sky-500/15 transition-all outline-none" 
          />
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Main Heading / Subtitle
          </label>
          <input 
            v-model="settings.portal_teaser_subtitle" 
            type="text" 
            placeholder="e.g. Unlock the Full Experience" 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-base font-display text-white placeholder-white/20 focus:border-sky-500/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-sky-500/15 transition-all outline-none" 
          />
        </div>

        <div class="space-y-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Description Text
          </label>
          <textarea 
            v-model="settings.portal_teaser_text" 
            rows="5" 
            placeholder="Create an account to browse our extensive list of services..." 
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white/80 placeholder-white/20 focus:border-sky-500/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-sky-500/15 transition-all outline-none resize-none leading-relaxed"
          ></textarea>
        </div>
      </div>

      <!-- Right: Real-time Public Card Mockup Preview -->
      <div class="space-y-3">
        <label class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-white/70">
          <SparklesIcon class="h-3.5 w-3.5 text-sky-400" />
          Live Public Visual Mockup
        </label>

        <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] p-6 sm:p-8 space-y-4 shadow-2xl">
          <div class="absolute -right-10 -bottom-10 h-36 w-36 rounded-full bg-sky-500/10 blur-2xl pointer-events-none"></div>

          <div class="space-y-1 relative z-10">
            <span class="text-[11px] font-bold uppercase tracking-widest text-sky-400 font-mono">
              {{ settings.portal_teaser_title || 'The CandyCutz Portal' }}
            </span>
            <h3 class="font-display text-xl sm:text-2xl text-white font-bold tracking-tight">
              {{ settings.portal_teaser_subtitle || 'Unlock the Full Experience' }}
            </h3>
          </div>

          <p class="text-xs sm:text-sm text-white/60 leading-relaxed relative z-10 line-clamp-4">
            {{ settings.portal_teaser_text || 'Create an account to browse our extensive list of services, check barber availability in real-time, view our exclusive gallery, and manage your appointments seamlessly from your own personalized dashboard.' }}
          </p>

          <div class="pt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 relative z-10 border-t border-white/[0.06]">
            <div class="flex items-center justify-center rounded-xl bg-gradient-to-r from-admin to-admin-light px-4 py-2.5 text-xs font-bold text-obsidian shadow-md cursor-default pointer-events-none">
              Log in to Customer Portal
            </div>
            <div class="flex items-center justify-center rounded-xl border border-white/20 bg-white/[0.05] px-4 py-2.5 text-xs font-bold text-white cursor-default pointer-events-none">
              Create Account
            </div>
          </div>
        </div>

        <p class="text-xs text-white/40 leading-relaxed">
          This section directly prompts guests to become registered customers, enabling instant appointment tracking and receipts.
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
  settings: {
    type: Object,
    required: true
  }
});
</script>
