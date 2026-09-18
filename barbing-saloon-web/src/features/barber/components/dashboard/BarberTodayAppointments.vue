<template>
  <div class="rounded-2xl border border-white/[0.06] bg-theme-surface/60 backdrop-blur-sm overflow-hidden">
    <div class="flex items-center justify-between border-b border-white/[0.04] px-6 py-4">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/10 border border-gold/15">
          <CalendarDaysIcon class="h-4.5 w-4.5 text-gold" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-theme-text">Today's Appointments</h2>
          <p class="text-[10px] text-ivory/35 mt-0.5">Your schedule for the day</p>
        </div>
      </div>
      <RouterLink
        to="/barber/appointments"
        class="text-[11px] font-semibold text-gold/70 hover:text-gold transition-colors"
      >
        View All →
      </RouterLink>
    </div>

    <div class="divide-y divide-white/[0.03]">
      <div
        v-for="appointment in appointments"
        :key="appointment.id"
        class="group flex items-start gap-5 px-6 py-5 transition-colors hover:bg-white/[0.02]"
      >
        <!-- Time Indicator -->
        <div class="flex flex-col items-center pt-1.5 min-w-[50px]">
          <span class="text-xs font-bold text-gold tracking-wider">{{ appointment.appointment_time }}</span>
        </div>
        
        <div class="h-full w-px bg-white/5 relative top-1"></div>

        <div class="flex-1 min-w-0 bg-[#1a1a1a]/50 p-4 rounded-xl border border-white/5 group-hover:border-white/10 transition-colors">
          <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-semibold text-theme-text truncate flex items-center gap-2">
              {{ appointment.client_name }}
              <span v-if="appointment.status === 'confirmed'" class="flex h-2 w-2 rounded-full bg-emerald-400"></span>
              <span v-else-if="appointment.status === 'completed'" class="flex h-2 w-2 rounded-full bg-blue-400"></span>
            </p>
          </div>
          <p class="mt-1 text-xs text-ivory/40 flex items-center gap-1.5">
            <SparklesIcon class="h-3.5 w-3.5 text-ivory/30" />
            {{ appointment.service?.name || 'General' }}
          </p>
          <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest"
              :class="statusClass(appointment.status)"
            >
              {{ appointment.status }}
            </span>
            <button v-if="appointment.status === 'confirmed'"
              @click="$emit('complete', appointment.id)"
              class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-3 py-1 text-[10px] font-bold text-emerald-400 uppercase tracking-widest hover:bg-emerald-500 hover:text-white transition-colors border border-emerald-500/20 hover:border-emerald-500"
            >
              <CheckIcon class="h-3.5 w-3.5" />
              Complete
            </button>
            <button v-if="appointment.status === 'confirmed' || appointment.status === 'pending'"
              @click="$emit('no-show', appointment.id)"
              class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/20 bg-red-500/5 px-3 py-1 text-[10px] font-bold text-red-400 uppercase tracking-widest hover:bg-red-500 hover:text-white transition-colors hover:border-red-500"
            >
              No show
            </button>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="appointments.length === 0" class="px-6 py-16 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white/[0.03] border border-white/[0.05] mb-4">
          <CalendarDaysIcon class="h-8 w-8 text-ivory/20" />
        </div>
        <p class="text-sm font-semibold text-ivory/50">No appointments today</p>
        <p class="mt-1 text-xs text-ivory/30 max-w-[200px] mx-auto leading-relaxed">Enjoy your day off or double check your schedule availability.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { CalendarDaysIcon, SparklesIcon, CheckIcon } from '@heroicons/vue/24/outline';

defineProps({
  appointments: { type: Array, required: true }
});

defineEmits(['complete', 'no-show']);

function statusClass(status) {
  const s = (status || '').toLowerCase();
  if (s === 'confirmed' || s === 'completed') return 'bg-emerald-400/10 text-emerald-400 border border-emerald-400/20';
  if (s === 'pending') return 'bg-gold/10 text-gold border border-gold/20';
  if (s === 'cancelled' || s === 'no_show') return 'bg-red-400/10 text-red-400 border border-red-400/20';
  return 'bg-white/[0.06] text-ivory/50 border border-white/10';
}
</script>
