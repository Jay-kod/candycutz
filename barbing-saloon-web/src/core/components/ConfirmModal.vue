<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="isOpen"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 select-none"
        role="dialog"
        aria-modal="true"
        :aria-label="title"
        @keydown.esc="cancel"
      >
        <!-- Backdrop with obsidian blur -->
        <div
          class="absolute inset-0 bg-black/80 backdrop-blur-md transition-opacity"
          @click="cancel"
        ></div>

        <!-- Modal Panel -->
        <Transition name="scale">
          <div
            v-if="isOpen"
            class="relative w-full max-w-[390px] overflow-hidden rounded-2xl border border-white/[0.09] bg-[#141418] p-6 text-center shadow-2xl shadow-black/80 transition-all"
            @click.stop
          >
            <!-- Top luminous accent rim -->
            <div
              class="absolute top-0 left-1/4 right-1/4 h-[2px] rounded-full blur-[0.5px]"
              :class="theme.topRim"
            ></div>

            <!-- Ambient Glow Halo & Icon Badge -->
            <div class="mx-auto mb-4 flex items-center justify-center">
              <div
                class="flex h-16 w-16 items-center justify-center rounded-2xl border transition-transform duration-300 hover:scale-105"
                :class="[theme.haloBg, theme.ringBorder]"
              >
                <!-- Danger Icon -->
                <svg
                  v-if="variant === 'danger'"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-8 w-8 text-red-400"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                  />
                </svg>

                <!-- Warning Icon -->
                <svg
                  v-else-if="variant === 'warning'"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-8 w-8 text-amber-400"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
                  />
                </svg>

                <!-- Info Icon -->
                <svg
                  v-else-if="variant === 'info'"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-8 w-8 text-blue-400"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"
                  />
                </svg>

                <!-- Primary / Action Icon -->
                <svg
                  v-else
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="h-8 w-8 text-amber-300"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"
                  />
                </svg>
              </div>
            </div>

            <!-- Title & Message -->
            <h3 class="font-sans text-xl font-bold tracking-tight text-white mb-2">
              {{ title }}
            </h3>
            <p class="text-sm text-gray-400 leading-relaxed mb-6 px-2">
              {{ message }}
            </p>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3 pt-1">
              <button
                type="button"
                class="w-full rounded-xl border border-white/10 bg-white/[0.04] px-4 py-2.5 text-sm font-semibold text-gray-300 transition-all hover:bg-white/[0.08] hover:text-white hover:border-white/20 active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-white/20"
                @click="cancel"
              >
                {{ cancelButtonText }}
              </button>
              <button
                type="button"
                class="w-full rounded-xl px-4 py-2.5 text-sm font-bold shadow-lg transition-all active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#141418]"
                :class="theme.confirmBtn"
                @click="agree"
              >
                {{ confirmButtonText }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { useConfirm } from '../composables/useConfirm';

const {
  isOpen,
  title,
  message,
  confirmButtonText,
  cancelButtonText,
  variant,
  agree,
  cancel,
} = useConfirm();

const theme = computed(() => {
  switch (variant.value) {
    case 'danger':
      return {
        topRim: 'bg-red-500/60 shadow-[0_0_12px_rgba(239,68,68,0.5)]',
        haloBg: 'bg-red-500/10 shadow-[0_0_24px_rgba(239,68,68,0.15)]',
        ringBorder: 'border-red-500/30',
        confirmBtn:
          'bg-gradient-to-r from-red-600 to-red-500 text-white shadow-red-500/25 hover:from-red-500 hover:to-red-400 focus:ring-red-500',
      };
    case 'warning':
      return {
        topRim: 'bg-amber-500/60 shadow-[0_0_12px_rgba(245,158,11,0.5)]',
        haloBg: 'bg-amber-500/10 shadow-[0_0_24px_rgba(245,158,11,0.15)]',
        ringBorder: 'border-amber-500/30',
        confirmBtn:
          'bg-gradient-to-r from-amber-600 to-amber-500 text-white shadow-amber-500/25 hover:from-amber-500 hover:to-amber-400 focus:ring-amber-500',
      };
    case 'info':
      return {
        topRim: 'bg-blue-500/60 shadow-[0_0_12px_rgba(59,130,246,0.5)]',
        haloBg: 'bg-blue-500/10 shadow-[0_0_24px_rgba(59,130,246,0.15)]',
        ringBorder: 'border-blue-500/30',
        confirmBtn:
          'bg-gradient-to-r from-blue-600 to-blue-500 text-white shadow-blue-500/25 hover:from-blue-500 hover:to-blue-400 focus:ring-blue-500',
      };
    case 'primary':
    default:
      return {
        topRim: 'bg-[#E5BA73]/70 shadow-[0_0_12px_rgba(229,186,115,0.5)]',
        haloBg: 'bg-[#E5BA73]/10 shadow-[0_0_24px_rgba(212,175,55,0.15)]',
        ringBorder: 'border-[#E5BA73]/30',
        confirmBtn:
          'bg-gradient-to-r from-[#FDE68A] via-[#E5BA73] to-[#D4AF37] text-black shadow-[#D4AF37]/25 hover:brightness-105 focus:ring-[#D4AF37]',
      };
  }
});

const handleKeyDown = (e) => {
  if (!isOpen.value) return;
  if (e.key === 'Escape') {
    cancel();
  } else if (e.key === 'Enter') {
    agree();
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.22s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.scale-enter-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.scale-leave-active {
  transition: all 0.18s cubic-bezier(0.4, 0, 1, 1);
}

.scale-enter-from {
  opacity: 0;
  transform: scale(0.92) translateY(8px);
}

.scale-leave-to {
  opacity: 0;
  transform: scale(0.96) translateY(4px);
}
</style>
