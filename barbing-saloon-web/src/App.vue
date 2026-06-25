<template>
  <div class="min-h-screen w-full overflow-x-hidden bg-[var(--color-bg)] text-[var(--color-text)] font-sans">
    <router-view />
    <ConfirmModal />
    <ToastNotification />
    <BackToTop />
  </div>
</template>

<script setup>
import ConfirmModal from './core/components/ConfirmModal.vue';
import ToastNotification from './core/components/ToastNotification.vue';
import BackToTop from './core/components/BackToTop.vue';
</script>