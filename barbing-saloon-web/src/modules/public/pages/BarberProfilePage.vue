<template>
  <PublicLayout>
    <main class="min-h-screen bg-theme-bg pb-32">
      
      <!-- Skeleton Loader -->
      <div v-if="isLoading" class="mx-auto max-w-7xl px-6 pt-32">
        <!-- Skeleton content remains same to keep simple -->
        <div class="h-96 w-full rounded-3xl skeleton-shimmer mb-12"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="mx-auto max-w-2xl px-6 text-center py-40">
        <h1 class="font-display text-5xl text-gold">Barber not found</h1>
        <p class="mt-6 text-theme-muted text-lg">The profile you're looking for doesn't exist or is currently unavailable.</p>
        <RouterLink to="/about" class="mt-10 inline-block rounded-full bg-gold px-8 py-4 text-obsidian font-bold tracking-wide hover:bg-gold-light transition-all">Return to Team</RouterLink>
      </div>

      <!-- Ultra Premium Content -->
      <div v-else>
        <!-- Hero Section with Parallax Feel -->
        <section class="relative h-[60vh] min-h-[500px] w-full overflow-hidden">
          <div class="absolute inset-0">
            <img v-if="barber.avatar" :src="barber.avatar" :alt="barber.name" class="h-full w-full object-cover scale-105" />
            <div class="absolute inset-0 bg-gradient-to-t from-obsidian via-obsidian/70 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-obsidian/90 via-obsidian/50 to-transparent"></div>
          </div>
          
          <div class="absolute inset-0 flex items-end">
            <div class="mx-auto w-full max-w-7xl px-6 pb-20">
              <div data-reveal class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-gold-light mb-4">Master Barber</p>
                <h1 class="font-display text-6xl md:text-8xl font-bold text-ivory drop-shadow-2xl">{{ barber.name }}</h1>
                
                <div class="mt-8 flex flex-wrap items-center gap-8">
                  <!-- Stat: Experience -->
                  <div class="flex items-center gap-3 bg-white/5 backdrop-blur-md px-6 py-3 rounded-2xl border border-white/10">
                    <span class="font-display text-3xl text-gold">{{ barber.years_experience }}</span>
                    <span class="text-xs uppercase tracking-widest text-ivory/80 leading-tight">Years<br>Exp</span>
                  </div>
                  <!-- Stat: Rating -->
                  <div class="flex items-center gap-3 bg-white/5 backdrop-blur-md px-6 py-3 rounded-2xl border border-white/10">
                    <span class="font-display text-3xl text-gold">{{ barber.rating }}</span>
                    <div class="flex flex-col">
                      <div class="flex text-gold">
                        <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3 h-3"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" /></svg>
                      </div>
                      <span class="text-xs uppercase tracking-widest text-ivory/80 mt-1">Rating</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- Detailed Content Grid -->
        <section class="mx-auto max-w-7xl px-6 py-20">
          <div class="grid gap-16 lg:grid-cols-12 lg:items-start">
            
            <!-- Left: Bio & Specialties -->
            <div class="lg:col-span-7 space-y-16">
              <div data-reveal>
                <h2 class="font-display text-4xl text-gold mb-6">The Artist</h2>
                <p class="text-xl text-theme-text leading-relaxed font-light">{{ barber.bio }}</p>
              </div>

              <div data-reveal data-reveal-delay="100" v-if="barber.specialties && barber.specialties.length">
                <h2 class="font-display text-4xl text-gold mb-6">Signatures</h2>
                <div class="flex flex-wrap gap-4">
                  <span v-for="spec in barber.specialties" :key="spec" class="rounded-2xl border border-gold/30 bg-gold/5 px-6 py-3 text-sm tracking-wide text-theme-text shadow-inner">
                    {{ spec }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Right: Booking Widget -->
            <div class="lg:col-span-5 lg:sticky lg:top-32" data-reveal data-reveal-delay="200">
              <div class="rounded-3xl border border-theme-border bg-theme-surface p-8 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gold/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
                
                <h3 class="font-display text-3xl text-ivory mb-2">Book an Appointment</h3>
                <p class="text-theme-muted text-sm mb-8">Secure your slot with {{ barber.name }} today.</p>
                
                <RouterLink to="/customer/login" class="group flex w-full items-center justify-center gap-3 rounded-2xl bg-gold py-5 text-lg font-bold text-obsidian shadow-[0_0_40px_-10px_rgba(212,175,55,0.5)] hover:shadow-[0_0_60px_-10px_rgba(212,175,55,0.7)] transition-all duration-300 hover:-translate-y-1">
                  Check Availability
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 transition-transform group-hover:translate-x-1"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </RouterLink>
                
                <ul class="mt-8 space-y-4 text-sm text-theme-muted">
                  <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Complimentary beverage included
                  </li>
                  <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Hot towel finish
                  </li>
                  <li class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Premium styling products
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </section>

        <!-- Barber's Gallery (Full Width Masonry) -->
        <section class="mt-10" v-if="barber.gallery && barber.gallery.length" data-reveal>
          <div class="mx-auto max-w-7xl px-6 mb-12 flex items-end justify-between">
            <div>
              <p class="text-sm font-semibold uppercase tracking-[0.3em] text-theme-muted mb-2">Portfolio</p>
              <h2 class="font-display text-5xl text-gold">The Masterpieces</h2>
            </div>
          </div>
          
          <div class="px-6 mx-auto max-w-7xl columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
            <article v-for="(item, index) in barber.gallery" :key="item.id" @click="openLightbox(index)" class="group overflow-hidden rounded-3xl border border-theme-border bg-theme-surface relative block mb-6 break-inside-avoid cursor-pointer shadow-lg hover:shadow-[0_0_30px_rgba(212,175,55,0.15)] transition-all duration-500 hover:-translate-y-1">
              <div class="relative w-full overflow-hidden bg-theme-bg" :style="{ aspectRatio: index % 3 === 0 ? '3/4' : (index % 2 === 0 ? '4/5' : '1/1') }">
                <img v-if="item.image_path" :src="item.image_path" :alt="item.title" class="absolute inset-0 h-full w-full object-cover transition-transform duration-1000 group-hover:scale-110" />
                <div class="absolute inset-0 bg-gradient-to-t from-obsidian via-obsidian/20 to-transparent opacity-80 transition-opacity duration-500 group-hover:opacity-95"></div>
              </div>
              <div class="absolute inset-0 z-10 flex flex-col justify-end p-8 text-white translate-y-8 transition-transform duration-500 group-hover:translate-y-0">
                <p class="text-xs uppercase tracking-[0.3em] text-gold font-bold opacity-0 transition-opacity duration-500 group-hover:opacity-100 delay-100">{{ item.category }}</p>
                <h3 class="mt-2 font-display text-3xl">{{ item.title }}</h3>
                
                <div class="absolute right-8 bottom-8 flex h-12 w-12 items-center justify-center rounded-full bg-gold text-obsidian shadow-lg opacity-0 transform translate-y-4 transition-all duration-500 group-hover:opacity-100 group-hover:translate-y-0 delay-200 hover:scale-110">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
                </div>
              </div>
            </article>
          </div>
        </section>
      </div>
    </main>

    <!-- Lightbox Overlay -->
    <div v-if="isLightboxOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-obsidian/95 backdrop-blur-sm" @keydown.esc="closeLightbox" tabindex="0" ref="lightboxRef">
      <!-- Close Button -->
      <button @click="closeLightbox" class="absolute top-6 right-6 z-10 rounded-full bg-theme-surface/10 p-2 text-white hover:bg-theme-surface/30 hover:text-gold transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
      </button>

      <!-- Previous Button -->
      <button @click.stop="prevImage" class="absolute left-6 z-10 rounded-full bg-theme-surface/10 p-3 text-white hover:bg-theme-surface/30 hover:text-gold transition-colors" :class="{ 'opacity-30 cursor-not-allowed': currentIdx === 0 }" :disabled="currentIdx === 0">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
      </button>

      <!-- Main Image -->
      <div class="relative max-h-[85vh] max-w-[85vw] w-full flex flex-col items-center justify-center">
        <img v-if="barber.gallery[currentIdx]?.image_path" :src="barber.gallery[currentIdx].image_path" :alt="barber.gallery[currentIdx].title" class="max-h-[80vh] w-auto rounded-xl object-contain shadow-2xl" />
        <div class="mt-6 text-center text-white">
          <p class="text-sm font-semibold uppercase tracking-widest text-gold-light">{{ barber.gallery[currentIdx]?.category }}</p>
          <h2 class="mt-2 font-display text-2xl md:text-3xl">{{ barber.gallery[currentIdx]?.title }}</h2>
          <p class="mt-2 text-theme-muted max-w-2xl mx-auto">{{ barber.gallery[currentIdx]?.description }}</p>
        </div>
      </div>

      <!-- Next Button -->
      <button @click.stop="nextImage" class="absolute right-6 z-10 rounded-full bg-theme-surface/10 p-3 text-white hover:bg-theme-surface/30 hover:text-gold transition-colors" :class="{ 'opacity-30 cursor-not-allowed': currentIdx === (barber.gallery.length - 1) }" :disabled="currentIdx === (barber.gallery.length - 1)">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
      </button>
    </div>
  </PublicLayout>
</template>

<script setup>
import { onMounted, ref, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { publicApi } from '../api/public.api';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

const route = useRoute();
const barber = ref(null);
const isLoading = ref(true);
const error = ref(false);

const { init: initScrollReveal } = useScrollReveal();

// Lightbox state
const isLightboxOpen = ref(false);
const currentIdx = ref(0);
const lightboxRef = ref(null);

onMounted(async () => {
  window.scrollTo(0, 0);
  try {
    const id = route.params.id;
    const response = await publicApi.barber(id);
    barber.value = response.data.data;
    isLoading.value = false;
    setTimeout(() => {
      initScrollReveal();
    }, 100);
  } catch (err) {
    console.error('Failed to load barber profile:', err);
    error.value = true;
    isLoading.value = false;
  }
});

function openLightbox(index) {
  currentIdx.value = index;
  isLightboxOpen.value = true;
  document.body.style.overflow = 'hidden';
  nextTick(() => {
    lightboxRef.value?.focus();
  });
}

function closeLightbox() {
  isLightboxOpen.value = false;
  document.body.style.overflow = '';
}

function nextImage() {
  if (barber.value?.gallery && currentIdx.value < barber.value.gallery.length - 1) {
    currentIdx.value++;
  }
}

function prevImage() {
  if (currentIdx.value > 0) {
    currentIdx.value--;
  }
}
</script>
