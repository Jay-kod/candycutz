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
        <span>All Flags</span>
        <span 
          class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold"
          :class="statusFilter === 'all' ? 'bg-obsidian/30 text-obsidian' : 'bg-white/10 text-white/50'"
        >
          {{ stats.totalCount }}
        </span>
      </button>

      <!-- Tab: Active -->
      <button
        @click="$emit('update:statusFilter', 'active')"
        class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0"
        :class="statusFilter === 'active' 
          ? 'bg-emerald-500 text-obsidian shadow-sm' 
          : 'text-white/60 hover:text-white hover:bg-white/[0.04]'"
      >
        <span class="h-1.5 w-1.5 rounded-full" :class="statusFilter === 'active' ? 'bg-obsidian' : 'bg-emerald-400'"></span>
        <span>Active</span>
        <span 
          class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold"
          :class="statusFilter === 'active' ? 'bg-obsidian/30 text-obsidian' : 'bg-white/10 text-white/50'"
        >
          {{ stats.activeCount }}
        </span>
      </button>

      <!-- Tab: Inactive -->
      <button
        @click="$emit('update:statusFilter', 'inactive')"
        class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0"
        :class="statusFilter === 'inactive' 
          ? 'bg-amber-500 text-obsidian shadow-sm' 
          : 'text-white/60 hover:text-white hover:bg-white/[0.04]'"
      >
        <span class="h-1.5 w-1.5 rounded-full" :class="statusFilter === 'inactive' ? 'bg-obsidian' : 'bg-amber-400'"></span>
        <span>Inactive</span>
        <span 
          class="px-1.5 py-0.2 rounded-full text-[10px] font-mono font-bold"
          :class="statusFilter === 'inactive' ? 'bg-obsidian/30 text-obsidian' : 'bg-white/10 text-white/50'"
        >
          {{ stats.inactiveCount }}
        </span>
      </button>
    </div>

    <!-- Right: Platform Filter & Sort Selectors -->
    <div class="flex flex-wrap items-center gap-3">
      <!-- Platform Selector -->
      <div class="flex items-center gap-2">
        <label for="platform-select" class="text-xs text-white/40 hidden sm:inline">Platform:</label>
        <select
          id="platform-select"
          :value="platformFilter"
          @change="$emit('update:platformFilter', $event.target.value)"
          class="bg-white/[0.04] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3 py-2 outline-none focus:border-admin/50 transition-colors cursor-pointer"
        >
          <option value="all" class="bg-[#12161C] text-white">All Platforms</option>
          <option value="web" class="bg-[#12161C] text-white">Web Portal</option>
          <option value="app" class="bg-[#12161C] text-white">Mobile App</option>
          <option value="both" class="bg-[#12161C] text-white">Cross-Platform (Both)</option>
        </select>
      </div>

      <!-- Sort Order Selector -->
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
          <option value="name-asc" class="bg-[#12161C] text-white">Name: A to Z</option>
          <option value="rollout-desc" class="bg-[#12161C] text-white">Rollout %: Highest First</option>
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
  platformFilter: {
    type: String,
    default: 'all'
  },
  sortOrder: {
    type: String,
    default: 'newest'
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

defineEmits(['update:statusFilter', 'update:platformFilter', 'update:sortOrder', 'reset']);
</script>

