<template>
  <div class="grid gap-5 grid-cols-2 lg:grid-cols-4">
    <article v-for="card in statsCards" :key="card.label" class="group rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-gold/30">
      <div class="flex items-center gap-2 text-theme-muted mb-2">
        <component :is="card.icon" class="w-5 h-5 shrink-0" :class="card.iconColor" />
        <p class="text-[11px] font-bold uppercase tracking-[0.2em]">{{ card.label }}</p>
      </div>
      <p class="text-3xl lg:text-4xl font-bold tracking-tight text-theme-text tabular-nums">
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
    iconColor: 'text-gold',
  },
  {
    label: 'Upcoming',
    value: formatNumber(props.stats?.upcoming_bookings),
    icon: ArrowTrendingUpIcon,
    iconColor: 'text-blue-400',
  },
  {
    label: 'Completed',
    value: formatNumber(props.stats?.completed_bookings),
    icon: UserGroupIcon,
    iconColor: 'text-emerald-400',
  },
  {
    label: 'No Shows',
    value: formatNumber(props.stats?.no_show_count),
    icon: ExclamationTriangleIcon,
    iconColor: 'text-red-400',
  },
]);
</script>
