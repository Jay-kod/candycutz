<template>
  <Transition
    enter-active-class="transition-all duration-200 ease-out"
    enter-from-class="opacity-0 -translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition-all duration-150 ease-in"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 -translate-y-2"
  >
    <section
      v-if="showFilters"
      aria-label="Filter notifications"
      class="rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 sm:p-5 space-y-4"
    >
      <!-- Search Input -->
      <div class="relative">
        <MagnifyingGlassIcon
          class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--color-text-muted)]"
        />
        <input
          :value="searchQuery"
          @input="$emit('update:searchQuery', $event.target.value)"
          type="search"
          placeholder="Search notifications, booking ref, or code..."
          aria-label="Search notifications"
          class="w-full min-h-[42px] rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-primary)] pl-9 pr-10 py-2 text-base sm:text-sm text-[var(--color-text-primary)] placeholder:text-[var(--color-text-muted)] focus:outline-none focus:border-[var(--color-accent)] transition-colors"
        />
        <button
          v-if="searchQuery"
          type="button"
          @click="$emit('update:searchQuery', '')"
          class="absolute right-1.5 top-1/2 -translate-y-1/2 min-h-[32px] min-w-[32px] rounded-md inline-flex items-center justify-center text-[var(--color-text-muted)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-bg-tertiary)] transition-colors"
          aria-label="Clear search"
        >
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>

      <!-- Time Filter -->
      <div>
        <div class="flex items-center gap-1.5 mb-2">
          <ClockIcon class="h-3.5 w-3.5 text-[var(--color-accent)]" />
          <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--color-accent)]">
            When
          </span>
        </div>
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5 sm:flex-wrap">
          <button
            v-for="f in timeFilters"
            :key="f.value"
            type="button"
            @click="$emit('update:activeTimeFilter', f.value)"
            class="shrink-0 min-h-[34px] px-3 py-1.5 rounded-lg text-xs font-semibold border whitespace-nowrap transition-colors"
            :class="
              activeTimeFilter === f.value
                ? 'bg-[var(--color-accent)]/15 border-[var(--color-accent)]/40 text-[var(--color-accent)]'
                : 'bg-[var(--color-bg-tertiary)] border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]'
            "
          >
            {{ f.label }}
          </button>
        </div>
      </div>

      <!-- Type Filter -->
      <div>
        <div class="flex items-center gap-1.5 mb-2">
          <TagIcon class="h-3.5 w-3.5 text-[var(--color-accent)]" />
          <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-[var(--color-accent)]">
            Category
          </span>
        </div>
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5 sm:flex-wrap">
          <button
            v-for="f in visibleTypeFilters"
            :key="f.value"
            type="button"
            @click="$emit('update:activeTypeFilter', f.value)"
            class="shrink-0 min-h-[34px] px-3 py-1.5 rounded-lg text-xs font-semibold border whitespace-nowrap inline-flex items-center gap-1.5 transition-colors"
            :class="
              activeTypeFilter === f.value
                ? (f.bg || 'bg-[var(--color-accent)]/15 border-[var(--color-accent)]/40') +
                  ' ' +
                  (f.color || 'text-[var(--color-accent)]')
                : 'bg-[var(--color-bg-tertiary)] border-[var(--color-border)] text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]'
            "
          >
            <span>{{ f.label }}</span>
            <span
              v-if="typeCounts && typeCounts[f.value] !== undefined"
              class="rounded-md px-1.5 py-0.5 text-[10px] font-bold tabular-nums leading-none bg-[var(--color-bg-secondary)]/80 text-[var(--color-text-muted)]"
            >
              {{ typeCounts[f.value] }}
            </span>
          </button>
        </div>
      </div>

      <!-- Active Filters Summary & Clear -->
      <div
        v-if="hasActiveFilters"
        class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-[var(--color-border)]"
      >
        <p class="text-xs text-[var(--color-text-secondary)] text-left-forced">
          Showing
          <span class="text-[var(--color-accent)] font-bold tabular-nums">{{ filteredCount }}</span>
          of
          <span class="font-bold tabular-nums text-[var(--color-text-primary)]">{{ totalCount }}</span>
          notifications
        </p>
        <button
          type="button"
          @click="$emit('clearFilters')"
          class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline min-h-[32px]"
        >
          <XMarkIcon class="h-3.5 w-3.5" />
          <span>Reset filters</span>
        </button>
      </div>
    </section>
  </Transition>
</template>

<script setup>
import { computed } from 'vue';
import { ClockIcon, MagnifyingGlassIcon, TagIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  showFilters: { type: Boolean, default: false },
  timeFilters: { type: Array, required: true },
  typeFilters: { type: Array, required: true },
  activeTimeFilter: { type: String, default: 'all' },
  activeTypeFilter: { type: String, default: 'all' },
  searchQuery: { type: String, default: '' },
  typeCounts: { type: Object, default: () => ({}) },
  hideStaffFilters: { type: Boolean, default: false },
  hasActiveFilters: { type: Boolean, default: false },
  filteredCount: { type: Number, default: 0 },
  totalCount: { type: Number, default: 0 },
});

defineEmits([
  'update:activeTimeFilter',
  'update:activeTypeFilter',
  'update:searchQuery',
  'clearFilters',
]);

const visibleTypeFilters = computed(() => {
  return props.typeFilters.filter((f) => {
    if (!f.staffOnly || !props.hideStaffFilters) return true;
    return (props.typeCounts?.[f.value] || 0) > 0;
  });
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
