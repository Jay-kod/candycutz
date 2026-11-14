<template>
  <article 
    class="group relative flex flex-col overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:border-[var(--color-accent)]/30"
  >
    <!-- Image / Media Container -->
    <RouterLink :to="`/customer/dashboard/services/${service.id}`" class="relative aspect-[4/3] w-full overflow-hidden block bg-[var(--color-bg-tertiary)]">
      <img 
        v-if="service.image || service.image_url" 
        :src="service.image || service.image_url" 
        :alt="service.name" 
        class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" 
        style="-webkit-mask-image: linear-gradient(to bottom, black 65%, transparent 100%); mask-image: linear-gradient(to bottom, black 65%, transparent 100%);"
      />
      <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-[var(--color-bg-tertiary)] to-[var(--color-bg-secondary)]" style="-webkit-mask-image: linear-gradient(to bottom, black 65%, transparent 100%); mask-image: linear-gradient(to bottom, black 65%, transparent 100%);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="w-14 h-14 text-[var(--color-accent)]/40">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7.848 8.25l1.536.887M12.152 8.25l-1.536.887m0 0l-3.328 1.921A3.75 3.75 0 005.25 14.298v3.452a.75.75 0 001.125.65l6-3.464a.75.75 0 00.375-.65V12.75m0 0l3.328-1.921a3.75 3.75 0 002.038-3.261V4.116a.75.75 0 00-1.125-.65l-6 3.464a.75.75 0 00-.375.65v1.52m0 0L9.384 10.638" />
        </svg>
      </div>
      
      <!-- Category Badge -->
      <span class="absolute left-3.5 top-3.5 rounded-full bg-[var(--color-bg-primary)]/90 backdrop-blur-md px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[var(--color-accent)] shadow-sm border border-[var(--color-accent)]/20">
        {{ categoryName }}
      </span>

      <!-- Home Service Badge if available -->
      <span 
        v-if="service.is_home_service_eligible" 
        class="absolute left-3.5 bottom-3.5 rounded-full bg-emerald-950/80 backdrop-blur-md px-2.5 py-0.5 text-[9px] font-semibold text-emerald-400 border border-emerald-500/30 shadow-sm flex items-center gap-1"
      >
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
        Home Service
      </span>
    </RouterLink>

    <!-- Wishlist Button -->
    <button 
      type="button"
      @click.prevent="$emit('toggle-wishlist', service.id)"
      :disabled="isToggling"
      class="absolute right-3.5 top-3.5 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-[var(--color-bg-primary)]/90 backdrop-blur-md text-[var(--color-text-primary)] border border-[var(--color-border)] shadow-sm transition-all hover:scale-110 hover:border-[var(--color-accent)]/40 focus:outline-none disabled:opacity-50"
      :class="{ 'text-rose-400 border-rose-500/30 bg-rose-500/10': isWishlisted }"
      :title="isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'"
    >
      <svg xmlns="http://www.w3.org/2000/svg" :fill="isWishlisted ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
      </svg>
    </button>

    <!-- Card Content -->
    <div class="flex flex-1 flex-col p-5 sm:p-6">
      <!-- Title & Price Row -->
      <div class="mb-2 flex items-start justify-between gap-3">
        <RouterLink :to="`/customer/dashboard/services/${service.id}`" class="block min-w-0 flex-1">
          <h2 class="font-display text-lg font-bold text-[var(--color-text-primary)] group-hover:text-[var(--color-accent)] transition-colors truncate">
            {{ service.name }}
          </h2>
          <p v-if="service.barber?.name" class="mt-1 text-xs text-[var(--color-accent)]/90 flex items-center gap-1.5 font-medium">
            <span class="h-1 w-1 rounded-full bg-[var(--color-accent)] inline-block"></span>
            By {{ service.barber.name }}
          </p>
        </RouterLink>
        <div class="flex shrink-0 flex-col items-end text-right">
          <span class="font-display text-lg font-bold text-[var(--color-accent)]">
            {{ formatNairaAmount(service.price) }}
          </span>
          <span class="text-[10px] uppercase tracking-wider text-[var(--color-text-muted)] mt-0.5 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ service.duration_minutes || 30 }} mins
          </span>
        </div>
      </div>
      
      <!-- Description -->
      <p class="text-xs leading-relaxed text-[var(--color-text-secondary)] line-clamp-2 flex-1 mt-1">
        {{ service.description || 'Master barbering grooming service with precision styling.' }}
      </p>

      <!-- Action Footer -->
      <div class="mt-5 pt-4 border-t border-[var(--color-border)] flex gap-2.5">
        <RouterLink 
          :to="`/customer/dashboard/services/${service.id}`" 
          class="flex flex-1 items-center justify-center rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-3 py-2.5 text-xs font-bold text-[var(--color-text-primary)] transition-all hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)]"
        >
          Details
        </RouterLink>
        <RouterLink 
          :to="`/customer/dashboard/book/${service.id}`" 
          class="flex flex-[1.6] items-center justify-center gap-1.5 rounded-xl bg-[var(--color-accent)] px-3 py-2.5 text-xs font-bold text-[var(--color-accent-foreground)] shadow-md shadow-[var(--color-accent)]/15 transition-all hover:bg-[var(--color-accent-hover)] hover:scale-[1.02]"
        >
          Book Now
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="h-3.5 w-3.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
          </svg>
        </RouterLink>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { formatNairaAmount } from '@/core/utils';

const props = defineProps({
  service: {
    type: Object,
    required: true
  },
  isWishlisted: {
    type: Boolean,
    default: false
  },
  isToggling: {
    type: Boolean,
    default: false
  }
});

defineEmits(['toggle-wishlist']);

const categoryName = computed(() => {
  const s = props.service;
  if (!s) return 'Service';
  if (s.category_name) return s.category_name;
  if (typeof s.category === 'string') return s.category;
  if (s.category?.name) return s.category.name;
  return 'Service';
});
</script>

