<template>
  <div class="w-full space-y-3">
    <!-- Optional Table Top Controls (Search / Filters / Title) -->
    <div v-if="$slots.header || title" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1">
      <div>
        <h3 v-if="title" class="font-display text-lg font-bold text-[var(--color-text-primary)]">
          {{ title }}
        </h3>
        <p v-if="subtitle" class="text-xs text-[var(--color-text-secondary)]">
          {{ subtitle }}
        </p>
      </div>
      <div v-if="$slots.header" class="flex items-center gap-2">
        <slot name="header" />
      </div>
    </div>

    <!-- Table Container Card -->
    <div
      class="overflow-hidden rounded-2xl border bg-[var(--color-bg-secondary)] dark:bg-[#121824] border-[var(--color-border)] dark:border-white/[0.08] shadow-sm"
    >
      <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-xs sm:text-sm">
          <!-- Table Head -->
          <thead class="border-b border-[var(--color-border-subtle)] dark:border-white/[0.06] bg-[var(--color-bg-tertiary)]/50 dark:bg-white/[0.02]">
            <tr>
              <th
                v-for="col in columns"
                :key="col.key"
                scope="col"
                :class="[
                  'py-3.5 px-4 font-semibold text-[11px] uppercase tracking-wider text-[var(--color-text-secondary)]',
                  col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                  col.sortable ? 'cursor-pointer select-none hover:text-[var(--color-text-primary)]' : '',
                  col.class || ''
                ]"
                @click="col.sortable && handleSort(col.key)"
              >
                <div :class="['inline-flex items-center gap-1.5', col.align === 'right' ? 'justify-end' : col.align === 'center' ? 'justify-center' : '']">
                  <span>{{ col.label }}</span>
                  <span v-if="col.sortable" class="text-[10px] text-[var(--color-text-muted)]">
                    <span v-if="sortKey === col.key && sortOrder === 'asc'">▲</span>
                    <span v-else-if="sortKey === col.key && sortOrder === 'desc'">▼</span>
                    <span v-else class="opacity-40">↕</span>
                  </span>
                </div>
              </th>
            </tr>
          </thead>

          <!-- Table Body -->
          <tbody class="divide-y divide-[var(--color-border-subtle)] dark:divide-white/[0.04]">
            <!-- Skeleton Loading State -->
            <template v-if="loading">
              <tr v-for="i in skeletonRows" :key="i" class="animate-pulse">
                <td v-for="col in columns" :key="col.key" class="py-4 px-4">
                  <div class="h-4 bg-slate-200 dark:bg-white/10 rounded w-3/4"></div>
                </td>
              </tr>
            </template>

            <!-- Empty State -->
            <tr v-else-if="!items || items.length === 0">
              <td :colspan="columns.length" class="py-12 text-center">
                <div class="mx-auto flex flex-col items-center justify-center max-w-sm space-y-2">
                  <div class="h-12 w-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mb-1">
                    <slot name="empty-icon">
                      <span class="text-xl">📭</span>
                    </slot>
                  </div>
                  <p class="font-semibold text-sm text-[var(--color-text-primary)]">
                    {{ emptyMessage || 'No records found' }}
                  </p>
                  <p v-if="emptySubtext" class="text-xs text-[var(--color-text-muted)]">
                    {{ emptySubtext }}
                  </p>
                  <div v-if="$slots.emptyAction" class="pt-2">
                    <slot name="emptyAction" />
                  </div>
                </div>
              </td>
            </tr>

            <!-- Data Rows -->
            <template v-else>
              <tr
                v-for="(row, rIdx) in items"
                :key="row.id || rIdx"
                :class="[
                  'transition-colors duration-150',
                  'hover:bg-slate-500/5 dark:hover:bg-white/[0.03]',
                  rowClickable ? 'cursor-pointer' : ''
                ]"
                @click="rowClickable && $emit('row-click', row)"
              >
                <td
                  v-for="col in columns"
                  :key="col.key"
                  :class="[
                    'py-3.5 px-4 text-[var(--color-text-primary)] align-middle',
                    col.align === 'right' ? 'text-right' : col.align === 'center' ? 'text-center' : 'text-left',
                    col.cellClass || ''
                  ]"
                >
                  <slot :name="`cell(${col.key})`" :row="row" :value="row[col.key]">
                    <span class="truncate">{{ row[col.key] !== null && row[col.key] !== undefined ? row[col.key] : '—' }}</span>
                  </slot>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Table Footer / Pagination -->
      <div
        v-if="$slots.pagination"
        class="border-t border-[var(--color-border-subtle)] dark:border-white/[0.06] bg-[var(--color-bg-tertiary)]/30 dark:bg-white/[0.01] px-4 py-3"
      >
        <slot name="pagination" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
  columns: {
    type: Array,
    required: true,
  },
  items: {
    type: Array,
    default: () => [],
  },
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  skeletonRows: {
    type: Number,
    default: 5,
  },
  emptyMessage: {
    type: String,
    default: '',
  },
  emptySubtext: {
    type: String,
    default: '',
  },
  rowClickable: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['sort', 'row-click']);

const sortKey = ref('');
const sortOrder = ref('asc');

const handleSort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
  emit('sort', { key: sortKey.value, order: sortOrder.value });
};
</script>

