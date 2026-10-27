<template>
  <div>
    <!-- List of Feature Flags -->
    <div 
      v-if="flags.length > 0"
      class="space-y-3"
    >
      <FeatureFlagCard
        v-for="flag in flags"
        :key="flag.id"
        :flag="flag"
        :is-toggling="togglingId === flag.id"
        @toggle="$emit('toggle', $event)"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>

    <!-- Empty State: Search Query -->
    <div 
      v-else-if="searchQuery"
      class="flex flex-col items-center justify-center py-20 px-4 text-center rounded-3xl border border-dashed border-white/10 bg-[#12161C]/50 backdrop-blur-sm"
    >
      <div class="h-16 w-16 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center text-white/40 mb-4">
        <MagnifyingGlassIcon class="h-8 w-8" />
      </div>
      <h3 class="font-display text-xl text-white font-semibold mb-2">No Matching Feature Flags</h3>
      <p class="text-sm text-white/50 max-w-md mb-6">
        No flags matched your search query "<span class="text-white font-medium">{{ searchQuery }}</span>". Try searching by another key or title.
      </p>
      <button
        @click="$emit('reset')"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white hover:bg-white/10 transition-colors"
      >
        <ArrowPathIcon class="h-4 w-4" />
        <span>Clear Search</span>
      </button>
    </div>

    <!-- Empty State: Filter Empty -->
    <div 
      v-else-if="statusFilter !== 'all'"
      class="flex flex-col items-center justify-center py-20 px-4 text-center rounded-3xl border border-dashed border-white/10 bg-[#12161C]/50 backdrop-blur-sm"
    >
      <div class="h-16 w-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 mb-4">
        <FlagIcon class="h-8 w-8" />
      </div>
      <h3 class="font-display text-xl text-white font-semibold mb-2">No Flags Match Filter</h3>
      <p class="text-sm text-white/50 max-w-md mb-6">
        There are currently no feature flags matching the selected status.
      </p>
      <button
        @click="$emit('reset')"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white hover:bg-white/10 transition-colors"
      >
        <span>Reset Filters</span>
      </button>
    </div>

    <!-- Empty State: Zero Flags Overall -->
    <div 
      v-else
      class="flex flex-col items-center justify-center py-24 px-4 text-center rounded-3xl border border-dashed border-white/10 bg-[#12161C]/50 backdrop-blur-sm"
    >
      <div class="h-20 w-20 rounded-3xl bg-admin/10 border border-admin/20 flex items-center justify-center text-admin mb-5 shadow-[0_0_30px_rgba(255,103,0,0.15)]">
        <FlagIcon class="h-10 w-10" />
      </div>
      <h3 class="font-display text-2xl text-white font-semibold mb-2">No Feature Flags Yet</h3>
      <p class="text-sm text-white/50 max-w-md mb-6 leading-relaxed">
        Feature flags give you dynamic, instant control over client releases, experimental features, and selective rollouts without redeploying code.
      </p>
      <button
        @click="$emit('create')"
        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-admin to-admin-light text-obsidian text-xs font-bold shadow-[0_4px_25px_rgba(255,103,0,0.3)] hover:shadow-[0_6px_35px_rgba(255,103,0,0.45)] hover:scale-105 transition-all"
      >
        <PlusIcon class="h-4 w-4 stroke-[2.5]" />
        <span>Create First Flag</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { 
  MagnifyingGlassIcon, 
  ArrowPathIcon, 
  FlagIcon, 
  PlusIcon 
} from '@heroicons/vue/24/outline';
import FeatureFlagCard from './FeatureFlagCard.vue';

defineProps({
  flags: {
    type: Array,
    required: true
  },
  searchQuery: {
    type: String,
    default: ''
  },
  statusFilter: {
    type: String,
    default: 'all'
  },
  togglingId: {
    type: [Number, String],
    default: null
  }
});

defineEmits(['toggle', 'edit', 'delete', 'reset', 'create']);
</script>
