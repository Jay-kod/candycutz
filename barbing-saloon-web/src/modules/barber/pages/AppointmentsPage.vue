<template>
  <BarberLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Appointments</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
              Manage Your <span class="text-gold">Chair List</span>
            </h1>
          </div>
          <div class="flex items-center gap-2 rounded-xl bg-white/[0.04] border border-theme-border px-4 py-2.5">
            <MagnifyingGlassIcon class="h-4 w-4 text-ivory/30" />
            <input
              v-model="search"
              type="text"
              placeholder="Search client..."
              class="bg-transparent text-sm text-theme-text placeholder:text-ivory/25 outline-none w-40"
            />
          </div>
        </div>
      </div>

      <!-- Appointments List -->
      <div class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm overflow-hidden">
        <!-- Table Header -->
        <div class="hidden md:grid grid-cols-[1fr_1fr_0.7fr_0.5fr_auto] gap-4 border-b border-theme-border px-6 py-3.5 text-[11px] uppercase tracking-wider text-ivory/30 font-semibold">
          <span>Client</span>
          <span>Service</span>
          <span>Date & Time</span>
          <span>Status</span>
          <span class="text-right">Actions</span>
        </div>

        <div class="divide-y divide-white/[0.04]">
          <div
            v-for="appointment in filteredAppointments"
            :key="appointment.id"
            class="group md:grid md:grid-cols-[1fr_1fr_0.7fr_0.5fr_auto] md:items-center gap-4 px-6 py-4 transition-colors hover:bg-theme-surface/50"
          >
            <!-- Client -->
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gold/10 text-gold text-xs font-bold">
                {{ initials(appointment.client_name) }}
              </div>
              <div>
                <p class="text-sm font-semibold text-theme-text">{{ appointment.client_name }}</p>
                <p class="text-[11px] text-ivory/30 md:hidden">{{ appointment.service?.name || 'General' }}</p>
              </div>
            </div>

            <!-- Service -->
            <p class="hidden md:block text-sm text-theme-muted">{{ appointment.service?.name || 'General' }}</p>

            <!-- Date & Time -->
            <div class="mt-1 md:mt-0">
              <p class="text-sm text-theme-muted font-medium tracking-wider">{{ appointment.appointment_date }}</p>
              <p class="text-[11px] text-gold/50 font-medium tracking-wider">{{ appointment.appointment_time }}</p>
            </div>

            <!-- Status -->
            <div class="mt-2 md:mt-0">
              <span
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider"
                :class="statusClass(appointment.status)"
              >
                {{ appointment.status }}
              </span>
            </div>

            <!-- Actions -->
            <div class="mt-2 md:mt-0 flex items-center gap-2 justify-end">
              <button
                @click="complete(appointment.id)"
                class="inline-flex items-center gap-1 rounded-lg bg-gold/10 px-3 py-1.5 text-[11px] font-semibold text-gold hover:bg-gold/20 transition-colors"
              >
                <CheckIcon class="h-3.5 w-3.5" />
                Complete
              </button>
              <button
                @click="markNoShow(appointment.id)"
                class="inline-flex items-center gap-1 rounded-lg border border-theme-border px-3 py-1.5 text-[11px] font-semibold text-ivory/40 hover:border-red-400/30 hover:text-red-400 transition-colors"
              >
                No show
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredAppointments.length === 0" class="px-6 py-16 text-center">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/[0.04]">
            <ClipboardDocumentCheckIcon class="h-7 w-7 text-ivory/20" />
          </div>
          <p class="mt-4 text-sm text-ivory/30">No appointments found.</p>
        </div>
      </div>
    </section>
  </BarberLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import {
  MagnifyingGlassIcon,
  CheckIcon,
  ClipboardDocumentCheckIcon,
} from '@heroicons/vue/24/outline';

const appointments = ref([]);
const search = ref('');

const filteredAppointments = computed(() => {
  const list = appointments.value.data || appointments.value;
  if (!Array.isArray(list)) return [];
  if (!search.value) return list;
  const q = search.value.toLowerCase();
  return list.filter(a => (a.client_name || '').toLowerCase().includes(q));
});

function initials(name) {
  return (name || 'U').split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

function statusClass(status) {
  const s = (status || '').toLowerCase();
  if (s === 'confirmed' || s === 'completed') return 'bg-emerald-400/10 text-emerald-400';
  if (s === 'pending') return 'bg-gold/10 text-gold';
  if (s === 'cancelled' || s === 'no_show') return 'bg-red-400/10 text-red-400';
  return 'bg-white/[0.06] text-ivory/50';
}

async function loadAppointments() {
  try {
    const response = await barberApi.appointments();
    appointments.value = response.data.data;
  } catch (e) {
    // API may not have data yet
  }
}

async function complete(id) {
  await barberApi.complete(id);
  await loadAppointments();
}

async function markNoShow(id) {
  await barberApi.noShow(id);
  await loadAppointments();
}

onMounted(loadAppointments);
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