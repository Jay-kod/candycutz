<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-4 backdrop-blur-xl shadow-xl space-y-3.5">
    <!-- Top Row: Category Tabs & Search Bar -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <!-- Category Tabs -->
      <div class="flex items-center gap-1.5 overflow-x-auto hide-scrollbar">
        <button
          v-for="cat in categories"
          :key="cat.value"
          type="button"
          @click="$emit('update:activeTypeFilter', cat.value)"
          class="rounded-xl px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap"
          :class="[
            activeTypeFilter === cat.value
              ? 'bg-gradient-to-r from-[#FF6700]/20 to-amber-500/20 text-[#FF6700] border border-[#FF6700]/30 shadow-md shadow-[#FF6700]/10'
              : 'text-white/50 hover:text-white hover:bg-white/[0.04]'
          ]"
        >
          {{ cat.label }}
        </button>
      </div>

      <!-- Live Search -->
      <div class="relative w-full sm:w-72">
        <MagnifyingGlassIcon class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-white/30" />
        <input
          :value="search"
          @input="$emit('update:search', $event.target.value)"
          type="text"
          placeholder="Search notifications..."
          class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 py-2 pl-9 pr-8 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50"
        />
        <button
          v-if="search"
          type="button"
          @click="$emit('update:search', '')"
          class="absolute right-2.5 top-1/2 -translate-y-1/2 text-white/40 hover:text-white text-xs"
        >
          ✕
        </button>
      </div>
    </div>

    <!-- Bottom Row: Time Range Pills & Clear Filters -->
    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-white/[0.05]">
      <div class="flex items-center gap-1.5 flex-wrap">
        <span class="text-[10px] uppercase tracking-wider font-semibold text-white/40 mr-1">Time:</span>
        <button
          v-for="time in timeOptions"
          :key="time.value"
          type="button"
          @click="$emit('update:activeTimeFilter', time.value)"
          class="rounded-lg px-2.5 py-1 text-[11px] font-semibold transition-colors"
          :class="[
            activeTimeFilter === time.value
              ? 'bg-white/10 text-white border border-white/20'
              : 'text-white/50 hover:text-white hover:bg-white/[0.04]'
          ]"
        >
          {{ time.label }}
        </button>
      </div>

      <div class="flex items-center gap-3 text-xs">
        <span class="text-white/40 text-[11px]">
          Showing {{ filteredCount }} of {{ totalCount }}
        </span>
        <button
          v-if="hasActiveFilters"
          type="button"
          @click="$emit('clear-filters')"
          class="text-xs font-semibold text-[#FF6700] hover:underline"
        >
          Clear Filters
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const categories = [
  { label: 'All Streams', value: 'all' },
  { label: 'Bookings', value: 'booking' },
  { label: 'Payments', value: 'payment' },
  { label: 'Security', value: 'security' },
  { label: 'System', value: 'system' }
];

const timeOptions = [
  { label: 'All Time', value: 'all' },
  { label: 'Today', value: 'today' },
  { label: 'Yesterday', value: 'yesterday' },
  { label: 'This Week', value: 'week' },
  { label: 'This Month', value: 'month' }
];

defineProps({
  activeTypeFilter: {
    type: String,
    required: true
  },
  activeTimeFilter: {
    type: String,
    required: true
  },
  search: {
    type: String,
    default: ''
  },
  filteredCount: {
    type: Number,
    default: 0
  },
  totalCount: {
    type: Number,
    default: 0
  },
  hasActiveFilters: {
    type: Boolean,
    default: false
  }
});

defineEmits([
  'update:activeTypeFilter',
  'update:activeTimeFilter',
  'update:search',
  'clear-filters'
]);
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

