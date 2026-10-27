<template>
  <div class="grid gap-5 grid-cols-2 lg:grid-cols-4">
    <article v-for="card in statsCards" :key="card.label" class="group rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6 transition-colors duration-300 hover:border-[var(--color-accent)]/30">
      <div class="flex items-center gap-2 text-[var(--color-text-secondary)] mb-2">
        <component :is="card.icon" class="w-5 h-5 shrink-0" :class="card.iconColor" />
        <p class="text-[11px] font-bold uppercase tracking-[0.2em]">{{ card.label }}</p>
      </div>
      <p class="text-3xl lg:text-4xl font-bold tracking-tight text-[var(--color-text-primary)] tabular-nums">
        {{ card.value }}
      </p>
    </article>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  CalendarDaysIcon,
  ArrowTrendingUpIcon,
  UserGroupIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  stats: { type: Object, required: true }
});

const formatNumber = (val) => new Intl.NumberFormat('en-NG').format(Number(val || 0));

const statsCards = computed(() => [
  {
    label: "Today's Bookings",
    value: formatNumber(props.stats?.today_bookings),
    icon: CalendarDaysIcon,
    iconColor: 'text-[var(--color-accent)]',
  },
  {
    label: 'Upcoming',
    value: formatNumber(props.stats?.upcoming_bookings),
    icon: ArrowTrendingUpIcon,
    iconColor: 'text-blue-500 dark:text-blue-400',
  },
  {
    label: 'Completed',
    value: formatNumber(props.stats?.completed_bookings),
    icon: UserGroupIcon,
    iconColor: 'text-[var(--color-success)]',
  },
  {
    label: 'No Shows',
    value: formatNumber(props.stats?.no_show_count),
    icon: ExclamationTriangleIcon,
    iconColor: 'text-[var(--color-danger)]',
  },
]);
</script>
