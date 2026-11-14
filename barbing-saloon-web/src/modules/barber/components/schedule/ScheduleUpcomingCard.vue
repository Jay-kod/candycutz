<template>
  <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] overflow-hidden shadow-sm dark:shadow-2xl">
    <div class="flex items-center gap-3 border-b border-slate-200 dark:border-white/[0.08] px-6 py-5 bg-slate-50 dark:bg-[#0B0F13]/40">
      <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gold/10 border border-gold/20">
        <CalendarDaysIcon class="h-4 w-4 text-gold" />
      </div>
      <div>
        <h2 class="font-display text-lg text-slate-900 dark:text-white font-bold">Upcoming Bookings</h2>
        <p class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-widest font-semibold">Pending and confirmed appointments</p>
      </div>
    </div>

    <div v-if="appointments.length === 0" class="px-6 py-14 text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06] mb-3">
        <CalendarDaysIcon class="h-6 w-6 text-slate-500" />
      </div>
      <p class="text-sm font-display font-bold text-slate-900 dark:text-white mb-0.5">No Upcoming Appointments</p>
      <p class="text-xs text-slate-500 dark:text-slate-400">Future scheduled clients will appear here automatically.</p>
    </div>

    <div v-else class="divide-y divide-slate-100 dark:divide-white/[0.06]">
      <div
        v-for="appointment in appointments"
        :key="appointment.id"
        class="flex items-start gap-3.5 px-6 py-4 hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors"
      >
        <!-- Date Block -->
        <div class="flex flex-col items-center shrink-0 rounded-2xl bg-slate-100 dark:bg-[#0B0F13] border border-slate-200 dark:border-white/[0.08] px-3 py-2 min-w-[50px] shadow-inner text-center">
          <span class="text-[9px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-bold">
            {{ getMonth(appointment.appointment_date) }}
          </span>
          <span class="text-lg font-bold text-slate-900 dark:text-white font-display leading-tight">
            {{ getDay(appointment.appointment_date) }}
          </span>
        </div>

        <div class="flex-1 min-w-0">
          <p class="text-xs font-bold text-slate-900 dark:text-white truncate">
            {{ appointment.client_name || appointment.customer?.name || 'Client' }}
          </p>
          <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
            {{ appointment.service?.name || 'Saloon Service' }}
          </p>
          <div class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-gold/10 px-2 py-0.5 border border-gold/20">
            <ClockIcon class="h-3 w-3 text-gold" />
            <span class="text-[10px] text-amber-800 dark:text-gold font-bold tracking-wider">
              {{ formatTimeDisplay(appointment.appointment_time) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { CalendarDaysIcon, ClockIcon } from '@heroicons/vue/24/outline';

defineProps({
  appointments: {
    type: Array,
    default: () => [],
  },
  getMonth: {
    type: Function,
    required: true,
  },
  getDay: {
    type: Function,
    required: true,
  },
  formatTimeDisplay: {
    type: Function,
    required: true,
  },
});
</script>
