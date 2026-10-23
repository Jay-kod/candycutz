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
  },
  {
    heading: '6. Account Deactivation & Data Erasure (App Store & Play Store Compliance)',
    body: "In accordance with Apple Guideline 5.1.1(v) and Google Play user data policies, users have full autonomy to deactivate their accounts and request data deletion:\n\n• In-App Deactivation: You may initiate immediate account deactivation within the CandyCutz mobile app under Profile > Security > Deactivate Account.\n• Web Deletion Request: You can submit an account erasure request at https://candycutz.com/account-deletion or by emailing privacy@candycutz.com.\n• Data Retention Policy: Upon deactivation, your profile is immediately deactivated and active authentication tokens are revoked. Historical financial transaction logs are retained strictly in accordance with statutory accounting and tax regulations in Nigeria before being permanently purged."
  }
]);

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings) {
      if (settings.privacy_sections) {
        try {
          const parsed = typeof settings.privacy_sections === 'string'
            ? JSON.parse(settings.privacy_sections)
            : settings.privacy_sections;
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
