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
        <div 
          class="absolute inset-0 bg-black/75 backdrop-blur-md transition-opacity" 
          @click="$emit('close')"
        ></div>

        <!-- Modal Card -->
        <div class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-theme-border bg-theme-surface shadow-2xl z-10 animate-scale-up">
          <!-- Ambient luxury glow -->
          <div class="absolute -right-20 -top-20 h-44 w-44 rounded-full bg-admin/15 blur-[60px] pointer-events-none"></div>

          <!-- Header -->
          <div class="flex items-center justify-between border-b border-theme-border px-6 py-5">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-admin/15 border border-admin/30 text-admin">
                <UserPlusIcon v-if="mode === 'create'" class="h-5 w-5" />
                <PencilSquareIcon v-else class="h-5 w-5" />
              </div>
              <div>
                <h3 class="text-base font-bold text-theme-text font-display">
                  {{ mode === 'create' ? 'Register New Customer' : 'Edit Customer Profile' }}
                </h3>
                <p class="text-xs text-theme-text-muted mt-0.5">
                  {{ mode === 'create' ? 'Add client to the salon directory.' : 'Update customer contact information.' }}
                </p>
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
            <!-- Full Name -->
            <div>
              <label class="block text-xs font-semibold text-theme-text uppercase tracking-wider mb-1.5">
                Full Name <span class="text-admin">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="e.g. Samuel Adekunle"
                class="w-full rounded-xl border border-theme-border bg-theme-surface px-4 py-2.5 text-xs text-theme-text outline-none focus:border-admin/60 focus:ring-2 focus:ring-admin/20 transition-all placeholder:text-theme-text-muted/40"
              />
            </div>

            <!-- Email Address -->
            <div>
              <label class="block text-xs font-semibold text-theme-text uppercase tracking-wider mb-1.5">
                Email Address <span class="text-admin">*</span>
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="e.g. samuel@example.com"
                class="w-full rounded-xl border border-theme-border bg-theme-surface px-4 py-2.5 text-xs text-theme-text outline-none focus:border-admin/60 focus:ring-2 focus:ring-admin/20 transition-all placeholder:text-theme-text-muted/40"
              />
            </div>

            <!-- Phone Number -->
            <div>
              <label class="block text-xs font-semibold text-theme-text uppercase tracking-wider mb-1.5">
                Phone Number
              </label>
              <input
                v-model="form.phone"
                type="tel"
                placeholder="e.g. +234 801 234 5678"
                class="w-full rounded-xl border border-theme-border bg-theme-surface px-4 py-2.5 text-xs text-theme-text outline-none focus:border-admin/60 focus:ring-2 focus:ring-admin/20 transition-all placeholder:text-theme-text-muted/40"
              />
            </div>

            <!-- Optional Initial Password on Create -->
            <div v-if="mode === 'create'">
              <div class="flex items-center justify-between mb-1.5">
                <label class="text-xs font-semibold text-theme-text uppercase tracking-wider">
                  Initial Password
                </label>
                <span class="text-[10px] text-theme-text-muted">Optional</span>
              </div>
              <input
                v-model="form.password"
                type="text"
                minlength="8"
                placeholder="Leave blank to auto-generate password"
                class="w-full rounded-xl border border-theme-border bg-theme-surface px-4 py-2.5 text-xs font-mono text-theme-text outline-none focus:border-admin/60 focus:ring-2 focus:ring-admin/20 transition-all placeholder:text-theme-text-muted/40"
              />
              <p class="text-[10px] text-theme-text-muted mt-1">
                If left empty, a secure password will be generated automatically.
              </p>
            </div>

            <!-- Actions Footer -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-theme-border mt-6">
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
                :disabled="loading || !form.name || !form.email"
                class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-5 py-2.5 text-xs font-bold text-obsidian shadow-lg hover:shadow-admin/30 transition-all disabled:opacity-50"
              >
                <span v-if="loading" class="h-3.5 w-3.5 border-2 border-obsidian/30 border-t-obsidian rounded-full animate-spin"></span>
                <span>{{ mode === 'create' ? 'Create Customer' : 'Save Changes' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { reactive, watch } from 'vue';
import { UserPlusIcon, PencilSquareIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  isOpen: Boolean,
  mode: {
    type: String,
    default: 'create'
  },
  initialData: {
    type: Object,
    default: () => ({})
  },
  loading: Boolean
});

const emit = defineEmits(['close', 'save']);

const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: ''
});

watch(
  () => props.initialData,
  (newVal) => {
    if (newVal) {
      form.name = newVal.name || '';
      form.email = newVal.email || '';
      form.phone = newVal.phone === 'N/A' ? '' : (newVal.phone || '');
      form.password = newVal.password || '';
    } else {
      form.name = '';
      form.email = '';
      form.phone = '';
      form.password = '';
    }
  },
  { immediate: true, deep: true }
);

function handleSubmit() {
  emit('save', { ...form });
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

