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
          <p class="text-xs text-white/50 mt-0.5">Enables one-tap Google login across customer web portal and mobile app.</p>
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

        <!-- Android Client ID -->
        <div class="space-y-1.5 md:col-span-2">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/50">Google Android Client ID</label>
          <input 
            v-model="form.google_android_client_id" 
            type="text" 
            class="w-full bg-white/[0.03] border border-white/[0.08] rounded-xl px-4 py-3 text-xs text-white placeholder:text-white/25 focus:border-cyan-500/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-cyan-500/15 outline-none transition-all font-mono" 
            placeholder="1234567890-abcdef.apps.googleusercontent.com" 
          />
          <p class="text-[11px] text-white/40">From Google Cloud Console: OAuth 2.0 Client ID with application type Android using the metadata below.</p>
        </div>

        <!-- Android Setup Helper Box -->
        <div class="md:col-span-2 rounded-2xl border border-cyan-500/20 bg-cyan-500/[0.03] p-5 space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
              <h4 class="text-xs font-bold text-white uppercase tracking-wider">Android OAuth Setup Details</h4>
              <p class="text-[11px] text-white/40 mt-0.5">Use these exact parameters when creating the Android OAuth client in Google Cloud Console.</p>
            </div>
            <button 
              @click="$emit('generateAndroid')" 
              type="button" 
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-cyan-300 border border-cyan-500/30 bg-cyan-500/10 hover:bg-cyan-500/20 transition-colors"
            >
              <span>Auto-Fill Defaults</span>
            </button>
          </div>

          <div class="grid md:grid-cols-3 gap-4 pt-1">
            <!-- App Name -->
            <div class="space-y-1">
              <label class="block text-[10px] font-bold uppercase tracking-wider text-white/40">App Name</label>
              <input 
                v-model="form.google_android_name" 
                class="w-full bg-black/30 border border-white/10 rounded-xl px-3 py-2 text-xs text-white outline-none focus:border-cyan-500/50" 
              />
            </div>

            <!-- Package Name -->
            <div class="space-y-1">
              <label class="block text-[10px] font-bold uppercase tracking-wider text-white/40">Package Name</label>
              <div class="flex gap-1.5">
                <input 
                  v-model="form.google_android_package" 
                  class="min-w-0 flex-1 bg-black/30 border border-white/10 rounded-xl px-3 py-2 text-xs text-white font-mono outline-none focus:border-cyan-500/50" 
                />
                <button 
                  @click="$emit('copy', form.google_android_package, 'Package Name')" 
                  type="button" 
                  class="px-2.5 py-1.5 rounded-xl border border-white/10 text-xs text-white/70 hover:text-white hover:bg-white/5"
                  title="Copy package name"
                >
                  <DocumentDuplicateIcon class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>

            <!-- SHA-1 Fingerprint -->
            <div class="space-y-1">
              <label class="block text-[10px] font-bold uppercase tracking-wider text-white/40">SHA-1 Fingerprint</label>
              <div class="flex gap-1.5">
                <input 
                  v-model="form.google_android_sha1" 
                  class="min-w-0 flex-1 bg-black/30 border border-white/10 rounded-xl px-3 py-2 text-xs text-white font-mono outline-none focus:border-cyan-500/50" 
                />
                <button 
                  @click="$emit('copy', form.google_android_sha1, 'SHA-1 Fingerprint')" 
                  type="button" 
                  class="px-2.5 py-1.5 rounded-xl border border-white/10 text-xs text-white/70 hover:text-white hover:bg-white/5"
                  title="Copy SHA-1 fingerprint"
                >
                  <DocumentDuplicateIcon class="h-3.5 w-3.5" />
                </button>
              </div>
            </div>
          </div>
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
  ArrowPathIcon, 
  DocumentDuplicateIcon 
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

defineEmits(['save', 'generateAndroid', 'copy']);
</script>
