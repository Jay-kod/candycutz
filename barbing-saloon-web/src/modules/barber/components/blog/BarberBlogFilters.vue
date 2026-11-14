<template>
  <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-4 sm:p-5 shadow-sm dark:shadow-xl flex flex-col lg:flex-row lg:items-center justify-between gap-4 backdrop-blur-xl">
    <!-- Left: Status Filter Tabs -->
    <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-100 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] overflow-x-auto max-w-full shrink-0">
      <!-- Tab: All -->
      <button
        type="button"
        @click="$emit('update:statusFilter', 'all')"
        class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shrink-0"
        :class="
          statusFilter === 'all'
            ? 'bg-gradient-to-r from-gold via-amber-400 to-amber-500 text-obsidian shadow-md'
            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-white/[0.04]'
        "
        data-test="filter-all-btn"
      >
        <span>All</span>
        <span
          class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
          :class="statusFilter === 'all' ? 'bg-obsidian/25 text-obsidian' : 'bg-slate-200 dark:bg-white/10 text-slate-700 dark:text-slate-300'"
        >
          {{ stats.totalCount }}
        </span>
      </button>

      <!-- Tab: Published -->
      <button
        type="button"
        @click="$emit('update:statusFilter', 'published')"
        class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shrink-0"
        :class="
          statusFilter === 'published'
            ? 'bg-emerald-500 text-obsidian shadow-md'
            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-white/[0.04]'
        "
        data-test="filter-published-btn"
      >
        <span
          class="h-1.5 w-1.5 rounded-full"
          :class="statusFilter === 'published' ? 'bg-obsidian' : 'bg-emerald-500'"
        ></span>
        <span>Live</span>
        <span
          class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
          :class="statusFilter === 'published' ? 'bg-obsidian/25 text-obsidian' : 'bg-slate-200 dark:bg-white/10 text-slate-700 dark:text-slate-300'"
        >
          {{ stats.publishedCount }}
        </span>
      </button>

      <!-- Tab: Drafts -->
      <button
        type="button"
        @click="$emit('update:statusFilter', 'draft')"
        class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all shrink-0"
        :class="
          statusFilter === 'draft'
            ? 'bg-amber-400 text-obsidian shadow-md'
            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-white/[0.04]'
        "
        data-test="filter-draft-btn"
      >
        <span
          class="h-1.5 w-1.5 rounded-full"
          :class="statusFilter === 'draft' ? 'bg-obsidian' : 'bg-amber-500'"
        ></span>
        <span>Drafts</span>
        <span
          class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold"
          :class="statusFilter === 'draft' ? 'bg-obsidian/25 text-obsidian' : 'bg-slate-200 dark:bg-white/10 text-slate-700 dark:text-slate-300'"
        >
          {{ stats.draftsCount }}
        </span>
      </button>
    </div>

    <!-- Right: Search, Sort & View Mode Controls -->
    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
      <!-- Search Input -->
      <div class="relative flex-1 sm:w-64 sm:flex-initial">
        <MagnifyingGlassIcon class="h-4 w-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
        <input
          type="text"
          :value="searchQuery"
          @input="$emit('update:searchQuery', $event.target.value)"
          placeholder="Search articles..."
          class="w-full rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] pl-9.5 pr-8 py-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all"
        />
        <button
          v-if="searchQuery"
          type="button"
          @click="$emit('update:searchQuery', '')"
          class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-900 dark:hover:text-white p-0.5 rounded-md"
          title="Clear search"
        >
          <XMarkIcon class="h-3.5 w-3.5" />
        </button>
      </div>

      <!-- Sort Dropdown -->
      <div class="flex items-center gap-2">
        <select
          :value="sortOrder"
          @change="$emit('update:sortOrder', $event.target.value)"
          class="rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] px-3.5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 outline-none focus:border-gold/50 transition-all cursor-pointer"
        >
          <option value="newest" class="bg-white dark:bg-[#12161C] text-slate-900 dark:text-white">Newest First</option>
          <option value="oldest" class="bg-white dark:bg-[#12161C] text-slate-900 dark:text-white">Oldest First</option>
          <option value="title-asc" class="bg-white dark:bg-[#12161C] text-slate-900 dark:text-white">Title: A to Z</option>
          <option value="most-liked" class="bg-white dark:bg-[#12161C] text-slate-900 dark:text-white">Most Loved</option>
        </select>
      </div>

      <!-- View Mode Buttons (Grid vs List) -->
      <div class="flex items-center p-1 rounded-2xl bg-slate-100 dark:bg-[#0B0F13] border border-slate-200 dark:border-white/[0.08]">
        <button
          type="button"
          @click="$emit('update:viewMode', 'grid')"
          class="p-1.5 rounded-xl transition-colors"
          :class="viewMode === 'grid' ? 'bg-gold/20 text-amber-800 dark:text-gold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
          title="Grid View"
          data-test="view-grid-btn"
        >
          <Squares2X2Icon class="h-4 w-4" />
        </button>
        <button
          type="button"
          @click="$emit('update:viewMode', 'list')"
          class="p-1.5 rounded-xl transition-colors"
          :class="viewMode === 'list' ? 'bg-gold/20 text-amber-800 dark:text-gold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
          title="List View"
          data-test="view-list-btn"
        >
          <Bars3Icon class="h-4 w-4" />
        </button>
      </div>

      <!-- Reset Filters Button -->
      <button
        v-if="hasActiveFilters"
        type="button"
        @click="$emit('reset')"
        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-2xl border border-gold/30 bg-gold/10 text-xs font-semibold text-amber-800 dark:text-gold hover:bg-gold/20 transition-colors"
        title="Reset all active filters"
        data-test="reset-filters-btn"
      >
        <ArrowPathIcon class="h-3.5 w-3.5" />
        <span>Reset</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import {
  MagnifyingGlassIcon,
  XMarkIcon,
  Squares2X2Icon,
  Bars3Icon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  searchQuery: {
    type: String,
    default: '',
  },
  statusFilter: {
    type: String,
    default: 'all',
  },
  sortOrder: {
    type: String,
    default: 'newest',
  },
  viewMode: {
    type: String,
    default: 'grid',
  },
  stats: {
    type: Object,
    required: true,
  },
  hasActiveFilters: {
    type: Boolean,
    default: false,
  },
});

defineEmits([
  'update:searchQuery',
  'update:statusFilter',
  'update:sortOrder',
  'update:viewMode',
  'reset',
]);
</script>
