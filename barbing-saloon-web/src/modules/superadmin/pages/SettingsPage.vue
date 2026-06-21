<template>
  <SuperAdminLayout>
    <section class="space-y-6">
      <div class="rounded-3xl border border-theme-border bg-theme-surface p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-theme-muted">Settings</p>
        <h1 class="mt-3 font-display text-4xl text-gold">System configuration</h1>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <article v-for="(value, group) in settings" :key="group" class="rounded-3xl border border-theme-border bg-theme-surface p-6">
          <h2 class="font-display text-2xl text-gold capitalize">{{ group }}</h2>
          <div class="mt-6 space-y-4">
            <div v-for="(val, key) in value" :key="key" class="grid gap-2">
              <label class="text-sm text-theme-muted">{{ key }}</label>
              <div class="rounded-2xl border border-theme-border bg-white px-4 py-3 text-obsidian">{{ val }}</div>
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

const settings = ref({});

onMounted(async () => {
  const response = await superadminApi.settings();
  settings.value = response.data.data;
});
</script>
