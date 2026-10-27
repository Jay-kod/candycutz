<template>
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-3 sm:p-4 backdrop-blur-xl shadow-xl">
    <!-- Filter Tabs -->
    <div class="flex items-center gap-1.5 overflow-x-auto hide-scrollbar">
      <button
        v-for="tab in tabs"
        :key="tab.value"
        type="button"
        @click="$emit('update:filter', tab.value)"
        class="relative rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition-all whitespace-nowrap"
        :class="[
          currentFilter === tab.value
            ? 'bg-gradient-to-r from-[#FF6700]/20 to-amber-500/20 text-[#FF6700] border border-[#FF6700]/30 shadow-lg shadow-[#FF6700]/10'
            : 'text-white/50 hover:text-white hover:bg-white/[0.04]'
        ]"
      >
        <span class="relative z-10 flex items-center gap-2">
          {{ tab.label }}
          <span
            v-if="tab.value === 'confirmed' && pendingCount > 0"
            class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"
          ></span>
        </span>
      </button>
    </div>

    <!-- Live Search Bar -->
    <div class="relative w-full sm:w-80">
      <MagnifyingGlassIcon class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-white/30" />
      <input
        :value="search"
        @input="$emit('update:search', $event.target.value)"
        type="text"
        placeholder="Search code or customer name..."
        class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 py-2.5 pl-10 pr-9 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:bg-[#0B0F13]"
      />
      <button
        v-if="search"
        type="button"
        @click="$emit('update:search', '')"
        class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white text-xs"
      >
        ✕
      </button>
    </div>
  </div>
</template>

<script setup>
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

defineProps({
  currentFilter: {
    type: String,
    required: true
  },
  tabs: {
    type: Array,
    required: true
  },
  search: {
    type: String,
    default: ''
  },
  pendingCount: {
    type: Number,
    default: 0
  }
});

defineEmits(['update:filter', 'update:search']);
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

