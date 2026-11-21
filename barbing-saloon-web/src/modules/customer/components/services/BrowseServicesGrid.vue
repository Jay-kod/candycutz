<template>
  <div>
    <!-- Services Grid -->
    <div v-if="services.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <BrowseServiceCard
        v-for="service in services"
        :key="service.id"
        :service="service"
        :is-wishlisted="wishlist.includes(Number(service.id))"
        :is-toggling="isTogglingWishlist === Number(service.id)"
        @toggle-wishlist="$emit('toggle-wishlist', $event)"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] py-20 px-6 text-center backdrop-blur-sm">
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-[var(--color-accent)] mb-4 shadow-inner">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
        </svg>
      </div>
      <h3 class="text-xl font-display text-[var(--color-text-primary)] mb-2">No matching services</h3>
      <p class="text-sm text-[var(--color-text-secondary)] max-w-md mx-auto">
        We couldn't find any services matching your current search or category filter. Try selecting a different category or clearing search terms.
      </p>
      <button 
        type="button"
        @click="$emit('reset-filters')"
        class="mt-6 inline-flex items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] px-6 py-2.5 text-xs font-bold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-all shadow-sm"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        Reset All Filters
      </button>
    </div>
  </div>
</template>

<script setup>
import BrowseServiceCard from './BrowseServiceCard.vue';

defineProps({
  services: {
    type: Array,
    default: () => []
  },
  wishlist: {
    type: Array,
    default: () => []
  },
  isTogglingWishlist: {
    type: [Number, String, null],
    default: null
  }
});

defineEmits(['toggle-wishlist', 'reset-filters']);
</script>

