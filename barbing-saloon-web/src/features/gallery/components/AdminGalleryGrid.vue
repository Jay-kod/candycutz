<template>
  <div>
    <!-- Empty State -->
    <div
      v-if="items.length === 0"
      class="rounded-3xl border border-white/[0.08] bg-[#12161C] p-12 text-center flex flex-col items-center justify-center min-h-[380px] shadow-xl"
    >
      <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white/[0.03] border border-white/[0.08] mb-5">
        <PhotoIcon class="h-8 w-8 text-white/30" />
      </div>

      <h3 class="text-xl font-bold text-white mb-2">
        {{ emptyHeading }}
      </h3>

      <p class="text-xs text-white/40 max-w-md mx-auto mb-6 leading-relaxed">
        {{ emptyDescription }}
      </p>

      <div class="flex items-center gap-3">
        <button
          v-if="hasActiveFilters"
          @click="$emit('reset-filters')"
          class="rounded-xl px-4 py-2.5 text-xs font-bold bg-white/10 text-white hover:bg-white/20 transition-all border border-white/10"
        >
          Reset All Filters
        </button>

        <button
          @click="$emit('upload')"
          class="rounded-xl px-5 py-2.5 text-xs font-bold bg-gradient-to-r from-admin to-amber-500 text-obsidian shadow-[0_4px_15px_rgba(255,103,0,0.3)] hover:shadow-[0_6px_20px_rgba(255,103,0,0.4)] transition-all flex items-center gap-1.5"
        >
          <PlusIcon class="h-4 w-4" />
          <span>Upload New Image</span>
        </button>
      </div>
    </div>

    <!-- Active Gallery Grid -->
    <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <AdminGalleryCard
        v-for="item in items"
        :key="item.id"
        :item="item"
        :get-image-url="getImageUrl"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { PhotoIcon, PlusIcon } from '@heroicons/vue/24/outline';
import AdminGalleryCard from './AdminGalleryCard.vue';

const props = defineProps({
  items: {
    type: Array,
    required: true
  },
  search: {
    type: String,
    default: ''
  },
  activeCategory: {
    type: String,
    default: 'all'
  },
  barberFilter: {
    type: [String, Number],
    default: 'all'
  },
  getImageUrl: {
    type: Function,
    required: true
  }
});

defineEmits(['edit', 'delete', 'upload', 'reset-filters']);

const hasActiveFilters = computed(() => {
  return props.search.trim() !== '' || props.activeCategory !== 'all' || props.barberFilter !== 'all';
});

const emptyHeading = computed(() => {
  if (props.search.trim()) return 'No Matching Photos Found';
  if (props.activeCategory !== 'all') return `No Photos in "${props.activeCategory}"`;
  if (props.barberFilter !== 'all') return 'No Photos for Selected Stylist';
  return 'No Gallery Photos Uploaded';
});

const emptyDescription = computed(() => {
  if (props.search.trim()) {
    return `No portfolio items matched your search query "${props.search}". Try refining your keywords or clearing the filter.`;
  }
  if (props.activeCategory !== 'all') {
    return `There are currently no photos categorized under "${props.activeCategory}".`;
  }
  if (props.barberFilter !== 'all') {
    return 'There are currently no portfolio uploads attributed to this stylist.';
  }
  return 'Your salon gallery is currently empty. Upload your best cuts, beard grooming, and shop photography to inspire customers.';
});
</script>
