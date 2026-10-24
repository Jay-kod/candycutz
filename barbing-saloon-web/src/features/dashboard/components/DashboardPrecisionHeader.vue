<template>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
    <!-- Greeting -->
    <div>
      <h1 class="text-2xl sm:text-[26px] font-bold text-white tracking-tight flex items-center gap-2">
        <span>Welcome back, {{ userName }}!</span>
        <span class="inline-block hover:rotate-12 transition-transform cursor-default">👋</span>
      </h1>
    </div>

    <!-- Right Quick Action -->
    <div class="flex items-center gap-3">
      <RouterLink
        to="/admin/walk-in"
        class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-[#16181D] hover:bg-[#1E2128] hover:border-white/20 text-xs font-semibold text-white/90 px-3.5 py-2 transition-all active:scale-95 shadow-sm group"
      >
        <PlusIcon class="h-3.5 w-3.5 text-white/70 group-hover:text-white transition-colors" />
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
  // Use first name for a warm, clean greeting
  return name.split(' ')[0] || 'Admin';
});
</script>

