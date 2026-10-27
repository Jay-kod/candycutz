<template>
  <div class="rounded-[2rem] border border-white/[0.08] bg-[#12161C] overflow-hidden shadow-2xl transition-all">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-5 bg-white/[0.02] border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-11 w-11 rounded-2xl bg-violet-500/10 border border-violet-500/30 flex items-center justify-center text-violet-400 shadow-[0_0_20px_rgba(139,92,246,0.15)]">
          <DevicePhoneMobileIcon class="h-5 w-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="font-display text-lg font-bold text-white tracking-tight">Apple Sign-In</h2>
            <span class="text-[10px] font-mono font-bold text-violet-300 px-2 py-0.5 rounded bg-violet-500/10 border border-violet-500/20">Services ID</span>
          </div>
          <p class="text-xs text-white/50 mt-0.5">Allows iOS and web clients to authenticate using Apple ID accounts.</p>
        </div>
      </div>

      <!-- Status Pill -->
      <span 
        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border shrink-0"
        :class="appleConfigured 
          ? 'bg-violet-950/80 text-violet-300 border-violet-500/30' 
          : 'bg-white/[0.03] text-white/40 border-white/[0.08]'"
      >
        <span class="h-1.5 w-1.5 rounded-full" :class="appleConfigured ? 'bg-violet-400' : 'bg-white/30'"></span>
        <span>{{ appleConfigured ? 'Apple Active' : 'Not Configured' }}</span>
      </span>
    </div>

    <!-- Body Settings -->
    <div class="p-6 sm:p-8 space-y-6">
      <div class="grid md:grid-cols-3 gap-5">
        <!-- Services ID -->
        <div class="space-y-1.5 md:col-span-3">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/50">Apple Services ID (Client ID)</label>
          <input 
            v-model="form.apple_client_id" 
            type="text" 
            class="w-full bg-white/[0.03] border border-white/[0.08] rounded-xl px-4 py-3 text-xs text-white placeholder:text-white/25 focus:border-violet-500/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-violet-500/15 outline-none transition-all font-mono" 
            placeholder="com.candycutz.app" 
          />
          <p class="text-[11px] text-white/40">From Apple Developer → Certificates, IDs &amp; Profiles → Identifiers → Services IDs.</p>
        </div>

        <!-- Team ID -->
        <div class="space-y-1.5">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/50">Apple Team ID</label>
          <input 
            v-model="form.apple_team_id" 
            type="text" 
            class="w-full bg-white/[0.03] border border-white/[0.08] rounded-xl px-4 py-3 text-xs text-white placeholder:text-white/25 focus:border-violet-500/50 focus:bg-white/[0.06] outline-none transition-all font-mono" 
            placeholder="e.g. 10-char Team ID" 
          />
        </div>

        <!-- Key ID -->
        <div class="space-y-1.5">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/50">Apple Key ID</label>
          <input 
            v-model="form.apple_key_id" 
            type="text" 
            class="w-full bg-white/[0.03] border border-white/[0.08] rounded-xl px-4 py-3 text-xs text-white placeholder:text-white/25 focus:border-violet-500/50 focus:bg-white/[0.06] outline-none transition-all font-mono" 
            placeholder="e.g. 10-char Key ID" 
          />
        </div>

        <!-- App URL -->
        <div class="space-y-1.5">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/50">Application Callback URL</label>
          <input 
            v-model="form.app_url" 
            type="text" 
            class="w-full bg-white/[0.03] border border-white/[0.08] rounded-xl px-4 py-3 text-xs text-white placeholder:text-white/25 focus:border-violet-500/50 focus:bg-white/[0.06] outline-none transition-all font-mono" 
            placeholder="https://candycutz.com" 
          />
        </div>
      </div>

      <!-- Footer CTA -->
      <div class="pt-4 border-t border-white/[0.06]">
        <button 
          @click="$emit('save')" 
          :disabled="saving" 
          class="inline-flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold text-obsidian bg-gradient-to-r from-admin to-admin-light shadow-[0_4px_20px_rgba(255,103,0,0.25)] hover:shadow-[0_6px_28px_rgba(255,103,0,0.4)] hover:scale-[1.01] active:scale-[0.99] transition-all disabled:opacity-50"
        >
          <ArrowPathIcon v-if="saving" class="h-4 w-4 animate-spin" />
          <span>{{ saving ? 'Saving Apple Settings...' : 'Save Apple Settings' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  DevicePhoneMobileIcon, 
  ArrowPathIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  form: {
    type: Object,
    required: true
  },
  appleConfigured: {
    type: Boolean,
    default: false
  },
  saving: {
    type: Boolean,
    default: false
  }
});

defineEmits(['save']);
</script>

