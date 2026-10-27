<template>
  <button
    type="button"
    role="switch"
    :aria-checked="isDark"
    aria-label="Switch between Light mode and Dark mode"
    class="theme-toggle-switch group relative inline-flex items-center w-[86px] h-[28px] p-[2px] rounded-full transition-all duration-300 cursor-pointer select-none focus:outline-none focus-visible:ring-2 focus-visible:ring-gold shrink-0"
    :class="[
      isDark
        ? 'bg-obsidian border border-gold/30 shadow-[inset_0_2px_4px_rgba(0,0,0,0.8),_0_0_10px_rgba(255,153,0,0.12)] hover:border-gold/60'
        : 'bg-[#F4F0E8] border border-gold/35 shadow-[inset_0_2px_4px_rgba(204,122,0,0.06),_0_2px_5px_rgba(255,153,0,0.08)] hover:border-gold/60'
    ]"
    @click="toggleTheme"
  >
    <!-- LIGHT label (visible on the left when in Light mode) -->
    <span
      class="absolute left-3 text-[9px] font-extrabold tracking-[0.14em] text-neutral-900 transition-all duration-300 pointer-events-none select-none uppercase"
      :class="!isDark ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-2'"
    >
      LIGHT
    </span>

    <!-- DARK label (visible on the right when in Dark mode) -->
    <span
      class="absolute right-3 text-[9px] font-extrabold tracking-[0.14em] text-gold transition-all duration-300 pointer-events-none select-none uppercase drop-shadow-[0_1px_3px_rgba(255,153,0,0.25)]"
      :class="isDark ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-2'"
    >
      DARK
    </span>

    <!-- Sliding Knob with Sun/Moon Icon -->
    <div
      class="relative z-10 flex items-center justify-center size-[24px] rounded-full bg-white transition-transform duration-350 ease-[cubic-bezier(0.34,1.56,0.64,1)]"
      :class="[
        isDark
          ? 'translate-x-0 border-[1.5px] border-gold shadow-[0_2px_6px_rgba(255,153,0,0.35)]'
          : 'translate-x-[58px] border border-gold/50 shadow-[0_2px_6px_rgba(255,153,0,0.2)]'
      ]"
    >
      <!-- Sun Icon in Light Mode -->
      <Sun
        v-if="!isDark"
        class="size-3.5 text-gold transition-transform duration-300 group-hover:rotate-45 drop-shadow-[0_0_3px_rgba(255,153,0,0.3)]"
        stroke-width="2.4"
      />

      <!-- Moon with Stars Icon in Dark Mode -->
      <MoonStar
        v-else
        class="size-3.5 text-gold transition-transform duration-300 group-hover:-rotate-12 drop-shadow-[0_0_3px_rgba(255,153,0,0.3)]"
        stroke-width="2.4"
      />
    </div>
  </button>
</template>

<script setup>
import { useDark, useToggle } from '@vueuse/core';
import { Sun, MoonStar } from 'lucide-vue-next';

const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
  onChanged(dark) {
    if (typeof document !== 'undefined') {
      const el = document.documentElement;
      el.setAttribute('data-theme', dark ? 'dark' : 'light');
      if (dark) {
        el.classList.add('dark');
        el.classList.remove('light');
      } else {
        el.classList.remove('dark');
        el.classList.add('light');
      }
    }
  },
});

const toggleDark = useToggle(isDark);

const toggleTheme = () => {
  toggleDark();
};
</script>

<style scoped>
.theme-toggle-switch {
  -webkit-tap-highlight-color: transparent;
}

.theme-toggle-switch:hover {
  filter: brightness(1.05);
}

.theme-toggle-switch:active .size-\[24px\] {
  transform: scale(0.94);
}
</style>
