<template>
  <CustomerLayout>
    <section class="space-y-8 animate-fade-in pb-12">
      <!-- Luxury Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-[#12161C] p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-[var(--color-accent)]/5 blur-[80px] pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-[60px] pointer-events-none"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-bold mb-2 flex items-center gap-2">
            <PhotoIcon class="w-4 h-4 text-[var(--color-accent)]" />
            Style Showcase
          </p>
          <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl text-slate-900 dark:text-white drop-shadow-sm">
            {{ pageTitle }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-500 dark:to-amber-200">({{ gallery.length }})</span>
          </h1>
          <p class="mt-2 max-w-xl text-xs sm:text-sm text-slate-600 dark:text-[var(--color-text-secondary)] leading-relaxed">
            {{ pageSubtitle }}
          </p>
        </div>

        <div class="relative z-10 flex items-center gap-3">
          <RouterLink 
            to="/customer/dashboard/services"
            class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-accent)] px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-[0.98] transition-all"
          >
            <ScissorsIcon class="w-4 h-4" />
            Book a Cut
          </RouterLink>
        </div>
      </div>

      <!-- Categories Filter Tabs -->
      <div v-if="!isLoading && categories.length > 0" class="flex items-center flex-wrap gap-2 sm:gap-2.5">
        <button
          type="button"
          @click="selectedCategory = 'All'"
          class="px-4 py-2 rounded-full text-xs font-semibold transition-all duration-200 border"
          :class="selectedCategory === 'All'
            ? 'bg-[var(--color-accent)] text-white border-[var(--color-accent)] shadow-md'
            : 'bg-white dark:bg-[#12161C] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:border-[var(--color-accent)]'"
        >
          All ({{ gallery.length }})
        </button>
        <button
          v-for="cat in categories"
          :key="cat"
          type="button"
          @click="selectedCategory = cat"
          class="px-4 py-2 rounded-full text-xs font-semibold transition-all duration-200 border flex items-center gap-1.5"
          :class="selectedCategory === cat
            ? 'bg-[var(--color-accent)] text-white border-[var(--color-accent)] shadow-md'
            : 'bg-white dark:bg-[#12161C] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:border-[var(--color-accent)]'"
        >
          <CategoryIcon v-if="getCategoryIcon(cat)" :name="getCategoryIcon(cat)" custom-class="size-3.5" />
          <span>{{ formatCategory(cat) }}</span>
        </button>
      </div>

      <!-- Zero-CLS Skeleton Grid -->
      <div v-if="isLoading" class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
        <div v-for="n in 6" :key="'gal-skel-'+n" class="overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-slate-100 dark:bg-[#12161C] mb-6 break-inside-avoid">
          <div class="w-full bg-slate-200/60 dark:bg-white/5 animate-pulse" :style="{ aspectRatio: n % 2 === 0 ? '4/5' : '4/3' }"></div>
        </div>
      </div>

      <!-- Gallery Grid -->
      <div v-else-if="filteredGallery.length > 0" class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
        <article 
          v-for="(item, index) in filteredGallery" 
          :key="item.id" 
          @click="openLightbox(index)" 
          class="group overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] relative block mb-6 break-inside-avoid cursor-pointer shadow-lg hover:border-gold/40 hover:shadow-2xl transition-all duration-300"
        >
          <div class="relative w-full overflow-hidden bg-white/[0.02]" :style="{ aspectRatio: index % 3 === 0 ? '3/4' : (index % 2 === 0 ? '4/5' : '4/3') }">
             <img 
               v-if="item.image_path || item.image_url" 
               :src="getImageUrl(item.image_path || item.image_url)" 
               :alt="item.title" 
               class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" 
             />
             <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent opacity-80 group-hover:opacity-95 transition-opacity duration-300"></div>
          </div>

          <!-- Card Overlay Content -->
          <div class="absolute inset-0 z-10 flex flex-col justify-end p-5 text-white">
            <div class="flex items-center justify-between gap-2">
              <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--color-accent)] font-bold bg-[var(--color-accent)]/10 px-2.5 py-0.5 rounded-md border border-[var(--color-accent)]/20 backdrop-blur-sm inline-flex items-center gap-1">
                <CategoryIcon v-if="getCategoryIcon(item.category)" :name="getCategoryIcon(item.category)" custom-class="size-3" />
                {{ formatCategory(item.category) }}
              </span>
              <span class="text-[10px] font-bold text-white/80 bg-black/60 px-2.5 py-0.5 rounded-md border border-white/10 backdrop-blur-sm flex items-center gap-1">
                <UserIcon class="w-3 h-3 text-[var(--color-accent)]" />
                {{ item.barber_name || 'CandyCutz Master' }}
              </span>
            </div>
            
            <h2 class="mt-2 font-display text-lg font-bold text-white group-hover:text-[var(--color-accent)] transition-colors">{{ item.title }}</h2>
            <p v-if="item.description" class="mt-1 text-xs text-white/70 line-clamp-2">{{ item.description }}</p>
          </div>
        </article>
      </div>

      <!-- Empty State -->
      <div v-if="!isLoading && filteredGallery.length === 0" class="rounded-3xl border border-dashed border-slate-300 dark:border-white/10 p-16 text-center bg-slate-50/50 dark:bg-white/[0.02]">
        <PhotoIcon class="w-12 h-12 text-slate-400 dark:text-white/30 mx-auto mb-3" />
        <h3 class="font-display text-xl text-slate-900 dark:text-white mb-1 font-bold">No Gallery Images Available</h3>
        <p class="text-xs text-slate-600 dark:text-[var(--color-text-secondary)]">Check back soon for fresh portfolio updates from our stylists.</p>
      </div>
    </section>

    <!-- Lightbox Modal -->
    <div 
      v-if="isLightboxOpen" 
      class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-md p-4" 
      @keydown.esc="closeLightbox" 
      tabindex="0" 
      ref="lightboxRef"
    >
      <button 
        @click="closeLightbox" 
        class="absolute top-6 right-6 z-10 rounded-full bg-white/10 p-2.5 text-white hover:bg-white/20 transition-colors"
        title="Close"
      >
        <XMarkIcon class="w-6 h-6" />
      </button>

      <button 
        @click.stop="prevImage" 
        class="absolute left-6 z-10 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 transition-colors disabled:opacity-30 disabled:cursor-not-allowed" 
        :disabled="currentIdx === 0"
      >
        <ChevronLeftIcon class="w-6 h-6" />
      </button>

      <div class="relative max-h-[85vh] max-w-4xl w-full flex flex-col items-center justify-center">
        <img 
          v-if="filteredGallery[currentIdx]?.image_path || filteredGallery[currentIdx]?.image_url" 
          :src="getImageUrl(filteredGallery[currentIdx]?.image_path || filteredGallery[currentIdx]?.image_url)" 
          :alt="filteredGallery[currentIdx]?.title" 
          class="max-h-[70vh] w-auto rounded-2xl object-contain shadow-2xl border border-white/10" 
        />
        <div class="mt-5 text-center text-white">
          <div class="flex items-center justify-center gap-2 mb-1.5">
            <span class="text-xs font-bold uppercase tracking-wider text-[var(--color-accent)]">{{ formatCategory(filteredGallery[currentIdx]?.category) }}</span>
            <span class="text-white/30">•</span>
            <span class="text-xs text-white/70">Crafted by {{ filteredGallery[currentIdx]?.barber_name || 'CandyCutz Master' }}</span>
          </div>
          <h2 class="font-display text-2xl font-bold">{{ filteredGallery[currentIdx]?.title }}</h2>
          <p v-if="filteredGallery[currentIdx]?.description" class="mt-2 text-xs text-white/60 max-w-xl mx-auto">{{ filteredGallery[currentIdx]?.description }}</p>
        </div>
      </div>

      <button 
        @click.stop="nextImage" 
        class="absolute right-6 z-10 rounded-full bg-white/10 p-3 text-white hover:bg-white/20 transition-colors disabled:opacity-30 disabled:cursor-not-allowed" 
        :disabled="currentIdx === filteredGallery.length - 1"
      >
        <ChevronRightIcon class="w-6 h-6" />
      </button>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { computed, onMounted, ref, nextTick } from 'vue';
import { RouterLink } from 'vue-router';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import CategoryIcon from '@/shared/ui/CategoryIcon.vue';
import { client } from '@/shared/api/client';
import { getStorageUrl as getImageUrl } from '@/core/utils/url';
import { 
  PhotoIcon, 
  ScissorsIcon, 
  UserIcon, 
  XMarkIcon, 
  ChevronLeftIcon, 
  ChevronRightIcon 
} from '@heroicons/vue/24/outline';

const pageTitle = ref('Our Gallery');
const pageSubtitle = ref('Browse our portfolio of cuts, fades, beard care, and grooming styles.');
const gallery = ref([]);
const backendCategories = ref([]);
const selectedCategory = ref('All');
const isLoading = ref(true);

const isLightboxOpen = ref(false);
const currentIdx = ref(0);
const lightboxRef = ref(null);

const categories = computed(() => {
  const itemCats = gallery.value.map(g => g.category).filter(Boolean);
  if (backendCategories.value.length > 0) {
    const slugs = backendCategories.value.map(c => c.slug);
    const known = new Set(slugs.map(s => s.toLowerCase()));
    const extra = [...new Set(itemCats.filter(c => !known.has(String(c).toLowerCase())))].sort();
    return [...slugs, ...extra];
  }
  return [...new Set(itemCats)].sort();
});

const filteredGallery = computed(() => {
  if (selectedCategory.value === 'All') return gallery.value;
  const target = selectedCategory.value.toLowerCase();
  return gallery.value.filter(g => (g.category || '').toLowerCase() === target);
});

function getCategoryIcon(catKey) {
  const match = backendCategories.value.find(
    c => c.slug?.toLowerCase() === String(catKey).toLowerCase() || c.name?.toLowerCase() === String(catKey).toLowerCase()
  );
  return match?.icon || null;
}

function formatCategory(cat) {
  if (!cat) return 'Cut';
  const match = backendCategories.value.find(
    c => c.slug?.toLowerCase() === String(cat).toLowerCase()
  );
  if (match) return match.name;
  return String(cat).replace(/_/g, ' ');
}

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
  if (currentIdx.value < filteredGallery.value.length - 1) {
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
    const [settingsRes, galleryRes, categoriesRes] = await Promise.all([
      client.get('/settings').catch(() => ({ data: { data: {} } })),
      client.get('/gallery').catch(() => ({ data: { data: [] } })),
      client.get('/gallery-categories').catch(() => ({ data: { data: [] } }))
    ]);

    const settings = settingsRes.data?.data;
    if (settings) {
      if (settings.gallery_title) pageTitle.value = settings.gallery_title;
      if (settings.gallery_subtitle) pageSubtitle.value = settings.gallery_subtitle;
    }

    gallery.value = galleryRes.data?.data || [];
    backendCategories.value = categoriesRes.data?.data || [];
  } catch (error) {
    console.error('Failed to load gallery:', error);
  } finally {
    isLoading.value = false;
  }
});
</script>