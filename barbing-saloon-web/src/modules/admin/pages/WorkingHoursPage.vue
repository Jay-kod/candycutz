<template>
  <AdminLayout>
    <section class="space-y-6">
      <div class="rounded-3xl border border-theme-border bg-theme-surface p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-theme-muted">Working Hours</p>
        <h1 class="mt-3 font-display text-4xl text-admin">Schedule coverage</h1>
      </div>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="hour in workingHours" :key="hour.id" class="rounded-3xl border border-theme-border bg-theme-surface p-6">
          <p class="font-semibold text-obsidian">Day {{ hour.day_of_week }}</p>
          <p class="mt-2 text-sm text-theme-muted">{{ hour.is_closed ? 'Closed' : `${hour.open_time} - ${hour.close_time}` }}</p>
        </article>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';

const workingHours = ref([]);

onMounted(async () => {
  const response = await adminApi.workingHours();
  workingHours.value = response.data.data;
});
</script>