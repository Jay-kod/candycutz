<template>
  <PublicLayout>
    <section class="mx-auto max-w-7xl px-4 md:px-6 py-20 text-theme-text">
      <!-- Header -->
      <div data-reveal class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
        <div>
          <p class="text-sm uppercase tracking-[0.3em] text-gold-light">Our Services</p>
          <h1 class="mt-3 font-display text-4xl sm:text-5xl text-gold md:text-6xl">The Full Menu</h1>
          <p class="mt-4 text-theme-muted max-w-2xl text-lg">
            From classic cuts to hot towel shaves, experience grooming that elevates your style.
          </p>
        </div>
      </div>

      <!-- Category Filter -->
      <div class="mt-10 flex flex-wrap gap-3" data-reveal>
        <button 
          @click="changeCategory('All')"
          :class="[
            'px-6 py-2.5 rounded-full text-sm font-semibold transition-all focus:outline-none',
            activeCategory === 'All' ? 'bg-gold text-obsidian shadow-md' : 'bg-theme-surface text-theme-muted hover:text-gold hover:border-gold border border-theme-border'
          ]"
        >
          All
        </button>
        <button 
          v-for="cat in availableCategories" 
          :key="cat"
          @click="changeCategory(cat)"
          :class="[
            'px-6 py-2.5 rounded-full text-sm font-semibold transition-all focus:outline-none',
            activeCategory === cat ? 'bg-gold text-obsidian shadow-md' : 'bg-theme-surface text-theme-muted hover:text-gold hover:border-gold border border-theme-border'
          ]"
        >
          {{ cat }}
        </button>
      </div>

      <!-- Skeleton Loader State -->
      <div v-if="isLoading" class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        <div 
          v-for="n in 6" 
          :key="'skeleton-' + n" 
          class="relative flex flex-col overflow-hidden rounded-3xl border border-theme-border bg-theme-surface shadow-md"
          :style="{ animationDelay: (n - 1) * 80 + 'ms' }"
        >
          <!-- Skeleton Image -->
          <div class="relative aspect-[4/3] w-full overflow-hidden">
            <div class="skeleton-shimmer h-full w-full"></div>
            <!-- Fake Badge -->
            <div class="absolute left-4 top-4 h-6 w-20 rounded-full skeleton-shimmer"></div>
            <!-- Fake Wishlist -->
            <div class="absolute right-4 top-4 h-10 w-10 rounded-full skeleton-shimmer"></div>
          </div>
          <!-- Skeleton Content -->
          <div class="flex flex-1 flex-col p-6 gap-4">
            <!-- Title -->
            <div class="h-7 w-3/4 rounded-lg skeleton-shimmer"></div>
            <!-- Description lines -->
            <div class="space-y-2">
              <div class="h-4 w-full rounded skeleton-shimmer"></div>
              <div class="h-4 w-5/6 rounded skeleton-shimmer"></div>
            </div>
            <!-- Price / Duration row -->
            <div class="flex items-center justify-between border-b border-theme-border pb-6 mt-2">
              <div class="flex flex-col gap-1.5">
                <div class="h-3 w-10 rounded skeleton-shimmer"></div>
                <div class="h-6 w-16 rounded skeleton-shimmer"></div>
              </div>
              <div class="flex flex-col items-end gap-1.5">
                <div class="h-3 w-14 rounded skeleton-shimmer"></div>
                <div class="h-5 w-16 rounded skeleton-shimmer"></div>
              </div>
            </div>
            <!-- Button -->
            <div class="h-12 w-full rounded-xl skeleton-shimmer mt-2"></div>
          </div>
        </div>
      </div>

      <!-- Services Grid -->
      <div v-else-if="filteredServices.length > 0" class="mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        <article 
          v-for="(service, index) in filteredServices" 
          :key="service.id" 
          :data-reveal-delay="index * 50" 
          data-reveal 
          class="group relative flex flex-col overflow-hidden rounded-3xl border border-theme-border bg-theme-surface shadow-md transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-gold/10 hover:border-gold/40"
        >
          <!-- Image Container -->
          <div class="relative aspect-[4/3] w-full overflow-hidden bg-theme-bg">
            <img 
              v-if="service.image" 
              :src="service.image" 
              :alt="service.name" 
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" 
              style="-webkit-mask-image: linear-gradient(to bottom, black 60%, transparent 100%); mask-image: linear-gradient(to bottom, black 60%, transparent 100%);"
            />
            <div v-else class="h-full w-full flex items-center justify-center bg-theme-bg" style="-webkit-mask-image: linear-gradient(to bottom, black 60%, transparent 100%); mask-image: linear-gradient(to bottom, black 60%, transparent 100%);">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 text-ivory/20">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.525W6.75a3.75 3.75 0 00-3.75 3.75v8.25c0 2.071 1.679 3.75 3.75 3.75h10.5a3.75 3.75 0 003.75-3.75v-8.25c0-2.071-1.679-3.75-3.75-3.75h-3.375c-.621 0-1.125-.504-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125z" />
              </svg>
            </div>
            
            <!-- Category Badge -->
            <span class="absolute left-4 top-4 rounded-full bg-theme-bg/80 backdrop-blur-md px-3 py-1 text-xs font-semibold uppercase tracking-widest text-gold shadow-sm">
              {{ service.category?.name || 'Service' }}
            </span>

            <!-- Wishlist Button -->
            <button 
              @click="toggleWishlist(service.id)"
              class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-theme-bg/80 backdrop-blur-md text-theme-text shadow-sm transition-all hover:bg-theme-bg hover:scale-110 focus:outline-none"
              :class="{ 'text-danger': wishlist.includes(service.id) }"
              aria-label="Add to wishlist"
            >
              <svg xmlns="http://www.w3.org/2000/svg" :fill="wishlist.includes(service.id) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
              </svg>
            </button>
          </div>

          <!-- Content -->
          <div class="flex flex-1 flex-col p-6">
            <!-- Header Row: Title & Barber vs Price & Time -->
            <div class="mb-3 flex items-start justify-between gap-4">
              <div class="min-w-0">
                <h2 class="font-display text-xl font-bold text-theme-text group-hover:text-gold transition-colors truncate">{{ service.name }}</h2>
                <p v-if="service.barber?.name" class="mt-1 text-xs font-semibold text-gold/80 flex items-center gap-1.5">
                  <span class="h-px w-3 bg-gold/50 inline-block"></span>
                  by {{ service.barber.name }}
                </p>
              </div>
              <div class="flex shrink-0 flex-col items-end text-right">
                <span class="font-display text-lg font-bold text-gold drop-shadow-sm">₦{{ Number(service.price).toLocaleString() }}</span>
                <span class="text-[10px] uppercase tracking-widest text-theme-muted mt-1 flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ service.duration_minutes }} mins</span>
              </div>
            </div>
            
            <p class="text-sm leading-relaxed text-theme-muted line-clamp-2 flex-1">
              {{ service.description || 'Premium grooming service.' }}
            </p>

            <!-- Action Buttons -->
            <div class="mt-6 pt-5 border-t border-theme-border/50 flex gap-3">
              <RouterLink 
                :to="`/customer/login?service=${service.id}`" 
                class="flex flex-1 items-center justify-center rounded-xl border border-gold/30 bg-gold/5 px-3 py-3 text-sm font-bold text-gold transition-all hover:bg-gold/10 hover:scale-[1.02]"
              >
                Details
              </RouterLink>
              <RouterLink 
                :to="`/customer/login?service=${service.id}`" 
                class="flex flex-[2] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-gold to-gold-light px-4 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_20px_rgba(212,175,55,0.4)] hover:-translate-y-0.5"
              >
                Book Now
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
              </RouterLink>
            </div>
          </div>
        </article>
      </div>

      <!-- Empty State -->
      <div v-else class="mt-12 text-center py-24 bg-theme-surface rounded-3xl border border-theme-border" data-reveal>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-theme-muted mb-4 opacity-50">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />
        </svg>
        <h3 class="text-2xl font-display text-gold mb-2">No services found</h3>
        <p class="text-theme-muted max-w-md mx-auto text-center">There are currently no services available under the <span class="font-semibold text-theme-text">{{ activeCategory }}</span> category.</p>
        <button @click="changeCategory('All')" class="mt-8 rounded-full border border-gold px-6 py-2 font-semibold text-gold transition hover:bg-gold/10 focus:outline-none">View all services</button>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { onMounted, ref, watch, computed } from 'vue';
import { RouterLink } from 'vue-router';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { publicApi } from '../api/public.api';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

const services = ref([]);
const wishlist = ref([]);
const activeCategory = ref('All');
const isLoading = ref(false);

const availableCategories = computed(() => {
  const dbCategories = services.value.map(s => s.category?.name).filter(Boolean);
  return [...new Set(dbCategories)].sort();
});

const filteredServices = computed(() => {
  if (activeCategory.value === 'All') return services.value;
  return services.value.filter(s => s.category?.name === activeCategory.value);
});

const { init: initScrollReveal } = useScrollReveal();

function changeCategory(cat) {
  if (activeCategory.value === cat) return;
  activeCategory.value = cat;
  
  // Simulate loading state to provide visual feedback during instant JS filtering
  isLoading.value = true;
  setTimeout(() => {
    isLoading.value = false;
    // Re-trigger scroll reveal animations after DOM has been updated
    setTimeout(() => {
      initScrollReveal();
    }, 50);
  }, 400); // 400ms visual loading delay
}

function toggleWishlist(id) {
  if (wishlist.value.includes(id)) {
    wishlist.value = wishlist.value.filter(itemId => itemId !== id);
  } else {
    wishlist.value.push(id);
  }
}

watch(wishlist, (newWishlist) => {
  localStorage.setItem('candycutz_wishlist', JSON.stringify(newWishlist));
}, { deep: true });

onMounted(async () => {
  try {
    const response = await publicApi.services();
    services.value = response.data.data;
    
    // Load wishlist from local storage if available
    const savedWishlist = localStorage.getItem('candycutz_wishlist');
    if (savedWishlist) {
      wishlist.value = JSON.parse(savedWishlist);
    }
    
    setTimeout(() => {
      initScrollReveal();
    }, 100);
  } catch (error) {
    console.error('Failed to load services:', error);
  }
});
</script>