<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
        @keydown.esc="$emit('close')"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/75 backdrop-blur-md transition-opacity" @click="$emit('close')"></div>

        <!-- Modal Card -->
        <div class="relative w-full max-w-md overflow-hidden rounded-3xl border border-theme-border bg-theme-surface shadow-2xl z-10 animate-scale-up">
          <div class="absolute -right-20 -top-20 h-40 w-40 rounded-full bg-amber-500/15 blur-[60px] pointer-events-none"></div>

          <!-- Header -->
          <div class="flex items-center justify-between border-b border-theme-border px-6 py-5">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-500">
                <KeyIcon class="h-5 w-5" />
              </div>
              <div>
                <h3 class="text-base font-bold text-theme-text font-display">Reset Password</h3>
                <p class="text-xs text-theme-text-muted mt-0.5">Set a new password for {{ customer?.name }}</p>
              </div>
            </div>
            <button
              @click="$emit('close')"
              class="flex h-8 w-8 items-center justify-center rounded-xl border border-theme-border text-theme-text-muted hover:text-theme-text hover:bg-theme-border/30 transition-all"
            >
              <XMarkIcon class="h-4 w-4" />
            </button>
          </div>

          <!-- Form Body -->
          <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
            <!-- Account Info with Verification Status & Admin Override -->
            <div class="rounded-xl border border-theme-border bg-theme-border/10 p-3.5 space-y-2">
              <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded-lg bg-admin/20 text-admin flex items-center justify-center font-bold text-xs shrink-0">
                  {{ initials(customer?.name) }}
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-xs font-bold text-theme-text truncate">{{ customer?.name }}</p>
                  <p class="text-[11px] font-mono text-theme-text-muted truncate">{{ customer?.email }}</p>
                </div>
              </div>
              <div class="pt-2 border-t border-theme-border/50 flex items-center justify-between gap-2 flex-wrap">
                <span
                  v-if="customer?.email_verified_at || customer?.is_email_verified"
                  class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-500 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                  Verified Email
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-amber-500 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-md"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                  Unverified Email (Admin Override)
                </span>
                <span class="text-[10px] text-theme-text-muted">
                  Dashboard Override Active
                </span>
              </div>
            </div>

            <!-- Password Input with Generate Options & Presets -->
            <div class="space-y-2">
              <div class="flex items-center justify-between mb-1">
                <label class="text-xs font-semibold text-theme-text uppercase tracking-wider">
                  New Password <span class="text-admin">*</span>
                </label>
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    @click="generateSimplePassword"
                    class="text-[11px] font-semibold text-amber-500 hover:text-amber-400 hover:underline flex items-center gap-1 transition-colors"
                    title="Generate an easy-to-read memorable temporary password"
                  >
                    <BoltIcon class="h-3 w-3" />
                    <span>Generate Simple</span>
                  </button>
                  <span class="text-theme-border">|</span>
                  <button
                    type="button"
                    @click="generatePassword"
                    class="text-[11px] font-semibold text-admin hover:underline flex items-center gap-1"
                    title="Generate a complex 12-character random password"
                  >
                    <SparklesIcon class="h-3 w-3" />
                    <span>Generate Strong</span>
                  </button>
                </div>
              </div>

              <!-- Quick Presets -->
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[10px] uppercase font-bold text-theme-text-muted mr-1">Quick Presets:</span>
                <button
                  v-for="preset in quickPresets"
                  :key="preset"
                  type="button"
                  @click="applyPassword(preset, `Preset applied: ${preset}`)"
                  class="rounded-lg border border-theme-border bg-theme-border/20 hover:bg-admin/15 hover:border-admin/40 hover:text-admin px-2 py-0.5 text-[11px] font-mono text-theme-text transition-all"
                >
                  {{ preset }}
                </button>
              </div>

              <div class="relative">
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  minlength="8"
                  placeholder="Min. 8 characters (e.g. Candy1234)"
                  class="w-full rounded-xl border border-theme-border bg-theme-surface pl-4 pr-16 py-2.5 text-xs font-mono text-theme-text outline-none focus:border-admin/60 focus:ring-2 focus:ring-admin/20 transition-all"
                />
                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1.5 text-theme-text-muted">
                  <button
                    v-if="password"
                    type="button"
                    @click="copyPassword"
                    title="Copy to clipboard"
                    class="hover:text-theme-text p-0.5 rounded transition-colors"
                  >
                    <ClipboardDocumentCheckIcon v-if="copied" class="h-4 w-4 text-emerald-500" />
                    <ClipboardDocumentIcon v-else class="h-4 w-4" />
                  </button>
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="hover:text-theme-text p-0.5 rounded transition-colors"
                  >
                    <EyeSlashIcon v-if="showPassword" class="h-4 w-4" />
                    <EyeIcon v-else class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Confirm Password -->
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-semibold text-theme-text uppercase tracking-wider">
                  Confirm Password <span class="text-admin">*</span>
                </label>
                <button
                  v-if="password && password !== confirmPassword"
                  type="button"
                  @click="confirmPassword = password"
                  class="text-[11px] text-admin hover:underline font-semibold"
                >
                  Match New Password
                </button>
              </div>
              <input
                v-model="confirmPassword"
                :type="showPassword ? 'text' : 'password'"
                required
                minlength="8"
                placeholder="Re-enter password"
                class="w-full rounded-xl border border-theme-border bg-theme-surface px-4 py-2.5 text-xs font-mono text-theme-text outline-none focus:border-admin/60 focus:ring-2 focus:ring-admin/20 transition-all"
              />
              <p v-if="mismatchError" class="text-[11px] text-red-500 mt-1">
                Passwords do not match.
              </p>
            </div>

            <p class="text-[11px] text-theme-text-muted">
              Admins can assign any simple password (min. 8 characters, e.g. simple words or numbers). No symbols required.
            </p>

            <!-- Warning Notice -->
            <div class="rounded-xl bg-amber-500/10 border border-amber-500/20 p-3 text-[11px] text-amber-600 dark:text-amber-400">
              Resetting will immediately terminate all active sessions for this customer.
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-theme-border mt-4">
              <button
                type="button"
                @click="$emit('close')"
                :disabled="loading"
                class="rounded-xl border border-theme-border px-4 py-2.5 text-xs font-semibold text-theme-text hover:bg-theme-border/30 transition-all"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading || !password || password !== confirmPassword"
                class="flex items-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-400 px-5 py-2.5 text-xs font-bold text-obsidian shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50"
              >
                <span v-if="loading" class="h-3.5 w-3.5 border-2 border-obsidian/30 border-t-obsidian rounded-full animate-spin"></span>
                <span>Reset Password</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import {
  KeyIcon,
  XMarkIcon,
  SparklesIcon,
  EyeIcon,
  EyeSlashIcon,
  BoltIcon,
  ClipboardDocumentIcon,
  ClipboardDocumentCheckIcon
} from '@heroicons/vue/24/outline';
import { useToast } from '@/core/composables/useToast';

const props = defineProps({
  isOpen: Boolean,
  customer: {
    type: Object,
    default: null
  },
  loading: Boolean
});

const emit = defineEmits(['close', 'save']);
const toast = useToast();

const password = ref('');
const confirmPassword = ref('');
const showPassword = ref(true);
const copied = ref(false);

const quickPresets = computed(() => {
  const presets = ['Candy1234', 'Welcome123'];
  const rawName = props.customer?.name?.trim();
  if (rawName) {
    const firstName = rawName.split(' ')[0].replace(/[^a-zA-Z]/g, '');
    if (firstName.length >= 3) {
      const capName = firstName.charAt(0).toUpperCase() + firstName.slice(1).toLowerCase();
      const customPreset = `${capName}1234`;
      if (!presets.includes(customPreset)) {
        presets.unshift(customPreset);
      }
    }
  }
  return presets;
});

watch(() => props.isOpen, (open) => {
  if (open) {
    password.value = '';
    confirmPassword.value = '';
    showPassword.value = true;
    copied.value = false;
  }
});

const mismatchError = computed(() => {
  return password.value && confirmPassword.value && password.value !== confirmPassword.value;
});

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function applyPassword(val, successMessage) {
  password.value = val;
  confirmPassword.value = val;
  showPassword.value = true;
  copyToClipboard(val);
  if (successMessage) {
    toast.success(successMessage);
  }
}

function generateSimplePassword() {
  const words = ['Candy', 'Fresh', 'Clean', 'Sharp', 'Style', 'Smart'];
  const word = words[Math.floor(Math.random() * words.length)];
  const num = Math.floor(1000 + Math.random() * 9000);
  const simplePass = `${word}${num}`;
  applyPassword(simplePass, `Simple password generated & copied: ${simplePass}`);
}

function generatePassword() {
  const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%^&*';
  let pass = '';
  for (let i = 0; i < 12; i++) {
    pass += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  applyPassword(pass, 'Generated strong password & copied to clipboard');
}

function copyToClipboard(text) {
  if (!text) return;
  if (navigator?.clipboard?.writeText) {
    navigator.clipboard.writeText(text).catch(() => fallbackCopy(text));
  } else {
    fallbackCopy(text);
  }
  copied.value = true;
  setTimeout(() => {
    copied.value = false;
  }, 2000);
}

function fallbackCopy(text) {
  try {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
  } catch {
    // Fallback best effort
  }
}

function copyPassword() {
  if (!password.value) return;
  copyToClipboard(password.value);
  toast.success('Password copied to clipboard');
}

function handleSubmit() {
  const trimmed = password.value.trim();
  if (!trimmed || trimmed.length < 8 || trimmed !== confirmPassword.value.trim()) return;
  emit('save', trimmed);
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.animate-scale-up {
  animation: scaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes scaleUp {
  from { opacity: 0; transform: scale(0.96) translateY(6px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>

