<template>
  <PublicLayout>
    <PageHero
      page-key="gallery"
      eyebrow="Portfolio & Transformations"
      fallback-title="Our Gallery"
      fallback-subtitle="Browse our portfolio of premium cuts, styles, and grooming transformations."
      title-setting-key="gallery_title"
      subtitle-setting-key="gallery_subtitle"
    />
    <section class="mx-auto max-w-7xl px-4 md:px-6 pt-12 pb-24 text-theme-text min-h-[50vh]">

      <!-- Categories Filter Tabs -->
      <div data-reveal class="flex items-center justify-center flex-wrap gap-2.5 sm:gap-3 mb-12">
        <button
          type="button"
          @click="selectedCategory = 'All'"
          class="px-5 py-2.5 rounded-full text-xs sm:text-sm font-semibold transition-all duration-200 border capitalize"
          :class="selectedCategory === 'All'
            ? 'bg-gold text-obsidian border-gold shadow-lg shadow-gold/20 scale-105'
            : 'bg-theme-surface text-theme-muted border-theme-border hover:border-gold/40 hover:text-gold'"
        >
          All ({{ gallery.length }})
        </button>
        <button
          v-for="cat in categories"
          :key="cat"
          type="button"
          @click="selectedCategory = cat"
          class="px-5 py-2.5 rounded-full text-xs sm:text-sm font-semibold transition-all duration-200 border capitalize"
          :class="selectedCategory === cat
            ? 'bg-gold text-obsidian border-gold shadow-lg shadow-gold/20 scale-105'
            : 'bg-theme-surface text-theme-muted border-theme-border hover:border-gold/40 hover:text-gold'"
        >
          {{ formatCategory(cat) }}
        </button>
      </div>

      <!-- Gallery Skeleton Loader -->
      <div v-if="isLoading" class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
        <div
          v-for="n in 6"
          :key="'gal-skel-' + n"
          class="overflow-hidden rounded-3xl border border-theme-border bg-theme-surface mb-6 break-inside-avoid"
        >
          <div class="skeleton-shimmer w-full" :style="{ aspectRatio: n % 2 === 0 ? '4/5' : '4/3' }"></div>
        </div>
      </div>

      <!-- Real Gallery Masonry -->
      <div v-else-if="filteredGallery.length > 0" class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
        <article
          v-for="(item, index) in filteredGallery"
          :key="item.id"
          @click="openLightbox(index)"
          data-reveal
          class="group overflow-hidden rounded-3xl border border-theme-border bg-theme-surface relative block mb-6 break-inside-avoid cursor-pointer shadow-lg hover:shadow-2xl hover:shadow-gold/15 transition-all duration-500 hover:-translate-y-1 hover:border-gold/40"
        >
          <div
            class="relative w-full overflow-hidden bg-theme-bg"
            :style="{ aspectRatio: index % 3 === 0 ? '3/4' : (index % 2 === 0 ? '4/5' : '4/3') }"
          >
            <img
              v-if="item.image_path || item.image_url"
              :src="getImageUrl(item.image_path || item.image_url)"
              :alt="item.title"
              class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-obsidian via-obsidian/30 to-transparent opacity-80 transition-opacity duration-500 group-hover:opacity-95"></div>
          </div>

          <!-- Hover Details Overlay -->
          <div class="absolute inset-0 z-10 flex flex-col justify-end p-4 xs:p-6 text-white translate-y-0 sm:translate-y-6 transition-transform duration-500 sm:group-hover:translate-y-0">
            <div class="flex items-center justify-between opacity-100 sm:opacity-0 transition-opacity duration-500 sm:group-hover:opacity-100 delay-100">
              <span class="text-[10px] uppercase tracking-[0.25em] text-gold font-bold">
                {{ formatCategory(item.category) }}
              </span>
              <span v-if="item.barber_name" class="text-[10px] uppercase font-bold tracking-wider text-white/80 bg-black/60 px-2.5 py-1 rounded-full backdrop-blur-sm border border-white/10 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 text-gold">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                {{ item.barber_name }}
              </span>
            </div>

            <h2 class="mt-2 font-display text-xl sm:text-2xl font-bold drop-shadow-md">
              {{ item.title }}
            </h2>

            <p v-if="item.description" class="mt-2 text-xs sm:text-sm text-white/80 line-clamp-2 opacity-100 sm:opacity-0 transition-opacity duration-500 sm:group-hover:opacity-100 delay-150">
              {{ item.description }}
            </p>

            <!-- Expand Icon Button -->
            <button
              class="absolute right-4 bottom-4 xs:right-6 xs:bottom-6 flex h-11 w-11 items-center justify-center rounded-full bg-gold text-obsidian shadow-lg opacity-100 sm:opacity-0 transform translate-y-0 sm:translate-y-4 transition-all duration-500 sm:group-hover:opacity-100 sm:group-hover:translate-y-0 delay-200 hover:scale-110 hover:bg-gold-light"
              aria-label="View larger"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
              </svg>
            </button>
          </div>
        </article>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-20 rounded-3xl border border-theme-border bg-theme-surface">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-14 h-14 mx-auto text-theme-muted mb-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
        </svg>
        <h3 class="text-xl font-display text-theme-text font-bold">No gallery items found</h3>
        <p class="text-sm text-theme-muted mt-2">There are currently no photos under {{ formatCategory(selectedCategory) }}.</p>
        <button
          type="button"
          @click="selectedCategory = 'All'"
          class="mt-6 inline-flex items-center gap-2 rounded-xl border border-gold/40 bg-gold/10 px-6 py-2.5 text-xs font-bold text-gold hover:bg-gold hover:text-obsidian transition-colors"
        >
          View All Gallery
        </button>
      </div>
    </section>

    <!-- Lightbox Overlay -->
    <div
      v-if="isLightboxOpen"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-md"
      @keydown.esc="closeLightbox"
      @keydown.left="prevImage"
      @keydown.right="nextImage"
      tabindex="0"
      ref="lightboxRef"
    >
      <!-- Close Button -->
      <button
        @click="closeLightbox"
        class="absolute top-4 right-4 sm:top-6 sm:right-6 z-20 min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full bg-white/10 p-3 text-white hover:bg-white/20 hover:text-gold transition-colors"
        aria-label="Close lightbox"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- Previous Button -->
      <button
        @click.stop="prevImage"
        class="absolute left-2 sm:left-6 z-20 min-h-[44px] min-w-[44px] flex items-center justify-center rounded-full bg-white/10 p-3 text-white hover:bg-white/20 hover:text-gold transition-colors"
        :class="{ 'opacity-30 cursor-not-allowed': currentIdx === 0 }"
        :disabled="currentIdx === 0"
        aria-label="Previous image"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
        </svg>
      </button>

      <!-- Main Image View -->
      <div class="relative max-h-[85vh] max-w-[90vw] w-full flex flex-col items-center justify-center p-4">
        <img
          v-if="filteredGallery[currentIdx]?.image_path || filteredGallery[currentIdx]?.image_url"
          :src="getImageUrl(filteredGallery[currentIdx]?.image_path || filteredGallery[currentIdx]?.image_url)"
          :alt="filteredGallery[currentIdx]?.title"
          class="max-h-[75vh] w-auto max-w-full rounded-2xl object-contain shadow-2xl border border-white/10"
        />
        <div class="mt-5 text-center text-white max-w-2xl">
          <div class="flex items-center justify-center gap-3">
            <span class="text-xs font-bold uppercase tracking-widest text-gold">
              {{ formatCategory(filteredGallery[currentIdx]?.category) }}
            </span>
            <span v-if="filteredGallery[currentIdx]?.barber_name" class="text-white/40">•</span>
            <span v-if="filteredGallery[currentIdx]?.barber_name" class="text-xs font-semibold text-white/80">
              By {{ filteredGallery[currentIdx]?.barber_name }}
            </span>
          </div>
          <h2 class="mt-2 font-display text-2xl sm:text-3xl font-bold">
            {{ filteredGallery[currentIdx]?.title }}
          </h2>
          <p v-if="filteredGallery[currentIdx]?.description" class="mt-2 text-sm text-white/70">
            {{ filteredGallery[currentIdx]?.description }}
          </p>
        </div>
      </div>

      <!-- Next Button -->
      <button
        @click.stop="nextImage"
        class="absolute right-4 sm:right-8 z-20 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 hover:text-gold transition-colors"
        :class="{ 'opacity-30 cursor-not-allowed': currentIdx === filteredGallery.length - 1 }"
        :disabled="currentIdx === filteredGallery.length - 1"
        aria-label="Next image"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
      </button>
    </div>
  </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import PageHero from '@/shared/ui/PageHero.vue';
import { publicApi } from '@/shared/api/old_publicApi';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import { getStorageUrl as getImageUrl } from '@/core/utils/url';

const { init: initScrollReveal } = useScrollReveal();


const gallery = ref([]);
const selectedCategory = ref('All');
const isLoading = ref(true);

const isLightboxOpen = ref(false);
const currentIdx = ref(0);
const lightboxRef = ref(null);

const formatCategory = (cat) => {
  if (!cat) return 'Style';
  return cat.replace(/_/g, ' ');
};

const categories = computed(() => {
  const cats = gallery.value.map(g => g.category).filter(Boolean);
  return [...new Set(cats)].sort();
});

const filteredGallery = computed(() => {
  if (selectedCategory.value === 'All') return gallery.value;
  return gallery.value.filter(g => g.category === selectedCategory.value);
});

const openLightbox = (index) => {
  currentIdx.value = index;
  isLightboxOpen.value = true;
  nextTick(() => {
    if (lightboxRef.value) lightboxRef.value.focus();
  });
};

const closeLightbox = () => {
  isLightboxOpen.value = false;
};

const prevImage = () => {
  if (currentIdx.value > 0) currentIdx.value--;
};

const nextImage = () => {
  if (currentIdx.value < filteredGallery.value.length - 1) currentIdx.value++;
};

const fetchContent = async () => {
  try {
    const galleryRes = await publicApi.gallery().catch(() => ({ data: { data: [] } }));
    gallery.value = galleryRes.data?.data || [];
  } catch (error) {
    console.error('Failed to load gallery data:', error);
  } finally {
    isLoading.value = false;
    setTimeout(() => {
      initScrollReveal();
    }, 100);
  }
};

onMounted(() => {
  fetchContent();
});
</script>
