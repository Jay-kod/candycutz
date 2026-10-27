<script setup>
import {
  MagnifyingGlassIcon,
  XMarkIcon,
  FunnelIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  search: {
    type: String,
    default: '',
  },
  statusFilter: {
    type: String,
    default: '',
  },
  severityFilter: {
    type: String,
    default: '',
  },
  categoryFilter: {
    type: String,
    default: '',
  },
})

defineEmits([
  'update:search',
  'update:statusFilter',
  'update:severityFilter',
  'update:categoryFilter',
  'clearFilters',
])
</script>

<template>
  <div class="flex flex-wrap gap-3 items-center">
    <!-- Search Field -->
    <div class="relative flex-1 min-w-[240px]">
      <MagnifyingGlassIcon class="w-4 h-4 text-zinc-500 absolute left-3.5 top-1/2 -translate-y-1/2" />
      <input
        :value="search"
        @input="$emit('update:search', $event.target.value)"
        type="text"
        placeholder="Filter error signatures, messages, exceptions…"
        class="w-full pl-10 pr-9 py-2.5 rounded-xl bg-[#12161C] border border-white/[0.08] text-white text-xs placeholder-zinc-500 focus:outline-none focus:border-white/[0.18] transition-colors"
      />
      <button
        v-if="search"
        type="button"
        @click="$emit('update:search', '')"
        class="absolute right-3 top-1/2 -translate-y-1/2 p-0.5 text-zinc-500 hover:text-white transition-colors"
      >
        <XMarkIcon class="w-3.5 h-3.5" />
      </button>
    </div>

    <!-- Status Selector -->
    <div class="relative">
      <select
        :value="statusFilter"
        @change="$emit('update:statusFilter', $event.target.value)"
        class="px-3.5 py-2.5 rounded-xl bg-[#12161C] border border-white/[0.08] text-zinc-300 text-xs focus:outline-none focus:border-white/[0.18] cursor-pointer appearance-none pr-8 transition-colors"
      >
        <option value="">All Statuses</option>
        <option value="open">Open</option>
        <option value="acknowledged">Acknowledged</option>
        <option value="resolved">Resolved</option>
        <option value="ignored">Ignored</option>
      </select>
      <FunnelIcon class="w-3 h-3 text-zinc-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
    </div>

    <!-- Severity Selector -->
    <div class="relative">
      <select
        :value="severityFilter"
        @change="$emit('update:severityFilter', $event.target.value)"
        class="px-3.5 py-2.5 rounded-xl bg-[#12161C] border border-white/[0.08] text-zinc-300 text-xs focus:outline-none focus:border-white/[0.18] cursor-pointer appearance-none pr-8 transition-colors"
      >
        <option value="">All Severities</option>
        <option value="critical">Critical</option>
        <option value="error">Error</option>
        <option value="warning">Warning</option>
        <option value="info">Info</option>
      </select>
      <FunnelIcon class="w-3 h-3 text-zinc-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
    </div>

    <!-- Category Selector -->
    <div class="relative">
      <select
        :value="categoryFilter"
        @change="$emit('update:categoryFilter', $event.target.value)"
        class="px-3.5 py-2.5 rounded-xl bg-[#12161C] border border-white/[0.08] text-zinc-300 text-xs focus:outline-none focus:border-white/[0.18] cursor-pointer appearance-none pr-8 transition-colors"
      >
        <option value="">All Categories</option>
        <option value="auth">Auth & Security</option>
        <option value="booking">Booking Service</option>
        <option value="payment">Payment Gateway</option>
        <option value="upload">Asset Uploads</option>
        <option value="external">External API</option>
        <option value="system">Core System</option>
      </select>
      <FunnelIcon class="w-3 h-3 text-zinc-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" />
    </div>

    <!-- Clear Filters Button -->
    <button
      v-if="search || statusFilter || severityFilter || categoryFilter"
      type="button"
      @click="$emit('clearFilters')"
      class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/[0.04] border border-white/[0.08] text-zinc-400 hover:text-white hover:bg-white/[0.08] text-xs transition-colors"
    >
      <XMarkIcon class="w-3.5 h-3.5" />
      <span>Reset Filters</span>
    </button>
  </div>
</template>
