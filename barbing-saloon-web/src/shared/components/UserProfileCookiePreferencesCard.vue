<template>
  <article
    class="rounded-3xl border border-slate-200/80 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-8 shadow-xl space-y-6"
    data-testid="user-profile-cookie-card"
  >
    <!-- Card Header with Title & Status Badge -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-100 dark:border-white/[0.06]">
      <div class="space-y-1">
        <div class="flex items-center gap-2">
          <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-gold/10 text-gold border border-gold/20">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
          </span>
          <span class="text-[11px] font-bold uppercase tracking-wider text-gold">Privacy &amp; Security</span>
        </div>
        <h2 class="font-display text-xl text-slate-900 dark:text-white">
          Cookie &amp; Tracking Preferences
        </h2>
        <p class="text-xs text-[var(--color-text-secondary)] leading-relaxed max-w-md">
          Control which cookies and local storage capabilities are active on this device.
        </p>
      </div>

      <!-- Live Consent State Badge -->
      <div class="flex flex-col sm:items-end gap-1">
        <span class="text-[10px] font-mono uppercase tracking-wider text-[var(--color-text-muted)] font-semibold">Active Profile State</span>
        <span
          class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold font-mono tracking-wide border"
          :class="statusBadgeClasses"
        >
          <span class="h-1.5 w-1.5 rounded-full" :class="statusDotClasses"></span>
          {{ statusLabel }}
        </span>
        <span v-if="consentedAt" class="text-[10px] text-[var(--color-text-muted)]">
          Last saved {{ formattedConsentedAt }}
        </span>
      </div>
    </div>

    <!-- Category Toggles List -->
    <div class="space-y-4">
      <div
        v-for="cat in categories"
        :key="cat.key"
        class="rounded-2xl border border-slate-100 dark:border-white/[0.06] bg-slate-50/70 dark:bg-white/[0.02] p-4 sm:p-5 transition-all hover:border-gold/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        :data-testid="`profile-cookie-cat-${cat.key}`"
      >
        <div class="space-y-1 pr-2">
          <div class="flex items-center gap-2">
            <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
              {{ content[cat.titleKey] || cat.label }}
            </h3>
            <span
              v-if="cat.required"
              class="rounded-md border border-emerald-500/30 bg-emerald-500/10 px-2 py-0.5 text-[9px] font-mono font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400"
            >
              Always Active
            </span>
            <span
              v-else
              class="rounded-md border border-slate-200 dark:border-white/10 bg-white/50 dark:bg-white/[0.03] px-2 py-0.5 text-[9px] font-mono text-slate-500 dark:text-white/40 uppercase tracking-wider"
            >
              Optional
            </span>
          </div>
          <p class="text-[11px] sm:text-xs text-[var(--color-text-secondary)] leading-relaxed max-w-xl">
            {{ content[cat.descriptionKey] }}
          </p>
        </div>

        <!-- Toggle Switch -->
        <div class="shrink-0">
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
                : 'bg-slate-300 dark:bg-white/20 hover:bg-slate-400 dark:hover:bg-white/30'
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

    <!-- Action Buttons -->
    <div class="pt-2 border-t border-slate-100 dark:border-white/[0.06] flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
        <button
          type="button"
          class="flex-1 sm:flex-none rounded-xl bg-gold px-5 py-2.5 text-xs font-bold text-obsidian shadow-sm transition hover:bg-gold-light focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
          data-testid="profile-cookie-save"
          @click="handleSave"
        >
          Save Privacy Choices
        </button>

        <button
          type="button"
          class="flex-1 sm:flex-none rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-white/80 hover:bg-slate-100 dark:hover:bg-white/[0.08] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
          @click="handleAllowAll"
        >
          Allow All
        </button>

        <button
          type="button"
          class="flex-1 sm:flex-none rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.04] px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-white/80 hover:text-rose-500 dark:hover:text-rose-400 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-gold"
          @click="handleRejectOptional"
        >
          Essential Only
        </button>
      </div>

      <button
        type="button"
        class="text-xs text-[var(--color-text-muted)] hover:text-amber-500 dark:hover:text-amber-400 transition-colors underline underline-offset-4 self-center sm:self-auto"
        @click="handleReset"
      >
        Reset to Website Prompt
      </button>
    </div>

    <!-- Success Message Banner -->
    <div
      v-if="statusMessage"
      class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-3 text-xs font-semibold text-emerald-600 dark:text-emerald-400"
      role="status"
      aria-live="polite"
    >
      {{ statusMessage }}
    </div>
  </article>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useCookieConsent } from '@/shared/composables/useCookieConsent';

const {
  categories,
  content,
  preferences,
  hasConsented,
  consentedAt,
  consentStatus,
  acceptAll,
  rejectNonEssential,
  savePreferences,
  resetConsent,
} = useCookieConsent();

const statusMessage = ref('');

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
      return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
    case 'necessary_only':
      return 'border-blue-500/30 bg-blue-500/10 text-blue-600 dark:text-blue-400';
    case 'custom':
      return 'border-gold/30 bg-gold/10 text-gold';
    default:
      return 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400';
  }
});

const statusDotClasses = computed(() => {
  switch (consentStatus.value) {
    case 'all':
      return 'bg-emerald-500 dark:bg-emerald-400';
    case 'necessary_only':
      return 'bg-blue-500 dark:bg-blue-400';
    case 'custom':
      return 'bg-gold';
    default:
      return 'bg-amber-500 dark:bg-amber-400';
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
  statusMessage.value = 'Your cookie and privacy preferences have been updated on this device.';
  setTimeout(() => {
    statusMessage.value = '';
  }, 4000);
};

const handleAllowAll = () => {
  acceptAll();
  syncLocalPrefs();
  statusMessage.value = 'All optional cookie categories have been granted.';
  setTimeout(() => {
    statusMessage.value = '';
  }, 4000);
};

const handleRejectOptional = () => {
  rejectNonEssential();
  syncLocalPrefs();
  statusMessage.value = 'Optional cookies disabled. Essential storage remains active for booking security.';
  setTimeout(() => {
    statusMessage.value = '';
  }, 4000);
};

const handleReset = () => {
  resetConsent();
  syncLocalPrefs();
  statusMessage.value = 'Consent reset. The floating pill bar will be shown on next public visit.';
  setTimeout(() => {
    statusMessage.value = '';
  }, 4000);
};

onMounted(() => {
  syncLocalPrefs();
});
</script>
