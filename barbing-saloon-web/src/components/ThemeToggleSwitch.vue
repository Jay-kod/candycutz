<template>
  <div class="theme-toggle-container relative inline-flex items-center" ref="switchContainerRef">
    <!-- Single Unified Compact Toggle Switch (Light <-> Dark) -->
    <button
      type="button"
      role="switch"
      :aria-checked="isDark"
      :aria-label="`Theme switch: currently ${isDark ? 'Dark' : 'Light'} mode. Click to switch to ${isDark ? 'Light' : 'Dark'} mode`"
      :title="`Theme: ${isDark ? 'Dark' : 'Light'} mode. Click to switch to ${isDark ? 'Light' : 'Dark'} mode.`"
      class="theme-toggle-switch group relative inline-flex items-center w-[66px] h-[24px] p-[2px] rounded-full transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] cursor-pointer select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-gold shrink-0 overflow-hidden"
      :class="[
        isDark
          ? 'bg-obsidian border border-gold/30 shadow-[inset_0_2px_4px_rgba(0,0,0,0.8),_0_0_8px_rgba(255,153,0,0.12)] hover:border-gold/60'
          : 'bg-[var(--color-bg-secondary)] border border-gold/35 shadow-[inset_0_2px_3px_rgba(204,122,0,0.06),_0_1px_4px_rgba(255,153,0,0.08)] hover:border-gold/60'
      ]"
      @click="toggleTheme"
    >
      <!-- LIGHT label (visible on the left when in Light mode) -->
      <span
        class="absolute left-2 text-[8px] font-extrabold tracking-[0.12em] text-[var(--color-text-primary)] transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] pointer-events-none select-none uppercase"
        :class="!isDark ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-1'"
      >
        LIGHT
      </span>

      <!-- DARK label (visible on the right when in Dark mode) -->
      <span
        class="absolute right-2 text-[8px] font-extrabold tracking-[0.12em] text-gold transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] pointer-events-none select-none uppercase drop-shadow-[0_1px_2px_rgba(255,153,0,0.25)]"
        :class="isDark ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-1'"
      >
        DARK
      </span>

      <!-- Sliding Knob with Smoothly Morphing Sun/Moon Icon -->
      <div
        class="knob-handle transform-gpu will-change-transform relative z-10 flex items-center justify-center size-[20px] rounded-full bg-white transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
        :class="[
          isDark
            ? 'translate-x-0 border-[1.5px] border-gold shadow-[0_1px_5px_rgba(255,153,0,0.4)]'
            : 'translate-x-[42px] border border-gold/50 shadow-[0_1px_4px_rgba(255,153,0,0.25)]'
        ]"
      >
        <!-- Sun Icon in Light Mode (Cross-fade & rotate) -->
        <Sun
          class="absolute size-3 text-gold transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] drop-shadow-[0_0_2px_rgba(255,153,0,0.3)]"
          :class="!isDark ? 'opacity-100 rotate-0 scale-100' : 'opacity-0 -rotate-90 scale-50 pointer-events-none'"
          stroke-width="2.4"
        />

        <!-- Moon with Stars Icon in Dark Mode (Cross-fade & rotate) -->
        <MoonStar
          class="absolute size-3 text-gold transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] drop-shadow-[0_0_2px_rgba(255,153,0,0.3)]"
          :class="isDark ? 'opacity-100 rotate-0 scale-100' : 'opacity-0 rotate-90 scale-50 pointer-events-none'"
          stroke-width="2.4"
        />
      </div>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useTheme } from '@/core/composables/useTheme';
import { Sun, MoonStar } from 'lucide-vue-next';

const { isDark, toggleTheme } = useTheme();
const switchContainerRef = ref(null);
</script>

<style scoped>
.theme-toggle-switch {
  -webkit-tap-highlight-color: transparent;
}

.theme-toggle-switch:hover {
  filter: brightness(1.04);
}

.theme-toggle-switch:active .knob-handle {
  transform: scale(0.92);
}
</style>
