<template>
  <div class="rounded-2xl border border-white/[0.07] bg-[#0A0F12]/80 backdrop-blur-xl shadow-xl overflow-hidden">
    <!-- Header -->
    <div class="px-5 lg:px-6 py-4 border-b border-white/[0.05] flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-admin/10 border border-admin/20 text-admin shrink-0">
          <CalendarDaysIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-white tracking-wide">Recent Appointments</h2>
          <p class="text-[11px] text-white/40">Latest bookings and walk-ins</p>
        </div>
      </div>

      <RouterLink 
        to="/admin/appointments" 
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-admin hover:text-admin-light transition-colors group"
      >
        <span>View All</span>
        <ArrowRightIcon class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
      </RouterLink>
    </div>

    <!-- Appointment Rows -->
    <div class="divide-y divide-white/[0.04]">
      <div 
        v-for="appt in appointments" 
        :key="appt.id" 
        class="px-5 lg:px-6 py-3.5 flex items-center justify-between gap-4 hover:bg-white/[0.02] transition-colors group"
      >
        <!-- Client & Service Info -->
        <div class="flex items-center gap-3.5 min-w-0">
          <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-admin/20 to-admin/5 border border-admin/25 flex items-center justify-center shrink-0 shadow-sm">
            <span class="text-xs font-bold text-admin">{{ getInitials(appt.client_name) }}</span>
          </div>
          <div class="min-w-0">
            <p class="text-sm font-semibold text-white group-hover:text-admin-light transition-colors truncate">
              {{ appt.client_name || 'Walk-In Client' }}
            </p>
            <div class="flex items-center gap-2 text-xs text-white/40 mt-0.5 truncate">
              <span class="truncate">{{ appt.service?.name || 'General Service' }}</span>
              <span class="text-white/20">&bull;</span>
              <span class="text-white/50 truncate">{{ appt.barber?.name || 'Any Barber' }}</span>
            </div>
          </div>
        </div>

        <!-- Time & Status Badges -->
        <div class="flex items-center gap-3 shrink-0">
          <div class="text-right hidden sm:block">
            <p class="text-xs font-semibold text-white/80 tabular-nums">
              {{ appt.appointment_time || '--:--' }}
            </p>
            <p class="text-[10px] text-white/35 mt-0.5 font-medium">
              {{ formatDate(appt.appointment_date) }}
            </p>
          </div>

          <span 
            class="rounded-full px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase border"
            :class="statusBadgeClass(appt.status)"
          >
            {{ appt.status }}
          </span>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!appointments?.length" class="flex flex-col items-center justify-center py-12 text-white/30">
        <CalendarDaysIcon class="h-10 w-10 mb-2 opacity-40 text-admin" />
        <p class="text-xs font-medium">No recent appointment activity</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { CalendarDaysIcon, ArrowRightIcon } from '@heroicons/vue/24/outline';

defineProps({
  appointments: { type: Array, default: () => [] }
});

const getInitials = (name) => {
  if (!name) return 'WI';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'Today';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const statusBadgeClass = (status) => {
  const map = {
    completed: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    confirmed: 'bg-sky-500/10 text-sky-400 border-sky-500/20',
    pending: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    cancelled: 'bg-red-500/10 text-red-400 border-red-500/20',
    no_show: 'bg-purple-500/10 text-purple-400 border-purple-500/20'
  };
  return map[status] || 'bg-white/5 text-white/60 border-white/10';
};
</script>
