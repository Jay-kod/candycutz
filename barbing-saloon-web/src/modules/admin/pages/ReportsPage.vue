<template>
  <AdminLayout>
    <section class="space-y-6">
      <div class="rounded-3xl border border-theme-border bg-theme-surface p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-theme-muted">Reports</p>
        <h1 class="mt-3 font-display text-4xl text-admin">Operational reports</h1>
      </div>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl border border-theme-border bg-theme-surface p-6">
          <p class="text-sm text-theme-muted">Revenue estimate</p>
          <p class="mt-3 font-display text-3xl text-obsidian">₦{{ reports.revenue_estimate ?? 0 }}</p>
        </article>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';

const reports = ref({});

onMounted(async () => {
  const response = await adminApi.reports();
  reports.value = response.data.data;
});
</script>