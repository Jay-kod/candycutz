<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-3 max-h-0"
    enter-to-class="opacity-100 translate-y-0 max-h-[500px]"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0 max-h-[500px]"
    leave-to-class="opacity-0 -translate-y-3 max-h-0"
  >
    <div v-if="showFilters" class="overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] backdrop-blur-xl shadow-lg">
      <div class="p-6 space-y-5">
        <!-- Time Filter -->
        <div>
          <div class="flex items-center gap-2 mb-3">
            <ClockIcon class="h-4 w-4 text-[var(--color-accent)]/60" />
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-accent)]">When</span>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="f in timeFilters"
              :key="f.value"
              @click="$emit('update:activeTimeFilter', f.value)"
              class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider border transition-all duration-200"
              :class="activeTimeFilter === f.value 
                ? 'bg-[var(--color-accent)]/20 border-[var(--color-accent)]/40 text-[var(--color-accent)] shadow-[0_0_10px_rgba(212,175,55,0.15)]' 
                : 'bg-[var(--color-bg-tertiary)] border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] hover:border-[var(--color-border)]'"
            >
              {{ f.label }}
            </button>
          </div>
        </div>

        <!-- Type Filter -->
        <div>
          <div class="flex items-center gap-2 mb-3">
            <TagIcon class="h-4 w-4 text-[var(--color-accent)]/60" />
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-[var(--color-accent)]">Type</span>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="f in typeFilters"
              :key="f.value"
              @click="$emit('update:activeTypeFilter', f.value)"
              class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider border transition-all duration-200"
              :class="activeTimeFilter === f.value 
                ? (f.bg || 'bg-[var(--color-accent)]/20 border-[var(--color-accent)]/40') + ' ' + (f.color || 'text-[var(--color-accent)]') + ' shadow-sm' 
                : 'bg-[var(--color-bg-tertiary)] border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] hover:border-[var(--color-border)]'"
            >
              {{ f.label }}
            </button>
          </div>
        </div>

        <!-- Active Filters Summary & Clear -->
        <div v-if="hasActiveFilters" class="flex items-center justify-between pt-3 border-t border-[var(--color-border)]">
          <p class="text-xs text-[var(--color-text-secondary)]">
            Showing <span class="text-[var(--color-accent)] font-bold">{{ filteredCount }}</span> of 
            <span class="text-[var(--color-text-secondary)] font-bold">{{ totalCount }}</span> notifications
          </p>
          <button @click="$emit('clearFilters')" class="flex items-center gap-1.5 text-xs font-bold text-[var(--color-danger)] hover:opacity-80 transition-opacity">
            <XMarkIcon class="h-4 w-4" />
            Clear Filters
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ClockIcon, TagIcon, XMarkIcon } from '@heroicons/vue/24/outline';

defineProps({
  showFilters: { type: Boolean, default: false },
  timeFilters: { type: Array, required: true },
  typeFilters: { type: Array, required: true },
  activeTimeFilter: { type: String, default: 'all' },
  activeTypeFilter: { type: String, default: 'all' },
  hasActiveFilters: { type: Boolean, default: false },
  filteredCount: { type: Number, default: 0 },
  totalCount: { type: Number, default: 0 },
});

defineEmits(['update:activeTimeFilter', 'update:activeTypeFilter', 'clearFilters']);
</script>
