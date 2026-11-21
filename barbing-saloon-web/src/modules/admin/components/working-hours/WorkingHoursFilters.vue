<template>
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-4 rounded-2xl border border-white/[0.08] bg-[#12161C] shadow-lg backdrop-blur-xl">
    <!-- Left: Day of Week Pills -->
    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-white/[0.03] border border-white/[0.06] overflow-x-auto max-w-full">
      <!-- All Days -->
      <button
        @click="$emit('update:dayFilter', 'all')"
        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0"
        :class="dayFilter === 'all' 
          ? 'bg-admin text-obsidian shadow-sm' 
          : 'text-white/60 hover:text-white hover:bg-white/[0.04]'"
      >
        All Days
      </button>

      <!-- Today -->
      <button
        @click="$emit('update:dayFilter', 'today')"
        class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0"
        :class="dayFilter === 'today' 
          ? 'bg-emerald-500 text-obsidian shadow-sm' 
          : 'text-white/60 hover:text-white hover:bg-white/[0.04]'"
      >
        <span class="h-1.5 w-1.5 rounded-full" :class="dayFilter === 'today' ? 'bg-obsidian' : 'bg-emerald-400'"></span>
        <span>Today ({{ todayName.slice(0, 3) }})</span>
      </button>

      <!-- 7 Individual Days -->
      <button
        v-for="(day, idx) in shortDays"
        :key="idx"
        @click="$emit('update:dayFilter', String(idx))"
        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0"
        :class="dayFilter === String(idx) 
          ? 'bg-admin text-obsidian shadow-sm' 
          : 'text-white/60 hover:text-white hover:bg-white/[0.04]'"
      >
        {{ day }}
      </button>
    </div>

    <!-- Right: Availability Filter & Reset -->
    <div class="flex flex-wrap items-center gap-3">
      <!-- Status Filter -->
      <div class="flex items-center gap-2">
        <label for="status-filter" class="text-xs text-white/40 hidden sm:inline">Status:</label>
        <select
          id="status-filter"
          :value="statusFilter"
          @change="$emit('update:statusFilter', $event.target.value)"
          class="bg-white/[0.04] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3 py-2 outline-none focus:border-admin/50 transition-colors cursor-pointer"
        >
          <option value="all" class="bg-[#12161C] text-white">All Shifts</option>
          <option value="open" class="bg-[#12161C] text-white">Open Only</option>
          <option value="closed" class="bg-[#12161C] text-white">Closed / Day Off</option>
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

const shortDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

defineProps({
  dayFilter: {
    type: String,
    default: 'all'
  },
  statusFilter: {
    type: String,
    default: 'all'
  },
  todayIndex: {
    type: Number,
    default: 0
  },
  todayName: {
    type: String,
    default: 'Today'
  },
  hasActiveFilters: {
    type: Boolean,
    default: false
  }
});

defineEmits(['update:dayFilter', 'update:statusFilter', 'reset']);
</script>

