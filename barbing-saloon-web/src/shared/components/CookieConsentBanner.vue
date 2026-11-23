<template>
  <div class="cookie-consent-system">
    <!-- Floating Minimalist Pill Banner (Exact reference design) -->
    <Transition name="cookie-pill">
      <aside
        v-if="isBannerVisible"
        role="region"
        aria-label="Cookie consent banner"
        class="cookie-pill-container fixed inset-x-0 mx-auto z-50 w-fit max-w-[calc(100vw-2rem)] px-2 select-none pointer-events-auto"
        data-testid="cookie-consent-aside"
      >
        <div
          class="flex items-center gap-4 sm:gap-5 rounded-full border border-white/10 bg-[#12161C]/95 px-5 py-2.5 text-white shadow-2xl backdrop-blur-md transition-all duration-300 hover:border-white/20"
          data-testid="cookie-consent-pill"
        >
          <!-- Notice Label -->
          <span class="text-sm font-medium tracking-tight text-white/95 whitespace-nowrap">
            {{ content.banner_text || 'We use cookies.' }}
          </span>

          <!-- 3 Actions: Settings, Reject, Accept -->
          <div class="flex items-center gap-3 sm:gap-3.5">
            <!-- ⚙ Gear / Preferences Button -->
            <button
              type="button"
              class="inline-flex items-center justify-center text-white/70 hover:text-white transition-all duration-200 hover:rotate-45 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold focus-visible:ring-offset-1 focus-visible:ring-offset-[#12161C] rounded-full p-1 cursor-pointer"
              aria-label="Customize cookie preferences"
              title="Preferences"
              data-testid="cookie-btn-settings"
              @click="openModal"
            >
              <svg
                class="w-4.5 h-4.5 sm:w-5 sm:h-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <circle cx="12" cy="12" r="3" />
                <path
                  d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"
                />
              </svg>
            </button>

            <!-- ✕ Reject Non-Essential Button -->
            <button
              type="button"
              class="inline-flex items-center justify-center text-white/70 hover:text-rose-400 hover:scale-110 transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 focus-visible:ring-offset-1 focus-visible:ring-offset-[#12161C] rounded-full p-1 cursor-pointer"
              aria-label="Decline optional cookies"
              title="Decline optional"
              data-testid="cookie-btn-reject"
              @click="handleReject"
            >
              <svg
                class="w-4.5 h-4.5 sm:w-5 sm:h-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <path d="M18 6L6 18M6 6l12 12" />
              </svg>
            </button>

            <!-- ✓ Accept All Button -->
            <button
              type="button"
              class="inline-flex items-center justify-center text-white/90 hover:text-emerald-400 hover:scale-110 transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-1 focus-visible:ring-offset-[#12161C] rounded-full p-1 cursor-pointer"
              aria-label="Accept all cookies"
              title="Accept all"
              data-testid="cookie-btn-accept"
              @click="handleAccept"
            >
              <svg
                class="w-4.5 h-4.5 sm:w-5 sm:h-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
              >
                <path d="M5 13l4 4L19 7" />
              </svg>
            </button>
          </div>
        </div>
      </aside>
    </Transition>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useCookieConsent } from '@/shared/composables/useCookieConsent';

const {
  content,
  hasConsented,
  isBannerVisible,
  isModalOpen,
  initCookieConsent,
  acceptAll,
  rejectNonEssential,
  openModal,
} = useCookieConsent();

const handleAccept = () => {
  acceptAll();
};

const handleReject = () => {
  rejectNonEssential();
};

onMounted(() => {
  initCookieConsent();
});
</script>

<style scoped>
.cookie-pill-container {
  bottom: max(1.5rem, calc(env(safe-area-inset-bottom) + 0.75rem));
}

.cookie-pill-enter-active,
.cookie-pill-leave-active {
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}

.cookie-pill-enter-from,
.cookie-pill-leave-to {
  opacity: 0;
  transform: translateY(16px) scale(0.97);
}
</style>
