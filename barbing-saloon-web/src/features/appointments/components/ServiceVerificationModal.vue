<template>
  <transition name="fade">
    <div
      v-if="open"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
      role="dialog"
      aria-modal="true"
      aria-labelledby="service-verification-title"
      @keydown.esc="handleClose"
    >
      <div
        class="absolute inset-0 bg-black/65 backdrop-blur-sm transition-opacity"
        @click="handleClose"
      ></div>

      <div
        class="relative w-full max-w-md rounded-3xl border border-theme-border bg-theme-surface p-6 sm:p-8 shadow-2xl overflow-hidden animate-fade-in text-center"
        @click.stop
      >
        <!-- Top accent rim -->
        <div
          class="absolute top-0 left-1/4 right-1/4 h-[2px] rounded-full bg-gradient-to-r from-transparent via-gold to-transparent"
        ></div>

        <!-- Close button -->
        <button
          type="button"
          @click="handleClose"
          :disabled="isVerifying"
          aria-label="Close verification modal"
          class="absolute top-4 right-4 inline-flex h-10 w-10 items-center justify-center rounded-full text-theme-muted hover:bg-theme-surface-subtle hover:text-theme-text transition-colors disabled:opacity-40"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>

        <!-- Icon badge -->
        <div
          class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gold/10 border border-gold/30 text-gold shadow-sm"
        >
          <ShieldCheckIcon class="h-7 w-7" />
        </div>

        <h3 id="service-verification-title" class="font-display text-2xl font-bold text-theme-text mb-1.5">
          Service Verification
        </h3>
        <p class="text-center text-sm text-theme-muted leading-relaxed mb-5">
          Ask the customer for their booking verification code (e.g.
          <span class="font-mono font-semibold text-theme-text">CC-ESNNSR</span>) to complete this
          booking.
        </p>

        <!-- Booking Context Summary -->
        <div
          v-if="booking"
          class="mb-5 rounded-2xl border border-theme-border bg-theme-surface-subtle/70 px-4 py-3 text-left"
        >
          <div class="flex items-center justify-between gap-2 mb-1">
            <span class="text-xs font-bold text-theme-text truncate">
              {{ customerLabel }}
            </span>
            <span
              v-if="booking.status"
              class="shrink-0 rounded-full border border-theme-border bg-theme-surface px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-theme-muted"
            >
              {{ formatStatusLabel(booking.status) }}
            </span>
          </div>
          <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-xs text-theme-muted">
            <span class="truncate font-medium text-gold-dark dark:text-gold">
              {{ serviceLabel }}
            </span>
            <span v-if="scheduleLabel" class="font-mono text-[11px] shrink-0">
              {{ scheduleLabel }}
            </span>
          </div>
        </div>

        <div class="space-y-4">
          <!-- Input Header -->
          <div class="text-left">
            <div class="flex items-center justify-between gap-2 mb-1.5">
              <label
                for="service-verification-code"
                class="text-[11px] font-bold uppercase tracking-wider text-theme-muted"
              >
                Verification Code
              </label>
              <span
                v-if="isCodeValid"
                class="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 border border-emerald-500/25 px-2 py-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400"
              >
                <CheckCircleIcon class="h-3 w-3" />
                Ready to verify
              </span>
              <span v-else class="text-[10px] font-medium text-theme-muted">
                {{ codeInput ? 'Enter full code (e.g. CC-ESNNSR)' : 'From My Codes / Bookings' }}
              </span>
            </div>

            <div class="relative">
              <input
                ref="codeInputRef"
                id="service-verification-code"
                :value="codeInput"
                @input="handleInput"
                @paste="handleNativePaste"
                @keydown.enter.prevent="handleSubmit"
                type="text"
                placeholder="e.g. CC-ESNNSR"
                maxlength="24"
                autocomplete="off"
                autocapitalize="characters"
                spellcheck="false"
                :disabled="isVerifying"
                class="w-full rounded-xl border bg-theme-bg py-3.5 pl-4 pr-11 text-center font-mono text-lg sm:text-xl font-bold tracking-[0.2em] uppercase text-theme-text placeholder:text-theme-muted/50 placeholder:tracking-normal placeholder:font-sans placeholder:text-sm outline-none transition-all focus:ring-4"
                :class="
                  errorMessage
                    ? 'border-red-500/70 focus:border-red-500 focus:ring-red-500/15'
                    : isCodeValid
                    ? 'border-emerald-500/60 focus:border-emerald-500 focus:ring-emerald-500/15'
                    : 'border-theme-border focus:border-gold focus:ring-gold/15'
                "
              />
              <button
                v-if="codeInput && !isVerifying"
                type="button"
                @click="clearCode"
                aria-label="Clear verification code"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 inline-flex h-8 w-8 items-center justify-center rounded-lg text-theme-muted hover:bg-theme-surface-subtle hover:text-theme-text transition-colors"
              >
                <XMarkIcon class="h-4 w-4" />
              </button>
            </div>

            <p class="text-center text-[11px] text-theme-muted mt-2">
              Accepts the full code (<span class="font-mono">CC-XXXXXX</span>) or its 6-character
              passcode.
            </p>
          </div>

          <!-- Inline Validation / Server Error -->
          <div
            v-if="errorMessage"
            role="alert"
            class="flex items-start gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-3.5 py-2.5 text-left text-xs font-medium text-red-600 dark:text-red-400"
          >
            <ExclamationCircleIcon class="h-4 w-4 shrink-0 mt-0.5" />
            <span>{{ errorMessage }}</span>
          </div>

          <!-- Action Buttons -->
          <div class="space-y-2.5 pt-1">
            <button
              type="button"
              @click="pasteCode"
              :disabled="isVerifying"
              class="w-full min-h-[44px] flex items-center justify-center gap-2 rounded-xl border border-theme-border bg-theme-surface-subtle py-2.5 px-4 text-xs font-bold uppercase tracking-wider text-theme-text transition-all hover:bg-theme-surface-hover hover:border-gold/40 disabled:opacity-50"
            >
              <ClipboardDocumentIcon class="h-4 w-4 text-gold-dark dark:text-gold" />
              <span>Paste from Clipboard</span>
            </button>

            <button
              type="button"
              @click="handleSubmit"
              :disabled="!isCodeValid || isVerifying"
              class="w-full min-h-[48px] flex items-center justify-center gap-2 rounded-xl py-3.5 px-5 text-sm font-bold transition-all"
              :class="
                isCodeValid && !isVerifying
                  ? 'bg-gold text-obsidian hover:bg-gold-light shadow-lg shadow-gold/25 active:scale-[0.99]'
                  : 'bg-theme-surface-subtle text-theme-muted border border-theme-border cursor-not-allowed'
              "
            >
              <span
                v-if="isVerifying"
                class="w-5 h-5 border-2 border-current/30 border-t-current rounded-full animate-spin"
              ></span>
              <ShieldCheckIcon v-else class="h-5 w-5" />
              <span>{{ isVerifying ? 'Verifying...' : 'Verify & Complete' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import {
  CheckCircleIcon,
  ClipboardDocumentIcon,
  ExclamationCircleIcon,
  ShieldCheckIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';
import { useToast } from '@/core/composables/useToast';
import {
  isValidVerificationCode,
  normalizeVerificationCode,
} from '@/features/appointments/utils/verificationCode';

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  booking: {
    type: Object,
    default: null,
  },
  isVerifying: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['close', 'submit', 'clear-error']);

const toast = useToast();
const codeInput = ref('');
const codeInputRef = ref(null);

const isCodeValid = computed(() => isValidVerificationCode(codeInput.value));

const customerLabel = computed(() => {
  return (
    props.booking?.customer_name ||
    props.booking?.client_name ||
    props.booking?.customer?.name ||
    'Customer Booking'
  );
});

const serviceLabel = computed(() => {
  return (
    props.booking?.service_name ||
    props.booking?.service?.name ||
    'Grooming Service'
  );
});

const scheduleLabel = computed(() => {
  const date = props.booking?.appointment_date;
  const time = props.booking?.appointment_time;
  if (!date && !time) return '';
  if (date && time) {
    const formattedDate = new Date(date).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
    });
    return `${formattedDate} • ${time}`;
  }
  return time || date;
});

watch(
  () => props.open,
  async (isOpen) => {
    if (isOpen) {
      codeInput.value = '';
      await nextTick();
      codeInputRef.value?.focus();
    } else {
      codeInput.value = '';
    }
  }
);

function formatStatusLabel(status) {
  return String(status || '').replace(/_/g, ' ');
}

function handleInput(event) {
  codeInput.value = normalizeVerificationCode(event.target.value);
  if (props.errorMessage) {
    emit('clear-error');
  }
}

function handleNativePaste(event) {
  const pasted = event.clipboardData?.getData('text');
  if (pasted) {
    event.preventDefault();
    codeInput.value = normalizeVerificationCode(pasted);
    if (props.errorMessage) {
      emit('clear-error');
    }
  }
}

function clearCode() {
  codeInput.value = '';
  if (props.errorMessage) {
    emit('clear-error');
  }
  codeInputRef.value?.focus();
}

async function pasteCode() {
  try {
    if (!navigator.clipboard || !navigator.clipboard.readText) {
      toast.error('Browser does not support clipboard pasting directly. Please paste manually.');
      return;
    }
    const text = await navigator.clipboard.readText();
    const normalized = normalizeVerificationCode(text);
    if (normalized) {
      codeInput.value = normalized;
      if (props.errorMessage) {
        emit('clear-error');
      }
      toast.success('Code pasted');
      codeInputRef.value?.focus();
    } else {
      toast.error('Clipboard is empty');
    }
  } catch (err) {
    console.error('Clipboard error:', err);
    toast.error('Could not read from clipboard. Please paste manually.');
  }
}

function handleClose() {
  if (props.isVerifying) return;
  emit('close');
}

function handleSubmit() {
  if (!isCodeValid.value || props.isVerifying) return;
  emit('submit', normalizeVerificationCode(codeInput.value));
}
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px) scale(0.98);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
