<template>
  <BarberLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Schedule</p>
          <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
            Working Hours & <span class="text-gold">Bookings</span>
          </h1>
        </div>
      </div>

      <!-- Grid: Working Hours + Upcoming -->
      <div class="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
        <!-- Working Hours Card -->
        <div class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm overflow-hidden">
          <div class="flex items-center gap-3 border-b border-theme-border px-6 py-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/10">
              <ClockIcon class="h-5 w-5 text-gold" />
            </div>
            <h2 class="font-display text-lg text-theme-text">Working Hours</h2>
          </div>
          <div class="p-4 space-y-1.5">
            <div
              v-for="hour in schedule.working_hours || []"
              :key="hour.id"
              class="group flex items-center justify-between rounded-xl px-4 py-3 transition-colors hover:bg-theme-surface/50"
            >
              <div class="flex items-center gap-3">
                <div class="h-2 w-2 rounded-full" :class="hour.is_closed ? 'bg-red-400/60' : 'bg-emerald-400/60'"></div>
                <span class="text-sm text-ivory/70 font-medium">{{ dayName(hour.day_of_week) }}</span>
              </div>
              <span
                class="text-sm font-medium tracking-wider"
                :class="hour.is_closed ? 'text-red-400/50' : 'text-ivory/50'"
              >
                {{ hour.is_closed ? 'Closed' : `${hour.open_time} – ${hour.close_time}` }}
              </span>
            </div>

            <div v-if="(schedule.working_hours || []).length === 0" class="py-8 text-center">
              <p class="text-sm text-ivory/30">No working hours configured.</p>
            </div>
          </div>
        </div>

        <!-- Upcoming Appointments Card -->
        <div class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm overflow-hidden">
          <div class="flex items-center gap-3 border-b border-theme-border px-6 py-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-400/10">
              <CalendarDaysIcon class="h-5 w-5 text-blue-400" />
            </div>
            <h2 class="font-display text-lg text-theme-text">Upcoming Appointments</h2>
          </div>

          <div class="divide-y divide-white/[0.04]">
            <div
              v-for="appointment in schedule.appointments || []"
              :key="appointment.id"
              class="group flex items-start gap-4 px-6 py-4 transition-colors hover:bg-theme-surface/50"
            >
              <!-- Date Block -->
              <div class="flex flex-col items-center shrink-0 rounded-xl bg-white/[0.04] border border-theme-border px-3 py-2 min-w-[56px]">
                <span class="text-[10px] uppercase tracking-wider text-ivory/30 font-semibold">{{ getMonth(appointment.appointment_date) }}</span>
                <span class="text-lg font-bold text-theme-text font-display leading-tight">{{ getDay(appointment.appointment_date) }}</span>
              </div>

              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-theme-text truncate">{{ appointment.client_name }}</p>
                <p class="mt-0.5 text-xs text-ivory/40">{{ appointment.service?.name || 'General' }}</p>
                <div class="mt-1.5 flex items-center gap-2">
                  <ClockIcon class="h-3 w-3 text-gold/50" />
                  <span class="text-xs text-gold/50 font-medium tracking-wider">{{ appointment.appointment_time }}</span>
                </div>
              </div>
            </div>

            <div v-if="(schedule.appointments || []).length === 0" class="px-6 py-12 text-center">
              <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/[0.04]">
                <CalendarDaysIcon class="h-7 w-7 text-ivory/20" />
              </div>
              <p class="mt-4 text-sm text-ivory/30">No upcoming appointments.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </BarberLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { ClockIcon, CalendarDaysIcon } from '@heroicons/vue/24/outline';

const schedule = ref({});

const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

function dayName(dayNum) {
  return dayNames[dayNum] || `Day ${dayNum}`;
}

function getMonth(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleString('en-US', { month: 'short' });
}

function getDay(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.getDate();
}

onMounted(async () => {
  try {
    const response = await barberApi.schedule();
    schedule.value = response.data.data;
  } catch (e) {
    // API may not have data yet
  }
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>