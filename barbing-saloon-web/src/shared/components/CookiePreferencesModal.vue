<template>
  <Teleport to="body">
    <Transition name="cookie-modal">
      <div
        v-if="isModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="cookie-preferences-title"
        @keydown.esc="closeModal"
      >
        <!-- Backdrop -->
        <div
          class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity"
          aria-hidden="true"
          @click="closeModal"
        />

        <!-- Modal Shell -->
        <div
          class="relative w-full max-w-xl overflow-hidden rounded-2xl border border-white/10 bg-[#0E1318] text-white shadow-2xl transition-all z-10 max-h-[90vh] flex flex-col"
          data-testid="cookie-preferences-modal"
        >
          <!-- Modal Header -->
          <div class="border-b border-white/[0.08] px-6 py-5 flex items-start justify-between gap-4 bg-[#12161C]">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-gold"></span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-gold">Privacy Settings</span>
              </div>
              <h2 id="cookie-preferences-title" class="text-lg sm:text-xl font-bold tracking-tight text-white">
                {{ content.cookie_title || 'Cookie & Privacy Preferences' }}
              </h2>
              <p class="text-xs text-white/60 leading-relaxed max-w-md">
                {{ content.cookie_intro || 'Choose which optional cookie categories you want to allow. Necessary cookies ensure platform stability and security.' }}
              </p>
            </div>

            <!-- Close (X) Button -->
            <button
              type="button"
              class="rounded-xl border border-white/10 bg-white/[0.04] p-2 text-white/60 hover:bg-white/10 hover:text-white transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
              aria-label="Close preferences dialog"
              data-testid="cookie-modal-close"
              @click="closeModal"
            >
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Modal Scrollable Content: Categories -->
          <div class="overflow-y-auto px-6 py-5 space-y-4 divide-y divide-white/[0.04]">
            <div
              v-for="cat in categories"
              :key="cat.key"
              class="pt-4 first:pt-0 flex items-start justify-between gap-4"
              :data-testid="`cookie-category-${cat.key}`"
            >
              <div class="space-y-1 min-w-0 pr-2">
                <div class="flex items-center gap-2">
                  <h3 class="text-xs sm:text-sm font-semibold text-white">
                    {{ content[cat.titleKey] || cat.label }}
                  </h3>
                  <span
                    v-if="cat.required"
                    class="rounded-md border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[9px] font-mono font-bold uppercase tracking-wider text-emerald-400"
                  >
                    Required
                  </span>
                  <span
                    v-else
                    class="rounded-md border border-white/10 bg-white/[0.03] px-2 py-0.5 text-[9px] font-mono text-white/40 uppercase tracking-wider"
                  >
                    Optional
                  </span>
                </div>
                <p class="text-[11px] sm:text-xs text-white/50 leading-relaxed">
                  {{ content[cat.descriptionKey] }}
                </p>
              </div>

              <!-- Switch Toggle -->
              <div class="shrink-0 pt-0.5">
                <button
                  type="button"
                  role="switch"
                  :aria-checked="cat.required ? 'true' : Boolean(localPrefs[cat.key])"
                  :aria-label="`Toggle ${cat.label}`"
                  :disabled="cat.required"
                  class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-gold disabled:cursor-not-allowed disabled:opacity-80"
                  :class="[
                    cat.required || localPrefs[cat.key]
                      ? 'bg-gold'
                      : 'bg-white/20 hover:bg-white/30'
                  ]"
                  @click="toggleCategory(cat.key)"
                >
                  <span
                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                    :class="[
                      cat.required || localPrefs[cat.key]
                        ? 'translate-x-5'
                        : 'translate-x-0'
                    ]"
                  />
                </button>
              </div>
            </div>
          </div>

          <!-- Modal Actions Footer -->
          <div class="border-t border-white/[0.08] bg-[#12161C] px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <RouterLink
              to="/cookies"
              class="text-xs text-white/50 hover:text-gold transition-colors underline underline-offset-4"
              @click="closeModal"
            >
              Full Cookie Policy
            </RouterLink>

            <div class="flex flex-wrap items-center justify-end gap-2 w-full sm:w-auto">
              <button
                type="button"
                class="flex-1 sm:flex-none rounded-xl border border-white/10 bg-white/[0.04] px-4 py-2.5 text-xs font-semibold text-white/70 hover:bg-white/[0.08] hover:text-white transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
                data-testid="cookie-modal-reject"
                @click="handleReject"
              >
                Reject Non-Essential
              </button>

              <button
                type="button"
                class="flex-1 sm:flex-none rounded-xl border border-white/10 bg-white/[0.08] px-4 py-2.5 text-xs font-semibold text-white hover:bg-white/[0.14] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
                data-testid="cookie-modal-save"
                @click="handleSave"
              >
                Save Choices
              </button>

              <button
                type="button"
                class="w-full sm:w-auto rounded-xl bg-gold px-4 py-2.5 text-xs font-bold text-obsidian shadow-md hover:bg-gold-light transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
                data-testid="cookie-modal-accept"
                @click="handleAccept"
              >
                Accept All
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { reactive, watch } from 'vue';
import { RouterLink } from 'vue-router';
import { useCookieConsent } from '@/shared/composables/useCookieConsent';

const {
  categories,
  content,
  isModalOpen,
  preferences,
  acceptAll,
  rejectNonEssential,
  savePreferences,
  closeModal,
} = useCookieConsent();

const localPrefs = reactive({
  necessary: true,
  preferences: false,
  analytics: false,
  marketing: false,
});

watch(
  isModalOpen,
  (open) => {
    if (open) {
      localPrefs.necessary = true;
      localPrefs.preferences = Boolean(preferences.preferences);
      localPrefs.analytics = Boolean(preferences.analytics);
      localPrefs.marketing = Boolean(preferences.marketing);
    }
  },
  { immediate: true }
);

const toggleCategory = (key) => {
  if (key === 'necessary') return;
  localPrefs[key] = !localPrefs[key];
};

const handleSave = () => {
  savePreferences(localPrefs);
};

const handleReject = () => {
  rejectNonEssential();
};

const handleAccept = () => {
  acceptAll();
};
</script>

<style scoped>
.cookie-modal-enter-active,
.cookie-modal-leave-active {
  transition: opacity 0.25s ease-out;
}

.cookie-modal-enter-from,
.cookie-modal-leave-to {
  opacity: 0;
}
</style>
