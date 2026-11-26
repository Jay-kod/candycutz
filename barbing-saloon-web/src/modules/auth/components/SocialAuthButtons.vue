<template>
  <div class="w-full my-4" data-testid="social-auth-buttons">
    <div class="flex items-center gap-3 mb-3.5">
      <div class="flex-1 h-px bg-theme-border/60"></div>
      <span class="text-[10px] uppercase tracking-wider text-theme-muted font-semibold shrink-0">{{ label }}</span>
      <div class="flex-1 h-px bg-theme-border/60"></div>
    </div>

    <!-- Error message if social auth fails -->
    <p v-if="socialError" class="bg-red-500/10 border border-red-500/30 rounded-xl p-3 text-xs text-red-400 font-medium mb-3 flex items-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-red-400" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
      </svg>
      <span>{{ socialError }}</span>
    </p>

    <button
      v-if="!googleButtonRendered"
      type="button"
      @click="googleSignIn"
      :disabled="socialBusy"
      data-testid="google-signin-btn"
      :aria-label="socialBusy ? 'Connecting to Google...' : displayButtonText"
      class="group relative flex w-full min-h-[44px] items-center justify-center gap-3 rounded-lg border border-neutral-200/90 bg-white px-4 py-2.5 text-sm font-semibold text-obsidian shadow-sm transition-all duration-300 hover:border-gold/60 hover:bg-neutral-50 hover:shadow-[0_0_20px_rgba(212,175,55,0.22)] active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-gold/50 disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer touch-manipulation select-none"
    >
      <svg
        v-if="!socialBusy"
        viewBox="0 0 24 24"
        class="h-5 w-5 shrink-0 transition-transform duration-200 group-hover:scale-105"
        aria-hidden="true"
      >
        <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.19-2.27H12v4.51h6.47c-.29 1.48-1.14 2.73-2.4 3.58v3h3.86c2.26-2.09 3.56-5.17 3.56-8.82z"/>
        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.86-3c-1.08.72-2.45 1.16-4.07 1.16-3.13 0-5.78-2.11-6.73-4.96H1.29v3.09C3.26 21.3 7.31 24 12 24z"/>
        <path fill="#FBBC05" d="M5.27 14.29c-.25-.72-.38-1.49-.38-2.29s.14-1.57.38-2.29V6.62H1.29C.47 8.24 0 10.06 0 12s.47 3.76 1.29 5.38l3.98-3.09z"/>
        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.7 1.29 6.62l3.98 3.09c.95-2.85 3.6-4.96 6.73-4.96z"/>
      </svg>
      <svg
        v-else
        class="h-5 w-5 animate-spin text-obsidian shrink-0"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        aria-hidden="true"
      >
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span class="tracking-normal font-medium text-obsidian">
        {{ socialBusy ? 'Connecting to Google...' : displayButtonText }}
      </span>
    </button>
    <div
      v-show="googleButtonRendered"
      ref="googleButtonContainer"
      class="flex w-full min-h-[44px] items-center justify-center overflow-hidden rounded-lg"
      data-testid="google-signin-container"
    ></div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { useSocialAuth } from '../composables/useSocialAuth';

const props = defineProps({
  label: {
    type: String,
    default: 'or continue with',
  },
  buttonText: {
    type: String,
    default: '',
  },
});

const { socialBusy, socialError, googleSignIn, renderGoogleButton } = useSocialAuth();
const googleButtonContainer = ref(null);
const googleButtonRendered = ref(false);
const displayButtonText = computed(() => {
  if (props.buttonText) {
    return props.buttonText;
  }
  if (props.label && props.label.toLowerCase().includes('sign up')) {
    return 'Sign up with Google';
  }
  return 'Continue with Google';
});

let checkTimer;
onMounted(async () => {
  await nextTick();
  if (typeof renderGoogleButton !== 'function') {
    return;
  }
  await renderGoogleButton(googleButtonContainer.value);

  let elapsed = 0;
  checkTimer = setInterval(() => {
    elapsed += 500;
    const renderedButton = googleButtonContainer.value?.querySelector('[role="button"], .nsm7Bb-HzV7m-LgbsSe');
    if (renderedButton) {
      googleButtonRendered.value = true;
      clearInterval(checkTimer);
    } else if (elapsed >= 8000) {
      clearInterval(checkTimer);
    }
  }, 500);
});

onBeforeUnmount(() => {
  clearInterval(checkTimer);
});
</script>
