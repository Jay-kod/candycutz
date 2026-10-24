<template>
  <PublicLayout>
    <PageHero
      page-key="about"
      eyebrow="Our Story"
      fallback-title="About CandyCutz"
      fallback-subtitle="Our story, our craft, and the standard behind every cut."
    />
    <section class="mx-auto max-w-7xl px-4 md:px-6 pt-16 pb-20 text-theme-text">
      <!-- Shop Section -->
      <div data-reveal class="grid gap-12 lg:grid-cols-2 lg:items-center">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-gold-light">{{ shopTitle }}</p>
          <h2 class="mt-3 font-display text-4xl sm:text-5xl text-gold md:text-6xl">{{ shopSubtitle }}</h2>
          <p class="mt-6 text-lg text-theme-muted leading-relaxed whitespace-pre-wrap">{{ shopText1 }}</p>
          <p class="mt-4 text-lg text-theme-muted leading-relaxed whitespace-pre-wrap">{{ shopText2 }}</p>
        </div>
        <div class="relative overflow-hidden rounded-3xl aspect-[4/3] bg-theme-surface border border-theme-border">
          <img :src="shopImage || 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80'" alt="CandyCutz Shop Interior" class="w-full h-full object-cover" />
        </div>
      </div>

      <!-- Barbers Section -->
      <div data-reveal class="mt-32">
        <h2 class="font-display text-3xl sm:text-4xl text-gold text-center">{{ teamTitle }}</h2>
        <p class="mt-4 text-center text-theme-muted max-w-2xl mx-auto whitespace-pre-wrap">{{ teamSubtitle }}</p>
        
        <div v-if="isLoading" class="mt-12 mx-auto grid max-w-6xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="index in 3" :key="index" class="overflow-hidden rounded-2xl border border-theme-border bg-theme-surface">
            <div class="aspect-[0.88] skeleton-shimmer"></div>
            <div class="space-y-4 p-5">
              <div class="mx-auto h-3 w-24 rounded skeleton-shimmer"></div>
              <div class="mx-auto h-8 w-3/4 rounded skeleton-shimmer"></div>
              <div class="h-11 w-full rounded-xl skeleton-shimmer"></div>
            </div>
          </div>
        </div>

        <p v-else-if="barbers.length === 0" class="mt-12 text-center text-theme-muted">
          Barber profiles will be available here soon.
        </p>

        <div v-else class="mt-12 mx-auto grid max-w-6xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <article
            v-for="barber in barbers"
            :key="barber.id"
            data-reveal
            class="group overflow-hidden rounded-2xl border border-gold/35 bg-theme-surface shadow-xl transition duration-300 hover:-translate-y-1 hover:border-gold/70 hover:shadow-2xl hover:shadow-gold/10"
          >
            <div class="relative aspect-[0.88] overflow-hidden bg-theme-bg">
              <img
                v-if="barber.avatar || barber.avatar_url"
                :src="getImageUrl(barber.avatar || barber.avatar_url)"
                :alt="barber.name"
                class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
              />
              <div v-else class="absolute inset-0 flex items-center justify-center text-theme-muted">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor" class="h-24 w-24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
              </div>
              <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-black/80"></div>

              <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-3 p-4">
                <span class="rounded-md border border-gold/50 bg-obsidian/75 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.2em] text-gold-light">
                  CandyCutz · Barber
                </span>
                <span
                  class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider backdrop-blur"
                  :class="barber.status === 'active' ? 'border-emerald-400/40 bg-emerald-950/75 text-emerald-200' : 'border-amber-400/40 bg-amber-950/75 text-amber-200'"
                >
                  <span class="h-1.5 w-1.5 rounded-full" :class="barber.status === 'active' ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                  {{ barber.status === 'active' ? 'Available' : 'Unavailable' }}
                </span>
              </div>

              <div class="absolute inset-x-3 bottom-3 rounded-lg border border-gold/35 bg-obsidian/90 px-3 sm:inset-x-4 sm:bottom-4 sm:px-4">
                <div class="grid grid-cols-2">
                  <div class="barber-card-stat border-b border-gold/25">
                    <span>Experience</span>
                    <strong>{{ barber.experience_years || barber.years_experience || 5 }} years</strong>
                  </div>
                  <div class="barber-card-stat border-b border-l border-gold/25">
                    <span>Rating</span>
                    <strong class="inline-flex items-center gap-1 text-gold-light">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true"><path d="M12 2.75l2.86 5.8 6.4.93-4.63 4.51 1.09 6.37L12 17.35l-5.72 3.01 1.09-6.37-4.63-4.51 6.4-.93L12 2.75z" /></svg>
                      {{ barber.rating || '5.0' }}
                    </strong>
                  </div>
                  <div class="barber-card-stat col-span-2">
                    <span>Specialties</span>
                    <strong class="truncate text-right">{{ (barber.specialties || []).slice(0, 3).join(' · ') || '—' }}</strong>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex flex-col items-center px-5 pb-5 pt-4 text-center">
              <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-gold">Master Barber</p>
              <h3 class="mt-1 font-display text-3xl font-bold leading-tight text-theme-text transition-colors group-hover:text-gold">
                {{ barber.name }}
              </h3>
              <RouterLink
                :to="`/customer/dashboard/barber/${barber.id}`"
                class="mt-4 inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg border border-gold/50 bg-gold/10 px-4 py-1.5 text-sm font-bold text-gold transition-colors hover:bg-gold hover:text-obsidian focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold"
              >
                View Profile & Book
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
              </RouterLink>
            </div>
          </article>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import PageHero from '@/shared/ui/PageHero.vue';
import { publicApi } from '@/shared/api/old_publicApi';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import { getStorageUrl as getImageUrl } from '@/core/utils/url';

const barbers = ref([]);
const isLoading = ref(true);

const shopTitle = ref('About Us');
const shopSubtitle = ref('A Sharper Standard');
const shopText1 = ref("Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut. Our shop combines traditional barbering techniques with modern trends in a relaxing, luxurious environment.");
const shopText2 = ref("Whether you're here for a quick shape-up or a complete restyle, our expert barbers are dedicated to making you look and feel your best. Experience the art of grooming at its finest.");
const shopImage = ref(null);

const teamTitle = ref('Meet the team');
const teamSubtitle = ref('Our barbers are masters of their craft, bringing years of experience and a passion for perfection to every cut.');

const { init: initScrollReveal } = useScrollReveal();

const fetchSettings = async () => {
  try {
    const response = await publicApi.settings();
    const settings = response.data.data;
    if (settings) {
      if (settings.about_shop_title) shopTitle.value = settings.about_shop_title;
      if (settings.about_shop_subtitle) shopSubtitle.value = settings.about_shop_subtitle;
      if (settings.about_shop_text_1) shopText1.value = settings.about_shop_text_1;
      if (settings.about_shop_text_2) shopText2.value = settings.about_shop_text_2;
      if (settings.about_shop_image) shopImage.value = getImageUrl(settings.about_shop_image);
      
      if (settings.about_team_title) teamTitle.value = settings.about_team_title;
      if (settings.about_team_subtitle) teamSubtitle.value = settings.about_team_subtitle;
    }
  } catch (error) {
    console.error('Failed to load settings:', error);
  }
};

onMounted(async () => {
  try {
    fetchSettings();
    const response = await publicApi.barbers();
    barbers.value = response.data.data;
    isLoading.value = false;
    setTimeout(() => {
      initScrollReveal();
    }, 100);
  } catch (error) {
    console.error('Failed to load barbers:', error);
    isLoading.value = false;
  }
});
</script>

<style scoped>
.barber-card-stat {
  display: flex;
  min-width: 0;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.65rem 0.4rem;
  color: #f5f5f5;
  font-size: 0.7rem;
}

.barber-card-stat span {
  color: rgba(245, 245, 245, 0.65);
  font-size: 0.58rem;
  font-weight: 700;
  letter-spacing: 0.09em;
  text-transform: uppercase;
}

.barber-card-stat strong {
  font-size: 0.7rem;
  font-weight: 700;
}

@media (min-width: 640px) {
  .barber-card-stat {
    padding-inline: 0.65rem;
  }
}
</style>
