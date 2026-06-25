<template>
  <transition name="fade">
    <button
      v-show="isVisible"
      @click="scrollToTop"
      class="fixed bottom-6 right-6 z-[9999] p-3 rounded-full bg-theme-bg border border-[var(--color-primary)] text-[var(--color-primary)] shadow-[0_4px_14px_rgba(201,168,76,0.39)] hover:bg-[var(--color-primary)] hover:text-black hover:scale-110 hover:shadow-[0_6px_20px_rgba(201,168,76,0.5)] transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:ring-offset-2 focus:ring-offset-[var(--color-bg)] flex items-center justify-center cursor-pointer"
      aria-label="Back to top"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
      </svg>
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

const checkScroll = (e) => {
  // Check either the specific container scroll or the window scroll
  const scrollY = mainContainer ? mainContainer.scrollTop : window.scrollY;
  isVisible.value = scrollY > 300;
};

const scrollToTop = () => {
  if (mainContainer && mainContainer.scrollTop > 0) {
    mainContainer.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  } else {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
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
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
