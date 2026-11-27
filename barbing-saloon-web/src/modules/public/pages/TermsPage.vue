<template>
  <PublicLayout>
    <PageHero
      page-key="terms"
      eyebrow="Legal"
      fallback-title="Terms of Service"
      fallback-subtitle="The terms that apply when you use CandyCutz services."
      title-setting-key="terms_title"
    />

    <!-- Content Section -->
    <section class="bg-theme-surface py-20">
      <div class="mx-auto max-w-3xl px-6 text-theme-text leading-relaxed prose dark:prose-invert prose-gold">
        <p v-if="termsVersion" class="mb-10 text-sm text-theme-muted">Version: {{ termsVersion }}</p>

        <p v-if="loading" class="py-10 text-center text-sm text-theme-muted">Loading Terms of Service...</p>

        <div v-else-if="pageSections.length === 0" class="rounded-2xl border border-theme-border bg-theme-surface p-6 text-sm text-theme-muted">
          Our Terms of Service are being updated. Please check back shortly, or contact us if you need a copy.
        </div>

        <template v-else>
          <div v-for="(section, index) in pageSections" :key="index" data-reveal :data-reveal-delay="100 + (index * 50)">
            <h2 class="font-display text-3xl text-gold mb-6">{{ section.heading }}</h2>
            <p class="mb-8 whitespace-pre-wrap">{{ section.body }}</p>
          </div>
        </template>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import PageHero from '@/shared/ui/PageHero.vue';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import { publicApi } from '@/shared/api/old_publicApi';

const { init: initScrollReveal } = useScrollReveal();

const termsVersion = ref('');
const pageSections = ref([]);
const loading = ref(true);

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings) {
      if (settings.terms_version) {
        termsVersion.value = settings.terms_version;
      }
      if (settings.terms_sections) {
        try {
          const parsed = typeof settings.terms_sections === 'string'
            ? JSON.parse(settings.terms_sections)
            : settings.terms_sections;
          if (Array.isArray(parsed) && parsed.length > 0) pageSections.value = parsed;
        } catch (e) { /* keep CMS content empty */ }
      }
    }
  } catch (error) {
    console.error('Failed to load terms settings:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  window.scrollTo(0, 0);
  fetchSettings();
  setTimeout(() => {
    initScrollReveal();
  }, 100);
});
</script>
