<template>
  <PublicLayout>
    <section class="mx-auto grid max-w-7xl gap-10 px-4 md:px-6 pt-32 pb-20 text-theme-text lg:grid-cols-[0.9fr_1.1fr]">
      <div data-reveal>
        <p class="text-sm uppercase tracking-[0.3em] text-gold-light">Contact</p>
        <h1 class="mt-3 font-display text-4xl sm:text-5xl text-gold md:text-6xl">Talk to CandyCutz</h1>
        <p class="mt-6 max-w-xl text-lg text-theme-muted">Use the form to ask a question, request availability, or start a booking conversation.</p>
        <div class="mt-10 space-y-6">
          <div>
            <h3 class="text-gold font-semibold uppercase tracking-widest text-sm">Location</h3>
            <p class="mt-2 text-theme-muted">123 Grooming Street<br/>Lagos, Nigeria</p>
          </div>
          <div>
            <h3 class="text-gold font-semibold uppercase tracking-widest text-sm">Hours</h3>
            <p class="mt-2 text-theme-muted">Mon - Sat: 9:00 AM - 8:00 PM<br/>Sun: Closed</p>
          </div>
        </div>
      </div>

      <form data-reveal data-reveal-delay="200" class="rounded-3xl border border-theme-border bg-theme-surface p-8 shadow-2xl backdrop-blur-md" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">
          <label class="grid gap-2">
            <span class="text-sm font-medium text-theme-text">Name</span>
            <input v-model="form.name" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors" placeholder="John Doe" required />
          </label>
          <label class="grid gap-2">
            <span class="text-sm font-medium text-theme-text">Email</span>
            <input v-model="form.email" type="email" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors" placeholder="john@example.com" required />
          </label>
        </div>
        <label class="mt-6 grid gap-2">
          <span class="text-sm font-medium text-theme-text">Phone</span>
          <input v-model="form.phone" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors" placeholder="+234..." required />
        </label>
        <label class="mt-6 grid gap-2">
          <span class="text-sm font-medium text-theme-text">Message</span>
          <textarea v-model="form.message" rows="5" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors resize-none" placeholder="How can we help?" required></textarea>
        </label>
        <button type="submit" class="mt-8 w-full rounded-xl bg-gold px-6 py-4 font-semibold text-obsidian transition hover:bg-gold-light hover:-translate-y-0.5 transform shadow-lg">Send message</button>
        
        <div v-if="status" class="mt-6 p-4 rounded-xl bg-theme-bg text-center text-sm font-medium border border-theme-border">
          <span class="text-gold-light">{{ status }}</span>
        </div>
      </form>
    </section>
  </PublicLayout>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { publicApi } from '../api/public.api';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

const form = reactive({ name: '', email: '', phone: '', message: '' });
const status = ref('');

const { init: initScrollReveal } = useScrollReveal();

onMounted(() => {
  setTimeout(() => {
    initScrollReveal();
  }, 100);
});

async function submit() {
  try {
    const response = await publicApi.contact(form);
    status.value = response.data.message || 'Message sent successfully. We will get back to you soon.';
    form.name = ''; form.email = ''; form.phone = ''; form.message = '';
  } catch (error) {
    status.value = 'Failed to send message. Please try again.';
  }
}
</script>