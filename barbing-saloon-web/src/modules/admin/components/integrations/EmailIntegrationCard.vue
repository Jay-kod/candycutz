<template>
  <div class="rounded-[2rem] border border-white/[0.08] bg-[#12161C] overflow-hidden shadow-2xl transition-all">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-5 bg-white/[0.02] border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-admin/20 to-amber-500/10 border border-admin/30 flex items-center justify-center text-admin shadow-[0_0_20px_rgba(255,103,0,0.15)]">
          <EnvelopeIcon class="h-5 w-5" />
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="font-display text-lg font-bold text-white tracking-tight">Email Notifications</h2>
            <span class="text-[10px] font-mono font-bold text-admin px-2 py-0.5 rounded bg-admin/10 border border-admin/20">Resend API</span>
          </div>
          <p class="text-xs text-white/50 mt-0.5">Drives customer signup, booking receipts, and password-reset emails.</p>
        </div>
      </div>

      <!-- Status Pill & Link -->
      <div class="flex items-center gap-2 shrink-0">
        <router-link
          to="/admin/resend"
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold text-admin hover:text-white bg-admin/10 hover:bg-admin/20 border border-admin/30 transition-colors"
        >
          <span>Dedicated Resend Console</span>
          <ArrowTopRightOnSquareIcon class="h-3 w-3" />
        </router-link>
        <span 
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border shrink-0"
          :class="mailConfigured 
            ? 'bg-emerald-950/80 text-emerald-400 border-emerald-500/30' 
            : 'bg-amber-950/80 text-amber-400 border-amber-500/30'"
        >
          <span class="h-1.5 w-1.5 rounded-full" :class="mailConfigured ? 'bg-emerald-400' : 'bg-amber-400'"></span>
          <span>{{ mailConfigured ? 'Gateway Connected' : 'Not Configured' }}</span>
        </span>
      </div>
    </div>

    <!-- Body Settings -->
    <div class="p-6 sm:p-8 space-y-6">
      <div class="grid md:grid-cols-2 gap-5">
        <!-- Resend API Key -->
        <div class="space-y-1.5 md:col-span-2">
          <div class="flex items-center justify-between">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-white/50">Resend API Key</label>
            <button 
              type="button" 
              @click="$emit('togglePassword')"
              class="text-[11px] text-admin hover:text-admin-light transition-colors"
            >
              {{ showPassword ? 'Hide Key' : 'Reveal Key' }}
            </button>
          </div>
          <input 
            v-model="form.resend_api_key" 
            :type="showPassword ? 'text' : 'password'" 
            autocomplete="off" 
            class="w-full bg-white/[0.03] border border-white/[0.08] rounded-xl px-4 py-3 text-xs text-white placeholder:text-white/25 focus:border-admin/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 outline-none transition-all font-mono" 
            placeholder="re_123456789_abcdefghijklmnopqrstuvwxyz" 
          />
          <p class="text-[11px] text-white/40">From Resend Dashboard → API Keys. Key starts with <code>re_</code>.</p>
        </div>

        <!-- From Email -->
        <div class="space-y-1.5">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/50">From Sender Email</label>
          <input 
            v-model="form.mail_from" 
            type="email" 
            class="w-full bg-white/[0.03] border border-white/[0.08] rounded-xl px-4 py-3 text-xs text-white placeholder:text-white/25 focus:border-admin/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 outline-none transition-all" 
            placeholder="no-reply@candycutz.com" 
          />
        </div>

        <!-- From Name -->
        <div class="space-y-1.5">
          <label class="block text-[11px] font-bold uppercase tracking-wider text-white/50">From Sender Name</label>
          <input 
            v-model="form.mail_from_name" 
            type="text" 
            class="w-full bg-white/[0.03] border border-white/[0.08] rounded-xl px-4 py-3 text-xs text-white placeholder:text-white/25 focus:border-admin/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 outline-none transition-all" 
            placeholder="CandyCutz" 
          />
        </div>
      </div>

      <!-- Action Toolbar with Test Email Tool -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t border-white/[0.06]">
        <!-- Save CTA -->
        <button 
          @click="$emit('save')" 
          :disabled="saving" 
          class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-xs font-bold text-obsidian bg-gradient-to-r from-admin to-admin-light shadow-[0_4px_20px_rgba(255,103,0,0.25)] hover:shadow-[0_6px_28px_rgba(255,103,0,0.4)] hover:scale-[1.01] active:scale-[0.99] transition-all disabled:opacity-50"
        >
          <ArrowPathIcon v-if="saving" class="h-4 w-4 animate-spin" />
          <span>{{ saving ? 'Saving Email Settings...' : 'Save Email Settings' }}</span>
        </button>

        <!-- Test Email Dispatcher -->
        <div class="flex flex-wrap items-center gap-2">
          <input 
            :value="testEmail" 
            @input="$emit('update:testEmail', $event.target.value)"
            type="email" 
            placeholder="test-recipient@example.com" 
            class="w-full sm:w-56 bg-white/[0.03] border border-white/[0.08] rounded-xl px-3.5 py-2.5 text-xs text-white placeholder:text-white/30 focus:border-admin/50 outline-none" 
          />
          <button 
            @click="$emit('test')" 
            :disabled="testing || !mailConfigured" 
            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-admin bg-admin/10 hover:bg-admin/20 border border-admin/30 transition-all disabled:opacity-40"
            title="Dispatch immediate test email"
          >
            <PaperAirplaneIcon v-if="!testing" class="h-3.5 w-3.5" />
            <ArrowPathIcon v-else class="h-3.5 w-3.5 animate-spin" />
            <span>{{ testing ? 'Sending...' : 'Send Test' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  EnvelopeIcon, 
  ArrowPathIcon, 
  PaperAirplaneIcon,
  ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline';

defineProps({
  form: {
    type: Object,
    required: true
  },
  mailConfigured: {
    type: Boolean,
    default: false
  },
  saving: {
    type: Boolean,
    default: false
  },
  testing: {
    type: Boolean,
    default: false
  },
  testEmail: {
    type: String,
    default: ''
  },
  showPassword: {
    type: Boolean,
    default: false
  }
});

defineEmits(['save', 'test', 'update:testEmail', 'togglePassword']);
</script>

