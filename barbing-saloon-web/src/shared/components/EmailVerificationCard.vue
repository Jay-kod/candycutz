<template>
  <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-xl space-y-6 transition-all">
    <!-- Header with Icon & Status Badge -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100 dark:border-white/[0.06]">
      <div class="flex items-center gap-3">
        <div
          class="h-10 w-10 rounded-2xl flex items-center justify-center shrink-0 border"
          :class="isVerified
            ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500'
            : 'bg-amber-500/10 border-amber-500/20 text-amber-500'"
        >
          <ShieldCheckIcon v-if="isVerified" class="w-5 h-5" />
          <ExclamationTriangleIcon v-else class="w-5 h-5" />
        </div>
        <div>
          <h3 class="font-display text-lg text-slate-900 dark:text-white font-bold flex items-center gap-2">
            <span>Email Verification &amp; Security</span>
          </h3>
          <p class="text-xs text-slate-500 dark:text-white/50">
            Account identity lock &amp; booking confirmation delivery
          </p>
        </div>
      </div>

      <!-- Status Pill -->
      <div>
        <span
          v-if="isVerified"
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 border border-emerald-500/30 text-emerald-600 dark:text-emerald-400"
        >
          <CheckCircleIcon class="w-4 h-4" />
          <span>Verified &amp; Protected</span>
        </span>
        <span
          v-else
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400"
        >
          <ClockIcon class="w-4 h-4" />
          <span>Unverified</span>
        </span>
      </div>
    </div>

    <!-- State A: VERIFIED -->
    <div v-if="isVerified" class="space-y-4">
      <div class="p-4 rounded-2xl bg-emerald-500/5 border border-emerald-500/20 space-y-2">
        <div class="flex items-center gap-2 text-xs font-bold text-emerald-700 dark:text-emerald-400">
          <CheckIcon class="w-4 h-4" />
          <span>Permanently Secured to Your Account</span>
        </div>
        <p class="text-xs text-slate-600 dark:text-white/70 leading-relaxed">
          Your email address <strong class="text-slate-900 dark:text-white font-mono">{{ displayEmail }}</strong> has been verified. It is locked to your CandyCutz identity and cannot be registered or claimed by any other user.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200/60 dark:border-white/[0.04] space-y-1">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-white/40">Delivery Destination</span>
          <p class="text-slate-800 dark:text-white font-semibold">Booking Confirmations &amp; Receipts</p>
          <p class="text-[11px] text-slate-500 dark:text-white/40">All invoices and schedule updates route here.</p>
        </div>
        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200/60 dark:border-white/[0.04] space-y-1">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-white/40">Verified On</span>
          <p class="text-slate-800 dark:text-white font-semibold">{{ formattedVerifiedDate }}</p>
          <p class="text-[11px] text-slate-500 dark:text-white/40">Secured via cryptographic hash validation.</p>
        </div>
      </div>
    </div>

    <!-- State B: UNVERIFIED -->
    <div v-else class="space-y-5">
      <div class="p-4 rounded-2xl bg-amber-500/5 border border-amber-500/20 space-y-2">
        <p class="text-xs text-slate-700 dark:text-white/80 leading-relaxed">
          Your current email <strong class="text-slate-900 dark:text-white font-mono">{{ displayEmail }}</strong> has not been verified yet. Verify your email now to:
        </p>
        <ul class="text-xs text-slate-600 dark:text-white/70 space-y-1 list-disc list-inside">
          <li>Prevent duplicate registration or identity claims with this email.</li>
          <li>Receive automated booking confirmation emails and payment receipts.</li>
          <li>Receive important appointment reminders, cancellations, and schedule notifications.</li>
        </ul>
      </div>

      <!-- Action 1: Not yet sent OTP -->
      <div v-if="!codeSent" class="space-y-3">
        <button
          type="button"
          @click="sendVerificationCode"
          :disabled="sending"
          class="w-full flex items-center justify-center gap-2 rounded-xl bg-[var(--color-accent)] py-3 px-5 text-xs font-bold text-white shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-[0.99] transition-all disabled:opacity-50 cursor-pointer"
        >
          <ArrowPathIcon v-if="sending" class="w-4 h-4 animate-spin" />
          <PaperAirplaneIcon v-else class="w-4 h-4" />
          <span>{{ sending ? 'Sending 6-Digit Code...' : 'Send Verification Code' }}</span>
        </button>
        <p class="text-[11px] text-center text-slate-500 dark:text-white/40">
          A 6-digit verification code will be sent to <span class="font-mono text-slate-700 dark:text-white/70">{{ displayEmail }}</span>.
        </p>
      </div>

      <!-- Action 2: Code sent -> Enter OTP -->
      <div v-else class="space-y-4 animate-fade-in">
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-white/70">
              Enter 6-Digit Code
            </label>
            <span class="text-[11px] text-slate-500 dark:text-white/40">Expires in 15 minutes</span>
          </div>

          <div class="flex gap-2">
            <input
              v-model="verificationCode"
              type="text"
              inputmode="numeric"
              maxlength="6"
              autocomplete="one-time-code"
              placeholder="123456"
              class="w-full bg-slate-50 dark:bg-white/[0.03] border border-slate-300 dark:border-white/[0.1] rounded-xl px-4 py-3 text-center text-lg font-mono tracking-[0.4em] font-bold text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-white/20 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 outline-none transition-all"
              @keyup.enter="verifyCode"
            />
            <button
              type="button"
              @click="pasteFromClipboard"
              class="px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.06] dark:hover:bg-white/[0.1] text-slate-700 dark:text-white font-semibold text-xs border border-slate-200 dark:border-white/[0.1] transition-all cursor-pointer shrink-0 flex items-center justify-center gap-1.5"
              title="Paste 6-digit code from clipboard"
            >
              <ClipboardDocumentIcon class="w-4 h-4 text-slate-500 dark:text-white/60" />
              <span class="hidden sm:inline">Paste</span>
            </button>
            <button
              type="button"
              @click="verifyCode"
              :disabled="verifying || verificationCode.length !== 6"
              class="px-6 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all disabled:opacity-40 cursor-pointer shrink-0 flex items-center justify-center gap-1.5"
            >
              <ArrowPathIcon v-if="verifying" class="w-4 h-4 animate-spin" />
              <CheckCircleIcon v-else class="w-4 h-4" />
              <span>{{ verifying ? 'Verifying...' : 'Verify' }}</span>
            </button>
          </div>
        </div>

        <!-- Resend and status row -->
        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-white/50 pt-2 border-t border-slate-100 dark:border-white/[0.04]">
          <span v-if="cooldownSeconds > 0" class="text-[11px]">
            Resend available in <strong class="text-slate-800 dark:text-white font-mono">{{ cooldownSeconds }}s</strong>
          </span>
          <button
            v-else
            type="button"
            @click="sendVerificationCode"
            :disabled="sending"
            class="text-[11px] font-semibold text-[var(--color-accent)] hover:underline cursor-pointer"
          >
            Resend Verification Code
          </button>

          <span class="text-[11px] text-slate-400 dark:text-white/30">
            Check inbox &amp; spam folder
          </span>
        </div>
      </div>
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
  ClipboardDocumentIcon
} from '@heroicons/vue/24/outline';
import { client } from '@/shared/api/client';
import { useAuthStore } from '@/modules/auth/store/auth.store';
import { useToast } from '@/core/composables/useToast';

const props = defineProps({
  email: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['verified']);

const authStore = useAuthStore();
const toast = useToast();

const sending = ref(false);
const verifying = ref(false);
const codeSent = ref(false);
const verificationCode = ref('');
const cooldownSeconds = ref(0);
let timerInterval = null;

const displayEmail = computed(() => {
  return props.email || authStore.user?.email || 'your email';
});

const isVerified = computed(() => {
  return !!authStore.user?.is_email_verified || !!authStore.user?.email_verified_at;
});

const formattedVerifiedDate = computed(() => {
  const raw = authStore.user?.email_verified_at;
  if (!raw) return 'Active';
  try {
    const d = new Date(raw);
    return isNaN(d.getTime()) ? String(raw) : d.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  } catch {
    return String(raw);
  }
});

async function checkStatus() {
  try {
    const res = await client.get('/auth/email/status');
    const data = res.data?.data || res.data;
    if (data?.is_email_verified && authStore.user) {
      authStore.setUser({
        ...authStore.user,
        is_email_verified: true,
        email_verified_at: data.email_verified_at
      });
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
    const msg = error?.response?.data?.message || error?.response?.data?.error?.message || 'Failed to send verification code. Please try again.';
    toast.error(msg);
  } finally {
    sending.value = false;
  }
}

async function pasteFromClipboard() {
  try {
    if (navigator?.clipboard?.readText) {
      const text = await navigator.clipboard.readText();
      const cleaned = (text || '').trim().replace(/\D/g, '').slice(0, 6);
      if (cleaned) {
        verificationCode.value = cleaned;
        if (cleaned.length === 6 && !verifying.value) {
          verifyCode();
        }
      }
    }
  } catch (err) {
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
      authStore.setUser({
        ...authStore.user,
        is_email_verified: true,
        email_verified_at: res.data?.data?.email_verified_at || new Date().toISOString()
      });
    }

    emit('verified', res.data?.data);
  } catch (error) {
    const msg = error?.response?.data?.message || error?.response?.data?.error?.message || 'Invalid or expired code.';
    toast.error(msg);
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

onMounted(() => {
  checkStatus();
});

onUnmounted(() => {
  clearInterval(timerInterval);
});
</script>
