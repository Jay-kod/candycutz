<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Browse</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
              Our <span class="text-gold">Services</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
              Explore our menu of premium grooming services and book your next appointment directly.
            </p>
          </div>
        </div>
      </div>

      <!-- Category Filter -->
      <div class="flex flex-wrap gap-3">
        <button 
          @click="changeCategory('All')"
          :class="[
            'px-6 py-2.5 rounded-full text-sm font-semibold transition-all focus:outline-none',
            activeCategory === 'All' ? 'bg-gold text-obsidian shadow-[0_0_15px_rgba(212,175,55,0.3)]' : 'bg-theme-surface text-theme-muted hover:text-gold hover:border-gold/50 border border-theme-border'
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
            activeCategory === cat ? 'bg-gold text-obsidian shadow-[0_0_15px_rgba(212,175,55,0.3)]' : 'bg-theme-surface text-theme-muted hover:text-gold hover:border-gold/50 border border-theme-border'
          ]"
        >
          {{ cat }}
        </button>
      </div>

      <!-- Skeleton Loader State -->
      <div v-if="isLoading" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <div 
          v-for="n in 6" 
          :key="'skeleton-' + n" 
          class="relative flex flex-col overflow-hidden rounded-2xl border border-white/5 bg-theme-surface/80"
          :style="{ animationDelay: (n - 1) * 80 + 'ms' }"
        >
          <div class="relative aspect-[4/3] w-full overflow-hidden bg-white/5 animate-pulse"></div>
          <div class="flex flex-1 flex-col p-6 gap-4">
            <div class="h-6 w-3/4 rounded-lg bg-white/5 animate-pulse"></div>
            <div class="space-y-2">
              <div class="h-4 w-full rounded bg-white/5 animate-pulse"></div>
              <div class="h-4 w-5/6 rounded bg-white/5 animate-pulse"></div>
            </div>
            <div class="mt-4 h-12 w-full rounded-xl bg-white/5 animate-pulse"></div>
          </div>
        </div>
      </div>

      <!-- Services Grid -->
      <div v-else-if="filteredServices.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <article 
          v-for="(service, index) in filteredServices" 
          :key="service.id" 
          class="group relative flex flex-col overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-gold/5 hover:border-gold/20"
        >
          <!-- Image Container -->
          <div class="relative aspect-[4/3] w-full overflow-hidden bg-theme-bg">
            <img 
              v-if="service.image" 
              :src="service.image" 
              :alt="service.name" 
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" 
            />
            <div v-else class="h-full w-full flex items-center justify-center bg-theme-bg">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 text-ivory/20">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.525W6.75a3.75 3.75 0 00-3.75 3.75v8.25c0 2.071 1.679 3.75 3.75 3.75h10.5a3.75 3.75 0 003.75-3.75v-8.25c0-2.071-1.679-3.75-3.75-3.75h-3.375c-.621 0-1.125-.504-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125z" />
              </svg>
            </div>
            
            <!-- Category Badge -->
            <span class="absolute left-4 top-4 rounded-full bg-theme-bg/90 backdrop-blur px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-gold shadow-sm border border-gold/20">
              {{ service.category?.name || 'Service' }}
            </span>

            <!-- Wishlist Button -->
            <button 
              @click="toggleWishlist(service.id)"
              class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-theme-bg/90 backdrop-blur text-theme-text border border-theme-border shadow-sm transition-all hover:bg-theme-bg hover:scale-110 focus:outline-none"
              :class="{ 'text-red-500 border-red-500/30 bg-red-500/10': wishlist.includes(service.id) }"
              title="Add to wishlist"
            >
              <svg xmlns="http://www.w3.org/2000/svg" :fill="wishlist.includes(service.id) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
              </svg>
            </button>
          </div>

          <!-- Content -->
          <div class="flex flex-1 flex-col p-6">
            <h2 class="font-display text-xl font-bold text-theme-text group-hover:text-gold transition-colors">{{ service.name }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-ivory/50 line-clamp-2 flex-1">
              {{ service.description || 'Premium grooming service.' }}
            </p>
            
            <div class="mt-6 flex items-center justify-between border-t border-theme-border pt-5">
              <div class="flex flex-col">
                <span class="text-[10px] uppercase tracking-widest text-gold/70">Price</span>
                <span class="font-display text-lg font-bold text-theme-text mt-0.5">₦{{ Number(service.price).toLocaleString() }}</span>
              </div>
              <div class="flex flex-col items-end">
                <span class="text-[10px] uppercase tracking-widest text-gold/70">Duration</span>
                <span class="text-sm font-medium text-ivory/80 mt-0.5">{{ service.duration_minutes }} mins</span>
              </div>
            </div>

            <!-- Book Button -->
            <RouterLink 
              :to="`/customer/dashboard/bookings?service=${service.id}`" 
              class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-gold to-gold-dark px-6 py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] hover:scale-[1.02]"
            >
              Book This Service
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
              </svg>
            </RouterLink>
          </div>
        </article>
      </div>

      <!-- Empty State -->
      <div v-else class="rounded-2xl border border-theme-border bg-theme-surface/80 py-24 text-center backdrop-blur-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-ivory/20 mb-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />
        </svg>
        <h3 class="text-xl font-display text-theme-text mb-2">No services found</h3>
        <p class="text-ivory/50 text-sm">There are currently no services available under this category.</p>
        <button @click="changeCategory('All')" class="mt-6 rounded-xl border border-gold/30 bg-gold/10 px-6 py-2.5 text-sm font-semibold text-gold transition hover:bg-gold/20 focus:outline-none">View all services</button>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, ref, watch, computed } from 'vue';
import { RouterLink } from 'vue-router';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';

const services = ref([]);
const wishlist = ref([]);
const activeCategory = ref('All');
const isLoading = ref(true);

const availableCategories = computed(() => {
  const dbCategories = services.value.map(s => s.category?.name).filter(Boolean);
  return [...new Set(dbCategories)].sort();
});

const filteredServices = computed(() => {
  if (activeCategory.value === 'All') return services.value;
  return services.value.filter(s => s.category?.name === activeCategory.value);
});

function changeCategory(cat) {
  if (activeCategory.value === cat) return;
  activeCategory.value = cat;
  
  isLoading.value = true;
  setTimeout(() => {
    isLoading.value = false;
  }, 400); 
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
    
    const savedWishlist = localStorage.getItem('candycutz_wishlist');
    if (savedWishlist) {
      wishlist.value = JSON.parse(savedWishlist);
    }
  } catch (error) {
    console.error('Failed to load services:', error);
  } finally {
    isLoading.value = false;
  }
});
</script>
