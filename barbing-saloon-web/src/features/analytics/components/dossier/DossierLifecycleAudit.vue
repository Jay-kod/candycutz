<template>
  <section class="mb-5 page-break-avoid">
    <div class="rounded-lg border border-gray-200 bg-white p-3.5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 flex items-center gap-1.5">
          <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
          Booking Lifecycle & Retention Audit
        </h4>
        <div class="flex items-center gap-2 text-[10px] font-mono">
          <span class="font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded">
            {{ completionRate }}% Fulfilled
          </span>
          <span v-if="attritionRate > 0" class="font-bold text-rose-800 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded">
            {{ attritionRate }}% Attrition
          </span>
          <span class="text-gray-500">
            {{ totalAppointments }} Total Bookings
          </span>
        </div>
      </div>

      <!-- Segmented Distribution Bar -->
      <div class="h-2.5 w-full rounded-full bg-gray-100 overflow-hidden flex mb-2.5">
        <div
          v-for="item in statusBreakdown"
          :key="'bar-' + item.status"
          :class="getStatusBarClass(item.status)"
          :style="{ width: `${computePercentage(item.count)}%` }"
          :title="`${item.status}: ${item.count} (${computePercentage(item.count)}%)`"
        ></div>
      </div>

      <!-- Compact Status Pills Grid -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2 text-xs font-mono">
        <div
          v-for="item in statusBreakdown"
          :key="'pill-' + item.status"
          class="flex items-center justify-between p-1.5 rounded border border-gray-200 bg-gray-50/70"
        >
          <div class="flex items-center gap-1.5 truncate">
            <span class="h-1.5 w-1.5 rounded-full shrink-0" :class="getStatusDotClass(item.status)"></span>
            <span class="capitalize text-gray-700 font-sans text-[11px] truncate">{{ formatStatus(item.status) }}</span>
          </div>
          <span class="font-bold text-gray-900 ml-1">
            {{ item.count }}
            <span class="text-[10px] font-normal text-gray-400">({{ computePercentage(item.count) }}%)</span>
          </span>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  statusBreakdown: { type: Array, default: () => [] }
});

const totalAppointments = computed(() => {
  return props.statusBreakdown.reduce((sum, s) => sum + Number(s.count || 0), 0);
});

const completionRate = computed(() => {
  const tot = totalAppointments.value;
  if (tot === 0) return 0;
  const completedItem = props.statusBreakdown.find(s => s.status === 'completed');
  return Math.round((Number(completedItem?.count || 0) / tot) * 100);
});

const attritionRate = computed(() => {
  const tot = totalAppointments.value;
  if (tot === 0) return 0;
  const lostItems = props.statusBreakdown.filter(s => s.status === 'cancelled' || s.status === 'no_show');
  const lostCount = lostItems.reduce((acc, curr) => acc + Number(curr.count || 0), 0);
  return Math.round((lostCount / tot) * 100);
});

const computePercentage = (count) => {
  const tot = totalAppointments.value;
  if (tot === 0) return 0;
  return Math.round((Number(count || 0) / tot) * 100);
};

const formatStatus = (s) => String(s || '').replace(/_/g, ' ');

const getStatusBarClass = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-500';
    case 'confirmed':
    case 'approved': return 'bg-sky-500';
    case 'pending': return 'bg-amber-500';
    case 'cancelled': return 'bg-rose-500';
    case 'no_show': return 'bg-purple-500';
    default: return 'bg-gray-400';
  }
};

const getStatusDotClass = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-500';
    case 'confirmed':
    case 'approved': return 'bg-sky-500';
    case 'pending': return 'bg-amber-500';
    case 'cancelled': return 'bg-rose-500';
    case 'no_show': return 'bg-purple-500';
    default: return 'bg-gray-400';
  }
};
</script>
