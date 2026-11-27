<template>
  <div
    v-if="isNavigating"
    class="fixed top-0 left-0 right-0 z-[99999] pointer-events-none h-[2.5px] overflow-hidden"
    role="progressbar"
    :aria-valuenow="Math.round(progress)"
    aria-valuemin="0"
    aria-valuemax="100"
    aria-label="Navigation progress"
  >
    <div
      class="h-full transition-all duration-150 ease-out"
      :style="{
        width: `${progress}%`,
        boxShadow: hasError ? '0 0 10px #ef4444' : '0 0 12px var(--color-accent, #FF9900)',
        background: hasError
          ? '#ef4444'
          : 'linear-gradient(90deg, var(--color-accent, #FF9900), var(--color-accent-hover, #FFB84D))'
      }"
    />
  </div>
</template>

<script setup>
import { useNavigationProgress } from '../composables/useNavigationProgress';

const { isNavigating, progress, hasError } = useNavigationProgress();
</script>
