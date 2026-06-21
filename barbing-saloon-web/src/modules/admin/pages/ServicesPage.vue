<template>
  <AdminLayout>
    <section class="space-y-6">
      <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)]">Services</p>
        <h1 class="mt-3 font-display text-4xl text-admin">Service catalog</h1>
      </div>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="service in services" :key="service.id" class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
          <p class="text-xs uppercase tracking-[0.25em] text-admin-dark">{{ service.category?.name }}</p>
          <h2 class="mt-3 font-display text-2xl text-obsidian">{{ service.name }}</h2>
          <p class="mt-3 text-sm text-[var(--color-text-muted)]">₦{{ service.price }} | {{ service.duration_minutes }} mins</p>
        </article>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';

const services = ref([]);

onMounted(async () => {
  const response = await adminApi.services();
  services.value = response.data.data;
});
</script>