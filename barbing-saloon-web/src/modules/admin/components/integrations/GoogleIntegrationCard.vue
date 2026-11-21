<template>
  <div class="rounded-[2rem] border border-white/[0.08] bg-[#12161C] overflow-hidden shadow-2xl transition-all">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-5 bg-white/[0.02] border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-11 w-11 rounded-2xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.15)]">
          <GlobeAltIcon class="h-5 w-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="font-display text-lg font-bold text-white tracking-tight">Google Sign-In</h2>
            <span class="text-[10px] font-mono font-bold text-cyan-300 px-2 py-0.5 rounded bg-cyan-500/10 border border-cyan-500/20">OAuth 2.0</span>
          </div>
          <p class="text-xs text-white/50 mt-0.5">Enables Google login for customers using the web portal.</p>
        </div>
      </div>

      <!-- Status Pill -->
      <span 
        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border shrink-0"
        :class="googleConfigured 
          ? 'bg-cyan-950/80 text-cyan-300 border-cyan-500/30' 
          : 'bg-white/[0.03] text-white/40 border-white/[0.08]'"
      >
        <span class="h-1.5 w-1.5 rounded-full" :class="googleConfigured ? 'bg-cyan-400' : 'bg-white/30'"></span>
        <span>{{ googleConfigured ? 'OAuth Active' : 'Not Configured' }}</span>
      </span>
    </div>

    <!-- Body Settings -->
    <div class="p-6 sm:p-8 space-y-6">
      <div class="grid md:grid-cols-2 gap-5">
        <!-- Web Client ID -->
        <div class="space-y-1.5 md:col-span-2">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/50">Google Web Client ID</label>
          <input 
            v-model="form.google_client_id" 
            type="text" 
            class="w-full bg-white/[0.03] border border-white/[0.08] rounded-xl px-4 py-3 text-xs text-white placeholder:text-white/25 focus:border-cyan-500/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-cyan-500/15 outline-none transition-all font-mono" 
            placeholder="1234567890-abcdef.apps.googleusercontent.com" 
          />
          <p class="text-[11px] text-white/40">From Google Cloud Console: APIs &amp; Services → Credentials → OAuth 2.0 Client ID (Web).</p>
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
          <span>{{ saving ? 'Saving Google Settings...' : 'Save Google Settings' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  GlobeAltIcon, 
  ArrowPathIcon
} from '@heroicons/vue/24/outline';

defineProps({
  form: {
    type: Object,
    required: true
  },
  googleConfigured: {
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
