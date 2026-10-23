<template>
  <transition name="fade">
    <button
      v-show="isVisible"
      @click="scrollToTop"
      class="back-to-top fixed z-[9999] flex items-center justify-center gap-2 rounded-full"
      aria-label="Back to top"
      title="Back to top"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.25" stroke="currentColor" class="back-to-top__icon" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
      </svg>
      <span class="back-to-top__label">Top</span>
    </button>
  </transition>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import { watch } from 'vue';

const isVisible = ref(false);
const route = useRoute();
let mainContainer = null;

const checkScroll = () => {
  // Check either the specific container scroll or the window scroll
  const scrollY = mainContainer ? mainContainer.scrollTop : window.scrollY;
  isVisible.value = scrollY > 300;
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
  // First remove any existing listeners to prevent duplicates
  window.removeEventListener('scroll', checkScroll);
  if (mainContainer) {
    mainContainer.removeEventListener('scroll', checkScroll);
  }

  // Look for the dashboard scroll container
  mainContainer = document.getElementById('main-scroll-container');
  
  if (mainContainer) {
    mainContainer.addEventListener('scroll', checkScroll);
  } else {
    window.addEventListener('scroll', checkScroll);
  }
  
  checkScroll();
};

onMounted(() => {
  attachListeners();
});

// Re-attach listeners when the route changes (e.g. moving between PublicLayout and DashboardLayout)
watch(() => route.path, async () => {
  await nextTick();
  // Brief delay to allow DOM to render the new layout
  setTimeout(attachListeners, 100);
});

onUnmounted(() => {
  window.removeEventListener('scroll', checkScroll);
  if (mainContainer) {
    mainContainer.removeEventListener('scroll', checkScroll);
  }
});
</script>

<style scoped>
.back-to-top {
  right: max(1rem, env(safe-area-inset-right));
  bottom: max(1rem, env(safe-area-inset-bottom));
  min-width: 48px;
  min-height: 48px;
  border: 1px solid rgba(255, 184, 77, 0.55);
  padding: 0.75rem;
  background: rgba(13, 13, 13, 0.92);
  color: #ffb84d;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4), 0 0 18px rgba(255, 153, 0, 0.16);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  cursor: pointer;
  transition: transform 180ms ease, border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease;
}

.back-to-top:hover {
  transform: translateY(-3px);
  border-color: #ff9900;
  background: #1b160f;
  box-shadow: 0 10px 28px rgba(0, 0, 0, 0.48), 0 0 22px rgba(255, 153, 0, 0.28);
}

.back-to-top:active {
  transform: translateY(0) scale(0.96);
}

.back-to-top:focus-visible {
  outline: 3px solid #ffb84d;
  outline-offset: 3px;
}

.back-to-top__icon {
  width: 1.25rem;
  height: 1.25rem;
  flex: none;
}

.back-to-top__label {
  display: none;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

@media (min-width: 640px) {
  .back-to-top {
    right: max(1.5rem, env(safe-area-inset-right));
    bottom: max(1.5rem, env(safe-area-inset-bottom));
    padding-inline: 1rem;
  }

  .back-to-top__label {
    display: inline;
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
