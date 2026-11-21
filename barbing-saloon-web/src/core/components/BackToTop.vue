<template>
  <transition name="fade">
    <button
      v-show="isVisible"
      @click="scrollToTop"
      class="back-to-top fixed z-[9999] flex items-center justify-center rounded-full"
      :class="{ 'back-to-top--above-mobile-tab': usesDashboardScroll }"
      aria-label="Back to top"
      title="Back to top"
    >
      <svg class="back-to-top__progress" viewBox="0 0 64 64" aria-hidden="true">
        <circle class="back-to-top__track" cx="32" cy="32" r="24" />
        <circle
          class="back-to-top__indicator"
          cx="32"
          cy="32"
          r="24"
          :stroke-dasharray="ringCircumference"
          :stroke-dashoffset="ringOffset"
        />
      </svg>
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.25" stroke="currentColor" class="back-to-top__icon" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
      </svg>
    </button>
  </transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { watch } from 'vue';

const isVisible = ref(false);
const scrollProgress = ref(0);
const usesDashboardScroll = ref(false);
const route = useRoute();
let mainContainer = null;
const ringCircumference = 2 * Math.PI * 24;
const ringOffset = computed(() => ringCircumference * (1 - scrollProgress.value / 100));

const checkScroll = () => {
  const scrollY = mainContainer ? mainContainer.scrollTop : window.scrollY;
  const scrollHeight = mainContainer ? mainContainer.scrollHeight : document.documentElement.scrollHeight;
  const clientHeight = mainContainer ? mainContainer.clientHeight : window.innerHeight;
  const maxScroll = Math.max(scrollHeight - clientHeight, 0);
  const clampedScrollY = Math.max(scrollY, 0);

  isVisible.value = clampedScrollY > 300;
  scrollProgress.value = maxScroll > 0 ? Math.min((clampedScrollY / maxScroll) * 100, 100) : 0;
};

const scrollToTop = () => {
  const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';

  if (mainContainer && mainContainer.scrollTop > 0) {
    mainContainer.scrollTo({
      top: 0,
      behavior
    });
  } else {
    window.scrollTo({
      top: 0,
      behavior
    });
  }
};

const attachListeners = () => {
  window.removeEventListener('scroll', checkScroll);
  window.removeEventListener('resize', checkScroll);
  if (mainContainer) {
    mainContainer.removeEventListener('scroll', checkScroll);
  }

  mainContainer = document.getElementById('main-scroll-container');
  usesDashboardScroll.value = Boolean(mainContainer);
  
  if (mainContainer) {
    mainContainer.addEventListener('scroll', checkScroll);
  } else {
    window.addEventListener('scroll', checkScroll);
  }

  window.addEventListener('resize', checkScroll);
  
  checkScroll();
};

onMounted(() => {
  attachListeners();
});

// Re-attach listeners when the route changes (e.g. moving between PublicLayout and DashboardLayout)
watch(() => route.path, async () => {
  await nextTick();
  setTimeout(attachListeners, 100);
});

onUnmounted(() => {
  window.removeEventListener('scroll', checkScroll);
  window.removeEventListener('resize', checkScroll);
  if (mainContainer) {
    mainContainer.removeEventListener('scroll', checkScroll);
  }
});
</script>

<style scoped>
.back-to-top {
  --back-to-top-gold: #c28216;
  right: max(1rem, env(safe-area-inset-right));
  bottom: max(1rem, env(safe-area-inset-bottom));
  width: 52px;
  height: 52px;
  padding: 0;
  border: 0;
  background: transparent;
  color: var(--back-to-top-gold);
  box-shadow: none;
  cursor: pointer;
  transition: transform 180ms ease, border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease;
}

.back-to-top::before {
  content: '';
  position: absolute;
  inset: 5px;
  border-radius: 9999px;
  background: rgba(13, 13, 13, 0.96);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.38);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  z-index: 0;
}

.back-to-top__progress {
  position: absolute;
  inset: 0;
  width: 52px;
  height: 52px;
  overflow: visible;
  pointer-events: none;
}

.back-to-top__track,
.back-to-top__indicator {
  fill: none;
  stroke-width: 3;
}

.back-to-top__track {
  stroke: color-mix(in srgb, var(--back-to-top-gold) 28%, transparent);
}

.back-to-top__indicator {
  stroke: var(--back-to-top-gold);
  stroke-linecap: round;
  filter: drop-shadow(0 0 3px color-mix(in srgb, var(--back-to-top-gold) 85%, transparent)) drop-shadow(0 0 7px color-mix(in srgb, var(--back-to-top-gold) 55%, transparent));
  transform: rotate(-90deg);
  transform-origin: 32px 32px;
  transition: stroke-dashoffset 100ms linear;
}

.back-to-top:hover {
  transform: translateY(-3px);
  filter: brightness(1.08);
}

.back-to-top:active {
  transform: translateY(0) scale(0.96);
}

.back-to-top:focus-visible {
  outline: 3px solid var(--back-to-top-gold);
  outline-offset: 3px;
}

.back-to-top__icon {
  position: relative;
  z-index: 1;
  width: 1.25rem;
  height: 1.25rem;
  flex: none;
}

@media (min-width: 640px) {
  .back-to-top {
    right: max(1.5rem, env(safe-area-inset-right));
    bottom: max(1.5rem, env(safe-area-inset-bottom));
  }
}

@media (max-width: 639px) {
  .back-to-top--above-mobile-tab {
    bottom: calc(max(1rem, env(safe-area-inset-bottom)) + 4.75rem);
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

@media (prefers-reduced-motion: reduce) {
  .back-to-top,
  .fade-enter-active,
  .fade-leave-active {
    transition: none;
  }
}
</style>
