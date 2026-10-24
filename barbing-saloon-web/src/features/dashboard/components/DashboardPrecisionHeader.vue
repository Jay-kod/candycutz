<template>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
    <!-- Greeting -->
    <div>
      <h1 class="text-2xl sm:text-[26px] font-bold text-white tracking-tight flex items-center gap-2 font-sans">
        <span>Welcome back, {{ userName }}!</span>
        <span class="inline-block hover:rotate-12 transition-transform cursor-default select-none">👋</span>
      </h1>
      <p class="text-xs text-ivory/50 mt-0.5">CandyCutz Salon Overview & Real-Time Performance</p>
    </div>

    <!-- Right Quick Action -->
    <div class="flex items-center gap-3">
      <RouterLink
        to="/admin/walk-in"
        class="inline-flex items-center gap-1.5 rounded-lg border border-admin/30 bg-admin/10 hover:bg-admin hover:text-white text-xs font-semibold text-admin px-3.5 py-2 transition-all active:scale-95 shadow-sm group"
      >
        <PlusIcon class="h-3.5 w-3.5 text-admin group-hover:text-white transition-colors" />
        <span>New Walk-In</span>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { PlusIcon } from '@heroicons/vue/24/outline';
import { useAuthStore } from '@/modules/auth/store/auth.store';

const authStore = useAuthStore();

const userName = computed(() => {
  const name = authStore.user?.name || 'Admin';
  return name.split(' ')[0] || 'Admin';
});
</script>
