<template>
  <AdminLayout>
    <section class="space-y-6">
      <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)]">Appointments</p>
        <h1 class="mt-3 font-display text-4xl text-admin">Manage bookings</h1>
      </div>

      <div class="grid gap-4">
        <article v-for="appointment in appointments.data || appointments" :key="appointment.id" class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
              <p class="font-display text-2xl text-obsidian">{{ appointment.client_name }}</p>
              <p class="text-sm text-[var(--color-text-muted)]">{{ appointment.service?.name }} with {{ appointment.barber?.name }} on {{ appointment.appointment_date }} at {{ appointment.appointment_time }}</p>
            </div>
            <div class="flex gap-3 text-xs font-semibold">
              <span class="rounded-full bg-cream px-3 py-1 text-obsidian">{{ appointment.status }}</span>
              <button class="rounded-full bg-admin px-3 py-1 text-obsidian" @click="approve(appointment.id)">Approve</button>
              <button class="rounded-full border border-[var(--color-border)] px-3 py-1" @click="cancel(appointment.id)">Cancel</button>
            </div>
          </div>
        </article>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';

const appointments = ref([]);

async function loadAppointments() {
  const response = await adminApi.appointments();
  appointments.value = response.data.data;
}

async function approve(id) {
  await adminApi.approveAppointment(id);
  await loadAppointments();
}

async function cancel(id) {
  await adminApi.cancelAppointment(id);
  await loadAppointments();
}

onMounted(loadAppointments);
</script>