<template>
  <AdminLayout>
    <section class="space-y-8">
      <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-[var(--color-text-muted)]">User Management</p>
        <h1 class="mt-3 font-display text-4xl text-admin">Customers</h1>
      </div>

      <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-8">
        <p class="text-theme-muted">Manage customer accounts and histories here.</p>
        <!-- Implementation goes here -->
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '../layouts/AdminLayout.vue';
</script>
