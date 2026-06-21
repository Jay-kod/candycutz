<template>
  <SuperAdminLayout>
    <section class="space-y-6">
      <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)]">Audit Logs</p>
        <h1 class="mt-3 font-display text-4xl text-gold">Activity log (read-only)</h1>
      </div>

      <div class="space-y-3">
        <article v-for="log in logs.data || logs" :key="log.id" class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
              <p class="font-semibold text-obsidian">{{ log.user?.name }}</p>
              <p class="text-sm text-[var(--color-text-muted)]">{{ log.action }} · {{ log.module }} · {{ log.target_type }}#{{ log.target_id }}</p>
              <p class="mt-1 text-xs text-[var(--color-text-muted)]">{{ log.created_at }}</p>
            </div>
            <div class="text-right text-xs">
              <p class="text-gold-dark">{{ log.ip_address }}</p>
              <p class="text-[var(--color-text-muted)]">{{ log.user_role }}</p>
            </div>
          </div>
        </article>
      </div>
    </section>
  </SuperAdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import SuperAdminLayout from '../layouts/SuperAdminLayout.vue';
import { superadminApi } from '../api/superadmin.api';

const logs = ref([]);

onMounted(async () => {
  const response = await superadminApi.auditLogs();
  logs.value = response.data.data;
});
</script>
