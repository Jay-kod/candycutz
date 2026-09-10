<template>
  <PublicLayout>
    <!-- Header Section -->
    <section class="relative border-b border-theme-border bg-theme-bg py-24 md:py-32">
      <div class="absolute inset-0 bg-gold/5 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-gold/10 via-theme-bg to-theme-bg"></div>
      <div class="relative mx-auto max-w-4xl px-6 text-center">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gold" data-reveal>Account Management</p>
        <h1 class="mt-4 font-display text-4xl font-bold tracking-tight text-theme-text md:text-5xl lg:text-6xl" data-reveal data-reveal-delay="100">
          Account Deactivation & Erasure
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-theme-muted" data-reveal data-reveal-delay="200">
          Instructions for requesting deletion or deactivation of your CandyCutz account and associated personal data.
        </p>
      </div>
    </section>

    <!-- Guide & Submission Section -->
    <section class="bg-theme-surface py-20">
      <div class="mx-auto max-w-3xl px-6 text-theme-text space-y-12">
        
        <!-- Step 1: In-App Deactivation -->
        <div class="rounded-2xl border border-theme-border bg-theme-bg/60 p-8" data-reveal>
          <div class="flex items-center gap-4 mb-4">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gold/10 text-gold font-bold text-lg">1</span>
            <h2 class="font-display text-2xl font-semibold text-gold">Instant In-App Deactivation</h2>
          </div>
          <p class="text-theme-muted leading-relaxed mb-4">
            You can immediately deactivate your account at any time using the CandyCutz mobile application:
          </p>
          <ol class="list-decimal list-inside space-y-2 text-theme-text/90 pl-2">
            <li>Open the <strong class="text-gold">CandyCutz</strong> app on your iOS or Android device.</li>
            <li>Navigate to your <strong class="text-gold">Profile</strong> tab in the bottom navigation.</li>
            <li>Tap on <strong class="text-gold">Security & Privacy</strong>.</li>
            <li>Tap <strong class="text-red-400">Deactivate Account</strong> and confirm your password.</li>
          </ol>
          <p class="mt-4 text-xs text-theme-muted">
            * This immediately revokes all active device sessions, terminates scheduled notifications, and removes your account from public discovery.
          </p>
        </div>

        <!-- Step 2: Web Deletion Request Form -->
        <div class="rounded-2xl border border-theme-border bg-theme-bg/60 p-8" data-reveal data-reveal-delay="100">
          <div class="flex items-center gap-4 mb-4">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gold/10 text-gold font-bold text-lg">2</span>
            <h2 class="font-display text-2xl font-semibold text-gold">Submit Deletion Request Online</h2>
          </div>
          <p class="text-theme-muted leading-relaxed mb-6">
            If you do not have access to the mobile application, complete the form below or contact our privacy team to initiate a manual data erasure request.
          </p>

          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div v-if="submitted" class="rounded-xl border border-gold/30 bg-gold/10 p-6 text-center">
              <h3 class="font-display text-xl font-bold text-gold">Request Received</h3>
              <p class="mt-2 text-sm text-theme-muted">
                Our Data Protection Officer has received your deactivation request. A confirmation email will be dispatched within 48 business hours.
              </p>
            </div>

            <div v-else class="space-y-5">
              <div>
                <label class="block text-sm font-medium text-theme-muted mb-2">Registered Email Address</label>
                <input
                  v-model="email"
                  type="email"
                  required
                  placeholder="you@example.com"
                  class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted/50 focus:border-gold focus:outline-none"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-theme-muted mb-2">Account Username or Full Name</label>
                <input
                  v-model="identifier"
                  type="text"
                  required
                  placeholder="@username or Full Name"
                  class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted/50 focus:border-gold focus:outline-none"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-theme-muted mb-2">Reason for Deactivation (Optional)</label>
                <textarea
                  v-model="reason"
                  rows="3"
                  placeholder="Help us understand how we can improve..."
                  class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted/50 focus:border-gold focus:outline-none"
                ></textarea>
              </div>

              <button
                type="submit"
                :disabled="loading"
                class="w-full rounded-xl bg-gold px-6 py-4 font-semibold text-black transition-all hover:bg-gold-light disabled:opacity-50"
              >
                {{ loading ? 'Submitting Request...' : 'Submit Account Deletion Request' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Retention Policy -->
        <div class="rounded-2xl border border-theme-border bg-theme-bg/40 p-6 text-sm text-theme-muted" data-reveal data-reveal-delay="200">
          <h3 class="font-bold text-theme-text mb-2">Statutory Data Retention Notice</h3>
          <p class="leading-relaxed">
            Per Nigerian financial reporting requirements and commercial audit standards, completed transaction receipts and tax records are retained for a minimum statutory period. All marketing subscriptions, identity markers, personal device tokens, and contact preferences are wiped immediately upon processing.
          </p>
        </div>

      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

const { init: initScrollReveal } = useScrollReveal();

const email = ref('');
const identifier = ref('');
const reason = ref('');
const loading = ref(false);
const submitted = ref(false);

const handleSubmit = async () => {
  loading.value = true;
  setTimeout(() => {
    loading.value = false;
    submitted.value = true;
  }, 1000);
};

onMounted(() => {
  window.scrollTo(0, 0);
  setTimeout(() => {
    initScrollReveal();
  }, 100);
});
</script>
