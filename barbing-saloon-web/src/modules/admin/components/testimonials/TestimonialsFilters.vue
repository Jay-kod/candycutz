<template>
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <!-- Left: Status Tabs -->
    <div class="flex items-center gap-1.5 bg-[#12161C] p-1.5 rounded-2xl border border-white/[0.08] overflow-x-auto scrollbar-hide max-w-full">
      <button
        @click="$emit('update:activeStatusFilter', 'all')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
        :class="activeStatusFilter === 'all'
          ? 'bg-admin text-obsidian shadow-lg'
          : 'text-white/50 hover:text-white hover:bg-white/[0.04]'"
      >
        All ({{ totalItems }})
      </button>

      <button
        @click="$emit('update:activeStatusFilter', 'pending')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all flex items-center gap-1.5"
        :class="activeStatusFilter === 'pending'
          ? 'bg-amber-500 text-obsidian shadow-lg'
          : 'text-amber-400/90 hover:text-amber-300 hover:bg-amber-500/10'"
      >
        <span v-if="pendingCount > 0" class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
        Pending Moderation ({{ pendingCount }})
      </button>

      <button
        @click="$emit('update:activeStatusFilter', 'approved')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
        :class="activeStatusFilter === 'approved'
          ? 'bg-emerald-500 text-obsidian shadow-lg'
          : 'text-white/50 hover:text-white hover:bg-white/[0.04]'"
      >
        Approved ({{ approvedCount }})
      </button>

      <button
        @click="$emit('update:activeStatusFilter', 'featured')"
        class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all flex items-center gap-1.5"
        :class="activeStatusFilter === 'featured'
          ? 'bg-white/20 text-white shadow-lg'
          : 'text-white/50 hover:text-white hover:bg-white/[0.04]'"
      >
        <SparklesIcon class="h-3.5 w-3.5 text-amber-400" />
        Featured Ribbon ({{ featuredCount }})
      </button>
    </div>

    <!-- Right: Dropdown Selectors (Service, Rating, Sort) -->
    <div class="flex items-center gap-2.5 flex-wrap">
      <!-- Service Filter -->
      <div class="relative">
        <select
          :value="activeServiceFilter"
          @change="$emit('update:activeServiceFilter', $event.target.value)"
          class="appearance-none bg-[#12161C] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3 py-2 pr-7 outline-none transition-all focus:border-admin/50 cursor-pointer font-medium"
        >
          <option value="all" class="bg-[#12161C] text-white">All Services</option>
          <option v-for="s in services" :key="s.id" :value="s.id" class="bg-[#12161C] text-white">{{ s.name }}</option>
        </select>
        <ChevronDownIcon class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-white/40" />
      </div>

      <!-- Rating Filter -->
      <div class="relative">
        <select
          :value="activeRatingFilter"
          @change="$emit('update:activeRatingFilter', $event.target.value)"
          class="appearance-none bg-[#12161C] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3 py-2 pr-7 outline-none transition-all focus:border-admin/50 cursor-pointer font-medium"
        >
          <option value="all" class="bg-[#12161C] text-white">All Ratings</option>
          <option value="5" class="bg-[#12161C] text-white">★ 5 Stars Only</option>
          <option value="4" class="bg-[#12161C] text-white">★ 4 Stars</option>
          <option value="3" class="bg-[#12161C] text-white">★ 3 Stars & Under</option>
        </select>
        <ChevronDownIcon class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-white/40" />
      </div>

      <!-- Sort Selector -->
      <div class="relative">
        <select
          :value="sortOrder"
          @change="$emit('update:sortOrder', $event.target.value)"
          class="appearance-none bg-[#12161C] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3 py-2 pr-7 outline-none transition-all focus:border-admin/50 cursor-pointer font-medium"
        >
          <option value="newest" class="bg-[#12161C] text-white">Newest First</option>
          <option value="oldest" class="bg-[#12161C] text-white">Oldest First</option>
          <option value="rating-desc" class="bg-[#12161C] text-white">Rating: Highest</option>
          <option value="rating-asc" class="bg-[#12161C] text-white">Rating: Lowest</option>
        </select>
        <ChevronDownIcon class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-white/40" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { SparklesIcon, ChevronDownIcon } from '@heroicons/vue/24/outline';

defineProps({
  activeStatusFilter: {
    type: String,
    default: 'all'
  },
  activeServiceFilter: {
    type: [String, Number],
    default: 'all'
  },
  activeRatingFilter: {
    type: String,
    default: 'all'
  },
  sortOrder: {
    type: String,
    default: 'newest'
  },
  services: {
    type: Array,
    default: () => []
  },
  totalItems: {
    type: Number,
    default: 0
  },
  pendingCount: {
    type: Number,
    default: 0
  },
  approvedCount: {
    type: Number,
    default: 0
  },
  featuredCount: {
    type: Number,
    default: 0
  }
});

defineEmits([
  'update:activeStatusFilter',
  'update:activeServiceFilter',
  'update:activeRatingFilter',
  'update:sortOrder'
]);
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
