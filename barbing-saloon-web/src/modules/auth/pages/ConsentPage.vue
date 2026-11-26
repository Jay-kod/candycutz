<template>
  <div class="min-h-[100dvh] flex flex-col items-center justify-center bg-theme-bg text-theme-text px-4 py-10 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-gold/10 via-transparent to-transparent pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-lg">
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center h-16 w-16 rounded-full border border-gold/40 bg-gold/10 mb-5">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8 text-gold">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
          </svg>
        </div>
        <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-gold mb-2">{{ brandName }}</p>
        <h1 class="font-display text-3xl sm:text-4xl font-bold text-theme-text">One last step</h1>
        <p class="mt-3 text-sm text-theme-muted leading-relaxed">
          Before you continue, please review and accept our Terms of Service and Privacy Policy.
          Your account stays locked until you do.
        </p>
      </div>

      <div class="rounded-2xl border border-graphite bg-theme-surface p-6 shadow-[0_20px_60px_rgba(0,0,0,0.35)]">
        <div class="space-y-3 text-sm text-theme-muted">
          <p>
            By accepting, you confirm that you have read and agree to the
            <a href="/terms" target="_blank" rel="noopener" class="font-semibold text-gold underline underline-offset-2 hover:text-gold-dark">Terms of Service</a>
            and consent to how we handle your personal data under our
            <a href="/privacy" target="_blank" rel="noopener" class="font-semibold text-gold underline underline-offset-2 hover:text-gold-dark">Privacy Policy</a>.
          </p>
          <p class="text-xs">
            For your protection and ours, we record the date, time, policy version, IP address and browser of every acceptance.
          </p>
        </div>

        <div v-if="termsVersion" class="mt-4 rounded-lg border border-graphite/70 bg-theme-bg/40 px-3 py-2 text-[11px] uppercase tracking-wider text-theme-muted">
          Policy version <span class="text-gold font-semibold">{{ termsVersion }}</span>
        </div>

        <label for="consent-accept" class="mt-5 flex items-start gap-3 cursor-pointer select-none">
          <input
            id="consent-accept"
            v-model="accepted"
            type="checkbox"
            class="mt-0.5 h-4 w-4 shrink-0 rounded border-graphite bg-theme-bg text-gold accent-gold focus:ring-gold"
          />
          <span class="text-sm text-theme-text leading-relaxed">
            I have read and agree to the Terms of Service and Privacy Policy.
          </span>
        </label>

        <p v-if="error" class="mt-3 text-xs text-red-400 font-medium">{{ error }}</p>

        <button
          type="button"
          :disabled="submitting"
          class="mt-6 w-full rounded-lg bg-gold px-4 py-3 text-sm font-bold text-obsidian tracking-wide transition-all duration-300 hover:bg-gold-dark hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-70 disabled:cursor-not-allowed"
          @click="submit"
        >
          <span class="flex items-center justify-center gap-2">
            <svg v-if="submitting" class="animate-spin h-4 w-4 text-obsidian" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ submitting ? 'Recording acceptance...' : 'Accept and continue' }}
          </span>
        </button>

        <button
          type="button"
          class="mt-3 w-full text-center text-xs text-theme-muted hover:text-gold transition-colors"
          @click="signOut"
        >
          Sign out instead
        </button>
      </div>

      <p class="mt-6 text-center text-[10px] uppercase tracking-[0.25em] text-theme-muted">
        &copy; {{ new Date().getFullYear() }} {{ brandName }}. All rights reserved.
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../store/auth.store';
import { authApi } from '@/shared/api/old_authApi';
import { useBrandIdentity } from '@/shared/composables/useBrandIdentity';

const { brandName } = useBrandIdentity();
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const accepted = ref(false);
const submitting = ref(false);
const error = ref('');

const termsVersion = computed(() => auth.user?.consent?.terms_version || null);

function nextRoute() {
  const redirect = route.query.redirect;
  if (typeof redirect === 'string' && redirect.startsWith('/') && !redirect.startsWith('//')) {
    return redirect;
  }
  return auth.redirectAfterLogin();
}

async function submit() {
  error.value = '';

  if (!accepted.value) {
    error.value = 'Please tick the box to confirm you accept the Terms of Service and Privacy Policy.';
    return;
  }

  submitting.value = true;

  try {
    await authApi.acceptConsent({
      type: 'terms',
      version: termsVersion.value,
    });

    await auth.fetchUser();
    await router.replace(nextRoute());
  } catch (err) {
    error.value = err?.message || 'We could not record your acceptance. Please try again.';
  } finally {
    submitting.value = false;
  }
}

async function signOut() {
  await auth.logout();
  await router.replace('/customer/login');
}

onMounted(() => {
  if (auth.isAuthenticated && !auth.user?.consent_required) {
    router.replace(nextRoute());
  }
});
</script>
