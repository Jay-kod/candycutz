<template>
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <!-- Filter Tabs -->
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
        <component :is="tab.icon" v-if="tab.icon" class="h-3.5 w-3.5" />
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

    <!-- Right Controls: Channel Filter & Sort Selector -->
    <div class="flex flex-wrap items-center gap-2.5 shrink-0">
      <!-- Channel / Auth Filter -->
      <div class="relative">
        <select 
          :value="channelFilter"
          @change="$emit('update:channelFilter', $event.target.value)"
          class="appearance-none bg-white/[0.03] border border-white/[0.08] text-xs font-semibold text-white/90 rounded-xl py-2 pl-3 pr-8 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.06] cursor-pointer"
        >
          <option value="all" class="bg-[#12161C] text-white">All Channels</option>
          <option value="app" class="bg-[#12161C] text-white">📱 Mobile App</option>
          <option value="web" class="bg-[#12161C] text-white">🌐 Web Registration</option>
          <option value="social" class="bg-[#12161C] text-white">Google / Social</option>
        </select>
        <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-white/40">
          <ChevronDownIcon class="h-3.5 w-3.5" />
        </div>
      </div>

      <!-- Sort Dropdown -->
      <div class="relative">
        <select 
          :value="sortOrder"
          @change="$emit('update:sortOrder', $event.target.value)"
          class="appearance-none bg-white/[0.03] border border-white/[0.08] text-xs font-semibold text-white/90 rounded-xl py-2 pl-3 pr-8 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.06] cursor-pointer"
        >
          <option value="default" class="bg-[#12161C] text-white">Default Ranking</option>
          <option value="highest_spent" class="bg-[#12161C] text-white">Highest Spenders</option>
          <option value="most_bookings" class="bg-[#12161C] text-white">Most Bookings</option>
          <option value="newest" class="bg-[#12161C] text-white">Newest Registered</option>
          <option value="name_asc" class="bg-[#12161C] text-white">Name (A → Z)</option>
        </select>
        <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-white/40">
          <ChevronDownIcon class="h-3.5 w-3.5" />
        </div>
      </div>

      <!-- Total Count Chip -->
      <div class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-xl bg-white/[0.02] border border-white/[0.06] text-xs text-white/40 font-mono">
        <span>Base: </span>
        <span class="ml-1 text-white font-bold">{{ totalCustomers }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

defineProps({
  tabs: {
    type: Array,
    required: true
  },
  currentFilter: {
    type: String,
    default: 'all'
  },
  channelFilter: {
    type: String,
    default: 'all'
  },
  sortOrder: {
    type: String,
    default: 'default'
  },
  totalCustomers: {
    type: Number,
    default: 0
  }
});

defineEmits(['update:currentFilter', 'update:channelFilter', 'update:sortOrder']);
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