<template>
  <div class="rounded-2xl border border-theme-border bg-theme-surface p-5 sm:p-6 relative overflow-hidden group hover:border-admin/40 transition-all">
    <!-- Technical Corner Ticks -->
    <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

    <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-theme-border">
      <div class="flex items-center gap-2">
        <ClockIcon class="h-4 w-4 text-admin" />
        <h3 class="text-xs font-bold text-theme-text uppercase tracking-wider">Today's Walk-In Ledger</h3>
      </div>
      <RouterLink 
        to="/admin/appointments" 
        class="text-[10px] text-admin hover:text-admin-light font-bold uppercase tracking-wider transition-colors"
      >
        View Full Ledger →
      </RouterLink>
    </div>

    <div v-if="walkIns.length === 0" class="text-xs text-theme-text-muted text-center py-6 font-mono bg-theme-bg rounded-xl border border-theme-border">
      No walk-ins registered yet today.
    </div>

    <div v-else class="space-y-2.5">
      <div
        v-for="item in walkIns"
        :key="item.id"
        class="flex items-center justify-between p-3 rounded-xl bg-theme-bg border border-theme-border hover:bg-theme-surface-hover transition-colors"
      >
        <div class="flex items-center gap-3 min-w-0">
          <div class="h-8 w-8 rounded-full bg-admin/15 border border-admin/30 flex items-center justify-center text-xs font-bold text-admin shrink-0">
            {{ getInitials(item.client_name || item.customer?.name) }}
          </div>
          <div class="min-w-0">
            <p class="text-xs font-bold text-theme-text truncate">
              {{ item.client_name || item.customer?.name || 'Walk-In Guest' }}
            </p>
            <p class="text-[10px] text-theme-text-muted truncate">
              {{ item.service?.name || 'Grooming' }} • {{ formatTime(item.appointment_time) }}
            </p>
          </div>
        </div>

        <div class="text-right shrink-0 ml-3">
          <p class="text-xs font-bold text-theme-text font-mono">
            ₦{{ Number(item.total_price || item.service?.price || 0).toLocaleString() }}
          </p>
          <span 
            class="inline-block px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider mt-0.5"
            :class="getStatusClass(item.status)"
          >
            {{ item.status }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { ClockIcon } from '@heroicons/vue/24/outline';

defineProps({
  walkIns: { type: Array, default: () => [] }
});

const getInitials = (name) => {
  if (!name) return 'WI';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const formatTime = (time) => {
  if (!time) return 'Now';
  return time.substring(0, 5);
};

const getStatusClass = (status) => {
  switch (status) {
    case 'completed':
      return 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
    case 'confirmed':
      return 'bg-sky-500/10 text-sky-400 border border-sky-500/20';
    case 'cancelled':
      return 'bg-red-500/10 text-red-400 border border-red-500/20';
    default:
      return 'bg-amber-500/10 text-amber-400 border border-amber-500/20';
  }
};
</script>
