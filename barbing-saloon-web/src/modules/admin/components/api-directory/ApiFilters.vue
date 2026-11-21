<template>
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-4 rounded-2xl border border-white/[0.08] bg-[#12161C] shadow-lg backdrop-blur-xl">
    <!-- Left: Group Tabs -->
    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-white/[0.03] border border-white/[0.06] overflow-x-auto max-w-full">
      <button
        @click="$emit('update:activeGroup', 'All')"
        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0"
        :class="activeGroup === 'All' 
          ? 'bg-admin text-obsidian shadow-sm' 
          : 'text-white/60 hover:text-white hover:bg-white/[0.04]'"
      >
        All Resources
      </button>

      <button
        v-for="group in groups"
        :key="group.id"
        @click="$emit('update:activeGroup', group.id)"
        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0"
        :class="activeGroup === group.id 
          ? 'bg-admin text-obsidian shadow-sm' 
          : 'text-white/60 hover:text-white hover:bg-white/[0.04]'"
      >
        {{ group.name }}
      </button>
    </div>

    <!-- Right: Method & Auth Selectors -->
    <div class="flex flex-wrap items-center gap-3">
      <!-- Method Selector -->
      <div class="flex items-center gap-2">
        <label for="method-select" class="text-xs text-white/40 hidden sm:inline">Method:</label>
        <select
          id="method-select"
          :value="activeMethod"
          @change="$emit('update:activeMethod', $event.target.value)"
          class="bg-white/[0.04] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3 py-2 outline-none focus:border-admin/50 transition-colors cursor-pointer"
        >
          <option value="All" class="bg-[#12161C] text-white">All Methods</option>
          <option value="GET" class="bg-[#12161C] text-white">GET</option>
          <option value="POST" class="bg-[#12161C] text-white">POST</option>
          <option value="PUT" class="bg-[#12161C] text-white">PUT</option>
          <option value="PATCH" class="bg-[#12161C] text-white">PATCH</option>
          <option value="DELETE" class="bg-[#12161C] text-white">DELETE</option>
        </select>
      </div>

      <!-- Auth Scope Selector -->
      <div class="flex items-center gap-2">
        <label for="auth-select" class="text-xs text-white/40 hidden sm:inline">Auth Scope:</label>
        <select
          id="auth-select"
          :value="activeAuth"
          @change="$emit('update:activeAuth', $event.target.value)"
          class="bg-white/[0.04] border border-white/[0.08] text-xs text-white/80 rounded-xl px-3 py-2 outline-none focus:border-admin/50 transition-colors cursor-pointer"
        >
          <option value="All" class="bg-[#12161C] text-white">All Scopes</option>
          <option value="public" class="bg-[#12161C] text-white">Public Access</option>
          <option value="protected" class="bg-[#12161C] text-white">Sanctum Token</option>
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
  activeGroup: {
    type: String,
    default: 'All'
  },
  activeMethod: {
    type: String,
    default: 'All'
  },
  activeAuth: {
    type: String,
    default: 'All'
  },
  groups: {
    type: Array,
    required: true
  },
  hasActiveFilters: {
    type: Boolean,
    default: false
  }
});

defineEmits(['update:activeGroup', 'update:activeMethod', 'update:activeAuth', 'reset']);
</script>

