<template>
  <PublicLayout>
    <PageHero
      :page-key="pageKey"
      :eyebrow="eyebrow"
      :fallback-title="fallbackTitle"
      :title-setting-key="`${settingPrefix}_title`"
    />

    <section class="bg-theme-surface px-6 py-12 sm:py-16">
      <div class="mx-auto max-w-3xl space-y-8 text-theme-text">
        <article
          v-for="(section, index) in sections"
          :key="`${section.heading}-${index}`"
          class="rounded-2xl border border-theme-border bg-theme-bg/40 p-5 sm:p-7"
          data-reveal
          :data-reveal-delay="index * 50"
        >
          <h2 class="font-display text-xl text-gold sm:text-2xl">{{ section.heading }}</h2>
          <p class="mt-3 whitespace-pre-wrap text-sm leading-7 text-theme-muted">{{ section.body }}</p>
        </article>

        <nav v-if="showLegalLinks" aria-label="Legal pages" class="grid gap-3 sm:grid-cols-2">
          <RouterLink
            v-for="link in legalLinks"
            :key="link.to"
            :to="link.to"
            class="rounded-xl border border-gold/20 bg-gold/5 px-5 py-4 font-semibold text-theme-text transition hover:border-gold/50 hover:bg-gold/10"
          >
            {{ link.label }}
            <span class="float-right text-gold" aria-hidden="true">→</span>
          </RouterLink>
        </nav>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import PageHero from '@/shared/ui/PageHero.vue';
import { publicApi } from '@/shared/api/old_publicApi';

const props = defineProps({
  settingPrefix: { type: String, required: true },
  eyebrow: { type: String, required: true },
  fallbackTitle: { type: String, required: true },
  defaultSections: { type: Array, default: () => [] },
  showLegalLinks: { type: Boolean, default: false },
});

const sections = ref(props.defaultSections.map(section => ({ ...section })));
const legalLinks = [
  { label: 'Terms of Service', to: '/terms' },
  { label: 'Privacy Policy', to: '/privacy' },
  { label: 'Security', to: '/security' },
  { label: 'Manage Cookies', to: '/cookies' },
];

const pageKey = computed(() => (props.settingPrefix === 'legal_stuff' ? 'legal' : 'security'));
const sectionsKey = computed(() => `${props.settingPrefix}_sections`);

onMounted(async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data?.data;
    if (!settings) return;

    const savedSections = settings[sectionsKey.value];
    if (savedSections) {
      const parsedSections = typeof savedSections === 'string' ? JSON.parse(savedSections) : savedSections;
      if (Array.isArray(parsedSections)) sections.value = parsedSections;
    }
  } catch (error) {
    console.error(`Failed to load ${props.settingPrefix} page settings:`, error);
  }
});
</script>
