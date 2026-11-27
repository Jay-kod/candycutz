<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-[9999] flex flex-col justify-end sm:items-center sm:justify-center p-0 sm:p-4 select-none"
        role="dialog"
        aria-modal="true"
        :aria-label="title || 'Modal sheet'"
      >
        <!-- Frosted Backdrop -->
        <div
          class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
          @click="dismissible ? $emit('close') : null"
        />

        <!-- Slide-up sheet panel -->
        <Transition name="sheet-slide">
          <div
            v-if="show"
            class="relative w-full sm:max-w-md bg-theme-surface border border-theme-border/60 rounded-t-[28px] sm:rounded-2xl p-5 shadow-2xl overflow-hidden max-h-[90dvh] flex flex-col pb-safe z-10 transition-all duration-300"
            :style="{ transitionTimingFunction: 'cubic-bezier(0.32, 0.72, 0, 1)' }"
            @click.stop
          >
            <!-- iOS Grabber Handle (Mobile only) -->
            <div class="sm:hidden w-9 h-1 bg-theme-muted/30 rounded-full mx-auto mb-3 shrink-0" />

            <!-- Sheet Header -->
            <div v-if="title || $slots.header" class="flex items-center justify-between pb-3 mb-2 border-b border-theme-border/30 shrink-0">
              <slot name="header">
                <h3 class="text-base font-semibold text-theme-text">{{ title }}</h3>
                <button
                  v-if="dismissible"
                  type="button"
                  class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-theme-muted hover:text-theme-text transition-colors cursor-pointer"
                  @click="$emit('close')"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </slot>
            </div>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto overscroll-contain pr-1 custom-scrollbar">
              <slot />
            </div>

            <!-- Sheet Footer -->
            <div v-if="$slots.footer" class="pt-3 mt-2 border-t border-theme-border/30 shrink-0">
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  dismissible: {
    type: Boolean,
    default: true,
  },
});

defineEmits(['close']);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.sheet-slide-enter-active,
.sheet-slide-leave-active {
  transition: transform 0.38s cubic-bezier(0.32, 0.72, 0, 1), opacity 0.3s ease;
}
.sheet-slide-enter-from,
.sheet-slide-leave-to {
  transform: translateY(100%);
  opacity: 0.9;
}
@media (min-width: 640px) {
  .sheet-slide-enter-from,
  .sheet-slide-leave-to {
    transform: scale(0.95);
    opacity: 0;
  }
}
</style>
