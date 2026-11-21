<template>
  <div class="space-y-4">
    <!-- Active Items List -->
    <template v-if="items.length > 0">
      <div class="space-y-4">
        <BarberReviewCard
          v-for="item in items"
          :key="item.id"
          :review="item"
          :format-date="formatDate"
        />
      </div>
    </template>

    <!-- Empty State -->
    <div
      v-else
      class="rounded-3xl border border-dashed border-theme-border/60 p-12 text-center bg-theme-surface/40 backdrop-blur-sm"
    >
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)] mb-4 shadow-lg">
        <ChatBubbleBottomCenterTextIcon class="h-8 w-8" />
      </div>

      <template v-if="hasActiveFilters">
        <h3 class="font-display text-lg font-bold text-theme-text">No Matching Reviews</h3>
        <p class="mt-1.5 text-xs text-theme-text-muted max-w-sm mx-auto leading-relaxed">
          No service testimonials matched your current search or filter combination.
        </p>
        <button
          type="button"
          @click="$emit('resetFilters')"
          class="mt-5 inline-flex items-center gap-2 rounded-xl bg-theme-surface border border-theme-border px-4 py-2 text-xs font-semibold text-theme-text hover:bg-theme-surface-subtle transition-all shadow-sm"
        >
          <ArrowPathIcon class="h-3.5 w-3.5" />
          Clear All Filters
        </button>
      </template>

      <template v-else>
        <h3 class="font-display text-lg font-bold text-theme-text">No Service Reviews Yet</h3>
        <p class="mt-1.5 text-xs text-theme-text-muted max-w-md mx-auto leading-relaxed">
          When clients complete bookings for your services or review your craft, their testimonials and ratings will appear here in real-time.
        </p>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ChatBubbleBottomCenterTextIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import BarberReviewCard from './BarberReviewCard.vue';

defineProps({
  items: { type: Array, default: () => [] },
  hasActiveFilters: { type: Boolean, default: false },
  search: { type: String, default: '' },
  formatDate: { type: Function, required: true },
});

defineEmits(['resetFilters']);
</script>

