<template>
  <div class="mt-6">
    <div class="flex items-center gap-3 mb-4">
      <div class="flex-1 h-px bg-theme-border"></div>
      <span class="text-[10px] uppercase tracking-wider text-theme-muted font-semibold">{{ label }}</span>
      <div class="flex-1 h-px bg-theme-border"></div>
    </div>

    <p v-if="socialError" class="bg-red-500/10 border border-red-500/30 rounded-lg p-3 text-xs text-red-400 font-medium mb-3">
      {{ socialError }}
    </p>

    <div class="grid grid-cols-2 gap-3">
      <button
        type="button"
        @click="googleSignIn"
        :disabled="socialBusy"
        class="flex items-center justify-center gap-2 rounded-full bg-white px-3 py-2.5 text-xs font-semibold text-obsidian transition-all duration-300 hover:shadow-[0_0_16px_rgba(212,175,55,0.25)] disabled:opacity-60"
      >
        <svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0"><path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.19-2.27H12v4.51h6.47c-.29 1.48-1.14 2.73-2.4 3.58v3h3.86c2.26-2.09 3.56-5.17 3.56-8.82z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.86-3c-1.08.72-2.45 1.16-4.07 1.16-3.13 0-5.78-2.11-6.73-4.96H1.29v3.09C3.26 21.3 7.31 24 12 24z"/><path fill="#FBBC05" d="M5.27 14.29c-.25-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29V6.62H1.29C.47 8.24 0 10.06 0 12s.47 3.76 1.29 5.38l3.98-3.09z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.7 1.29 6.62l3.98 3.09c.95-2.85 3.6-4.96 6.73-4.96z"/></svg>
        <span>Google</span>
      </button>

      <button
        type="button"
        @click="appleSignIn"
        :disabled="socialBusy"
        class="flex items-center justify-center gap-2 rounded-full bg-white px-3 py-2.5 text-xs font-semibold text-obsidian transition-all duration-300 hover:shadow-[0_0_16px_rgba(212,175,55,0.25)] disabled:opacity-60"
      >
        <svg viewBox="0 0 24 24" class="w-4 h-4 shrink-0" fill="currentColor"><path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.53 4.08zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/></svg>
        <span>{{ socialBusy ? 'Signing in...' : 'Apple' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { useSocialAuth } from '../composables/useSocialAuth';

defineProps({
  label: {
    type: String,
    default: 'or continue with',
  },
});

const social = useSocialAuth();
const { socialBusy, socialError, googleSignIn, appleSignIn } = social;
</script>