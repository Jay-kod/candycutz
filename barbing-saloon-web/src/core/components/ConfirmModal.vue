<template>
  <Transition name="fade">
    <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-obsidian/80 backdrop-blur-sm transition-opacity" @click="cancel"></div>
      
      <!-- Modal Panel -->
      <Transition name="scale">
        <div v-if="isOpen" class="relative w-full max-w-sm transform overflow-hidden rounded-2xl border border-white/[0.08] bg-charcoal p-6 text-left align-middle shadow-2xl transition-all">
          <div class="flex items-center gap-4 mb-4">
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-red-500/10">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 text-red-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <h3 class="font-display text-xl font-bold text-ivory">{{ title }}</h3>
          </div>
          
          <div class="mt-2 mb-6 ml-14">
            <p class="text-sm text-theme-muted leading-relaxed">{{ message }}</p>
          </div>

          <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-3">
            <button type="button" class="w-full sm:w-auto rounded-xl border border-white/10 bg-transparent px-5 py-2.5 text-sm font-semibold text-ivory transition-colors hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-white/20" @click="cancel">
              Cancel
            </button>
            <button type="button" class="w-full sm:w-auto rounded-xl bg-red-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-500/20 transition-all hover:bg-red-600 hover:shadow-red-500/30 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-charcoal" @click="agree">
              {{ confirmButtonText }}
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup>
import { useConfirm } from '../composables/useConfirm';

const { isOpen, title, message, confirmButtonText, agree, cancel } = useConfirm();
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.scale-enter-from,
.scale-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}
</style>
