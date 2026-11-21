<template>
  <div>
    <!-- Empty State -->
    <div
      v-if="items.length === 0"
      class="rounded-3xl border border-theme-border bg-theme-surface p-12 text-center flex flex-col items-center justify-center min-h-[360px] shadow-xl"
    >
      <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-theme-surface-subtle border border-theme-border mb-5">
        <ChatBubbleBottomCenterTextIcon class="h-8 w-8 text-theme-text-muted" />
      </div>

      <h3 class="text-xl font-bold text-theme-text mb-2">
        {{ emptyHeading }}
      </h3>

      <p class="text-xs text-theme-text-muted max-w-md mx-auto mb-6 leading-relaxed">
        {{ emptyDescription }}
      </p>

      <div v-if="hasActiveFilters">
        <button
          @click="$emit('reset-filters')"
          class="rounded-xl px-5 py-2.5 text-xs font-bold bg-theme-surface-subtle text-theme-text hover:bg-theme-surface transition-all border border-theme-border"
        >
          Reset All Filters
        </button>
      </div>
    </div>

    <!-- Active List -->
    <div v-else class="grid gap-5">
      <AdminTestimonialCard
        v-for="item in items"
        :key="item.id"
        :item="item"
        :is-moderating="moderatingIds.has(item.id)"
        @toggle-approval="$emit('toggle-approval', $event)"
        @toggle-visibility="$emit('toggle-visibility', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChatBubbleBottomCenterTextIcon } from '@heroicons/vue/24/outline';
import AdminTestimonialCard from '../AdminTestimonialCard.vue';

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  moderatingIds: {
    type: Object,
    default: () => new Set()
  },
  hasActiveFilters: {
    type: Boolean,
    default: false
  },
  search: {
    type: String,
    default: ''
  },
  activeStatusFilter: {
    type: String,
    default: 'all'
  }
});

defineEmits(['toggle-approval', 'toggle-visibility', 'delete', 'reset-filters']);

const emptyHeading = computed(() => {
  if (props.search.trim()) return 'No Matching Reviews Found';
  if (props.activeStatusFilter === 'pending') return 'No Reviews Pending Moderation';
  if (props.activeStatusFilter === 'approved') return 'No Approved Reviews';
  if (props.activeStatusFilter === 'featured') return 'No Reviews Featured on Homepage Ribbon';
  return 'No Customer Testimonials Yet';
});

const emptyDescription = computed(() => {
  if (props.search.trim()) {
    return `No client feedback matched your search query "${props.search}". Try clearing your search keyword or resetting filters.`;
  }
  if (props.activeStatusFilter === 'pending') {
    return 'All client reviews have already been reviewed and approved or moderated.';
  }
  if (props.activeStatusFilter === 'featured') {
    return 'There are currently no reviews set to drift on the public homepage marquee ribbon. Feature approved reviews to display them.';
  }
  return 'When clients leave reviews after appointments, they will appear here for admin moderation and website ribbon selection.';
});
</script>
