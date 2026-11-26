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
import { defineAsyncComponent, onMounted, onUnmounted } from 'vue';
import NavigationProgressBar from './core/components/NavigationProgressBar.vue';
const ConfirmModal = defineAsyncComponent(() => import('./core/components/ConfirmModal.vue'));
const SystemConnectionBanner = defineAsyncComponent(() => import('./core/components/SystemConnectionBanner.vue'));
const ToastNotification = defineAsyncComponent(() => import('./core/components/ToastNotification.vue'));
const BackToTop = defineAsyncComponent(() => import('./core/components/BackToTop.vue'));
const CookieConsentBanner = defineAsyncComponent(() => import('@/shared/components/CookieConsentBanner.vue'));
const CookiePreferencesModal = defineAsyncComponent(() => import('@/shared/components/CookiePreferencesModal.vue'));
import { useBrandIdentity } from '@/shared/composables/useBrandIdentity';
import { useConnectionStatus } from '@/core/composables/useConnectionStatus';

let revealObserver = null;
const { loadBrandIdentity } = useBrandIdentity();
const { checkHealth } = useConnectionStatus();

onMounted(() => {
  const scheduleIdle = (callback, timeout) => {
    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(callback, { timeout });
      return;
    }
    window.setTimeout(callback, timeout);
  };
  scheduleIdle(loadBrandIdentity, 1200);
  scheduleIdle(checkHealth, 2500);
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