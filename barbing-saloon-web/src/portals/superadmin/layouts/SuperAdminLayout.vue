<template>
  <div class="min-h-screen bg-theme-bg text-theme-text">
    <header class="border-b border-theme-border bg-theme-surface/95 backdrop-blur">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
        <RouterLink to="/superadmin/dashboard" class="font-display text-2xl text-gold">CandyCutz Super Admin</RouterLink>
        <nav class="flex flex-wrap gap-4 text-sm font-semibold">
          <RouterLink to="/superadmin/dashboard">Dashboard</RouterLink>
          <RouterLink to="/superadmin/users">Users</RouterLink>
          <RouterLink to="/superadmin/settings">Settings</RouterLink>
          <RouterLink to="/superadmin/audit-logs">Audit Logs</RouterLink>
        </nav>
      </div>
    </header>
    <main class="mx-auto max-w-7xl px-6 py-10">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>
