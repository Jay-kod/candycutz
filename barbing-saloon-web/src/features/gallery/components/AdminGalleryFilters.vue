<template>
  <div class="space-y-4">
    <!-- Top Row: Stylist Filter & Sort Dropdown -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <!-- Left: Stylist Contributor Filter -->
      <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs text-theme-text-muted font-medium">Stylist:</span>
        <div class="relative">
          <select
            :value="barberFilter"
            @change="$emit('update:barberFilter', $event.target.value)"
            class="appearance-none bg-theme-surface border border-theme-border text-xs text-theme-text rounded-xl px-3.5 py-2.5 pr-8 outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/15 cursor-pointer font-medium"
          >
            <option value="all" class="bg-theme-surface text-theme-text">All Stylists & Shop</option>
            <option value="shop" class="bg-theme-surface text-theme-text">General Shop Only</option>
            <option 
              v-for="barber in barbers" 
              :key="barber.id" 
              :value="barber.id" 
              class="bg-theme-surface text-theme-text"
            >
              {{ barber.user?.name || barber.name || ('Barber #' + barber.id) }}
            </option>
          </select>
          <ChevronDownIcon class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-theme-text-muted" />
        </div>
      </div>

      <!-- Right: Sort Selector -->
      <div class="flex items-center gap-2 shrink-0">
        <span class="text-xs text-theme-text-muted font-medium">Sort By:</span>
        <div class="relative">
          <select
            :value="sortOrder"
            @change="$emit('update:sortOrder', $event.target.value)"
            class="appearance-none bg-theme-surface border border-theme-border text-xs text-theme-text rounded-xl px-3.5 py-2.5 pr-8 outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/15 cursor-pointer font-medium"
          >
            <option value="newest" class="bg-theme-surface text-theme-text">Newest Additions</option>
            <option value="oldest" class="bg-theme-surface text-theme-text">Oldest Additions</option>
            <option value="name-asc" class="bg-theme-surface text-theme-text">Title: A to Z</option>
            <option value="category" class="bg-theme-surface text-theme-text">Category Grouping</option>
          </select>
          <ChevronDownIcon class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-theme-text-muted" />
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
          : 'bg-theme-surface-subtle text-theme-text-muted border-theme-border hover:bg-theme-border/20 hover:text-theme-text'"
      >
        <span>{{ cat.name }}</span>
        <span 
          class="text-[10px] px-1.5 py-0.2 rounded-full"
          :class="activeCategory === cat.key ? 'bg-admin/30 text-admin font-bold' : 'bg-theme-surface text-theme-text-muted'"
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
