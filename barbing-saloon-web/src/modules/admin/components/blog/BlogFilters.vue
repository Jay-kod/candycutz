<template>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-2xl border border-white/[0.08] bg-[#12161C] shadow-lg backdrop-blur-xl">
    <!-- Left: Status Tabs -->
    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-white/[0.03] border border-white/[0.06] overflow-x-auto max-w-full">
      <!-- Tab: All -->
      <button
        @click="$emit('update:statusFilter', 'all')"
        class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0"
        :class="statusFilter === 'all' 
          ? 'bg-admin text-obsidian shadow-sm' 
          : 'text-white/60 hover:text-white hover:bg-white/[0.04]'"
      >
        <span>All Posts</span>
        <span 
          class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold"
          :class="statusFilter === 'all' ? 'bg-obsidian/30 text-obsidian' : 'bg-white/10 text-white/50'"
        >
          {{ stats.totalCount }}
        </span>
      </button>

      <!-- Tab: Published -->
      <button
        @click="$emit('update:statusFilter', 'published')"
        class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0"
        :class="statusFilter === 'published' 
          ? 'bg-emerald-500 text-obsidian shadow-sm' 
          : 'text-white/60 hover:text-white hover:bg-white/[0.04]'"
      >
        <span class="h-1.5 w-1.5 rounded-full" :class="statusFilter === 'published' ? 'bg-obsidian' : 'bg-emerald-400'"></span>
        <span>Published</span>
        <span 
          class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold"
          :class="statusFilter === 'published' ? 'bg-obsidian/30 text-obsidian' : 'bg-white/10 text-white/50'"
        >
          {{ stats.publishedCount }}
        </span>
      </button>

      <!-- Tab: Drafts -->
      <button
        @click="$emit('update:statusFilter', 'draft')"
        class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0"
        :class="statusFilter === 'draft' 
          ? 'bg-amber-500 text-obsidian shadow-sm' 
          : 'text-white/60 hover:text-white hover:bg-white/[0.04]'"
      >
        <span class="h-1.5 w-1.5 rounded-full" :class="statusFilter === 'draft' ? 'bg-obsidian' : 'bg-amber-400'"></span>
        <span>Drafts Queue</span>
        <span 
          class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold"
          :class="statusFilter === 'draft' ? 'bg-obsidian/30 text-obsidian' : 'bg-white/10 text-white/50'"
        >
          {{ stats.draftsCount }}
        </span>
      </button>
    </div>

    <!-- Right: Author Filter & Sort Selectors -->
    <div class="flex flex-wrap items-center gap-3">
      <!-- Author Selector -->
      <div v-if="authors.length > 0" class="flex items-center gap-2">
        <label for="author-select" class="text-xs text-white/40 hidden sm:inline">Author:</label>
        <select
          id="author-select"
          :value="authorFilter"
          @change="$emit('update:authorFilter', $event.target.value)"
          class="bg-white/[0.04] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3 py-2 outline-none focus:border-admin/50 transition-colors cursor-pointer"
        >
          <option value="all" class="bg-[#12161C] text-white">All Authors</option>
          <option 
            v-for="author in authors" 
            :key="author" 
            :value="author"
            class="bg-[#12161C] text-white"
          >
            {{ author }}
          </option>
        </select>
      </div>

      <!-- Sort Order -->
      <div class="flex items-center gap-2">
        <label for="sort-select" class="text-xs text-white/40 hidden sm:inline">Sort:</label>
        <select
          id="sort-select"
          :value="sortOrder"
          @change="$emit('update:sortOrder', $event.target.value)"
          class="bg-white/[0.04] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3 py-2 outline-none focus:border-admin/50 transition-colors cursor-pointer"
        >
          <option value="newest" class="bg-[#12161C] text-white">Newest First</option>
          <option value="oldest" class="bg-[#12161C] text-white">Oldest First</option>
          <option value="title-asc" class="bg-[#12161C] text-white">Title: A to Z</option>
          <option value="most-liked" class="bg-[#12161C] text-white">Most Reactions</option>
        </select>
      </div>

      <!-- Reset Filters Button -->
      <button
        v-if="hasActiveFilters"
        @click="$emit('reset')"
        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-white/10 bg-white/5 text-xs text-white/60 hover:text-white hover:bg-white/10 transition-colors"
        title="Reset all filters"
      >
        <ArrowPathIcon class="h-3.5 w-3.5" />
        <span class="hidden xs:inline">Reset</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ArrowPathIcon } from '@heroicons/vue/24/outline';

defineProps({
  statusFilter: {
    type: String,
    default: 'all'
  },
  authorFilter: {
    type: String,
    default: 'all'
  },
  sortOrder: {
    type: String,
    default: 'newest'
  },
  authors: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    required: true
  },
  hasActiveFilters: {
    type: Boolean,
    default: false
  }
});

defineEmits(['update:statusFilter', 'update:authorFilter', 'update:sortOrder', 'reset']);
</script>

