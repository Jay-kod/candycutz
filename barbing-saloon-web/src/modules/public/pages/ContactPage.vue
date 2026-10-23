<template>
  <PublicLayout>
    <PageHero
      page-key="contact"
      eyebrow="Contact"
      fallback-title="Talk to CandyCutz"
      fallback-subtitle="Use the form to ask a question, request availability, or start a booking conversation."
      title-setting-key="contact_title"
      subtitle-setting-key="contact_subtitle"
    />
    <section class="mx-auto grid max-w-7xl gap-10 px-4 md:px-6 pt-16 pb-20 text-theme-text lg:grid-cols-[0.9fr_1.1fr]">
      <div data-reveal>
        <div class="grid gap-6 sm:grid-cols-2">
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
        <div v-if="activeContactSocials.length > 0" class="mt-10 pt-10 border-t border-theme-border">
          <h3 class="text-gold font-semibold uppercase tracking-widest text-sm mb-4">Connect With Us</h3>
          <div class="flex flex-wrap gap-4">
            <a
              v-for="item in activeContactSocials"
              :key="item.key"
              :href="item.url"
              target="_blank"
              rel="noopener noreferrer"
              class="w-10 h-10 rounded-full bg-theme-surface border border-theme-border flex items-center justify-center text-theme-text hover:bg-gold hover:text-obsidian hover:border-gold transition-all duration-300"
              :aria-label="item.label"
            >
              <span class="sr-only">{{ item.label }}</span>
              <SocialIcon :name="item.key" class="w-5 h-5" />
            </a>
          </div>
        </div>
      </div>

      <form data-reveal data-reveal-delay="200" class="rounded-3xl border border-theme-border bg-theme-surface p-8 shadow-2xl backdrop-blur-md" @submit.prevent="submit">
        <div class="grid gap-6 md:grid-cols-2">
          <label class="grid gap-2">
            <span class="text-sm font-medium text-theme-text">Name</span>
            <input v-model="form.name" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors" :placeholder="formPlaceholders.name" required />
          </label>
          <label class="grid gap-2">
            <span class="text-sm font-medium text-theme-text">Email</span>
            <input v-model="form.email" type="email" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors" :placeholder="formPlaceholders.email" required />
          </label>
        </div>
        <label class="mt-6 grid gap-2">
          <span class="text-sm font-medium text-theme-text">Phone</span>
          <input v-model="form.phone" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors" :placeholder="formPlaceholders.phone" required />
        </label>
        <label class="mt-6 grid gap-2">
          <span class="text-sm font-medium text-theme-text">Message</span>
          <textarea v-model="form.message" rows="5" class="rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text outline-none focus:border-gold transition-colors resize-none" :placeholder="formPlaceholders.message" required></textarea>
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
import PageHero from '@/shared/ui/PageHero.vue';
import { publicApi } from '@/shared/api/old_publicApi';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import SocialIcon from '@/components/SocialIcon.vue';

const form = reactive({ name: '', email: '', phone: '', message: '' });
const status = ref('');
const formPlaceholders = reactive({
  name: 'John Doe',
  email: 'john@example.com',
  phone: '+234...',
  message: 'How can we help?',
});

const contactAddress = ref('123 Grooming Street\nLagos, Nigeria');
const contactHours = ref('Mon - Sat: 9:00 AM - 8:00 PM\nSun: Closed');
const contactPhone = ref('+234 800 000 0000');
const contactEmail = ref('info@candycutz.com');
const contactMapIframe = ref('');
const contactMapLink = ref('');

const socialSettings = ref({
  whatsapp: '',
  instagram: '',
  facebook: '',
  twitter: '',
  tiktok: '',
  youtube: '',
  linkedin: '',
  show_contact_whatsapp: true,
  show_contact_instagram: true,
  show_contact_facebook: true,
  show_contact_twitter: true,
  show_contact_tiktok: true,
  show_contact_youtube: true,
  show_contact_linkedin: true,
});

const formatWhatsAppUrl = (val) => {
  if (!val) return '';
  const trimmed = val.trim();
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) return trimmed;
  const digits = trimmed.replace(/\D/g, '');
  return digits ? `https://wa.me/${digits}` : '';
};

const parseBool = (val, defaultVal = true) => {
  if (val === undefined || val === null || val === '') return defaultVal;
  return val === true || val === 'true' || val === 1 || val === '1';
};

const activeContactSocials = computed(() => {
  const s = socialSettings.value;
  const list = [];
  if (s.whatsapp && s.show_contact_whatsapp) {
    list.push({ key: 'whatsapp', label: 'WhatsApp', url: formatWhatsAppUrl(s.whatsapp) });
  }
  if (s.instagram && s.show_contact_instagram) {
    list.push({ key: 'instagram', label: 'Instagram', url: s.instagram });
  }
  if (s.tiktok && s.show_contact_tiktok) {
    list.push({ key: 'tiktok', label: 'TikTok', url: s.tiktok });
  }
  if (s.facebook && s.show_contact_facebook) {
    list.push({ key: 'facebook', label: 'Facebook', url: s.facebook });
  }
  if (s.twitter && s.show_contact_twitter) {
    list.push({ key: 'twitter', label: 'X (formerly Twitter)', url: s.twitter });
  }
  if (s.youtube && s.show_contact_youtube) {
    list.push({ key: 'youtube', label: 'YouTube', url: s.youtube });
  }
  if (s.linkedin && s.show_contact_linkedin) {
    list.push({ key: 'linkedin', label: 'LinkedIn', url: s.linkedin });
  }
  return list;
});

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
    const settings = response.data?.data || {};
    if (settings) {
      if (settings.contact_address) contactAddress.value = settings.contact_address;
      if (settings.contact_form_name_placeholder) formPlaceholders.name = settings.contact_form_name_placeholder;
      if (settings.contact_form_email_placeholder) formPlaceholders.email = settings.contact_form_email_placeholder;
      if (settings.contact_form_phone_placeholder) formPlaceholders.phone = settings.contact_form_phone_placeholder;
      if (settings.contact_form_message_placeholder) formPlaceholders.message = settings.contact_form_message_placeholder;
      if (settings.contact_hours) contactHours.value = settings.contact_hours;
      if (settings.contact_phone) contactPhone.value = settings.contact_phone;
      if (settings.contact_email) contactEmail.value = settings.contact_email;
      if (settings.contact_map_iframe) contactMapIframe.value = settings.contact_map_iframe;
      if (settings.contact_map_link) contactMapLink.value = settings.contact_map_link;

      const platforms = ['whatsapp', 'instagram', 'facebook', 'twitter', 'tiktok', 'youtube', 'linkedin'];
      platforms.forEach((p) => {
        const val = settings[`social_${p}`] || settings[p] || settings.social?.[p] || '';
        socialSettings.value[p] = val ? String(val).trim() : '';

        const contactKey = `social_show_contact_${p}`;
        socialSettings.value[`show_contact_${p}`] = parseBool(
          settings[contactKey] ?? settings.social?.[contactKey],
          true
        );
      });
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