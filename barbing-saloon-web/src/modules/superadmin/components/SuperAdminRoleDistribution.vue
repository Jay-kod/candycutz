<template>
  <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-4 xs:p-6 shadow-sm">
    <div class="flex items-center justify-between">
      <h2 class="font-display text-lg font-bold text-[var(--color-text-primary)]">
        User Role Distribution
      </h2>
      <span class="text-xs font-mono text-[var(--color-text-muted)]">
        {{ totalUsers }} Total Accounts
      </span>
    </div>

    <div class="mt-6 space-y-4">
      <div v-for="bar in roleBars" :key="bar.label" class="space-y-1.5">
        <div class="flex items-center justify-between text-xs sm:text-sm">
          <span class="text-[var(--color-text-secondary)]">{{ bar.label }}</span>
          <span class="font-semibold text-[var(--color-text-primary)] tabular-nums">
            {{ bar.value }} ({{ bar.percent }}%)
          </span>
        </div>
        <div class="h-2.5 rounded-full overflow-hidden bg-[var(--color-bg-tertiary)] border border-[var(--color-border-subtle)]">
          <div
            :class="['h-full rounded-full transition-all duration-700', bar.barColor]"
            :style="{ width: bar.percent + '%' }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({}),
  },
});

const formatNumber = (val) => new Intl.NumberFormat('en-NG').format(Number(val || 0));

const totalUsers = computed(() => {
  return formatNumber(props.stats?.total_users || 0);
});

const roleBars = computed(() => {
  const s = props.stats || {};
  const total = Number(s.total_users || 1);

  return [
    {
      label: 'Admins & Super Admins',
      value: formatNumber(s.total_admins),
      percent: Math.round((Number(s.total_admins || 0) / total) * 100),
      barColor: 'bg-gradient-to-r from-rose-500 to-rose-600',
    },
    {
      label: 'Barbers & Stylists',
      value: formatNumber(s.barbers),
      percent: Math.round((Number(s.barbers || 0) / total) * 100),
      barColor: 'bg-gradient-to-r from-amber-500 to-amber-600',
    },
    {
      label: 'Clients & Customers',
      value: formatNumber(s.customers),
      percent: Math.round((Number(s.customers || 0) / total) * 100),
      barColor: 'bg-gradient-to-r from-sky-500 to-sky-600',
    },
  ];
});
</script>

