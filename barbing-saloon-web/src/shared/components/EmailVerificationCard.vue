<template>
  <div
    class="rounded-xl md:rounded-3xl border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] bg-[var(--color-bg-secondary)] md:bg-white md:dark:bg-[#12161C] p-4 sm:p-6 lg:p-8 shadow-none md:shadow-xl space-y-4 sm:space-y-6 transition-all"
  >
    <!-- Header with Icon & Status Badge -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 pb-4 sm:pb-5 border-b border-[var(--color-border)] md:border-slate-100 md:dark:border-white/[0.06]"
    >
      <div class="flex items-start sm:items-center gap-3 min-w-0">
        <div
          class="h-10 w-10 rounded-lg md:rounded-2xl flex items-center justify-center shrink-0 border"
          :class="
            isVerified
              ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500'
              : 'bg-amber-500/10 border-amber-500/20 text-amber-500'
          "
        >
          <ShieldCheckIcon v-if="isVerified" class="w-5 h-5" />
          <ExclamationTriangleIcon v-else class="w-5 h-5" />
        </div>
        <div class="min-w-0">
          <h3 class="font-display text-base sm:text-lg text-slate-900 dark:text-white font-bold leading-snug">
            Email Verification &amp; Security
          </h3>
          <p class="text-xs text-slate-500 dark:text-white/50 text-left-forced">
            Account identity lock &amp; booking confirmation delivery
          </p>
        </div>
      </div>

      <div class="shrink-0 self-start sm:self-auto">
        <span
          v-if="isVerified"
          class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-md md:rounded-full text-xs font-bold bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400"
        >
          <CheckCircleIcon class="w-4 h-4 shrink-0" />
          <span>Verified &amp; Protected</span>
        </span>
        <span
          v-else
          class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-md md:rounded-full text-xs font-bold bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400"
        >
          <ClockIcon class="w-4 h-4 shrink-0" />
          <span>Unverified</span>
        </span>
      </div>
    </div>

    <!-- State A: VERIFIED -->
    <div v-if="isVerified" class="space-y-3.5 sm:space-y-4">
      <div class="p-3.5 sm:p-4 rounded-xl md:rounded-2xl bg-emerald-500/5 border border-emerald-500/20 space-y-2">
        <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 dark:text-emerald-400">
          <CheckIcon class="w-4 h-4 shrink-0" />
          <span>Permanently Secured to Your Account</span>
        </div>
        <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed text-left-forced">
          Your email address
          <strong class="text-slate-900 dark:text-white font-mono break-all">{{ displayEmail }}</strong>
          has been verified. It is locked to your CandyCutz identity and cannot be registered or claimed by any other user.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 text-xs">
        <div
          class="p-3 sm:p-3.5 rounded-lg md:rounded-xl bg-[var(--color-bg-tertiary)]/50 md:bg-slate-50 md:dark:bg-white/[0.02] border border-[var(--color-border)] md:border-slate-200/60 md:dark:border-white/[0.04] space-y-1"
        >
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-white/40">
            Delivery Destination
          </span>
          <p class="text-slate-800 dark:text-white font-semibold text-left-forced">
            Booking Confirmations &amp; Receipts
          </p>
          <p class="text-[11px] text-slate-500 dark:text-white/40 text-left-forced">
            All invoices and schedule updates route here.
          </p>
        </div>
        <div
          class="p-3 sm:p-3.5 rounded-lg md:rounded-xl bg-[var(--color-bg-tertiary)]/50 md:bg-slate-50 md:dark:bg-white/[0.02] border border-[var(--color-border)] md:border-slate-200/60 md:dark:border-white/[0.04] space-y-1"
        >
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-white/40">
            Verified On
          </span>
          <p class="text-slate-800 dark:text-white font-semibold tabular-nums text-left-forced">
            {{ formattedVerifiedDate }}
          </p>
          <p class="text-[11px] text-slate-500 dark:text-white/40 text-left-forced">
            Secured via cryptographic hash validation.
          </p>
        </div>
      </div>
    </div>

    <!-- State B: UNVERIFIED -->
    <div v-else class="space-y-4 sm:space-y-5">
      <div class="p-3.5 sm:p-4 rounded-xl md:rounded-2xl bg-amber-500/5 border border-amber-500/20 space-y-2">
        <p class="text-xs text-slate-700 dark:text-white/80 leading-relaxed text-left-forced">
          Your current email
          <strong class="text-slate-900 dark:text-white font-mono break-all">{{ displayEmail }}</strong>
          has not been verified yet. Verify your email now to:
        </p>
        <ul class="text-xs text-slate-600 dark:text-white/70 space-y-1 list-disc list-inside text-left">
          <li>Prevent duplicate registration or identity claims with this email.</li>
          <li>Receive automated booking confirmation emails and payment receipts.</li>
          <li>Receive important appointment reminders, cancellations, and schedule notifications.</li>
        </ul>
      </div>

      <div v-if="!codeSent" class="space-y-2.5 sm:space-y-3">
        <button
          type="button"
          @click="sendVerificationCode"
          :disabled="sending"
          class="w-full min-h-[44px] flex items-center justify-center gap-2 rounded-lg md:rounded-xl bg-[var(--color-accent)] py-3 px-5 text-sm md:text-xs font-bold text-white shadow-sm md:shadow-lg md:shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-[0.99] transition-all disabled:opacity-50 cursor-pointer"
        >
          <ArrowPathIcon v-if="sending" class="w-4 h-4 animate-spin shrink-0" />
          <PaperAirplaneIcon v-else class="w-4 h-4 shrink-0" />
          <span>{{ sending ? 'Sending 6-Digit Code...' : 'Send Verification Code' }}</span>
        </button>
        <p class="text-[11px] text-center text-slate-500 dark:text-white/40 break-words">
          A 6-digit verification code will be sent to
          <span class="font-mono text-slate-700 dark:text-white/70 break-all">{{ displayEmail }}</span>.
        </p>
      </div>

      <EmailVerificationOtpForm
        v-else
        v-model:code="verificationCode"
        :verifying="verifying"
        :sending="sending"
        :cooldown-seconds="cooldownSeconds"
        @paste="pasteFromClipboard"
        @verify="verifyCode"
        @resend="sendVerificationCode"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import {
  ShieldCheckIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  CheckIcon,
  ClockIcon,
  PaperAirplaneIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline';
import { client } from '@/shared/api/client';
import { useAuthStore } from '@/modules/auth/store/auth.store';
import { useToast } from '@/core/composables/useToast';
import EmailVerificationOtpForm from './EmailVerificationOtpForm.vue';

const props = defineProps({ email: { type: String, default: '' } });
const emit = defineEmits(['verified']);
const authStore = useAuthStore();
const toast = useToast();

const sending = ref(false);
const verifying = ref(false);
const codeSent = ref(false);
const verificationCode = ref('');
const cooldownSeconds = ref(0);
let timerInterval = null;

const displayEmail = computed(() => props.email || authStore.user?.email || 'your email');
const isVerified = computed(() => !!authStore.user?.is_email_verified || !!authStore.user?.email_verified_at);
const formattedVerifiedDate = computed(() => {
  const raw = authStore.user?.email_verified_at;
  if (!raw) return 'Active';
  try {
    const d = new Date(raw);
    return isNaN(d.getTime()) ? String(raw) : d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
  } catch {
    return String(raw);
  }
});

async function checkStatus() {
  try {
    const res = await client.get('/auth/email/status');
    const data = res.data?.data || res.data;
    if (data?.is_email_verified && authStore.user) {
      authStore.setUser({ ...authStore.user, is_email_verified: true, email_verified_at: data.email_verified_at });
    }
  } catch {
    // Non-blocking
  }
}

async function sendVerificationCode() {
  if (sending.value || cooldownSeconds.value > 0) return;
  sending.value = true;
  try {
    const res = await client.post('/auth/email/verification-notification');
    codeSent.value = true;
    startCooldown(60);
    toast.success(res.data?.message || 'Verification code dispatched to your email.');
  } catch (error) {
    toast.error(error?.response?.data?.message || error?.response?.data?.error?.message || 'Failed to send verification code. Please try again.');
  } finally {
    sending.value = false;
  }
}

async function pasteFromClipboard() {
  try {
    if (navigator?.clipboard?.readText) {
      const cleaned = ((await navigator.clipboard.readText()) || '').trim().replace(/\D/g, '').slice(0, 6);
      if (cleaned) {
        verificationCode.value = cleaned;
        if (cleaned.length === 6 && !verifying.value) verifyCode();
      }
    }
  } catch {
    // Clipboard permission denied or unsupported
  }
}

async function verifyCode() {
  const code = verificationCode.value.trim();
  if (code.length !== 6 || verifying.value) return;
  verifying.value = true;
  try {
    const res = await client.post('/auth/email/verify-code', { code });
    toast.success(res.data?.message || 'Email verified successfully!');
    if (authStore.user) {
      authStore.setUser({ ...authStore.user, is_email_verified: true, email_verified_at: res.data?.data?.email_verified_at || new Date().toISOString() });
    }
    emit('verified', res.data?.data);
  } catch (error) {
    toast.error(error?.response?.data?.message || error?.response?.data?.error?.message || 'Invalid or expired code.');
  } finally {
    verifying.value = false;
  }
}

function startCooldown(sec) {
  cooldownSeconds.value = sec;
  clearInterval(timerInterval);
  timerInterval = setInterval(() => {
    if (cooldownSeconds.value <= 1) {
      clearInterval(timerInterval);
      cooldownSeconds.value = 0;
    } else {
      cooldownSeconds.value -= 1;
    }
  }, 1000);
}

onMounted(() => { checkStatus(); });
onUnmounted(() => { clearInterval(timerInterval); });
</script>
