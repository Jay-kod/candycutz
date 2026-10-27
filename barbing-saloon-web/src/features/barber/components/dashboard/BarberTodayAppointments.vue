<template>
  <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] backdrop-blur-sm overflow-hidden">
    <div class="flex items-center justify-between border-b border-[var(--color-border)] px-6 py-4">
      <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20">
          <CalendarDaysIcon class="h-4.5 w-4.5 text-[var(--color-accent)]" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-[var(--color-text-primary)]">Today's Appointments</h2>
          <p class="text-[10px] text-[var(--color-text-secondary)] mt-0.5">Your schedule for the day</p>
        </div>
      </div>
      <RouterLink
        to="/barber/appointments"
        class="text-[11px] font-semibold text-[var(--color-accent)] hover:brightness-110 transition-colors"
      >
        View All →
      </RouterLink>
    </div>

    <div class="divide-y divide-[var(--color-border)]">
      <div
        v-for="appointment in appointments"
        :key="appointment.id"
        class="group flex items-start gap-5 px-6 py-5 transition-colors hover:bg-[var(--color-bg-tertiary)]/50"
      >
        <!-- Time Indicator -->
        <div class="flex flex-col items-center pt-1.5 min-w-[50px]">
          <span class="text-xs font-bold text-[var(--color-accent)] tracking-wider">{{ appointment.appointment_time }}</span>
        </div>
        
        <div class="h-full w-px bg-[var(--color-border)] relative top-1"></div>

        <div class="flex-1 min-w-0 bg-[var(--color-bg-tertiary)] p-4 rounded-xl border border-[var(--color-border)] group-hover:border-[var(--color-accent)]/30 transition-colors">
          <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-semibold text-[var(--color-text-primary)] truncate flex items-center gap-2">
              {{ appointment.client_name }}
              <span v-if="appointment.status === 'confirmed'" class="flex h-2 w-2 rounded-full bg-emerald-400"></span>
              <span v-else-if="appointment.status === 'completed'" class="flex h-2 w-2 rounded-full bg-blue-400"></span>
            </p>
          </div>
          <p class="mt-1 text-xs text-[var(--color-text-secondary)] flex items-center gap-1.5">
            <SparklesIcon class="h-3.5 w-3.5 text-[var(--color-text-muted)]" />
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
              class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-3 py-1 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest hover:bg-emerald-500 hover:text-white transition-colors border border-emerald-500/20 hover:border-emerald-500"
            >
              <CheckIcon class="h-3.5 w-3.5" />
              Complete
            </button>
            <button v-if="appointment.status === 'confirmed' || appointment.status === 'pending'"
              @click="$emit('no-show', appointment.id)"
              class="inline-flex items-center gap-1.5 rounded-lg border border-red-500/20 bg-red-500/5 px-3 py-1 text-[10px] font-bold text-red-500 dark:text-red-400 uppercase tracking-widest hover:bg-red-500 hover:text-white transition-colors hover:border-red-500"
            >
              No show
            </button>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="appointments.length === 0" class="px-6 py-16 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] mb-4">
          <CalendarDaysIcon class="h-8 w-8 text-[var(--color-text-muted)]" />
        </div>
        <p class="text-sm font-semibold text-[var(--color-text-secondary)]">No appointments today</p>
        <p class="mt-1 text-xs text-[var(--color-text-muted)] max-w-[200px] mx-auto leading-relaxed">Enjoy your day off or double check your schedule availability.</p>
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
  if (s === 'confirmed' || s === 'completed') return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20';
  if (s === 'pending') return 'bg-[var(--color-accent)]/10 text-[var(--color-accent)] border border-[var(--color-accent)]/20';
  if (s === 'cancelled' || s === 'no_show') return 'bg-red-500/10 text-red-500 dark:text-red-400 border border-red-500/20';
  return 'bg-[var(--color-bg-tertiary)] text-[var(--color-text-muted)] border border-[var(--color-border)]';
}
</script>
