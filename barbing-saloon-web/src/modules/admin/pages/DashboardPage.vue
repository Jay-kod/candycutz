<template>
  <AdminLayout>
    <section class="space-y-8">
      <div class="rounded-3xl border border-theme-border bg-theme-surface p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-theme-muted">Admin Dashboard</p>
        <h1 class="mt-3 font-display text-4xl text-admin">Operational overview</h1>
      </div>

      <div class="grid gap-4 md:grid-cols-4">
        <article v-for="item in statsCards" :key="item.label" class="rounded-3xl border border-theme-border bg-theme-surface p-6">
          <p class="text-sm text-theme-muted">{{ item.label }}</p>
          <p class="mt-3 font-display text-3xl text-obsidian">{{ item.value }}</p>
        </article>
      </div>

      <div class="rounded-3xl border border-theme-border bg-theme-surface p-6">
        <h2 class="font-display text-2xl text-admin">Reports snapshot</h2>
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <article class="rounded-2xl border border-white/70 bg-white px-4 py-4">
            <p class="text-sm text-theme-muted">Revenue estimate</p>
            <p class="mt-2 font-display text-2xl text-obsidian">₦{{ dashboard.reports?.revenue_estimate ?? 0 }}</p>
          </article>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';

const dashboard = ref({ stats: {}, reports: {} });

const statsCards = computed(() => [
  { label: 'Appointments today', value: dashboard.value.stats?.appointments_today ?? 0 },
  { label: 'Pending', value: dashboard.value.stats?.pending_appointments ?? 0 },
  { label: 'Services', value: dashboard.value.stats?.services ?? 0 },
  { label: 'Barbers', value: dashboard.value.stats?.barbers ?? 0 },
]);

onMounted(async () => {
  const response = await adminApi.dashboard();
  dashboard.value = response.data.data;
});
</script>