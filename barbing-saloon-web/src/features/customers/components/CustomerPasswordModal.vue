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
            <!-- Account Info -->
            <div class="rounded-xl border border-theme-border bg-theme-border/10 p-3 flex items-center gap-3">
              <div class="h-8 w-8 rounded-lg bg-admin/20 text-admin flex items-center justify-center font-bold text-xs shrink-0">
                {{ initials(customer?.name) }}
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-theme-text truncate">{{ customer?.name }}</p>
                <p class="text-[11px] font-mono text-theme-text-muted truncate">{{ customer?.email }}</p>
              </div>
            </div>

            <!-- Password Input with Generate Option -->
            <div>
              <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-semibold text-theme-text uppercase tracking-wider">
                  New Password <span class="text-admin">*</span>
                </label>
                <button
                  type="button"
                  @click="generatePassword"
                  class="text-[11px] font-semibold text-admin hover:underline flex items-center gap-1"
                >
                  <SparklesIcon class="h-3 w-3" />
                  <span>Generate Strong</span>
                </button>
              </div>
              <div class="relative">
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  minlength="8"
                  placeholder="Minimum 8 characters"
                  class="w-full rounded-xl border border-theme-border bg-theme-surface pl-4 pr-10 py-2.5 text-xs font-mono text-theme-text outline-none focus:border-admin/60 focus:ring-2 focus:ring-admin/20 transition-all"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-theme-text-muted hover:text-theme-text"
                >
                  <EyeSlashIcon v-if="showPassword" class="h-4 w-4" />
                  <EyeIcon v-else class="h-4 w-4" />
                </button>
              </div>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="block text-xs font-semibold text-theme-text uppercase tracking-wider mb-1.5">
                Confirm Password <span class="text-admin">*</span>
              </label>
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
import { KeyIcon, XMarkIcon, SparklesIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';
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

watch(() => props.isOpen, (open) => {
  if (open) {
    password.value = '';
    confirmPassword.value = '';
    showPassword.value = true;
  }
});

const mismatchError = computed(() => {
  return password.value && confirmPassword.value && password.value !== confirmPassword.value;
});

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function generatePassword() {
  const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%^&*';
  let pass = '';
  for (let i = 0; i < 12; i++) {
    pass += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  password.value = pass;
  confirmPassword.value = pass;
  showPassword.value = true;
  navigator.clipboard?.writeText(pass);
  toast.success('Generated and copied password to clipboard');
}

function handleSubmit() {
  if (password.value !== confirmPassword.value) return;
  emit('save', password.value);
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

