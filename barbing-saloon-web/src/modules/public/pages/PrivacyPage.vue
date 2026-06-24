<template>
  <PublicLayout>
    <!-- Header Section -->
    <section class="relative border-b border-theme-border bg-theme-bg py-24 md:py-32">
      <div class="absolute inset-0 bg-gold/5 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-gold/10 via-theme-bg to-theme-bg"></div>
      <div class="relative mx-auto max-w-4xl px-6 text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gold" data-reveal>Legal</p>
        <h1 class="mt-4 font-display text-5xl font-bold tracking-tight text-theme-text md:text-6xl lg:text-7xl" data-reveal data-reveal-delay="100">{{ pageTitle }}</h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-theme-muted" data-reveal data-reveal-delay="200">Last updated: {{ new Date().toLocaleDateString() }}</p>
      </div>
    </section>

    <!-- Content Section -->
    <section class="bg-theme-surface py-20">
      <div class="mx-auto max-w-3xl px-6 text-theme-text leading-relaxed prose prose-invert prose-gold">
        <div v-for="(section, index) in pageSections" :key="index" data-reveal :data-reveal-delay="100 + (index * 50)">
          <h2 class="font-display text-3xl text-gold mb-6">{{ section.heading }}</h2>
          <p class="mb-8 whitespace-pre-wrap">{{ section.body }}</p>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import { publicApi } from '../api/public.api';

const { init: initScrollReveal } = useScrollReveal();

const pageTitle = ref('Privacy Policy');
const pageSections = ref([
  {
    heading: '1. Introduction',
    body: 'Welcome to CandyCutz. We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you as to how we look after your personal data when you visit our website (regardless of where you visit it from) and tell you about your privacy rights and how the law protects you.'
  },
  {
    heading: '2. The Data We Collect About You',
    body: "Personal data, or personal information, means any information about an individual from which that person can be identified. We may collect, use, store and transfer different kinds of personal data about you:\n\n• Identity Data includes first name, last name, username or similar identifier.\n• Contact Data includes billing address, email address and telephone numbers.\n• Transaction Data includes details about payments to and from you and other details of services you have purchased from us.\n• Profile Data includes your username and password, purchases or orders made by you, your interests, preferences, feedback and survey responses."
  },
  {
    heading: '3. How We Use Your Personal Data',
    body: 'We will only use your personal data when the law allows us to. Most commonly, we will use your personal data to register you as a new customer, process and deliver your order, manage our relationship with you, and to improve our website, products/services, marketing or customer relationships.'
  },
  {
    heading: '4. Data Security',
    body: 'We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used or accessed in an unauthorised way, altered or disclosed. In addition, we limit access to your personal data to those employees, agents, contractors and other third parties who have a business need to know.'
  },
  {
    heading: '5. Your Legal Rights',
    body: "Under certain circumstances, you have rights under data protection laws in relation to your personal data, including the right to:\n\n• Request access to your personal data.\n• Request correction of your personal data.\n• Request erasure of your personal data.\n• Object to processing of your personal data."
  }
]);

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings) {
      if (settings.privacy_title) pageTitle.value = settings.privacy_title;
      if (settings.privacy_sections) {
        try {
          const parsed = JSON.parse(settings.privacy_sections);
          if (Array.isArray(parsed) && parsed.length > 0) pageSections.value = parsed;
        } catch (e) { /* fallback to defaults */ }
      }
    }
  } catch (error) {
    console.error('Failed to load privacy settings:', error);
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
