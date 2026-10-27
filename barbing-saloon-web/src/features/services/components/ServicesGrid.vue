<template>
  <div>
    <!-- Empty State -->
    <div 
      v-if="services.length === 0" 
      class="rounded-3xl border border-white/[0.08] bg-[#12161C] p-12 text-center flex flex-col items-center justify-center min-h-[380px] shadow-xl"
    >
      <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white/[0.03] border border-white/[0.08] mb-5">
        <SparklesIcon class="h-8 w-8 text-white/30" />
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

        <router-link
          to="/admin/services/new"
          class="rounded-xl px-5 py-2.5 text-xs font-bold bg-gradient-to-r from-admin to-amber-500 text-obsidian shadow-[0_4px_15px_rgba(255,103,0,0.3)] hover:shadow-[0_6px_20px_rgba(255,103,0,0.4)] transition-all flex items-center gap-1.5"
        >
          <PlusIcon class="h-4 w-4" />
          <span>Add New Service</span>
        </router-link>
      </div>
    </div>

    <!-- Active Services Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <ServiceCard
        v-for="service in services"
        :key="service.id"
        :service="service"
        :approving-id="approvingId"
        :toggling-id="togglingId"
        @approve="$emit('approve', $event)"
        @reject="$emit('reject', $event)"
        @toggle-active="$emit('toggle-active', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { SparklesIcon, PlusIcon } from '@heroicons/vue/24/outline';
import ServiceCard from './ServiceCard.vue';

const props = defineProps({
  services: {
    type: Array,
    required: true
  },
  totalServices: {
    type: Number,
    default: 0
  },
  search: {
    type: String,
    default: ''
  },
  approvalFilter: {
    type: String,
    default: 'all'
  },
  activeCategory: {
    type: String,
    default: 'all'
  },
  approvingId: {
    type: [Number, String],
    default: null
  },
  togglingId: {
    type: [Number, String],
    default: null
  }
});

defineEmits(['approve', 'reject', 'toggle-active', 'delete', 'reset-filters']);

const hasActiveFilters = computed(() => {
  return props.search.trim() !== '' || props.approvalFilter !== 'all' || props.activeCategory !== 'all';
});

const emptyHeading = computed(() => {
  if (props.search.trim()) return 'No Matching Services Found';
  if (props.approvalFilter === 'pending') return 'No Pending Submissions';
  if (props.approvalFilter === 'approved') return 'No Approved Services';
  if (props.approvalFilter === 'active') return 'No Active Services';
  if (props.approvalFilter === 'inactive') return 'No Inactive Services';
  if (props.activeCategory !== 'all') return `No Services in "${props.activeCategory}"`;
  return 'No Services Registered';
});

const emptyDescription = computed(() => {
  if (props.search.trim()) {
    return `No salon offerings matched your query "${props.search}". Try refining your keywords or clearing the search filter.`;
  }
  if (props.approvalFilter === 'pending') {
    return 'All barber-submitted services have already been reviewed and resolved.';
  }
  if (props.activeCategory !== 'all') {
    return `There are currently no treatments assigned to the category "${props.activeCategory}".`;
  }
  return 'Your salon catalog is currently empty. Create your first service offering to start receiving customer bookings.';
});
</script>
