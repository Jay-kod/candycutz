<template>
  <SuperAdminLayout>
    <section class="space-y-6">
      <div class="rounded-3xl border border-theme-border bg-theme-surface p-8">
        <p class="text-sm uppercase tracking-[0.3em] text-theme-muted">Users</p>
        <h1 class="mt-3 font-display text-4xl text-gold">Staff management</h1>
      </div>

      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article v-for="user in users.data || users" :key="user.id" class="rounded-3xl border border-theme-border bg-theme-surface p-6">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h2 class="font-display text-2xl text-obsidian">{{ user.name }}</h2>
              <p class="text-xs uppercase tracking-[0.25em] text-gold-dark">{{ user.role }}</p>
              <p class="mt-2 text-sm text-theme-muted">{{ user.email }}</p>
            </div>
            <span :class="user.is_active ? 'bg-green-500' : 'bg-red-500'" class="h-3 w-3 rounded-full"></span>
          </div>
          <div class="mt-4 flex gap-2 text-xs font-semibold">
            <button v-if="!user.is_active" class="rounded-full bg-gold px-3 py-1 text-obsidian" @click="activate(user.id)">Activate</button>
            <button v-if="user.is_active" class="rounded-full border border-red-500 px-3 py-1 text-red-500" @click="deactivate(user.id)">Deactivate</button>
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

const users = ref([]);

async function loadUsers() {
  const response = await superadminApi.users();
  users.value = response.data.data;
}

async function activate(id) {
  await superadminApi.activateUser(id);
  await loadUsers();
}

async function deactivate(id) {
  await superadminApi.deactivateUser(id);
  await loadUsers();
}

onMounted(loadUsers);
</script>
