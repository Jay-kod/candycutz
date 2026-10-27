<template>
  <div class="space-y-6">
    <!-- Empty State -->
    <div
      v-if="items.length === 0"
      class="rounded-3xl border border-white/[0.08] bg-[#12161C] p-12 text-center flex flex-col items-center justify-center min-h-[360px] shadow-xl"
    >
      <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white/[0.03] border border-white/[0.08] mb-5">
        <ChatBubbleLeftRightIcon class="h-8 w-8 text-white/30" />
      </div>

      <h3 class="text-xl font-bold text-white mb-2">
        {{ emptyHeading }}
      </h3>

      <p class="text-xs text-white/40 max-w-md mx-auto mb-6 leading-relaxed">
        {{ emptyDescription }}
      </p>

      <div v-if="hasActiveFilters">
        <button
          @click="$emit('reset-filters')"
          class="rounded-xl px-5 py-2.5 text-xs font-bold bg-white/10 text-white hover:bg-white/20 transition-all border border-white/10"
        >
          Reset All Filters
        </button>
      </div>
    </div>

    <!-- Submissions List -->
    <div v-else class="space-y-5">
      <FeedbackCard
        v-for="item in items"
        :key="item.id"
        :item="item"
        :draft="drafts[item.id] || { status: item.status, admin_note: item.admin_note || '' }"
        :saving-id="savingId"
        :statuses="statuses"
        @save="$emit('save', $event)"
      />
    </div>

    <!-- Luxury Pagination Bar -->
    <div 
      v-if="lastPage > 1" 
      class="flex flex-col sm:flex-row items-center justify-between gap-4 rounded-2xl border border-white/[0.08] bg-[#12161C] p-4 shadow-lg"
    >
      <p class="text-xs text-white/50 font-mono">
        Showing Page <span class="text-white font-bold">{{ page }}</span> of <span class="text-white font-bold">{{ lastPage }}</span>
      </p>

      <div class="flex items-center gap-2">
        <button
          type="button"
          class="flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-bold text-white hover:bg-white/10 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
          :disabled="page <= 1 || loading"
          @click="$emit('page-change', page - 1)"
        >
          <ChevronLeftIcon class="h-4 w-4" />
          <span>Previous</span>
        </button>

        <button
          type="button"
          class="flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-bold text-white hover:bg-white/10 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
          :disabled="page >= lastPage || loading"
          @click="$emit('page-change', page + 1)"
        >
          <span>Next</span>
          <ChevronRightIcon class="h-4 w-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  ChatBubbleLeftRightIcon, 
  ChevronLeftIcon, 
  ChevronRightIcon 
} from '@heroicons/vue/24/outline';
import FeedbackCard from './FeedbackCard.vue';

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  drafts: {
    type: Object,
    required: true
  },
  savingId: {
    type: [Number, String],
    default: null
  },
  statuses: {
    type: Array,
    required: true
  },
  hasActiveFilters: {
    type: Boolean,
    default: false
  },
  search: {
    type: String,
    default: ''
  },
  activeStatus: {
    type: String,
    default: 'all'
  },
  activeCategory: {
    type: String,
    default: 'all'
  },
  page: {
    type: Number,
    default: 1
  },
  lastPage: {
    type: Number,
    default: 1
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['save', 'page-change', 'reset-filters']);

const emptyHeading = computed(() => {
  if (props.search.trim()) return 'No Matching Inquiries';
  if (props.activeStatus === 'new') return 'No Fresh Submissions Awaiting Review';
  if (props.activeStatus === 'reviewing') return 'No Inquiries Currently Under Review';
  if (props.activeStatus === 'planned') return 'No Planned Inquiries';
  if (props.activeStatus === 'completed') return 'No Completed Submissions';
  if (props.activeCategory !== 'all') return 'No Submissions for Selected Topic';
  return 'No Feedback Submissions Found';
});

const emptyDescription = computed(() => {
  if (props.search.trim()) {
    return `No customer feedback reports matched your search keyword "${props.search}". Try clearing your search keyword or resetting filters.`;
  }
  if (props.activeStatus === 'new') {
    return 'All incoming client feedback has been reviewed and triaged by the leadership team.';
  }
  return 'Customer suggestions and maintenance reports will appear here in the private inbox after submission.';
});
</script>

