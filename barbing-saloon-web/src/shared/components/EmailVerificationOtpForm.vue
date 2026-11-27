<template>
  <div class="space-y-3.5 sm:space-y-4 animate-fade-in">
    <div class="space-y-2">
      <div class="flex flex-wrap items-center justify-between gap-1.5">
        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-white/70">
          Enter 6-Digit Code
        </label>
        <span class="text-[11px] text-slate-500 dark:text-white/40">Expires in 15 minutes</span>
      </div>

      <div class="flex flex-col sm:flex-row gap-2">
        <input
          :value="code"
          @input="$emit('update:code', $event.target.value)"
          type="text"
          inputmode="numeric"
          maxlength="6"
          autocomplete="one-time-code"
          placeholder="123456"
          class="w-full min-h-[46px] bg-slate-50 dark:bg-white/[0.03] border border-slate-300 dark:border-white/[0.1] rounded-lg md:rounded-xl px-3 sm:px-4 py-2.5 text-center text-base sm:text-lg font-mono tracking-[0.28em] sm:tracking-[0.4em] font-bold text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/20 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 outline-none transition-all"
          @keyup.enter="$emit('verify')"
        />
        <div class="grid grid-cols-2 sm:contents gap-2">
          <button
            type="button"
            @click="$emit('paste')"
            class="min-h-[42px] px-3.5 rounded-lg md:rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-slate-700 dark:text-white font-semibold text-xs border border-slate-200 dark:border-white/[0.1] transition-all cursor-pointer shrink-0 flex items-center justify-center gap-1.5"
            title="Paste 6-digit code from clipboard"
          >
            <ClipboardDocumentIcon class="w-4 h-4 text-slate-500 dark:text-white/60 shrink-0" />
            <span>Paste</span>
          </button>
          <button
            type="button"
            @click="$emit('verify')"
            :disabled="verifying || code.length !== 6"
            class="min-h-[42px] px-4 sm:px-6 rounded-lg md:rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm md:shadow-md md:shadow-emerald-600/20 transition-all disabled:opacity-40 cursor-pointer shrink-0 flex items-center justify-center gap-1.5"
          >
            <ArrowPathIcon v-if="verifying" class="w-4 h-4 animate-spin shrink-0" />
            <CheckCircleIcon v-else class="w-4 h-4 shrink-0" />
            <span>{{ verifying ? 'Verifying...' : 'Verify' }}</span>
          </button>
        </div>
      </div>
    </div>

    <div
      class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 dark:text-white/50 pt-2 border-t border-slate-100 dark:border-white/[0.04]"
    >
      <span v-if="cooldownSeconds > 0" class="text-[11px] tabular-nums">
        Resend available in <strong class="text-slate-800 dark:text-white font-mono">{{ cooldownSeconds }}s</strong>
      </span>
      <button
        v-else
        type="button"
        @click="$emit('resend')"
        :disabled="sending"
        class="min-h-[36px] text-xs font-semibold text-[var(--color-accent)] hover:underline cursor-pointer"
      >
        Resend Verification Code
      </button>

      <span class="text-[11px] text-slate-400 dark:text-white/30">Check inbox &amp; spam folder</span>
    </div>
  </div>
</template>

<script setup>
import { ArrowPathIcon, CheckCircleIcon, ClipboardDocumentIcon } from '@heroicons/vue/24/outline';

defineProps({
  code: { type: String, default: '' },
  verifying: { type: Boolean, default: false },
  sending: { type: Boolean, default: false },
  cooldownSeconds: { type: Number, default: 0 },
});

defineEmits(['update:code', 'paste', 'verify', 'resend']);
</script>
