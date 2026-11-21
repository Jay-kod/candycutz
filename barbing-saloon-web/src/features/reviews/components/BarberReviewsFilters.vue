<template>
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <!-- Left: Status Tabs -->
    <div class="flex items-center gap-1.5 bg-theme-surface p-1.5 rounded-2xl border border-theme-border overflow-x-auto scrollbar-hide max-w-full">
      <button
        type="button"
        @click="$emit('update:activeStatusFilter', 'all')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
        :class="activeStatusFilter === 'all'
          ? 'bg-[var(--color-accent)] text-white shadow-lg shadow-[var(--color-accent)]/20'
          : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-surface-subtle'"
      >
        All Reviews ({{ totalItems }})
      </button>

      <button
        type="button"
        @click="$emit('update:activeStatusFilter', 'approved')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all flex items-center gap-1.5"
        :class="activeStatusFilter === 'approved'
          ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
          : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-surface-subtle'"
      >
        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
        Published ({{ approvedCount }})
      </button>

      <button
        type="button"
        @click="$emit('update:activeStatusFilter', 'pending')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all flex items-center gap-1.5"
        :class="activeStatusFilter === 'pending'
          ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20'
          : 'text-amber-400/90 hover:text-amber-300 hover:bg-amber-500/10'"
      >
        <span v-if="pendingCount > 0" class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
        Pending Moderation ({{ pendingCount }})
      </button>
    </div>

    <!-- Right: Dropdown Selectors (Service, Rating, Sort, Reset) -->
    <div class="flex items-center gap-2.5 flex-wrap">
      <!-- Service Filter -->
      <div class="relative">
        <select
          :value="activeServiceFilter"
          @change="$emit('update:activeServiceFilter', $event.target.value)"
          class="appearance-none bg-theme-surface border border-theme-border text-xs text-theme-text rounded-xl px-3 py-2 pr-7 outline-none transition-all focus:border-[var(--color-accent)] cursor-pointer font-medium"
        >
          <option value="all" class="bg-[#12161C] text-white">All Services</option>
          <option v-for="s in services" :key="s.id" :value="s.id" class="bg-[#12161C] text-white">{{ s.name }}</option>
        </select>
        <ChevronDownIcon class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-theme-text-muted" />
      </div>

      <!-- Rating Filter -->
      <div class="relative">
        <select
          :value="activeRatingFilter"
          @change="$emit('update:activeRatingFilter', $event.target.value)"
          class="appearance-none bg-theme-surface border border-theme-border text-xs text-theme-text rounded-xl px-3 py-2 pr-7 outline-none transition-all focus:border-[var(--color-accent)] cursor-pointer font-medium"
        >
          <option value="all" class="bg-[#12161C] text-white">All Ratings</option>
          <option value="5" class="bg-[#12161C] text-white">5 Stars ★★★★★</option>
          <option value="4" class="bg-[#12161C] text-white">4 Stars ★★★★☆</option>
          <option value="3" class="bg-[#12161C] text-white">3 Stars ★★★☆☆</option>
          <option value="2" class="bg-[#12161C] text-white">2 Stars ★★☆☆☆</option>
          <option value="1" class="bg-[#12161C] text-white">1 Star ★☆☆☆☆</option>
        </select>
        <ChevronDownIcon class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-theme-text-muted" />
      </div>

      <!-- Sort Order -->
      <div class="relative">
        <select
          :value="sortOrder"
          @change="$emit('update:sortOrder', $event.target.value)"
          class="appearance-none bg-theme-surface border border-theme-border text-xs text-theme-text rounded-xl px-3 py-2 pr-7 outline-none transition-all focus:border-[var(--color-accent)] cursor-pointer font-medium"
        >
          <option value="newest" class="bg-[#12161C] text-white">Newest First</option>
          <option value="oldest" class="bg-[#12161C] text-white">Oldest First</option>
          <option value="highest_rating" class="bg-[#12161C] text-white">Highest Rating</option>
          <option value="lowest_rating" class="bg-[#12161C] text-white">Lowest Rating</option>
        </select>
        <ChevronDownIcon class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-theme-text-muted" />
      </div>

      <!-- Reset Filters Button -->
      <button
        v-if="hasActiveFilters"
        type="button"
        @click="$emit('resetFilters')"
        class="rounded-xl border border-dashed border-theme-border px-3 py-2 text-xs font-semibold text-theme-text-muted hover:text-theme-text hover:border-theme-text-muted/60 transition-all flex items-center gap-1.5"
      >
        <ArrowPathIcon class="h-3.5 w-3.5" />
        Reset
      </button>
    </div>
  </div>
</template>

<script setup>
import { ChevronDownIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

defineProps({
  activeStatusFilter: { type: String, default: 'all' },
  activeServiceFilter: { type: [String, Number], default: 'all' },
  activeRatingFilter: { type: [String, Number], default: 'all' },
  sortOrder: { type: String, default: 'newest' },
  services: { type: Array, default: () => [] },
  totalItems: { type: Number, default: 0 },
  approvedCount: { type: Number, default: 0 },
  pendingCount: { type: Number, default: 0 },
  hasActiveFilters: { type: Boolean, default: false },
});

defineEmits([
  'update:activeStatusFilter',
  'update:activeServiceFilter',
  'update:activeRatingFilter',
  'update:sortOrder',
  'resetFilters',
]);
</script>

