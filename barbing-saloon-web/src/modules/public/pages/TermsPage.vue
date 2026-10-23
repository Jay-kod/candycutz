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
      <div class="mx-auto max-w-3xl px-6 text-theme-text leading-relaxed prose prose-invert prose-gold">
        <p class="mb-10 text-sm text-theme-muted">Last updated: {{ currentDate }}</p>
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
import PageHero from '@/shared/ui/PageHero.vue';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import { publicApi } from '@/shared/api/old_publicApi';

const { init: initScrollReveal } = useScrollReveal();

const currentDate = new Date().toLocaleDateString();
const pageSections = ref([
  {
    heading: '1. Agreement to Terms',
    body: 'By accessing or using CandyCutz services, website, or mobile application, you agree to be bound by these Terms of Service. If you disagree with any part of the terms, then you may not access our services.'
  },
  {
    heading: '2. Booking and Appointments',
    body: "We strive to provide excellent service and value your time. Please note our policies regarding appointments:\n\n• Appointments can be booked up to 30 days in advance.\n• We require at least 24 hours notice for cancellations or rescheduling.\n• Late arrivals (more than 15 minutes) may result in a shortened service or cancellation.\n• No-shows may be subject to a fee equal to 50% of the scheduled service cost."
  },
  {
    heading: '3. Payment and Pricing',
    body: 'All prices for our services are subject to change without notice. We accept cash, credit/debit cards, and mobile payments. Payment is due in full at the time of service. For certain premium packages or group bookings, a deposit may be required in advance.'
  },
  {
    heading: '4. Intellectual Property',
    body: 'The service and its original content, features, and functionality are and will remain the exclusive property of CandyCutz and its licensors. The service is protected by copyright, trademark, and other laws of both the United States and foreign countries. Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of CandyCutz.'
  },
  {
    heading: '5. Contact Us',
    body: "If you have any questions about these Terms, please contact us:\n\n• By email: legal@candycutz.com\n• By visiting the contact page on our website."
  }
]);

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings) {
      if (settings.terms_sections) {
        try {
          const parsed = typeof settings.terms_sections === 'string'
            ? JSON.parse(settings.terms_sections)
            : settings.terms_sections;
          if (Array.isArray(parsed) && parsed.length > 0) pageSections.value = parsed;
        } catch (e) { /* fallback to defaults */ }
      }
    }
  } catch (error) {
    console.error('Failed to load terms settings:', error);
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
