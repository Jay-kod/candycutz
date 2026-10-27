<template>
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <!-- Left: Status Tabs -->
    <div class="flex items-center gap-1.5 bg-theme-surface p-1.5 rounded-2xl border border-theme-border overflow-x-auto scrollbar-hide max-w-full">
      <button
        @click="$emit('update:activeStatus', 'all')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
        :class="activeStatus === 'all'
          ? 'bg-admin text-obsidian shadow-lg'
          : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-surface-subtle'"
      >
        All ({{ totalItems }})
      </button>

      <button
        @click="$emit('update:activeStatus', 'new')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all flex items-center gap-1.5"
        :class="activeStatus === 'new'
          ? 'bg-amber-500 text-obsidian shadow-lg'
          : 'text-amber-400/90 hover:text-amber-300 hover:bg-amber-500/10'"
      >
        <span v-if="newCount > 0" class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
        New ({{ newCount }})
      </button>

      <button
        @click="$emit('update:activeStatus', 'reviewing')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
        :class="activeStatus === 'reviewing'
          ? 'bg-sky-500 text-obsidian shadow-lg'
          : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-surface-subtle'"
      >
        Reviewing
      </button>

      <button
        @click="$emit('update:activeStatus', 'planned')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
        :class="activeStatus === 'planned'
          ? 'bg-indigo-500 text-white shadow-lg'
          : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-surface-subtle'"
      >
        Planned
      </button>

      <button
        @click="$emit('update:activeStatus', 'completed')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
        :class="activeStatus === 'completed'
          ? 'bg-emerald-500 text-obsidian shadow-lg'
          : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-surface-subtle'"
      >
        Completed
      </button>

      <button
        @click="$emit('update:activeStatus', 'declined')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
        :class="activeStatus === 'declined'
          ? 'bg-theme-surface-subtle text-theme-text shadow-lg'
          : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-surface-subtle'"
      >
        Declined
      </button>
    </div>

    <!-- Right: Dropdown Controls (Topic Category & Sort) -->
    <div class="flex items-center gap-2.5 flex-wrap">
      <!-- Topic Category Selector -->
      <div class="relative">
        <select
          :value="activeCategory"
          @change="$emit('update:activeCategory', $event.target.value)"
          class="appearance-none bg-theme-surface border border-theme-border text-xs text-theme-text rounded-xl px-3.5 py-2 pr-8 outline-none transition-all focus:border-admin/50 cursor-pointer font-medium"
        >
          <option value="all" class="bg-theme-surface text-theme-text">All Topics</option>
          <option value="feature_request" class="bg-theme-surface text-theme-text">Feature Requests</option>
          <option value="maintenance" class="bg-theme-surface text-theme-text">Maintenance</option>
          <option value="improvement" class="bg-theme-surface text-theme-text">Improvements</option>
        </select>
        <ChevronDownIcon class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-theme-text-muted" />
      </div>

      <!-- Sort Selector -->
      <div class="relative">
        <select
          :value="sortOrder"
          @change="$emit('update:sortOrder', $event.target.value)"
          class="appearance-none bg-theme-surface border border-theme-border text-xs text-theme-text rounded-xl px-3.5 py-2 pr-8 outline-none transition-all focus:border-admin/50 cursor-pointer font-medium"
        >
          <option value="newest" class="bg-theme-surface text-theme-text">Newest First</option>
          <option value="oldest" class="bg-theme-surface text-theme-text">Oldest First</option>
          <option value="name-asc" class="bg-theme-surface text-theme-text">Title: A to Z</option>
        </select>
        <ChevronDownIcon class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-theme-text-muted" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

defineProps({
  activeStatus: {
    type: String,
    default: 'all'
  },
  activeCategory: {
    type: String,
    default: 'all'
  },
  sortOrder: {
    type: String,
    default: 'newest'
  },
  totalItems: {
    type: Number,
    default: 0
  },
  newCount: {
    type: Number,
    default: 0
  }
});

defineEmits(['update:activeStatus', 'update:activeCategory', 'update:sortOrder']);
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
