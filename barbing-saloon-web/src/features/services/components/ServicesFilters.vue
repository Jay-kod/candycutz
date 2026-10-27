<template>
  <div class="space-y-4">
    <!-- Top Row: Approval/Status Tabs & Sort Control -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
      <!-- Status & Approval Filter Tabs -->
      <div class="flex items-center gap-1.5 bg-[#12161C] p-1.5 rounded-2xl border border-white/[0.08] overflow-x-auto scrollbar-hide max-w-full">
        <button
          @click="$emit('update:approvalFilter', 'all')"
          class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
          :class="approvalFilter === 'all'
            ? 'bg-admin text-obsidian shadow-lg'
            : 'text-white/50 hover:text-white hover:bg-white/[0.04]'"
        >
          All ({{ totalServices }})
        </button>

        <button
          @click="$emit('update:approvalFilter', 'pending')"
          class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all flex items-center gap-1.5"
          :class="approvalFilter === 'pending'
            ? 'bg-amber-500 text-obsidian shadow-lg'
            : 'text-amber-400/90 hover:text-amber-300 hover:bg-amber-500/10'"
        >
          <span v-if="pendingCount > 0" class="h-2 w-2 rounded-full bg-amber-400 animate-pulse"></span>
          Pending Approval ({{ pendingCount }})
        </button>

        <button
          @click="$emit('update:approvalFilter', 'approved')"
          class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
          :class="approvalFilter === 'approved'
            ? 'bg-emerald-500 text-obsidian shadow-lg'
            : 'text-white/50 hover:text-white hover:bg-white/[0.04]'"
        >
          Approved ({{ approvedCount }})
        </button>

        <button
          @click="$emit('update:approvalFilter', 'active')"
          class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
          :class="approvalFilter === 'active'
            ? 'bg-white/20 text-white shadow-lg'
            : 'text-white/50 hover:text-white hover:bg-white/[0.04]'"
        >
          Active ({{ activeCount }})
        </button>

        <button
          @click="$emit('update:approvalFilter', 'inactive')"
          class="shrink-0 rounded-xl px-3.5 py-2 text-xs font-bold transition-all"
          :class="approvalFilter === 'inactive'
            ? 'bg-white/20 text-white shadow-lg'
            : 'text-white/50 hover:text-white hover:bg-white/[0.04]'"
        >
          Inactive ({{ inactiveCount }})
        </button>
      </div>

      <!-- Right: Sort Selector -->
      <div class="flex items-center gap-2 shrink-0">
        <span class="text-xs text-white/40 font-medium hidden sm:inline">Sort By:</span>
        <div class="relative">
          <select
            :value="sortOrder"
            @change="$emit('update:sortOrder', $event.target.value)"
            class="appearance-none bg-[#12161C] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3.5 py-2.5 pr-8 outline-none transition-all focus:border-admin/50 focus:ring-2 focus:ring-admin/15 cursor-pointer font-medium"
          >
            <option value="default" class="bg-[#12161C] text-white">Default Sequence</option>
            <option value="price-asc" class="bg-[#12161C] text-white">Price: Low to High</option>
            <option value="price-desc" class="bg-[#12161C] text-white">Price: High to Low</option>
            <option value="duration-asc" class="bg-[#12161C] text-white">Duration: Shortest</option>
            <option value="duration-desc" class="bg-[#12161C] text-white">Duration: Longest</option>
            <option value="name-asc" class="bg-[#12161C] text-white">Name: A to Z</option>
          </select>
          <ChevronDownIcon class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-white/40" />
        </div>
      </div>
    </div>

    <!-- Bottom Row: Category Horizontal Filter Pills -->
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
  approvalFilter: {
    type: String,
    default: 'all'
  },
  activeCategory: {
    type: String,
    default: 'all'
  },
  sortOrder: {
    type: String,
    default: 'default'
  },
  categories: {
    type: Array,
    default: () => []
  },
  totalServices: {
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
  activeCount: {
    type: Number,
    default: 0
  },
  inactiveCount: {
    type: Number,
    default: 0
  }
});

defineEmits(['update:approvalFilter', 'update:activeCategory', 'update:sortOrder']);
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

