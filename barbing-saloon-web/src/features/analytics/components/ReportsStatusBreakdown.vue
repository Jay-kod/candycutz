<template>
  <div class="rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm">
    <h2 class="text-lg font-bold text-theme-text mb-6 flex items-center gap-2">
      <ChartPieIcon class="h-5 w-5 text-admin" /> Appointment Status Breakdown
    </h2>
    
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" v-if="statusBreakdown.length > 0">
      <div v-for="status in statusBreakdown" :key="status.status" 
           class="p-5 rounded-xl border bg-white/[0.02]"
           :class="{
             'border-emerald-500/20': status.status === 'completed',
             'border-yellow-500/20': status.status === 'pending',
             'border-red-500/20': status.status === 'cancelled',
             'border-blue-500/20': status.status === 'approved'
           }">
        <p class="text-sm text-ivory/60 font-medium capitalize">{{ status.status.replace('_', ' ') }}</p>
        <p class="font-display text-2xl text-theme-text mt-1">{{ status.count }}</p>
        <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-black/40">
          <div class="h-full rounded-full transition-all duration-1000"
               :class="{
                 'bg-emerald-500': status.status === 'completed',
                 'bg-yellow-500': status.status === 'pending',
                 'bg-red-500': status.status === 'cancelled',
                 'bg-blue-500': status.status === 'approved'
               }"
               :style="{ width: totalAppointments > 0 ? Math.max((status.count / totalAppointments) * 100, 2) + '%' : '2%' }">
          </div>
        </div>
      </div>
    </div>
    <div v-else class="text-center py-10 text-theme-muted">
      No appointment data available for this period.
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { ChartPieIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  statusBreakdown: { type: Array, required: true }
});

const totalAppointments = computed(() => {
  return props.statusBreakdown.reduce((sum, s) => sum + Number(s.count), 0);
});
</script>
