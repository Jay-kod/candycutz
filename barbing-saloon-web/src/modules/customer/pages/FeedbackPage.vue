<template>
  <CustomerLayout>
    <section class="mx-auto max-w-4xl space-y-6 animate-fade-in">
      <header class="relative overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6 sm:p-8">
        <div class="absolute -right-16 -top-16 size-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl" aria-hidden="true"></div>
        <div class="relative">
          <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[var(--color-accent)]">Customer Feedback</p>
          <h1 class="mt-2 font-display text-3xl text-[var(--color-text-primary)] sm:text-4xl">Help Shape <span class="text-[var(--color-accent)]">CandyCutz</span></h1>
          <p class="mt-3 max-w-2xl text-sm leading-6 text-[var(--color-text-secondary)]">
            Tell us what features you would like, report maintenance issues, or suggest improvements. Your submission goes to our team privately and will not appear on the public website.
          </p>
        </div>
      </header>

      <form class="space-y-6 rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-5 sm:p-8" @submit.prevent="submit">
        <div>
          <label for="feedback-category" class="mb-2 block text-sm font-semibold text-[var(--color-text-primary)]">What is this about?</label>
          <select
            id="feedback-category"
            v-model="form.category"
            required
            class="min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-4 py-3 text-[var(--color-text-primary)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]/20"
          >
            <option value="feature_request">A feature I would like to see</option>
            <option value="maintenance">A problem or maintenance issue</option>
            <option value="improvement">A general improvement</option>
          </select>
        </div>

        <div>
          <label for="feedback-title" class="mb-2 block text-sm font-semibold text-[var(--color-text-primary)]">Short title</label>
          <input
            id="feedback-title"
            v-model.trim="form.title"
            type="text"
            maxlength="150"
            required
            placeholder="Summarize your suggestion or issue"
            class="min-h-12 w-full rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-4 py-3 text-[var(--color-text-primary)] placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]/20"
          />
        </div>

        <div>
          <label for="feedback-description" class="mb-2 block text-sm font-semibold text-[var(--color-text-primary)]">Details</label>
          <textarea
            id="feedback-description"
            v-model.trim="form.description"
            rows="6"
            maxlength="5000"
            required
            placeholder="Share what you would like us to add or improve, or describe the problem you experienced."
            class="w-full resize-y rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-4 py-3 text-[var(--color-text-primary)] placeholder:text-[var(--color-text-muted)] focus:border-[var(--color-accent)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]/20"
          ></textarea>
          <p class="mt-2 text-right text-xs text-[var(--color-text-muted)]">{{ form.description.length }}/5000</p>
        </div>

        <div class="flex flex-col gap-3 border-t border-[var(--color-border)] pt-5 sm:flex-row sm:items-center">
          <button
            type="submit"
            :disabled="submitting"
            class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[var(--color-accent)] px-6 py-3 font-semibold text-[var(--color-accent-foreground)] transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {{ submitting ? 'Sending...' : 'Send to the CandyCutz Team' }}
          </button>
          <p v-if="message" class="text-sm text-[var(--color-success)]" role="status" aria-live="polite">{{ message }}</p>
        </div>
      </form>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { customerApi } from '@/shared/api/old_customerApi';

const form = reactive({
  category: 'feature_request',
  title: '',
  description: '',
});
const submitting = ref(false);
const message = ref('');

const submit = async () => {
  message.value = '';
  submitting.value = true;

  try {
    await customerApi.submitFeedback({
      category: form.category,
      title: form.title.trim(),
      description: form.description.trim(),
    });
    form.title = '';
    form.description = '';
    form.category = 'feature_request';
    message.value = 'Thank you. Your feedback was sent privately to the CandyCutz team.';
  } catch (error) {
    console.error('Failed to submit customer feedback:', error);
    message.value = error.response?.data?.message || 'Your feedback could not be sent. Please try again.';
  } finally {
    submitting.value = false;
  }
};
</script>
