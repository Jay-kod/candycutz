<template>
  <CustomerLayout>
    <section class="mx-auto max-w-7xl px-4 md:px-6 py-20 text-theme-text">
      <div data-reveal>
        <p class="text-sm uppercase tracking-[0.3em] text-gold-light">Gallery</p>
        <h1 class="mt-3 font-display text-4xl sm:text-5xl text-gold md:text-6xl">Recent work</h1>
      </div>

      <!-- Gallery Skeleton -->
      <div v-if="isLoading" class="mt-10 columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
        <div v-for="n in 6" :key="'gal-skel-'+n" class="overflow-hidden rounded-3xl border border-theme-border bg-theme-surface mb-6 break-inside-avoid">
          <div class="skeleton-shimmer w-full" :style="{ aspectRatio: n % 2 === 0 ? '4/5' : '4/3' }"></div>
        </div>
      </div>

      <!-- Gallery Real -->
      <div v-else class="mt-10 columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
        <article v-for="(item, index) in gallery" :key="item.id" @click="openLightbox(index)" :data-reveal-delay="index * 100" data-reveal class="group overflow-hidden rounded-3xl border border-theme-border bg-theme-surface relative block mb-6 break-inside-avoid cursor-pointer shadow-md hover:shadow-2xl hover:shadow-gold/10 transition-shadow duration-500">
          <div class="relative w-full overflow-hidden bg-theme-bg" :style="{ aspectRatio: index % 3 === 0 ? '3/4' : (index % 2 === 0 ? '4/5' : '4/3') }">
             <img v-if="item.image_path" :src="item.image_path" :alt="item.title" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
             <!-- Smart Gradient Overlay -->
             <div class="absolute inset-0 bg-gradient-to-t from-obsidian via-obsidian/20 to-transparent opacity-80 transition-opacity duration-500 group-hover:opacity-95"></div>
          </div>
          <!-- Smart Hover Content -->
          <div class="absolute inset-0 z-10 flex flex-col justify-end p-6 text-white translate-y-8 transition-transform duration-500 group-hover:translate-y-0">
            <p class="text-xs uppercase tracking-[0.25em] text-gold-light font-semibold opacity-0 transition-opacity duration-500 group-hover:opacity-100 delay-100">{{ item.category }}</p>
            <h2 class="mt-2 font-display text-2xl font-semibold">{{ item.title }}</h2>
            <p class="mt-3 text-sm text-white/80 line-clamp-3 opacity-0 transition-opacity duration-500 group-hover:opacity-100 delay-150">{{ item.description }}</p>
            <!-- Expand action button -->
            <button class="absolute right-6 bottom-6 flex h-10 w-10 items-center justify-center rounded-full bg-gold text-obsidian shadow-lg opacity-0 transform translate-y-4 transition-all duration-500 group-hover:opacity-100 group-hover:translate-y-0 delay-200 hover:scale-110 hover:bg-gold-light" aria-label="View larger">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
            </button>
          </div>
        </article>
      </div>
    </section>

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
        <img v-if="gallery[currentIdx]?.image_path" :src="gallery[currentIdx].image_path" :alt="gallery[currentIdx].title" class="max-h-[80vh] w-auto rounded-xl object-contain shadow-2xl" />
        <div class="mt-6 text-center text-white">
          <p class="text-sm font-semibold uppercase tracking-widest text-gold-light">{{ gallery[currentIdx]?.category }}</p>
          <h2 class="mt-2 font-display text-2xl md:text-3xl">{{ gallery[currentIdx]?.title }}</h2>
          <p class="mt-2 text-theme-muted max-w-2xl mx-auto">{{ gallery[currentIdx]?.description }}</p>
        </div>
      </div>

      <!-- Next Button -->
      <button @click.stop="nextImage" class="absolute right-6 z-10 rounded-full bg-theme-surface/10 p-3 text-white hover:bg-theme-surface/30 hover:text-gold transition-colors" :class="{ 'opacity-30 cursor-not-allowed': currentIdx === gallery.length - 1 }" :disabled="currentIdx === gallery.length - 1">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
      </button>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, ref, nextTick } from 'vue';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

const gallery = ref([]);
const isLoading = ref(true);

const isLightboxOpen = ref(false);
const currentIdx = ref(0);
const lightboxRef = ref(null);

const { init: initScrollReveal } = useScrollReveal();

function openLightbox(index) {
  currentIdx.value = index;
  isLightboxOpen.value = true;
  document.body.style.overflow = 'hidden'; // Prevent background scrolling
  nextTick(() => {
    lightboxRef.value?.focus(); // Focus lightbox for keyboard events
  });
}

function closeLightbox() {
  isLightboxOpen.value = false;
  document.body.style.overflow = '';
}

function nextImage() {
  if (currentIdx.value < gallery.value.length - 1) {
    currentIdx.value++;
  }
}

function prevImage() {
  if (currentIdx.value > 0) {
    currentIdx.value--;
  }
}

onMounted(async () => {
  try {
    const response = await publicApi.gallery();
    gallery.value = response.data.data;
    isLoading.value = false;
    setTimeout(() => {
      initScrollReveal();
    }, 100);
  } catch (error) {
    console.error('Failed to load gallery:', error);
    isLoading.value = false;
  }
});
</script>