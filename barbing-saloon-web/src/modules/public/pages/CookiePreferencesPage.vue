<template>
  <PublicLayout>
    <PageHero
      page-key="cookies"
      eyebrow="Privacy Controls"
      fallback-title="Manage Cookies"
      fallback-subtitle="Choose which optional cookie categories you want to allow. Necessary storage is always active so the site can provide core features."
      title-setting-key="cookie_title"
      subtitle-setting-key="cookie_intro"
    />

    <section class="bg-theme-surface px-6 py-12 sm:py-16">
      <div class="mx-auto max-w-3xl space-y-4">
        <article
          v-for="category in categories"
          :key="category.key"
          class="flex flex-col gap-4 rounded-2xl border border-theme-border bg-theme-bg/40 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6"
        >
          <div>
            <h2 class="font-semibold text-theme-text">{{ content[category.titleKey] }}</h2>
            <p class="mt-1 max-w-xl whitespace-pre-wrap text-sm leading-6 text-theme-muted">{{ content[category.descriptionKey] }}</p>
          </div>
          <label class="inline-flex shrink-0 items-center gap-3 text-sm font-medium text-theme-text">
            <input
              v-if="category.required"
              type="checkbox"
              checked
              disabled
              class="size-5 accent-gold"
            />
            <input
              v-else
              v-model="preferences[category.key]"
              type="checkbox"
              class="size-5 accent-gold"
            />
            <span>{{ category.required ? 'Always active' : 'Allow' }}</span>
          </label>
        </article>

        <div class="flex flex-col gap-3 pt-3 sm:flex-row sm:items-center">
          <button
            type="button"
            class="inline-flex min-h-11 items-center justify-center rounded-xl bg-gold px-5 py-2.5 font-semibold text-obsidian transition hover:bg-gold-light focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold"
            @click="savePreferences"
          >
            Save Preferences
          </button>
          <button
            type="button"
            class="inline-flex min-h-11 items-center justify-center rounded-xl border border-theme-border px-5 py-2.5 font-semibold text-theme-text transition hover:border-gold/50 hover:text-gold focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold"
            @click="allowOptional = true; savePreferences()"
          >
            Allow Optional
          </button>
          <p v-if="message" class="text-sm text-theme-muted" role="status" aria-live="polite">{{ message }}</p>
        </div>
        <p class="text-xs leading-5 text-theme-muted">
          Necessary storage keeps core site features working. Optional preferences are saved in this browser and can be changed here at any time.
        </p>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import PageHero from '@/shared/ui/PageHero.vue';
import { publicApi } from '@/shared/api/old_publicApi';

const STORAGE_KEY = 'candycutz_cookie_preferences';
const allowOptional = ref(false);
const message = ref('');
const preferences = reactive({
  necessary: true,
  preferences: false,
  analytics: false,
  marketing: false,
});

const content = reactive({
  cookie_title: 'Manage Cookies',
  cookie_intro: 'Choose which optional cookie categories you want to allow. Necessary storage is always active so the site can provide core features.',
  cookie_necessary_title: 'Necessary',
  cookie_necessary_description: 'Required for core site functions such as keeping your session secure. These cannot be switched off.',
  cookie_preferences_title: 'Preferences',
  cookie_preferences_description: 'Remember choices that make your experience more convenient.',
  cookie_analytics_title: 'Analytics',
  cookie_analytics_description: 'Allow analytics tools to help understand how the site is used, if enabled.',
  cookie_marketing_title: 'Marketing',
  cookie_marketing_description: 'Allow marketing-related cookies or similar technologies, if enabled.',
});

const categories = computed(() => [
  { key: 'necessary', titleKey: 'cookie_necessary_title', descriptionKey: 'cookie_necessary_description', required: true },
  { key: 'preferences', titleKey: 'cookie_preferences_title', descriptionKey: 'cookie_preferences_description', required: false },
  { key: 'analytics', titleKey: 'cookie_analytics_title', descriptionKey: 'cookie_analytics_description', required: false },
  { key: 'marketing', titleKey: 'cookie_marketing_title', descriptionKey: 'cookie_marketing_description', required: false },
]);

const savePreferences = () => {
  try {
    preferences.necessary = true;
    preferences.preferences = allowOptional.value || preferences.preferences;
    preferences.analytics = allowOptional.value || preferences.analytics;
    preferences.marketing = allowOptional.value || preferences.marketing;
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
      ...preferences,
      updatedAt: new Date().toISOString(),
    }));
    allowOptional.value = false;
    message.value = 'Your cookie preferences have been saved in this browser.';
  } catch (error) {
    console.error('Failed to save cookie preferences:', error);
    message.value = 'Preferences could not be saved in this browser. Check your browser storage settings and try again.';
  }
};

onMounted(async () => {
  try {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
      const parsed = JSON.parse(saved);
      preferences.preferences = parsed.preferences === true;
      preferences.analytics = parsed.analytics === true;
      preferences.marketing = parsed.marketing === true;
    }
  } catch (error) {
    console.error('Failed to load cookie preferences:', error);
    message.value = 'Saved preferences could not be read in this browser.';
  }

  try {
    const response = await publicApi.settings();
    const settings = response.data?.data;
    if (settings) {
      for (const key of Object.keys(content)) {
        if (typeof settings[key] === 'string' && settings[key].trim()) content[key] = settings[key];
      }
    }
  } catch (error) {
    console.error('Failed to load cookie page settings:', error);
  }
});
</script>
