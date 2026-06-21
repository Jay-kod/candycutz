<template>
  <AdminLayout>
    <section class="space-y-6">
      <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)]">Blog</p>
        <h1 class="mt-3 font-display text-4xl text-admin">Content publishing</h1>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <article v-for="post in posts" :key="post.id" class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6">
          <p class="text-xs uppercase tracking-[0.25em] text-admin-dark">{{ post.status }}</p>
          <h2 class="mt-3 font-display text-2xl text-obsidian">{{ post.title }}</h2>
          <p class="mt-3 text-sm text-[var(--color-text-muted)]">{{ post.excerpt }}</p>
        </article>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';

const posts = ref([]);

onMounted(async () => {
  const response = await adminApi.blogPosts();
  posts.value = response.data.data;
});
</script>