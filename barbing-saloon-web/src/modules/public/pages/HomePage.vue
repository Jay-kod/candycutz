<template>
  <PublicLayout>
    <section class="relative px-4 md:px-6 min-h-[100dvh] flex flex-col text-theme-text pt-24 pb-0">
      <!-- Premium Background Gradient -->
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(201,168,76,0.15),transparent_50%),radial-gradient(ellipse_at_bottom_left,rgba(255,255,255,0.05),transparent_50%)] -z-10"></div>
      <div v-if="heroImage" class="absolute inset-0 -z-20 bg-cover bg-center bg-no-repeat opacity-20" :style="{ backgroundImage: `url(${heroImage})` }"></div>
      
      <!-- Main Hero Content -->
      <div class="relative w-full mx-auto max-w-5xl text-center flex-1 flex flex-col justify-center pb-20">
        <div data-reveal>
          <p class="inline-block text-sm md:text-base uppercase tracking-[0.4em] text-gold-light font-medium mr-[-0.4em]">Welcome to CandyCutz</p>
          <h1 class="mt-6 font-display text-5xl sm:text-6xl leading-tight text-gold md:text-7xl lg:text-8xl">{{ heroTitle }}</h1>
          <p class="mt-8 text-lg md:text-xl text-theme-muted font-light leading-relaxed mx-auto max-w-2xl">{{ heroSubtitle }}</p>
          <div class="mt-12 flex flex-wrap justify-center gap-4">
            <RouterLink to="/customer/login" class="rounded-full bg-gold px-10 py-4 font-semibold text-obsidian shadow-lg shadow-gold/20 transition hover:bg-gold-light hover:scale-105">Login & Book Now</RouterLink>
            <RouterLink to="/about" class="rounded-full border border-gold px-10 py-4 font-semibold text-gold transition hover:bg-gold/10">About Us</RouterLink>
          </div>
        </div>
      </div>

      <!-- Stats Banner (Anchored to bottom of hero) -->
      <div class="w-full border-t border-white/5 bg-obsidian/30 backdrop-blur-md py-8">
        <div class="mx-auto max-w-7xl px-4 md:px-6">
          <div class="grid grid-cols-2 gap-8 md:grid-cols-4 divide-x divide-white/10 text-center" data-reveal data-reveal-delay="200">
            <div class="px-4 hover:-translate-y-1 transition-transform">
              <p class="font-display text-4xl text-gold md:text-5xl">10+</p>
              <p class="mt-2 text-[10px] sm:text-xs uppercase tracking-widest text-theme-muted font-semibold">Years Experience</p>
            </div>
            <div class="px-4 hover:-translate-y-1 transition-transform">
              <p class="font-display text-4xl text-gold md:text-5xl">5+</p>
              <p class="mt-2 text-[10px] sm:text-xs uppercase tracking-widest text-theme-muted font-semibold">Expert Barbers</p>
            </div>
            <div class="px-4 hover:-translate-y-1 transition-transform">
              <p class="font-display text-4xl text-gold md:text-5xl">20+</p>
              <p class="mt-2 text-[10px] sm:text-xs uppercase tracking-widest text-theme-muted font-semibold">Premium Services</p>
            </div>
            <div class="px-4 hover:-translate-y-1 transition-transform">
              <p class="font-display text-4xl text-gold md:text-5xl">5k+</p>
              <p class="mt-2 text-[10px] sm:text-xs uppercase tracking-widest text-theme-muted font-semibold">Happy Clients</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- About Us Teaser -->
    <section class="bg-theme-surface text-theme-text py-16 md:py-24">
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <div data-reveal class="grid gap-12 lg:grid-cols-2 lg:items-center">
          <div>
            <p class="text-sm uppercase tracking-[0.3em] text-gold-light">Our Philosophy</p>
            <h2 class="mt-3 font-display text-4xl text-gold md:text-5xl">A Sharper Standard</h2>
            <p class="mt-6 text-lg text-theme-muted leading-relaxed">
              Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut. Our shop combines traditional barbering techniques with modern trends in a relaxing, luxurious environment.
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
            <img src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="CandyCutz Shop Interior" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" />
          </div>
        </div>
      </div>
    </section>

    <!-- Services Teaser -->
    <section class="mx-auto max-w-7xl px-4 md:px-6 py-16 md:py-24 text-center">
      <div data-reveal class="max-w-3xl mx-auto">
        <p class="text-sm uppercase tracking-[0.3em] text-gold-light font-medium">The CandyCutz Portal</p>
        <h2 class="mt-4 font-display text-4xl text-gold md:text-5xl">Unlock the Full Experience</h2>
        <p class="mt-6 text-lg text-theme-muted leading-relaxed">
          Create an account to browse our extensive list of services, check barber availability in real-time, view our exclusive gallery, and manage your appointments seamlessly from your own personalized dashboard.
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
const heroImage = ref(null);

const getStorageUrl = (path) => {
  if (!path) return '';
  return `${import.meta.env.VITE_API_BASE_URL.replace('/api', '')}/storage/${path}`;
};

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings && settings.hero) {
      if (settings.hero.hero_title) heroTitle.value = settings.hero.hero_title;
      if (settings.hero.hero_subtitle) heroSubtitle.value = settings.hero.hero_subtitle;
      if (settings.hero.hero_image) heroImage.value = getStorageUrl(settings.hero.hero_image);
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