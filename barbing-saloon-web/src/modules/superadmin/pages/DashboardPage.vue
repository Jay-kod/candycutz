<template>
  <SuperAdminLayout>
    <section class="space-y-8">
      <div class="rounded-xl border border-theme-border bg-theme-surface p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-theme-muted">Super Admin Dashboard</p>
        <h1 class="mt-3 font-display text-4xl text-gold">System overview</h1>
      </div>

      <div class="grid gap-4 md:grid-cols-5">
        <article v-for="item in statsCards" :key="item.label" class="rounded-xl border border-theme-border bg-theme-surface p-6">
          <p class="text-sm text-theme-muted">{{ item.label }}</p>
          <p class="mt-3 font-display text-3xl text-obsidian tabular-nums">{{ item.value }}</p>
        </article>
      </div>

      <div class="rounded-xl border border-theme-border bg-theme-surface p-6">
        <h2 class="font-display text-2xl text-gold">Recent activity</h2>
        <div class="mt-6 space-y-3">
          <div v-for="log in dashboard.recent_logs || []" :key="log.id" class="flex items-center justify-between rounded-lg border border-theme-border bg-white px-4 py-3">
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
import SuperAdminLayout from '@/portals/superadmin/layouts/SuperAdminLayout.vue';
import { superadminApi } from '@/shared/api/old_superadminApi';

const dashboard = ref({ stats: {}, recent_logs: [] });

const formatNumber = (val) => new Intl.NumberFormat('en-NG').format(Number(val || 0));

const statsCards = computed(() => [
  { label: 'Total users', value: formatNumber(dashboard.value.stats?.total_users) },
  { label: 'Active', value: formatNumber(dashboard.value.stats?.active_users) },
  { label: 'Admins', value: formatNumber(dashboard.value.stats?.total_admins) },
  { label: 'Barbers', value: formatNumber(dashboard.value.stats?.barbers) },
  { label: 'Customers', value: formatNumber(dashboard.value.stats?.customers) },
]);

onMounted(async () => {
  const response = await superadminApi.dashboard();
  dashboard.value = response.data.data;
});
</script>
