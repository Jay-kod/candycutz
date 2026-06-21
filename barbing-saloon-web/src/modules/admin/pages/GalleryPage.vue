<template>
  <AdminLayout>
    <section class="space-y-6">
      <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)]">Gallery</p>
        <h1 class="mt-3 font-display text-4xl text-admin">Gallery management</h1>
      </div>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="item in gallery" :key="item.id" class="overflow-hidden rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)]">
          <div class="flex aspect-[4/3] items-end bg-[linear-gradient(135deg,rgba(13,13,13,0.82),rgba(201,168,76,0.24))] p-6 text-ivory">
            <div>
              <p class="text-xs uppercase tracking-[0.25em] text-admin-light">{{ item.category }}</p>
              <h2 class="mt-2 font-display text-2xl">{{ item.title }}</h2>
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

const gallery = ref([]);

onMounted(async () => {
  const response = await adminApi.gallery();
  gallery.value = response.data.data;
});
</script>