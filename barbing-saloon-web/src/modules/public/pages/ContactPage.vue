<template>
  <PublicLayout>
    <section class="mx-auto grid max-w-7xl gap-10 px-4 md:px-6 pt-32 pb-20 text-theme-text lg:grid-cols-[0.9fr_1.1fr]">
      <div data-reveal>
        <p class="text-sm uppercase tracking-[0.3em] text-gold-light">Contact</p>
        <h1 class="mt-3 font-display text-4xl sm:text-5xl text-gold md:text-6xl">{{ contactTitle }}</h1>
        <p class="mt-6 max-w-xl text-lg text-theme-muted whitespace-pre-wrap">{{ contactSubtitle }}</p>
        <div class="mt-10 grid gap-6 sm:grid-cols-2">
          <!-- Location Card -->
          <div v-if="contactAddress" class="group rounded-2xl border border-theme-border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gold/50 hover:bg-white/5">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gold/10 text-gold group-hover:bg-gold group-hover:text-obsidian transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
            </div>
            <h3 class="text-sm font-bold uppercase tracking-widest text-gold mb-2">Location</h3>
            <p class="text-sm text-theme-muted whitespace-pre-wrap leading-relaxed">{{ contactAddress }}</p>
          </div>

          <!-- Hours Card -->
          <div v-if="contactHours" class="group rounded-2xl border border-theme-border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gold/50 hover:bg-white/5">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gold/10 text-gold group-hover:bg-gold group-hover:text-obsidian transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <h3 class="text-sm font-bold uppercase tracking-widest text-gold mb-2">Hours</h3>
            <p class="text-sm text-theme-muted whitespace-pre-wrap leading-relaxed">{{ contactHours }}</p>
          </div>

          <!-- Phone Card -->
          <div v-if="contactPhone" class="group rounded-2xl border border-theme-border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gold/50 hover:bg-white/5">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gold/10 text-gold group-hover:bg-gold group-hover:text-obsidian transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.896-1.596-5.25-3.95-6.847-6.847l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" /></svg>
            </div>
            <h3 class="text-sm font-bold uppercase tracking-widest text-gold mb-2">Phone</h3>
            <p class="text-sm text-theme-muted"><a :href="'tel:' + contactPhone" class="hover:text-ivory transition-colors">{{ contactPhone }}</a></p>
          </div>

          <!-- Email Card -->
          <div v-if="contactEmail" class="group rounded-2xl border border-theme-border bg-theme-surface p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gold/50 hover:bg-white/5">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gold/10 text-gold group-hover:bg-gold group-hover:text-obsidian transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg>
            </div>
            <h3 class="text-sm font-bold uppercase tracking-widest text-gold mb-2">Email</h3>
            <p class="text-sm text-theme-muted"><a :href="'mailto:' + contactEmail" class="hover:text-ivory transition-colors break-all">{{ contactEmail }}</a></p>
          </div>
        </div>

        <!-- Social Links -->
        <div class="mt-10 pt-10 border-t border-theme-border" v-if="socialInstagram || socialFacebook || socialTwitter || socialWhatsapp">
          <h3 class="text-gold font-semibold uppercase tracking-widest text-sm mb-4">Connect With Us</h3>
          <div class="flex flex-wrap gap-4">
            <a v-if="socialWhatsapp" :href="'https://wa.me/' + socialWhatsapp.replace(/\D/g, '')" target="_blank" class="w-10 h-10 rounded-full bg-theme-surface border border-theme-border flex items-center justify-center text-theme-text hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300">
              <span class="sr-only">WhatsApp</span>
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.031 21.996c-1.63 0-3.235-.417-4.664-1.216L2.617 22l1.241-4.613a9.988 9.988 0 01-1.31-4.99c0-5.505 4.492-9.985 10.01-9.985 5.518 0 10.007 4.48 10.007 9.985 0 5.506-4.489 9.999-10.01 9.999zm-4.721-13.06c-.274-.775-.561-.79-.817-.803h-.696c-.237 0-.623.089-.95.446-.326.357-1.246 1.218-1.246 2.972 0 1.754 1.275 3.448 1.453 3.685.178.238 2.512 3.834 6.082 5.38 3.57 1.547 3.57 1.031 4.223.972.652-.06 2.106-.862 2.403-1.695.297-.833.297-1.547.208-1.696-.089-.148-.326-.238-.682-.416s-2.106-1.04-2.433-1.16c-.326-.118-.564-.178-.801.179-.237.357-.92 1.159-1.127 1.397-.208.238-.415.267-.771.089s-1.503-.553-2.863-1.77c-1.057-.946-1.771-2.116-1.979-2.473-.208-.356-.022-.55.156-.728.16-.16.356-.416.534-.624.178-.208.237-.356.356-.594.119-.237.06-.445-.03-.624-.089-.178-.801-1.93-1.098-2.644z" clip-rule="evenodd" /></svg>
            </a>
            <a v-if="socialInstagram" :href="socialInstagram" target="_blank" class="w-10 h-10 rounded-full bg-theme-surface border border-theme-border flex items-center justify-center text-theme-text hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300">
              <span class="sr-only">Instagram</span>
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" /></svg>
            </a>
            <a v-if="socialTiktok" :href="socialTiktok" target="_blank" class="w-10 h-10 rounded-full bg-theme-surface border border-theme-border flex items-center justify-center text-theme-text hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300">
              <span class="sr-only">TikTok</span>
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.12-3.44-3.37-3.4-5.74.06-2.06.96-4.04 2.51-5.45 1.55-1.41 3.65-2.11 5.76-1.95v4.02c-1.14-.09-2.31.25-3.18.98-.87.72-1.32 1.83-1.28 2.96.04 1.13.56 2.19 1.41 2.92.85.73 1.98 1.05 3.08.91 1.54-.18 2.91-1.3 3.32-2.8.2-1.15.22-2.34.22-3.52V.02z" /></svg>
            </a>
            <a v-if="socialFacebook" :href="socialFacebook" target="_blank" class="w-10 h-10 rounded-full bg-theme-surface border border-theme-border flex items-center justify-center text-theme-text hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300">
              <span class="sr-only">Facebook</span>
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>
            </a>
            <a v-if="socialTwitter" :href="socialTwitter" target="_blank" class="w-10 h-10 rounded-full bg-theme-surface border border-theme-border flex items-center justify-center text-theme-text hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300">
              <span class="sr-only">Twitter</span>
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.008 5.922H5.078z" /></svg>
            </a>
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

    <!-- Map Section -->
    <section v-if="mapEmbedSrc" class="mx-auto max-w-7xl px-4 md:px-6 pb-20 text-theme-text" data-reveal>
      <div class="text-center mb-10">
        <h3 class="text-gold font-display text-4xl">Find Us on the Map</h3>
      </div>
      <div class="rounded-3xl border border-theme-border bg-theme-surface p-4 shadow-2xl">
        <div class="overflow-hidden rounded-2xl h-[450px] w-full bg-theme-bg">
          <iframe 
            :src="mapEmbedSrc" 
            width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>
      <div class="mt-10 text-center" v-if="contactMapLink">
        <a :href="contactMapLink" target="_blank" class="inline-flex items-center gap-3 rounded-xl border border-gold px-8 py-4 font-bold text-gold transition-all hover:bg-gold hover:text-obsidian hover:shadow-[0_0_20px_rgba(212,175,55,0.4)]">
          Open in Google Maps
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
        </a>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { publicApi } from '../api/public.api';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

const form = reactive({ name: '', email: '', phone: '', message: '' });
const status = ref('');

const contactTitle = ref('Talk to CandyCutz');
const contactSubtitle = ref('Use the form to ask a question, request availability, or start a booking conversation.');
const contactAddress = ref('123 Grooming Street\nLagos, Nigeria');
const contactHours = ref('Mon - Sat: 9:00 AM - 8:00 PM\nSun: Closed');
const contactPhone = ref('+234 800 000 0000');
const contactEmail = ref('info@candycutz.com');
const socialInstagram = ref('');
const socialFacebook = ref('');
const socialTwitter = ref('');
const socialWhatsapp = ref('');
const socialTiktok = ref('');
const contactMapIframe = ref('');
const contactMapLink = ref('');

const mapEmbedSrc = computed(() => {
  const raw = (contactMapIframe.value || '').trim();
  if (!raw) return '';
  
  if (raw.startsWith('https://www.google.com/maps/embed')) {
    return raw;
  }
  
  const match = raw.match(/src\s*=\s*["']([^"']+)["']/i);
  if (match) {
    const extracted = match[1].replace(/&amp;/g, '&');
    if (extracted.startsWith('https://www.google.com/maps/embed')) {
      return extracted;
    }
  }
  
  return '';
});

const { init: initScrollReveal } = useScrollReveal();

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings) {
      if (settings.contact_title) contactTitle.value = settings.contact_title;
      if (settings.contact_subtitle) contactSubtitle.value = settings.contact_subtitle;
      if (settings.contact_address) contactAddress.value = settings.contact_address;
      if (settings.contact_hours) contactHours.value = settings.contact_hours;
      if (settings.contact_phone) contactPhone.value = settings.contact_phone;
      if (settings.contact_email) contactEmail.value = settings.contact_email;
      if (settings.social_instagram) socialInstagram.value = settings.social_instagram;
      if (settings.social_facebook) socialFacebook.value = settings.social_facebook;
      if (settings.social_twitter) socialTwitter.value = settings.social_twitter;
      if (settings.social_whatsapp) socialWhatsapp.value = settings.social_whatsapp;
      if (settings.social_tiktok) socialTiktok.value = settings.social_tiktok;
      if (settings.contact_map_iframe) contactMapIframe.value = settings.contact_map_iframe;
      if (settings.contact_map_link) contactMapLink.value = settings.contact_map_link;
    }
  } catch (error) {
    console.error('Failed to load settings:', error);
  }
};

onMounted(() => {
  fetchSettings();
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