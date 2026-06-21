<template>
  <PublicLayout>
    <!-- Header Section -->
    <section class="relative border-b border-theme-border bg-theme-bg py-24 md:py-32">
      <div class="absolute inset-0 bg-gold/5 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-gold/10 via-theme-bg to-theme-bg"></div>
      <div class="relative mx-auto max-w-4xl px-6 text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gold" data-reveal>Legal</p>
        <h1 class="mt-4 font-display text-5xl font-bold tracking-tight text-theme-text md:text-6xl lg:text-7xl" data-reveal data-reveal-delay="100">Terms of Service</h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-theme-muted" data-reveal data-reveal-delay="200">Last updated: {{ new Date().toLocaleDateString() }}</p>
      </div>
    </section>

    <!-- Content Section -->
    <section class="bg-theme-surface py-20">
      <div class="mx-auto max-w-3xl px-6 text-theme-text leading-relaxed prose prose-invert prose-gold">
        <div data-reveal data-reveal-delay="100">
          <h2 class="font-display text-3xl text-gold mb-6">1. Agreement to Terms</h2>
          <p class="mb-8">By accessing or using CandyCutz services, website, or mobile application, you agree to be bound by these Terms of Service. If you disagree with any part of the terms, then you may not access our services.</p>
        </div>

        <div data-reveal data-reveal-delay="150">
          <h2 class="font-display text-3xl text-gold mb-6">2. Booking and Appointments</h2>
          <p class="mb-4">We strive to provide excellent service and value your time. Please note our policies regarding appointments:</p>
          <ul class="list-disc pl-6 mb-8 space-y-2 text-theme-muted">
            <li>Appointments can be booked up to 30 days in advance.</li>
            <li>We require at least 24 hours notice for cancellations or rescheduling.</li>
            <li>Late arrivals (more than 15 minutes) may result in a shortened service or cancellation.</li>
            <li>No-shows may be subject to a fee equal to 50% of the scheduled service cost.</li>
          </ul>
        </div>

        <div data-reveal data-reveal-delay="200">
          <h2 class="font-display text-3xl text-gold mb-6">3. Payment and Pricing</h2>
          <p class="mb-8">All prices for our services are subject to change without notice. We accept cash, credit/debit cards, and mobile payments. Payment is due in full at the time of service. For certain premium packages or group bookings, a deposit may be required in advance.</p>
        </div>

        <div data-reveal data-reveal-delay="250">
          <h2 class="font-display text-3xl text-gold mb-6">4. Intellectual Property</h2>
          <p class="mb-8">The service and its original content, features, and functionality are and will remain the exclusive property of CandyCutz and its licensors. The service is protected by copyright, trademark, and other laws of both the United States and foreign countries. Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of CandyCutz.</p>
        </div>

        <div data-reveal data-reveal-delay="300">
          <h2 class="font-display text-3xl text-gold mb-6">5. Contact Us</h2>
          <p class="mb-4">If you have any questions about these Terms, please contact us:</p>
          <ul class="list-disc pl-6 mb-8 space-y-2 text-theme-muted">
            <li>By email: legal@candycutz.com</li>
            <li>By visiting the contact page on our website.</li>
          </ul>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

const { init: initScrollReveal } = useScrollReveal();

onMounted(() => {
  window.scrollTo(0, 0);
  initScrollReveal();
});
</script>
