<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-3 max-h-0"
    enter-to-class="opacity-100 translate-y-0 max-h-[500px]"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0 max-h-[500px]"
    leave-to-class="opacity-0 -translate-y-3 max-h-0"
  >
    <div v-if="showFilters" class="overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-xl shadow-lg">
      <div class="p-6 space-y-5">
        <!-- Time Filter -->
        <div>
          <div class="flex items-center gap-2 mb-3">
            <ClockIcon class="h-4 w-4 text-gold/60" />
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold/70">When</span>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="f in timeFilters"
              :key="f.value"
              @click="$emit('update:activeTimeFilter', f.value)"
              class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider border transition-all duration-200"
              :class="activeTimeFilter === f.value 
                ? 'bg-gold/20 border-gold/40 text-gold shadow-[0_0_10px_rgba(212,175,55,0.15)]' 
                : 'bg-theme-bg/50 border-theme-border/50 text-theme-muted hover:text-theme-text hover:border-theme-border'"
            >
              {{ f.label }}
            </button>
          </div>
        </div>

        <!-- Type Filter -->
        <div>
          <div class="flex items-center gap-2 mb-3">
            <TagIcon class="h-4 w-4 text-gold/60" />
            <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold/70">Type</span>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="f in typeFilters"
              :key="f.value"
              @click="$emit('update:activeTypeFilter', f.value)"
              class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider border transition-all duration-200"
              :class="activeTypeFilter === f.value 
                ? (f.bg || 'bg-gold/20 border-gold/40') + ' ' + (f.color || 'text-gold') + ' shadow-sm' 
                : 'bg-theme-bg/50 border-theme-border/50 text-theme-muted hover:text-theme-text hover:border-theme-border'"
            >
              {{ f.label }}
            </button>
          </div>
        </div>

        <!-- Active Filters Summary & Clear -->
        <div v-if="hasActiveFilters" class="flex items-center justify-between pt-3 border-t border-theme-border/30">
          <p class="text-xs text-theme-muted">
            Showing <span class="text-gold font-bold">{{ filteredCount }}</span> of 
            <span class="text-theme-muted font-bold">{{ totalCount }}</span> notifications
          </p>
          <button @click="$emit('clearFilters')" class="flex items-center gap-1.5 text-xs font-bold text-red-400 hover:text-red-300 transition-colors">
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
