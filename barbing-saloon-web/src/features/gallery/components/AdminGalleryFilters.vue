<template>
  <div class="space-y-4">
    <!-- Top Row: Stylist Filter & Sort Dropdown -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <!-- Left: Stylist Contributor Filter -->
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs text-white/40 font-medium">Stylist:</span>
        <div class="relative">
          <select
            :value="barberFilter"
            @change="$emit('update:barberFilter', $event.target.value)"
            class="appearance-none bg-[#12161C] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3.5 py-2.5 pr-8 outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/15 cursor-pointer font-medium"
          >
            <option value="all" class="bg-[#12161C] text-white">All Stylists & Shop</option>
            <option value="shop" class="bg-[#12161C] text-white">General Shop Only</option>
            <option 
              v-for="barber in barbers" 
              :key="barber.id" 
              :value="barber.id" 
              class="bg-[#12161C] text-white"
            >
              {{ barber.user?.name || barber.name || ('Barber #' + barber.id) }}
            </option>
          </select>
          <ChevronDownIcon class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-white/40" />
        </div>
      </div>

      <!-- Right: Sort Selector -->
      <div class="flex items-center gap-2 shrink-0">
        <span class="text-xs text-white/40 font-medium">Sort By:</span>
        <div class="relative">
          <select
            :value="sortOrder"
            @change="$emit('update:sortOrder', $event.target.value)"
            class="appearance-none bg-[#12161C] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3.5 py-2.5 pr-8 outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/15 cursor-pointer font-medium"
          >
            <option value="newest" class="bg-[#12161C] text-white">Newest Additions</option>
            <option value="oldest" class="bg-[#12161C] text-white">Oldest Additions</option>
            <option value="name-asc" class="bg-[#12161C] text-white">Title: A to Z</option>
            <option value="category" class="bg-[#12161C] text-white">Category Grouping</option>
          </select>
          <ChevronDownIcon class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-white/40" />
        </div>
      </div>
    </div>

    <!-- Bottom Row: Category Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide max-w-full">
      <button
        v-for="cat in categories"
        :key="cat.key"
        @click="$emit('update:activeCategory', cat.key)"
        class="shrink-0 rounded-xl px-3 py-1.5 text-xs font-bold uppercase tracking-wider border transition-all flex items-center gap-1.5"
        :class="activeCategory === cat.key
          ? 'bg-admin/20 text-admin border-admin/40 shadow-sm'
          : 'bg-white/[0.02] text-white/40 border-white/[0.05] hover:bg-white/[0.05] hover:text-white/70'"
      >
        <span>{{ cat.name }}</span>
        <span 
          class="text-[10px] px-1.5 py-0.2 rounded-full"
          :class="activeCategory === cat.key ? 'bg-admin/30 text-admin font-bold' : 'bg-white/5 text-white/30'"
        >
          {{ cat.count }}
        </span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

defineProps({
  activeCategory: {
    type: String,
    default: 'all'
  },
  barberFilter: {
    type: [String, Number],
    default: 'all'
  },
  sortOrder: {
    type: String,
    default: 'newest'
  },
  categories: {
    type: Array,
    default: () => []
  },
  barbers: {
    type: Array,
    default: () => []
  },
  totalItems: {
    type: Number,
    default: 0
  }
});

defineEmits(['update:activeCategory', 'update:barberFilter', 'update:sortOrder']);
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
