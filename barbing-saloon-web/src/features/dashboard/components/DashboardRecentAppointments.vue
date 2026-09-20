<template>
  <div class="rounded-2xl border border-white/[0.05] bg-[#040709]/95 backdrop-blur-sm overflow-hidden">
    <div class="border-b border-white/[0.04] px-6 py-4 flex items-center justify-between">
      <h3 class="text-sm font-bold text-theme-text flex items-center gap-2">
        <ClipboardDocumentListIcon class="h-4 w-4 text-admin" />
        Recent Appointments
      </h3>
      <RouterLink to="/admin/appointments" class="text-[10px] font-semibold text-admin/60 hover:text-admin transition-colors">View All</RouterLink>
    </div>
    <div class="divide-y divide-white/[0.03]">
      <div v-for="appt in appointments" :key="appt.id" class="px-6 py-4 flex items-center justify-between hover:bg-white/[0.02] transition-colors group">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-full bg-admin/10 border border-admin/20 flex items-center justify-center shrink-0">
            <span class="text-xs font-bold text-admin">{{ getInitials(appt.client_name) }}</span>
          </div>
          <div>
            <p class="text-sm font-semibold text-theme-text group-hover:text-admin transition-colors">{{ appt.client_name }}</p>
            <p class="text-xs text-ivory/40 mt-0.5">{{ appt.service?.name || 'General Booking' }} &bull; {{ appt.barber?.name || 'Any Barber' }}</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-sm font-semibold text-white/90">{{ appt.appointment_time }}</p>
          <p class="text-[10px] uppercase tracking-wider font-bold mt-1" :class="statusClass(appt.status)">
            {{ appt.status }}
          </p>
        </div>
      </div>
      <div v-if="!appointments?.length" class="flex flex-col items-center py-10 text-ivory/20">
        <ClipboardDocumentListIcon class="h-8 w-8 mb-2" />
        <p class="text-xs">No recent appointments</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ClipboardDocumentListIcon } from '@heroicons/vue/24/outline';

defineProps({
  appointments: { type: Array, default: () => [] }
});

const getInitials = (name) => {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const statusClass = (status) => {
  const map = {
    completed: 'text-emerald-400',
    confirmed: 'text-blue-400',
    pending: 'text-amber-400',
    cancelled: 'text-red-400',
    no_show: 'text-purple-400'
  };
  return map[status] || 'text-white/50';
};
</script>
