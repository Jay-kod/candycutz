<template>
  <div class="space-y-4">
    <!-- Top Filter Row: Status Tabs & Quick Date Selectors -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <!-- Status Tabs -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-2 lg:pb-0 hide-scrollbar">
        <button 
          v-for="tab in tabs" 
          :key="tab.value"
          @click="$emit('update:currentFilter', tab.value)"
          class="relative px-3.5 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap overflow-hidden flex items-center gap-2"
          :class="currentFilter === tab.value 
            ? 'bg-admin text-obsidian shadow-[0_2px_10px_rgba(255,103,0,0.3)]' 
            : 'bg-white/[0.03] border border-white/[0.06] text-white/50 hover:text-white hover:bg-white/[0.06]'"
        >
          <span>{{ tab.label }}</span>
          <span 
            v-if="tab.count !== undefined" 
            class="flex items-center justify-center h-4 px-1.5 rounded-full text-[10px] tabular-nums font-mono font-bold" 
            :class="currentFilter === tab.value ? 'bg-black/25 text-obsidian' : 'bg-white/10 text-white/60'"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Quick Date & Dropdown Controls -->
      <div class="flex flex-wrap items-center gap-2.5 shrink-0">
        <!-- Date Scope Pills -->
        <div class="inline-flex rounded-xl bg-white/[0.03] border border-white/[0.08] p-1 gap-1">
          <button
            v-for="d in dateOptions"
            :key="d.value"
            @click="$emit('update:dateFilter', d.value)"
            class="px-2.5 py-1 text-[11px] font-semibold rounded-lg transition-all"
            :class="dateFilter === d.value 
              ? 'bg-white/15 text-white shadow-sm' 
              : 'text-white/40 hover:text-white/80'"
          >
            {{ d.label }}
          </button>
        </div>

        <!-- Source Filter Dropdown -->
        <div class="relative">
          <select 
            :value="sourceFilter"
            @change="$emit('update:sourceFilter', $event.target.value)"
            class="appearance-none bg-white/[0.03] border border-white/[0.08] text-xs font-semibold text-white/90 rounded-xl py-2 pl-3 pr-8 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.06] cursor-pointer"
          >
            <option value="all" class="bg-[#12161C] text-white">All Channels</option>
            <option value="app" class="bg-[#12161C] text-white">📱 Mobile App</option>
            <option value="walk_in" class="bg-[#12161C] text-white">🚶 Walk-In Desk</option>
            <option value="web" class="bg-[#12161C] text-white">🌐 Web Portal</option>
          </select>
          <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-white/40">
            <ChevronDownIcon class="h-3.5 w-3.5" />
          </div>
        </div>

        <!-- Sorting Dropdown -->
        <div class="relative">
          <select 
            :value="sortOrder"
            @change="$emit('update:sortOrder', $event.target.value)"
            class="appearance-none bg-white/[0.03] border border-white/[0.08] text-xs font-semibold text-white/90 rounded-xl py-2 pl-3 pr-8 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.06] cursor-pointer"
          >
            <option value="newest_created" class="bg-[#12161C] text-white">Newest Created</option>
            <option value="oldest_created" class="bg-[#12161C] text-white">Oldest Created</option>
            <option value="upcoming_date" class="bg-[#12161C] text-white">Earliest Schedule</option>
            <option value="past_date" class="bg-[#12161C] text-white">Latest Schedule</option>
          </select>
          <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-white/40">
            <ChevronDownIcon class="h-3.5 w-3.5" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

const dateOptions = [
  { label: 'All Dates', value: 'all' },
  { label: 'Today', value: 'today' },
  { label: 'Tomorrow', value: 'tomorrow' },
  { label: 'This Week', value: 'week' }
];

defineProps({
  tabs: {
    type: Array,
    required: true
  },
  currentFilter: {
    type: String,
    default: 'all'
  },
  dateFilter: {
    type: String,
    default: 'all'
  },
  sourceFilter: {
    type: String,
    default: 'all'
  },
  sortOrder: {
    type: String,
    default: 'newest_created'
  }
});

defineEmits(['update:currentFilter', 'update:dateFilter', 'update:sourceFilter', 'update:sortOrder']);
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>