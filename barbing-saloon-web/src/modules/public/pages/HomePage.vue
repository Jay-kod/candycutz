<template>
  <PublicLayout>
    <section class="relative px-4 md:px-6 min-h-[100dvh] flex flex-col text-theme-text pt-24 pb-0 overflow-hidden">
      <!-- Hero Background Image -->
      <div v-if="heroImage" class="absolute inset-0 z-0 bg-cover bg-center bg-no-repeat opacity-40" :style="{ backgroundImage: `url(${heroImage})` }"></div>
      <!-- Dark overlay for text readability -->
      <div class="absolute inset-0 z-10 bg-gradient-to-b from-obsidian/80 via-obsidian/50 to-obsidian pointer-events-none"></div>
      <!-- Premium Background Gradient -->
      <div class="absolute inset-0 z-10 bg-[radial-gradient(ellipse_at_top_right,rgba(201,168,76,0.15),transparent_50%),radial-gradient(ellipse_at_bottom_left,rgba(255,255,255,0.05),transparent_50%)] pointer-events-none"></div>
      
      <!-- Main Hero Content -->
      <div class="relative z-20 w-full mx-auto max-w-5xl text-center flex-1 flex flex-col justify-center pb-20">
        <div data-reveal>
          <p class="inline-block text-sm md:text-base uppercase tracking-[0.4em] text-gold-light font-medium mr-[-0.4em]">Welcome to CandyCutz</p>
          <h1 class="mt-6 font-display text-5xl sm:text-6xl leading-tight text-gold md:text-7xl lg:text-8xl">{{ heroTitle }}</h1>
          <p class="mt-8 text-lg md:text-xl text-theme-muted font-light leading-relaxed mx-auto max-w-2xl">{{ heroSubtitle }}</p>
          <div class="mt-12 flex flex-wrap justify-center gap-4">
            <RouterLink to="/customer/login" class="rounded-full bg-gold px-10 py-4 font-semibold text-obsidian shadow-lg shadow-gold/20 transition hover:bg-gold-light hover:scale-105">{{ heroBtn1Text }}</RouterLink>
            <RouterLink to="/about" class="rounded-full border border-gold px-10 py-4 font-semibold text-gold transition hover:bg-gold/10">{{ heroBtn2Text }}</RouterLink>
          </div>
        </div>
      </div>
    </section>

    <!-- Statistics Section -->
    <section class="bg-obsidian border-y border-white/5 py-16 relative z-20 shadow-2xl">
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 text-center" data-reveal>
          <div class="flex flex-col items-center justify-center text-center rounded-2xl border border-white/10 bg-white/5 p-8 shadow-xl hover:-translate-y-2 hover:border-gold/50 hover:bg-white/10 transition-all duration-300">
            <p class="font-display text-5xl text-gold md:text-6xl drop-shadow-md">{{ statsYears }}</p>
            <p class="mt-4 text-xs md:text-sm uppercase tracking-widest text-ivory/60 font-bold">Years Experience</p>
          </div>
          <div class="flex flex-col items-center justify-center text-center rounded-2xl border border-white/10 bg-white/5 p-8 shadow-xl hover:-translate-y-2 hover:border-gold/50 hover:bg-white/10 transition-all duration-300">
            <p class="font-display text-5xl text-gold md:text-6xl drop-shadow-md">{{ statsServices }}</p>
            <p class="mt-4 text-xs md:text-sm uppercase tracking-widest text-ivory/60 font-bold">Premium Services</p>
          </div>
          <div class="flex flex-col items-center justify-center text-center rounded-2xl border border-white/10 bg-white/5 p-8 shadow-xl hover:-translate-y-2 hover:border-gold/50 hover:bg-white/10 transition-all duration-300">
            <p class="font-display text-5xl text-gold md:text-6xl drop-shadow-md">{{ statsClients }}</p>
            <p class="mt-4 text-xs md:text-sm uppercase tracking-widest text-ivory/60 font-bold">Happy Clients</p>
          </div>
        </div>
      </div>
    </section>

    <!-- About Us Teaser -->
    <section class="bg-theme-surface text-theme-text py-16 md:py-24">
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <div data-reveal class="grid gap-12 lg:grid-cols-2 lg:items-center">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-gold-light">{{ aboutTitle }}</p>
            <h2 class="mt-3 font-display text-4xl text-gold md:text-5xl">{{ aboutSubtitle }}</h2>
            <p class="mt-6 text-lg text-theme-muted leading-relaxed whitespace-pre-wrap">
              {{ aboutText }}
            </p>
            <div class="mt-8">
              <RouterLink to="/about" class="inline-flex items-center gap-2 rounded-xl border border-gold/30 px-8 py-4 font-semibold text-gold transition-all hover:border-gold hover:bg-gold/10 hover:-translate-y-0.5">
                Learn More About Us
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
              </RouterLink>
            </div>
          </div>
          <div class="relative overflow-hidden rounded-3xl aspect-[4/3] border border-theme-border shadow-lg">
            <img :src="aboutImage || 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'" alt="CandyCutz Shop Interior" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" />
          </div>
        </div>
      </div>
    </section>

    <!-- Services Teaser -->
    <section class="mx-auto max-w-7xl px-4 md:px-6 py-16 md:py-24 text-center">
      <div data-reveal class="max-w-3xl mx-auto">
        <p class="text-sm uppercase tracking-[0.3em] text-gold-light font-medium">{{ portalTitle }}</p>
        <h2 class="mt-4 font-display text-4xl text-gold md:text-5xl">{{ portalSubtitle }}</h2>
        <p class="mt-6 text-lg text-theme-muted leading-relaxed whitespace-pre-wrap">
          {{ portalText }}
        </p>
        <div class="mt-10">
          <RouterLink to="/customer/login" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-gold to-gold-dark px-10 py-5 font-bold text-obsidian shadow-[0_0_20px_rgba(212,175,55,0.4)] transition-all hover:scale-105">
            Log in to Customer Portal
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
          </RouterLink>
        </div>
      </div>
    </section>

  </PublicLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import { publicApi } from '../api/public.api';

const { init: initScrollReveal } = useScrollReveal();

const heroTitle = ref('Premium grooming with a sharper standard.');
const heroSubtitle = ref('Experience the CandyCutz difference. Log in to explore our full menu of premium services, meet our expert barbers, and book your next appointment seamlessly.');
const heroBtn1Text = ref('Login & Book Now');
const heroBtn2Text = ref('About Us');
const heroImage = ref(null);

const statsYears = ref('10+');
const statsBarbers = ref('5+');
const statsServices = ref('20+');
const statsClients = ref('5k+');

const aboutTitle = ref('Our Philosophy');
const aboutSubtitle = ref('A Sharper Standard');
const aboutText = ref("Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut. Our shop combines traditional barbering techniques with modern trends in a relaxing, luxurious environment.");
const aboutImage = ref(null);

const portalTitle = ref('The CandyCutz Portal');
const portalSubtitle = ref('Unlock the Full Experience');
const portalText = ref('Create an account to browse our extensive list of services, check barber availability in real-time, view our exclusive gallery, and manage your appointments seamlessly from your own personalized dashboard.');

const getStorageUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  if (path.startsWith('data:')) return path;
  
  const baseUrl = import.meta.env.VITE_API_BASE_URL.replace(/\/api$/, '');
  
  if (path.startsWith('/uploads/') || path.startsWith('/storage/')) {
    return `${baseUrl}${path}`;
  }
  if (path.startsWith('uploads/') || path.startsWith('storage/')) {
    return `${baseUrl}/${path}`;
  }
  
  return path.startsWith('/') ? `${baseUrl}/storage${path}` : `${baseUrl}/storage/${path}`;
};

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings) {
      if (settings.hero_title) heroTitle.value = settings.hero_title;
      if (settings.hero_subtitle) heroSubtitle.value = settings.hero_subtitle;
      if (settings.hero_btn1_text) heroBtn1Text.value = settings.hero_btn1_text;
      if (settings.hero_btn2_text) heroBtn2Text.value = settings.hero_btn2_text;
      if (settings.hero_image) heroImage.value = getStorageUrl(settings.hero_image);
      
      if (settings.stats_years) statsYears.value = settings.stats_years;
      if (settings.stats_barbers) statsBarbers.value = settings.stats_barbers;
      if (settings.stats_services) statsServices.value = settings.stats_services;
      if (settings.stats_clients) statsClients.value = settings.stats_clients;
      
      if (settings.about_teaser_title) aboutTitle.value = settings.about_teaser_title;
      if (settings.about_teaser_subtitle) aboutSubtitle.value = settings.about_teaser_subtitle;
      if (settings.about_teaser_text) aboutText.value = settings.about_teaser_text;
      if (settings.about_teaser_image) aboutImage.value = getStorageUrl(settings.about_teaser_image);
      
      if (settings.portal_teaser_title) portalTitle.value = settings.portal_teaser_title;
      if (settings.portal_teaser_subtitle) portalSubtitle.value = settings.portal_teaser_subtitle;
      if (settings.portal_teaser_text) portalText.value = settings.portal_teaser_text;
    }
  } catch (error) {
    console.error('Failed to load public settings:', error);
  }
};

onMounted(() => {
  fetchSettings();
  setTimeout(() => {
    initScrollReveal();
  }, 100);
});
</script>