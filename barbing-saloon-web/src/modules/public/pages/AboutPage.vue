<template>
  <PublicLayout>
    <section class="mx-auto max-w-7xl px-4 md:px-6 pt-32 pb-20 text-theme-text">
      <!-- Shop Section -->
      <div data-reveal class="grid gap-12 lg:grid-cols-2 lg:items-center">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-gold-light">{{ shopTitle }}</p>
          <h1 class="mt-3 font-display text-4xl sm:text-5xl text-gold md:text-6xl">{{ shopSubtitle }}</h1>
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
        
        <!-- Single Featured Barber Skeleton -->
        <div v-if="isLoading" class="mt-16 mx-auto max-w-5xl overflow-hidden rounded-3xl border border-theme-border bg-theme-surface flex flex-col md:flex-row">
          <div class="md:w-1/2 aspect-[4/5] md:aspect-auto skeleton-shimmer"></div>
          <div class="p-8 md:p-12 md:w-1/2 flex flex-col justify-center gap-6">
            <div class="h-10 w-2/3 rounded-lg skeleton-shimmer"></div>
            <div class="space-y-3 mt-4">
              <div class="h-4 w-full rounded skeleton-shimmer"></div>
              <div class="h-4 w-full rounded skeleton-shimmer"></div>
              <div class="h-4 w-3/4 rounded skeleton-shimmer"></div>
            </div>
            <div class="flex gap-3 mt-4">
              <div class="h-8 w-24 rounded-full skeleton-shimmer"></div>
              <div class="h-8 w-24 rounded-full skeleton-shimmer"></div>
            </div>
            <div class="h-12 w-48 rounded-xl skeleton-shimmer mt-8"></div>
          </div>
        </div>
        
        <!-- Single Featured Barber Real -->
        <div v-else class="mt-16 mx-auto max-w-5xl">
          <article v-for="(barber, index) in barbers.slice(0, 1)" :key="barber.id" data-reveal class="group overflow-hidden rounded-3xl border border-theme-border bg-theme-surface flex flex-col md:flex-row shadow-xl hover:shadow-2xl hover:shadow-gold/5 transition-all duration-500">
            <!-- Barber Image -->
            <div class="md:w-1/2 relative overflow-hidden bg-theme-bg aspect-[4/5] md:aspect-auto">
              <img v-if="barber.avatar" :src="getImageUrl(barber.avatar)" :alt="barber.name" class="absolute inset-0 h-full w-full object-cover transition-transform duration-1000 group-hover:scale-105" />
              <div v-else class="absolute inset-0 flex items-center justify-center text-theme-muted">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-20 h-20">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-obsidian via-transparent to-transparent opacity-60"></div>
            </div>
            
            <!-- Barber Details -->
            <div class="p-8 md:p-12 md:w-1/2 flex flex-col justify-center">
              <span class="text-xs uppercase tracking-[0.2em] font-semibold text-gold mb-2">Master Barber</span>
              <h3 class="font-display text-4xl text-obsidian dark:text-theme-text">{{ barber.name }}</h3>
              
              <div class="mt-4 flex items-center gap-4 text-sm font-medium">
                <div class="flex items-center gap-1 text-gold">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" /></svg>
                  <span>{{ barber.rating }}</span>
                </div>
                <span class="text-theme-border text-xs">|</span>
                <span class="text-theme-muted">{{ barber.years_experience }} Years Exp</span>
                <span class="text-theme-border text-xs">|</span>
                <span class="flex items-center gap-1.5" :class="barber.status === 'active' ? 'text-emerald-400' : 'text-amber-400'">
                  <div class="h-1.5 w-1.5 rounded-full" :class="barber.status === 'active' ? 'bg-emerald-400' : 'bg-amber-400'"></div>
                  {{ barber.status === 'active' ? 'Available' : 'Not Active' }}
                </span>
              </div>
              
              <p class="mt-6 text-base leading-relaxed text-theme-text line-clamp-4">{{ barber.bio }}</p>
              
              <div class="mt-6 flex flex-wrap gap-2">
                <span v-for="spec in (barber.specialties || []).slice(0, 3)" :key="spec" class="rounded-full border border-theme-border bg-theme-bg px-3 py-1 text-xs font-medium text-theme-text">{{ spec }}</span>
              </div>
              
              <div class="mt-10">
                <RouterLink :to="`/customer/dashboard/barber/${barber.id}`" class="inline-flex items-center gap-2 rounded-xl bg-gold px-8 py-4 text-sm font-bold text-obsidian shadow-lg hover:bg-gold-light transition-all duration-300 hover:scale-[1.02]">
                  View Full Profile
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </RouterLink>
              </div>
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
import { publicApi } from '../api/public.api';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

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

const getImageUrl = (path) => {
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
