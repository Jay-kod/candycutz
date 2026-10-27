<template>
  <CustomerLayout>
    <section class="animate-fade-in space-y-8">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8 shadow-xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)]/80 font-medium">Browse</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-[var(--color-text-primary)]">
              {{ pageTitle }} <span class="text-xl text-[var(--color-text-muted)]">({{ services.length }})</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-[var(--color-text-secondary)] leading-relaxed">
              {{ pageSubtitle }}
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
            activeCategory === 'All' ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] shadow-sm' : 'bg-[var(--color-bg-secondary)] text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] hover:border-[var(--color-accent)]/50 border border-[var(--color-border)]'
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
            activeCategory === cat ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] shadow-sm' : 'bg-[var(--color-bg-secondary)] text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] hover:border-[var(--color-accent)]/50 border border-[var(--color-border)]'
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
          class="relative flex flex-col overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)]/80"
          :style="{ animationDelay: (n - 1) * 80 + 'ms' }"
        >
          <div class="relative aspect-[4/3] w-full overflow-hidden bg-[var(--color-bg-tertiary)] animate-pulse rounded-none"></div>
          <div class="flex flex-1 flex-col p-6 gap-4">
            <div class="h-6 w-3/4 bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
            <div class="space-y-2">
              <div class="h-4 w-full bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
              <div class="h-4 w-5/6 bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
            </div>
            <div class="mt-4 h-12 w-full rounded-xl bg-[var(--color-bg-tertiary)] animate-pulse"></div>
          </div>
        </div>
      </div>

      <!-- Services Grid -->
      <div v-else-if="filteredServices.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <article 
          v-for="service in filteredServices" 
          :key="service.id" 
          class="group relative flex flex-col overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] backdrop-blur-sm transition-all hover:-translate-y-1 hover:shadow-xl hover:border-[var(--color-accent)]/30"
        >
          <!-- Image Container -->
          <RouterLink :to="`/customer/dashboard/services/${service.id}`" class="relative aspect-[4/3] w-full overflow-hidden block">
            <img 
              v-if="service.image" 
              :src="service.image" 
              :alt="service.name" 
              class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" 
              style="-webkit-mask-image: linear-gradient(to bottom, black 60%, transparent 100%); mask-image: linear-gradient(to bottom, black 60%, transparent 100%);"
            />
            <div v-else class="h-full w-full flex items-center justify-center bg-[var(--color-bg-tertiary)]" style="-webkit-mask-image: linear-gradient(to bottom, black 60%, transparent 100%); mask-image: linear-gradient(to bottom, black 60%, transparent 100%);">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12 text-[var(--color-text-muted)]/50">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.525W6.75a3.75 3.75 0 00-3.75 3.75v8.25c0 2.071 1.679 3.75 3.75 3.75h10.5a3.75 3.75 0 003.75-3.75v-8.25c0-2.071-1.679-3.75-3.75-3.75h-3.375c-.621 0-1.125-.504-1.125-1.125v-1.5c0-.621.504-1.125 1.125-1.125z" />
              </svg>
            </div>
            
            <!-- Category Badge -->
            <span class="absolute left-4 top-4 rounded-full bg-[var(--color-bg-primary)]/90 backdrop-blur px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-[var(--color-accent)] shadow-sm border border-[var(--color-accent)]/20">
              {{ service.category?.name || 'Service' }}
            </span>
          </RouterLink>

          <!-- Wishlist Button -->
          <button 
            @click.prevent="toggleWishlist(service.id)"
            class="absolute right-4 top-4 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-[var(--color-bg-primary)]/90 backdrop-blur text-[var(--color-text-primary)] border border-[var(--color-border)] shadow-sm transition-all hover:bg-[var(--color-bg-secondary)] hover:scale-110 focus:outline-none"
            :class="{ 'text-[var(--color-danger)] border-[var(--color-danger)]/30 bg-[var(--color-danger)]/10': wishlist.includes(service.id) }"
            title="Add to wishlist"
          >
            <svg xmlns="http://www.w3.org/2000/svg" :fill="wishlist.includes(service.id) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
          </button>

          <!-- Content -->
          <div class="flex flex-1 flex-col p-6">
            <!-- Header Row: Title & Barber vs Price & Time -->
            <div class="mb-3 flex items-start justify-between gap-4">
              <RouterLink :to="`/customer/dashboard/services/${service.id}`" class="block min-w-0">
                <h2 class="font-display text-xl font-bold text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors truncate">{{ service.name }}</h2>
                <p v-if="service.barbers && service.barbers.length" class="mt-1 text-xs font-semibold text-[var(--color-accent)]/90 flex items-center gap-1.5 flex-wrap">
                  <span class="h-px w-3 bg-[var(--color-accent)]/50 inline-block"></span>
                  Barbers: {{ service.barbers.map(b => b.name).join(', ') }}
                </p>
                <p v-else-if="service.barber?.name" class="mt-1 text-xs font-semibold text-[var(--color-accent)]/90 flex items-center gap-1.5">
                  <span class="h-px w-3 bg-[var(--color-accent)]/50 inline-block"></span>
                  by {{ service.barber.name }}
                </p>
              </RouterLink>
              <div class="flex shrink-0 flex-col items-end text-right">
                <span class="font-display text-lg font-bold text-[var(--color-accent)] drop-shadow-sm">{{ formatNairaAmount(service.price) }}</span>
                <span class="text-[10px] uppercase tracking-widest text-[var(--color-text-muted)] mt-1 flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>{{ service.duration_minutes }} mins</span>
              </div>
            </div>
            
            <p class="text-sm leading-relaxed text-[var(--color-text-secondary)] line-clamp-2 flex-1">
              {{ service.description || 'Premium grooming service.' }}
            </p>

            <!-- Action Buttons -->
            <div class="mt-6 pt-5 border-t border-[var(--color-border)] flex gap-3">
              <RouterLink 
                :to="`/customer/dashboard/services/${service.id}`" 
                class="flex flex-1 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-3 py-3 text-sm font-bold text-[var(--color-text-primary)] transition-all hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] hover:scale-[1.02]"
              >
                Details
              </RouterLink>
              <RouterLink 
                :to="`/customer/dashboard/book/${service.id}`" 
                class="flex min-h-[30px] flex-[2] items-center justify-center gap-1 rounded-md bg-[var(--color-accent)] px-2.5 py-2 text-[10px] font-bold text-[var(--color-accent-foreground)] transition-all hover:bg-[var(--color-accent-hover)] hover:scale-[1.01]"
              >
                Book Now
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
              </RouterLink>
            </div>
          </div>
        </article>
      </div>

      <!-- Empty State -->
      <div v-else class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] py-24 text-center backdrop-blur-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-[var(--color-text-muted)]/50 mb-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />
        </svg>
        <h3 class="text-xl font-display text-[var(--color-text-primary)] mb-2">No services found</h3>
        <p class="text-[var(--color-text-secondary)] text-sm">There are currently no services available under this category.</p>
        <button @click="changeCategory('All')" class="mt-6 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-6 py-2.5 text-sm font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition focus:outline-none">View all services</button>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { RouterLink } from 'vue-router';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { publicApi } from '@/shared/api/old_publicApi';
import { customerApi } from '@/shared/api/old_customerApi';
import { useToast } from '../../../core/composables/useToast';
import { formatNairaAmount } from '../../../core/utils';

const toast = useToast();
const pageTitle = ref('Our Services');
const pageSubtitle = ref('Explore our menu of premium grooming services and book your next appointment directly.');
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

async function toggleWishlist(id) {
  const serviceName = services.value.find(s => s.id === id)?.name || 'Item';
  
  if (wishlist.value.includes(id)) {
    // Optimistic UI update
    wishlist.value = wishlist.value.filter(itemId => itemId !== id);
    try {
      await customerApi.removeFromWishlistByType('service', id);
      toast.info(`${serviceName} removed from wishlist`);
    } catch (err) {
      // Revert if failed
      wishlist.value.push(id);
      toast.error('Failed to remove from wishlist');
    }
  } else {
    // Optimistic UI update
    wishlist.value.push(id);
    try {
      await customerApi.addToWishlist({ item_type: 'service', item_id: id });
      toast.success(`${serviceName} added to wishlist`);
    } catch (err) {
      // Revert if failed
      wishlist.value = wishlist.value.filter(itemId => itemId !== id);
      toast.error('Failed to add to wishlist');
    }
  }
}

onMounted(async () => {
  try {
    const [settingsRes, servicesRes, wishlistRes] = await Promise.all([
      publicApi.settings().catch(() => ({ data: { data: {} } })),
      publicApi.services(),
      customerApi.getWishlist().catch(() => ({ data: [] }))
    ]);

    const settings = settingsRes.data?.data;
    if (settings) {
      if (settings.services_title) pageTitle.value = settings.services_title;
      if (settings.services_subtitle) pageSubtitle.value = settings.services_subtitle;
    }
    
    services.value = servicesRes.data.data;
    
    // Map wishlist to array of service IDs
    const dbWishlist = wishlistRes?.data?.data || (Array.isArray(wishlistRes?.data) ? wishlistRes.data : []);
    wishlist.value = dbWishlist
      .filter(item => item.item_type === 'service')
      .map(item => item.item_id);
      
  } catch (error) {
    console.error('Failed to load data:', error);
  } finally {
    isLoading.value = false;
  }
});
</script>
