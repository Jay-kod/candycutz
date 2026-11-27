<template>
  <PublicLayout>
    <PageHero
      page-key="cookies"
      eyebrow="Privacy & Compliance"
      fallback-title="Cookie Preferences"
      fallback-subtitle="Control your digital footprint. Configure which cookie categories are enabled for your browser session."
      title-setting-key="cookie_title"
      subtitle-setting-key="cookie_intro"
    />

    <section class="bg-[var(--color-bg-primary)] px-4 py-12 sm:px-6 sm:py-16">
      <div class="mx-auto max-w-4xl space-y-8">
        <!-- Live Consent Widget Showcase Banner (Exact reference visual simulation) -->
        <article class="rounded-2xl border border-theme-border bg-theme-surface p-6 sm:p-8 shadow-sm">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-theme-border">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-gold"></span>
                <span class="text-xs font-bold uppercase tracking-wider text-gold">Minimalist Floating Pill Bar</span>
              </div>
              <h2 class="text-lg font-bold text-theme-text">Live System Consent Widget</h2>
              <p class="text-xs text-theme-muted max-w-lg">
                This sleek floating pill appears for first-time visitors across the platform to provide immediate, friction-free control over their privacy.
              </p>
            </div>

            <!-- Current Browser Status Badge -->
            <div class="flex flex-col sm:items-end gap-1">
              <span class="text-[10px] font-mono uppercase tracking-wider text-theme-muted font-semibold">Consent State</span>
              <span
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold font-mono tracking-wide border"
                :class="statusBadgeClasses"
              >
                <span class="h-1.5 w-1.5 rounded-full" :class="statusDotClasses"></span>
                {{ statusLabel }}
              </span>
              <span v-if="consentedAt" class="text-[10px] text-theme-muted">
                Updated {{ formattedConsentedAt }}
              </span>
            </div>
          </div>

          <!-- Interactive Pill Demo Display -->
          <div class="mt-6 flex flex-col items-center justify-center rounded-xl border border-theme-border/60 bg-[#070D12] p-8 text-center">
            <p class="text-xs text-white/50 mb-4 font-mono">Interactive Preview • Test the Actions</p>
            
            <div class="inline-flex items-center gap-4 sm:gap-5 rounded-full border border-white/10 bg-[#12161C] px-5 py-2.5 text-white shadow-2xl transition-all duration-300 hover:border-white/20">
              <span class="text-sm font-medium tracking-tight text-white/95 whitespace-nowrap">
                {{ content.banner_text || 'We use cookies.' }}
              </span>

              <div class="flex items-center gap-3 sm:gap-3.5">
                <!-- ⚙ Gear / Preferences Button -->
                <button
                  type="button"
                  class="inline-flex items-center justify-center text-white/70 hover:text-white transition-all duration-200 hover:rotate-45 focus:outline-none focus-visible:ring-2 focus-visible:ring-gold rounded-full p-1"
                  aria-label="Demo: customize preferences"
                  title="Preferences Modal"
                  @click="openModal"
                >
                  <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3" />
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                  </svg>
                </button>

                <!-- ✕ Reject Non-Essential Button -->
                <button
                  type="button"
                  class="inline-flex items-center justify-center text-white/70 hover:text-rose-400 hover:scale-110 transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 rounded-full p-1"
                  aria-label="Demo: decline optional cookies"
                  title="Reject Optional"
                  @click="handleQuickReject"
                >
                  <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6L6 18M6 6l12 12" />
                  </svg>
                </button>

                <!-- ✓ Accept All Button -->
                <button
                  type="button"
                  class="inline-flex items-center justify-center text-white/90 hover:text-emerald-400 hover:scale-110 transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 rounded-full p-1"
                  aria-label="Demo: accept all cookies"
                  title="Accept All"
                  @click="handleQuickAccept"
                >
                  <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 13l4 4L19 7" />
                  </svg>
                </button>
              </div>
            </div>

            <p class="mt-4 text-[11px] text-white/40">
              Click the gear to open the full drawer, ✕ for essential only, or ✓ to allow all.
            </p>
          </div>
        </article>

        <!-- Detailed Category Preferences Form -->
        <article class="rounded-2xl border border-theme-border bg-theme-surface p-6 sm:p-8 space-y-6 shadow-sm">
          <div class="border-b border-theme-border pb-4">
            <h2 class="text-xl font-bold text-theme-text">Granular Cookie Categories</h2>
            <p class="mt-1 text-xs sm:text-sm text-theme-muted">
              Select which individual capabilities you grant to this browser. Your choice is saved locally and respected throughout your visit.
            </p>
          </div>

          <!-- Category Cards Grid -->
          <div class="space-y-4">
            <div
              v-for="category in categories"
              :key="category.key"
              class="group relative rounded-xl border border-theme-border bg-theme-bg/60 p-5 sm:p-6 transition-all hover:border-gold/30 space-y-3"
            >
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3">
                  <span
                    class="flex h-7 w-7 items-center justify-center rounded-lg border text-xs font-bold"
                    :class="[
                      category.required
                        ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-500'
                        : 'border-gold/30 bg-gold/10 text-gold'
                    ]"
                  >
                    {{ category.label[0] }}
                  </span>
                  <div>
                    <h3 class="font-semibold text-theme-text text-sm sm:text-base">
                      {{ content[category.titleKey] || category.label }}
                    </h3>
                    <span class="text-[10px] font-mono uppercase tracking-wider text-theme-muted">
                      {{ category.badgeText }}
                    </span>
                  </div>
                </div>

                <!-- Toggle Switch -->
                <div class="shrink-0">
                  <button
                    type="button"
                    role="switch"
                    :aria-checked="category.required ? 'true' : Boolean(localPrefs[category.key])"
                    :aria-label="`Toggle ${category.label}`"
                    :disabled="category.required"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-gold disabled:cursor-not-allowed disabled:opacity-80"
                    :class="[
                      category.required || localPrefs[category.key]
                        ? 'bg-gold'
                        : 'bg-theme-muted/30 hover:bg-theme-muted/40'
                    ]"
                    @click="toggleCategory(category.key)"
                  >
                    <span
                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-md ring-0 transition duration-200 ease-in-out"
                      :class="[
                        category.required || localPrefs[category.key]
                          ? 'translate-x-5'
                          : 'translate-x-0'
                      ]"
                    />
                  </button>
                </div>
              </div>

              <p class="text-xs sm:text-sm leading-relaxed text-theme-muted">
                {{ content[category.descriptionKey] }}
              </p>
            </div>
          </div>

          <!-- Action Buttons Bar -->
          <div class="pt-4 border-t border-theme-border flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
              <button
                type="button"
                class="flex-1 sm:flex-none inline-flex min-h-11 items-center justify-center rounded-xl bg-gold px-6 py-2.5 font-bold text-obsidian shadow-sm transition hover:bg-gold-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold"
                data-testid="page-save-preferences"
                @click="handleSave"
              >
                Save Preferences
              </button>
              <button
                type="button"
                class="flex-1 sm:flex-none inline-flex min-h-11 items-center justify-center rounded-xl border border-theme-border px-5 py-2.5 font-semibold text-theme-text transition hover:border-gold/50 hover:text-gold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold"
                @click="handleAllowAll"
              >
                Allow All
              </button>
              <button
                type="button"
                class="flex-1 sm:flex-none inline-flex min-h-11 items-center justify-center rounded-xl border border-theme-border px-5 py-2.5 font-semibold text-theme-text transition hover:border-rose-400 hover:text-rose-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold"
                @click="handleRejectOptional"
              >
                Essential Only
              </button>
            </div>

            <button
              type="button"
              class="text-xs text-theme-muted hover:text-rose-400 transition-colors underline underline-offset-4 self-center sm:self-auto"
              @click="handleReset"
            >
              Reset to First-Time Prompt
            </button>
          </div>

          <!-- Feedback Status Toast Message -->
          <p
            v-if="feedbackMessage"
            class="text-xs font-medium text-emerald-500 rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-3"
            role="status"
            aria-live="polite"
          >
            {{ feedbackMessage }}
          </p>
        </article>

        <!-- Compliance & Regulatory Assurance Footnote -->
        <div class="rounded-xl border border-theme-border bg-theme-surface/50 p-6 space-y-2 text-xs text-theme-muted leading-relaxed">
          <h4 class="font-bold text-theme-text uppercase tracking-wider text-[11px]">Privacy & Regulatory Compliance</h4>
          <p>
            CandyCutz complies with the Nigeria Data Protection Act (NDPA) and global privacy principles. We never sell your personal information or store sensitive banking details in browser storage. For additional details regarding your user rights and appointment data protection, review our 
            <RouterLink to="/privacy" class="text-gold hover:underline font-medium">Privacy Policy</RouterLink> and 
            <RouterLink to="/terms" class="text-gold hover:underline font-medium">Terms of Service</RouterLink>.
          </p>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { RouterLink } from 'vue-router';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import PageHero from '@/shared/ui/PageHero.vue';
import { useCookieConsent } from '@/shared/composables/useCookieConsent';
import { publicApi } from '@/shared/api/old_publicApi';

const {
  categories,
  content,
  preferences,
  consentedAt,
  consentStatus,
  acceptAll,
  rejectNonEssential,
  savePreferences,
  openModal,
  resetConsent,
  applyCmsSettings,
} = useCookieConsent();

const feedbackMessage = ref('');

const localPrefs = reactive({
  necessary: true,
  preferences: false,
  analytics: false,
  marketing: false,
});

const syncLocalPrefs = () => {
  localPrefs.necessary = true;
  localPrefs.preferences = Boolean(preferences.preferences);
  localPrefs.analytics = Boolean(preferences.analytics);
  localPrefs.marketing = Boolean(preferences.marketing);
};

watch(
  preferences,
  () => {
    syncLocalPrefs();
  },
  { deep: true }
);

const statusLabel = computed(() => {
  switch (consentStatus.value) {
    case 'all':
      return 'All Cookies Allowed';
    case 'necessary_only':
      return 'Essential Only Allowed';
    case 'custom':
      return 'Custom Preferences Active';
    default:
      return 'Awaiting Visitor Decision';
  }
});

const statusBadgeClasses = computed(() => {
  switch (consentStatus.value) {
    case 'all':
      return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400';
    case 'necessary_only':
      return 'border-blue-500/30 bg-blue-500/10 text-blue-400';
    case 'custom':
      return 'border-gold/30 bg-gold/10 text-gold';
    default:
      return 'border-amber-500/30 bg-amber-500/10 text-amber-400';
  }
});

const statusDotClasses = computed(() => {
  switch (consentStatus.value) {
    case 'all':
      return 'bg-emerald-400 animate-pulse';
    case 'necessary_only':
      return 'bg-blue-400';
    case 'custom':
      return 'bg-gold';
    default:
      return 'bg-amber-400';
  }
});

const formattedConsentedAt = computed(() => {
  if (!consentedAt.value) return '';
  try {
    const d = new Date(consentedAt.value);
    return d.toLocaleDateString('en-GB', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch {
    return '';
  }
});

const toggleCategory = (key) => {
  if (key === 'necessary') return;
  localPrefs[key] = !localPrefs[key];
};

const handleSave = () => {
  savePreferences(localPrefs);
  feedbackMessage.value = 'Your privacy preferences have been updated and stored in this browser.';
  setTimeout(() => {
    feedbackMessage.value = '';
  }, 4000);
};

const handleAllowAll = () => {
  acceptAll();
  syncLocalPrefs();
  feedbackMessage.value = 'All cookie categories have been granted.';
  setTimeout(() => {
    feedbackMessage.value = '';
  }, 4000);
};

const handleRejectOptional = () => {
  rejectNonEssential();
  syncLocalPrefs();
  feedbackMessage.value = 'Optional cookies disabled. Only strictly necessary storage is enabled.';
  setTimeout(() => {
    feedbackMessage.value = '';
  }, 4000);
};

const handleQuickAccept = () => {
  handleAllowAll();
};

const handleQuickReject = () => {
  handleRejectOptional();
};

const handleReset = () => {
  resetConsent();
  syncLocalPrefs();
  feedbackMessage.value = 'Preferences reset. The floating pill consent bar has been re-armed.';
  setTimeout(() => {
    feedbackMessage.value = '';
  }, 4000);
};

onMounted(async () => {
  syncLocalPrefs();
  try {
    const response = await publicApi.settings();
    const settings = response.data?.data;
    if (settings) {
      applyCmsSettings(settings);
    }
  } catch (error) {
    console.warn('Failed to load cookie page CMS settings:', error);
  }
});
</script>
