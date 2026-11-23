<template>
  <div class="min-h-[100dvh] w-full bg-[var(--color-bg-primary)] text-[var(--color-text-primary)] font-sans">
    <NavigationProgressBar />
    <SystemConnectionBanner />
    <router-view />
    <ConfirmModal />
    <ToastNotification />
    <BackToTop />
    <CookieConsentBanner />
    <CookiePreferencesModal />
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import ConfirmModal from './core/components/ConfirmModal.vue';
import SystemConnectionBanner from './core/components/SystemConnectionBanner.vue';
import ToastNotification from './core/components/ToastNotification.vue';
import BackToTop from './core/components/BackToTop.vue';
import NavigationProgressBar from './core/components/NavigationProgressBar.vue';
import CookieConsentBanner from '@/shared/components/CookieConsentBanner.vue';
import CookiePreferencesModal from '@/shared/components/CookiePreferencesModal.vue';
import { useBrandIdentity } from '@/shared/composables/useBrandIdentity';
import { useConnectionStatus } from '@/core/composables/useConnectionStatus';

let revealObserver = null;
const { loadBrandIdentity } = useBrandIdentity();
const { checkHealth } = useConnectionStatus();

onMounted(() => {
  loadBrandIdentity();
  checkHealth();
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