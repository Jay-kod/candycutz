<template>
  <CustomerLayout>
    <section class="mx-auto max-w-4xl space-y-8 animate-fade-in pb-12">
      <!-- Luxury Header Banner -->
      <header class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-[#12161C] p-8 shadow-2xl">
        <div class="absolute -right-16 -top-16 size-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="relative z-10">
          <div class="flex items-center gap-2 mb-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[var(--color-accent)]/15 text-[var(--color-accent)] border border-[var(--color-accent)]/20">
              <ChatBubbleLeftRightIcon class="h-3.5 w-3.5" />
            </span>
            <p class="text-xs font-bold uppercase tracking-[0.3em] text-[var(--color-accent)]">Customer Feedback & Suggestions</p>
          </div>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-slate-900 dark:text-white">
            Help Shape <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-200">CandyCutz</span>
          </h1>
          <p class="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--color-text-secondary)]">
            Tell us what features you would love to see, report issues or bugs, or suggest improvements. Your feedback is sent directly to management.
          </p>
        </div>
      </header>

      <!-- Feedback Form Card -->
      <form class="space-y-6 rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-xl" @submit.prevent="submit">
        <div class="space-y-2">
          <label for="feedback-category" class="block text-xs font-bold uppercase tracking-wider text-[var(--color-text-secondary)]">What is this about?</label>
          <div class="relative">
            <select
              id="feedback-category"
              v-model="form.category"
              required
              class="min-h-12 w-full appearance-none rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-3 text-sm text-slate-900 dark:text-white focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 focus:outline-none transition-all"
            >
              <option value="feature_request" class="bg-white dark:bg-[#12161C] text-slate-900 dark:text-white">A new feature I would like to see</option>
              <option value="maintenance" class="bg-white dark:bg-[#12161C] text-slate-900 dark:text-white">A problem or bug I experienced</option>
              <option value="improvement" class="bg-white dark:bg-[#12161C] text-slate-900 dark:text-white">General service improvement suggestion</option>
            </select>
          </div>
        </div>

        <div class="space-y-2">
          <label for="feedback-title" class="block text-xs font-bold uppercase tracking-wider text-[var(--color-text-secondary)]">Subject / Title</label>
          <input
            id="feedback-title"
            v-model.trim="form.title"
            type="text"
            maxlength="150"
            required
            placeholder="Summarize your thought or suggestion..."
            class="min-h-12 w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 focus:outline-none transition-all"
          />
        </div>

        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <label for="feedback-description" class="block text-xs font-bold uppercase tracking-wider text-[var(--color-text-secondary)]">Detailed Message</label>
            <span class="text-[10px] tabular-nums" :class="form.description.length > 4500 ? 'text-amber-500 dark:text-amber-400' : 'text-slate-400 dark:text-white/40'">{{ form.description.length }}/5000</span>
          </div>
          <textarea
            id="feedback-description"
            v-model.trim="form.description"
            rows="6"
            maxlength="5000"
            required
            placeholder="Share the details of what you'd like us to improve or fix..."
            class="w-full resize-y rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-white/[0.04] px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-white/30 focus:border-[var(--color-accent)] focus:bg-white dark:focus:bg-black/40 focus:outline-none transition-all leading-relaxed"
          ></textarea>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-100 dark:border-white/[0.06] pt-6 sm:flex-row sm:items-center justify-between">
          <button
            type="submit"
            :disabled="submitting"
            class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[var(--color-accent)] px-8 py-3 text-xs font-bold text-white shadow-lg shadow-[var(--color-accent)]/20 transition hover:brightness-110 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
          >
            <PaperAirplaneIcon v-if="!submitting" class="h-4 w-4" />
            <span v-if="submitting">Sending...</span>
            <span v-else>Send Feedback</span>
          </button>
          <p v-if="message" class="text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-4 py-2.5 rounded-xl border border-emerald-500/20" role="status" aria-live="polite">
            {{ message }}
          </p>
        </div>
      </form>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { reactive, ref } from 'vue';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { client } from '@/shared/api/client';
import { useToast } from '../../../core/composables/useToast';
import { 
  ChatBubbleLeftRightIcon,
  PaperAirplaneIcon
} from '@heroicons/vue/24/outline';

const toast = useToast();
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
    const res = await client.post('/customer/feedback', {
      category: form.category,
      title: form.title.trim(),
      description: form.description.trim(),
    });
    form.title = '';
    form.description = '';
    form.category = 'feature_request';
    message.value = res.data?.message || 'Thank you! Your feedback was sent to the CandyCutz management team.';
    toast.success('Feedback submitted successfully!');
  } catch (error) {
    console.error('Failed to submit customer feedback:', error);
    message.value = error.response?.data?.message || 'Your feedback could not be sent. Please try again.';
    toast.error('Failed to send feedback');
  } finally {
    submitting.value = false;
  }
};
</script>
