<template>
  <div
    class="min-h-[100dvh] flex flex-col items-center justify-center bg-theme-bg text-theme-text px-4 py-10 sm:py-14 relative overflow-hidden"
  >
    <div
      class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-gold/10 via-transparent to-transparent pointer-events-none"
    ></div>

    <div class="relative z-10 w-full max-w-lg">
      <!-- Brand & Intro -->
      <div class="text-center mb-7">
        <div
          class="inline-flex items-center justify-center h-14 w-14 rounded-2xl border border-gold/30 bg-gold/10 mb-4 shadow-sm"
        >
          <ShieldCheckIcon class="h-7 w-7 text-gold" />
        </div>

        <div class="flex items-center justify-center mb-2">
          <span class="text-xs font-bold uppercase tracking-[0.28em]">
            <BrandWordmark
              :first="brandNameFirst"
              :second="brandNameSecond"
              :gold-first="brandGoldPart === 'first'"
              :space-between="brandNameSeparator === 'space'"
            />
          </span>
        </div>

        <h1 class="font-display text-2xl sm:text-3xl font-bold text-theme-text tracking-tight">
          Review &amp; accept our policies
        </h1>

        <p class="mt-2.5 max-w-md mx-auto text-center text-sm text-theme-muted leading-relaxed">
          Please review our Terms of Service and Privacy Policy to continue to your account.
        </p>

        <div
          v-if="accountIdentifier"
          class="mt-3.5 inline-flex items-center gap-2 rounded-full border border-theme-border bg-theme-surface px-3 py-1 text-xs text-theme-muted"
        >
          <span class="h-1.5 w-1.5 rounded-full bg-gold"></span>
          <span>Signed in as</span>
          <span class="font-medium text-theme-text">{{ accountIdentifier }}</span>
        </div>
      </div>

      <!-- Consent Card -->
      <div
        class="rounded-2xl border border-theme-border bg-theme-surface p-5 sm:p-7 shadow-[0_20px_60px_rgba(0,0,0,0.28)]"
      >
        <div class="flex items-center justify-between gap-3 mb-4">
          <h2 class="text-xs font-semibold uppercase tracking-wider text-theme-muted">
            Required agreements
          </h2>
          <span
            v-if="termsVersion"
            class="inline-flex items-center rounded-full border border-gold/30 bg-gold/10 px-2.5 py-0.5 text-[11px] font-semibold text-gold"
          >
            Version {{ termsVersion }}
          </span>
        </div>

        <!-- Scannable Policy Links -->
        <div class="space-y-2.5">
          <a
            href="/terms"
            target="_blank"
            rel="noopener"
            class="group flex items-center justify-between gap-3 rounded-xl border border-theme-border bg-theme-bg/40 px-4 py-3 transition-all hover:border-gold/50 hover:bg-gold/5"
          >
            <div class="flex items-start gap-3 min-w-0">
              <div
                class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-theme-border bg-theme-surface text-gold"
              >
                <DocumentTextIcon class="h-4 w-4" />
              </div>
              <div class="min-w-0">
                <div class="text-sm font-semibold text-theme-text group-hover:text-gold transition-colors">
                  Terms of Service
                </div>
                <div class="text-xs text-theme-muted leading-snug mt-0.5">
                  Booking rules, cancellations, payments, and account use
                </div>
              </div>
            </div>
            <span class="inline-flex items-center gap-1 shrink-0 text-xs font-medium text-gold">
              <span>Read</span>
              <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
            </span>
          </a>

          <a
            href="/privacy"
            target="_blank"
            rel="noopener"
            class="group flex items-center justify-between gap-3 rounded-xl border border-theme-border bg-theme-bg/40 px-4 py-3 transition-all hover:border-gold/50 hover:bg-gold/5"
          >
            <div class="flex items-start gap-3 min-w-0">
              <div
                class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-theme-border bg-theme-surface text-gold"
              >
                <LockClosedIcon class="h-4 w-4" />
              </div>
              <div class="min-w-0">
                <div class="text-sm font-semibold text-theme-text group-hover:text-gold transition-colors">
                  Privacy Policy
                </div>
                <div class="text-xs text-theme-muted leading-snug mt-0.5">
                  How we collect, use, and protect your personal data
                </div>
              </div>
            </div>
            <span class="inline-flex items-center gap-1 shrink-0 text-xs font-medium text-gold">
              <span>Read</span>
              <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
            </span>
          </a>
        </div>

        <!-- Audit Note -->
        <div
          class="mt-4 flex items-start gap-2.5 rounded-xl border border-theme-border/70 bg-theme-bg/30 px-3.5 py-2.5 text-xs text-theme-muted leading-relaxed"
        >
          <InformationCircleIcon class="h-4 w-4 shrink-0 text-theme-muted mt-0.5" />
          <span>
            We record the date, time, policy version, IP address, and browser for your account consent history.
          </span>
        </div>

        <!-- Accessible Confirmation Checkbox -->
        <label
          for="consent-accept"
          :class="[
            'mt-5 flex items-start gap-3 rounded-xl border px-4 py-3.5 cursor-pointer select-none transition-all',
            accepted
              ? 'border-gold/60 bg-gold/10'
              : 'border-theme-border bg-theme-bg/30 hover:border-gold/40',
          ]"
        >
          <input
            id="consent-accept"
            v-model="accepted"
            type="checkbox"
            class="sr-only peer"
          />
          <span
            :class="[
              'mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-md border transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-gold/60',
              accepted
                ? 'border-gold bg-gold text-obsidian'
                : 'border-theme-border bg-theme-surface text-transparent',
            ]"
            aria-hidden="true"
          >
            <CheckIcon class="h-3.5 w-3.5 stroke-[2.5]" />
          </span>
          <span class="text-sm font-medium text-theme-text leading-snug">
            I have read and agree to the Terms of Service and Privacy Policy.
          </span>
        </label>

        <!-- Validation / API Error -->
        <div
          v-if="error"
          role="alert"
          class="mt-4 flex items-start gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-3.5 py-2.5 text-xs font-medium text-red-400"
        >
          <ExclamationCircleIcon class="h-4 w-4 shrink-0 mt-0.5" />
          <span>{{ error }}</span>
        </div>

        <!-- Primary Action -->
        <button
          type="button"
          :disabled="!accepted || submitting"
          :aria-disabled="!accepted || submitting"
          class="mt-5 w-full rounded-xl bg-gold px-4 py-3 text-sm font-bold text-obsidian tracking-wide transition-all duration-200 hover:bg-gold-light hover:shadow-[0_0_24px_rgba(255,153,0,0.28)] active:scale-[0.99] disabled:opacity-45 disabled:cursor-not-allowed disabled:hover:bg-gold disabled:hover:shadow-none disabled:active:scale-100 cursor-pointer"
          @click="submit"
        >
          <span class="flex items-center justify-center gap-2">
            <svg
              v-if="submitting"
              class="animate-spin h-4 w-4 text-obsidian"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              />
            </svg>
            {{ submitting ? 'Recording acceptance...' : 'Accept and continue' }}
          </span>
        </button>

        <!-- Secondary Action -->
        <div class="mt-4 pt-3.5 border-t border-theme-border/60 text-center">
          <button
            type="button"
            class="text-xs font-medium text-theme-muted hover:text-gold transition-colors cursor-pointer"
            @click="signOut"
          >
            Sign out and switch account
          </button>
        </div>
      </div>

      <p class="mt-6 text-center text-[10px] uppercase tracking-[0.25em] text-theme-muted">
        &copy; {{ currentYear }} {{ brandName }}. All rights reserved.
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  ArrowTopRightOnSquareIcon,
  CheckIcon,
  DocumentTextIcon,
  ExclamationCircleIcon,
  InformationCircleIcon,
  LockClosedIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/24/outline';
import { useAuthStore } from '../store/auth.store';
import { authApi } from '@/shared/api/old_authApi';
import { useBrandIdentity } from '@/shared/composables/useBrandIdentity';
import BrandWordmark from '@/shared/ui/BrandWordmark.vue';

const {
  brandName,
  brandNameFirst,
  brandNameSecond,
  brandNameSeparator,
  brandGoldPart,
} = useBrandIdentity();
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const accepted = ref(false);
const submitting = ref(false);
const error = ref('');
const currentYear = new Date().getFullYear();

const termsVersion = computed(() => auth.user?.consent?.terms_version || null);
const accountIdentifier = computed(() => auth.user?.email || auth.user?.name || '');

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
