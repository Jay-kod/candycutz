<template>
  <div
    class="flex flex-col md:flex-row md:items-center justify-between gap-2.5 sm:gap-4 border-b border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.08] pb-3.5 sm:pb-4"
  >
    <div
      role="tablist"
      aria-label="Filter bookings by status"
      class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5 max-w-full min-w-0 md:flex-wrap"
    >
      <button
        v-for="filter in filters"
        :key="filter.value"
        type="button"
        role="tab"
        :aria-selected="currentFilter === filter.value"
        @click="$emit('update:currentFilter', filter.value)"
        :class="[
          'min-h-[38px] sm:min-h-[40px] flex items-center gap-1.5 sm:gap-2 rounded-lg md:rounded-xl px-3.5 sm:px-4 py-1.5 sm:py-2 text-xs font-bold transition-all shrink-0 whitespace-nowrap',
          currentFilter === filter.value
            ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] md:text-white border border-transparent shadow-none md:shadow-md md:shadow-[var(--color-accent)]/20'
            : 'bg-[var(--color-bg-secondary)] md:bg-slate-100 md:dark:bg-white/[0.04] text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-tertiary)] md:hover:bg-slate-200 md:dark:hover:bg-white/[0.08] hover:text-[var(--color-text-primary)] md:hover:text-slate-900 md:dark:hover:text-white border border-[var(--color-border)] md:border-slate-200 md:dark:border-white/[0.06]'
        ]"
      >
        <span>{{ filter.label }}</span>
        <span
          :class="[
            'px-1.5 py-0.5 rounded-md md:rounded-full text-[10px] font-mono tabular-nums leading-none',
            currentFilter === filter.value
              ? 'bg-black/20 dark:bg-black/30 text-current'
              : 'bg-[var(--color-bg-tertiary)] md:bg-slate-200 md:dark:bg-white/10 text-[var(--color-text-secondary)] md:text-slate-700 md:dark:text-[var(--color-text-secondary)]'
          ]"
        >
          {{ filter.count }}
        </span>
      </button>
    </div>

    <p class="text-[11px] sm:text-xs text-[var(--color-text-secondary)] font-mono tabular-nums text-left-forced shrink-0">
      Showing {{ filteredCount }} of {{ totalCount }} appointments
    </p>
  </div>
</template>

<script setup>
defineProps({
  filters: { type: Array, required: true },
  currentFilter: { type: String, required: true },
  filteredCount: { type: Number, required: true },
  totalCount: { type: Number, required: true },
});

defineEmits(['update:currentFilter']);
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
