<template>
  <SuperAdminLayout>
    <section class="space-y-8">
      <div class="rounded-3xl border border-theme-border bg-theme-surface p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-theme-muted">Super Admin Dashboard</p>
        <h1 class="mt-3 font-display text-4xl text-gold">System overview</h1>
      </div>

      <div class="grid gap-4 md:grid-cols-5">
        <article v-for="item in statsCards" :key="item.label" class="rounded-3xl border border-theme-border bg-theme-surface p-6">
          <p class="text-sm text-theme-muted">{{ item.label }}</p>
          <p class="mt-3 font-display text-3xl text-obsidian">{{ item.value }}</p>
        </article>
      </div>

      <div class="rounded-3xl border border-theme-border bg-theme-surface p-6">
        <h2 class="font-display text-2xl text-gold">Recent activity</h2>
        <div class="mt-6 space-y-3">
          <div v-for="log in dashboard.recent_logs || []" :key="log.id" class="flex items-center justify-between rounded-2xl border border-white/70 bg-white px-4 py-3">
            <div>
              <p class="font-semibold text-obsidian">{{ log.user?.name }}</p>
              <p class="text-xs text-theme-muted">{{ log.action }} in {{ log.module }}</p>
            </div>
            <span class="text-xs text-theme-muted">{{ log.created_at }}</span>
          </div>
        </div>
      </div>
    </section>
  </SuperAdminLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import SuperAdminLayout from '../layouts/SuperAdminLayout.vue';
import { superadminApi } from '../api/superadmin.api';

const dashboard = ref({ stats: {}, recent_logs: [] });

const statsCards = computed(() => [
  { label: 'Total users', value: dashboard.value.stats?.total_users ?? 0 },
  { label: 'Active', value: dashboard.value.stats?.active_users ?? 0 },
  { label: 'Admins', value: dashboard.value.stats?.total_admins ?? 0 },
  { label: 'Barbers', value: dashboard.value.stats?.barbers ?? 0 },
  { label: 'Customers', value: dashboard.value.stats?.customers ?? 0 },
]);

onMounted(async () => {
  const response = await superadminApi.dashboard();
  dashboard.value = response.data.data;
});
</script>
