<template>
  <div class="min-h-screen w-full overflow-x-hidden bg-[var(--color-bg)] text-[var(--color-text)] font-sans">
    <div class="scroll-progress" aria-hidden="true"></div>
    <router-view />
    <ConfirmModal />
    <ToastNotification />
    <BackToTop />
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import ConfirmModal from './core/components/ConfirmModal.vue';
import ToastNotification from './core/components/ToastNotification.vue';
import BackToTop from './core/components/BackToTop.vue';

let revealObserver = null;

onMounted(() => {
  const mapDelays = () => {
    document.querySelectorAll('[data-reveal]').forEach((el) => {
      const delay = el.dataset.revealDelay;
      if (delay && !el.style.getPropertyValue('--reveal-delay')) {
        el.style.setProperty('--reveal-delay', `${delay}ms`);
      }
    });
  };
  mapDelays();
  revealObserver = new MutationObserver(mapDelays);
  revealObserver.observe(document.body, { childList: true, subtree: true });
});

onUnmounted(() => {
  revealObserver?.disconnect();
});
</script>