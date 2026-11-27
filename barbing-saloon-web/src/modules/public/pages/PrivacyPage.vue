<template>
  <PublicLayout>
    <PageHero
      page-key="privacy"
      eyebrow="Legal"
      fallback-title="Privacy Policy"
      fallback-subtitle="How we collect, use, and protect your information."
      title-setting-key="privacy_title"
    />

    <!-- Content Section -->
    <section class="bg-theme-surface py-20">
      <div class="mx-auto max-w-3xl px-6 text-theme-text leading-relaxed prose dark:prose-invert prose-gold">
        <p v-if="privacyVersion" class="mb-10 text-sm text-theme-muted">Version: {{ privacyVersion }}</p>

        <p v-if="loading" class="py-10 text-center text-sm text-theme-muted">Loading Privacy Policy...</p>

        <div v-else-if="pageSections.length === 0" class="rounded-2xl border border-theme-border bg-theme-surface p-6 text-sm text-theme-muted">
          Our Privacy Policy is being updated. Please check back shortly, or contact us if you need a copy.
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

const privacyVersion = ref('');
const pageSections = ref([]);
const loading = ref(true);

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings) {
      if (settings.privacy_version) {
        privacyVersion.value = settings.privacy_version;
      }
      if (settings.privacy_sections) {
        try {
          const parsed = typeof settings.privacy_sections === 'string'
            ? JSON.parse(settings.privacy_sections)
            : settings.privacy_sections;
          if (Array.isArray(parsed) && parsed.length > 0) pageSections.value = parsed;
        } catch (e) { /* keep CMS content empty */ }
      }
    }
  } catch (error) {
    console.error('Failed to load privacy settings:', error);
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
