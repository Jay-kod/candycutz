<template>
  <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6 shadow-sm lg:col-span-2">
    <div class="flex items-center justify-between pb-4 border-b border-[var(--color-border-subtle)]">
      <h2 class="font-display text-lg sm:text-xl text-[var(--color-text-primary)] flex items-center gap-2">
        <BellIcon class="w-5 h-5 text-[var(--color-accent)]" />
        <span>Recent <strong class="text-[var(--color-accent)] font-semibold">Activity & Alerts</strong></span>
      </h2>
      <RouterLink to="/customer/dashboard/notifications" class="text-xs font-bold text-[var(--color-accent)] hover:underline">
        View notification inbox &rarr;
      </RouterLink>
    </div>

    <div class="mt-5 space-y-3">
      <div 
        v-for="notif in notifications.slice(0, 5)" 
        :key="notif.id" 
        class="flex gap-3.5 p-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 hover:bg-[var(--color-bg-tertiary)] transition-colors"
      >
        <div class="h-9 w-9 shrink-0 overflow-hidden rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 flex items-center justify-center text-[var(--color-accent)] font-display text-xs font-bold">
          {{ notif.sender_name?.charAt(0) || 'C' }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <h3 class="font-semibold text-xs sm:text-sm text-[var(--color-text-primary)] truncate">
              {{ notif.title || notif.data?.title || 'System Notification' }}
            </h3>
            <span class="text-[10px] font-mono text-[var(--color-text-muted)] shrink-0">
              {{ notif.created_at ? new Date(notif.created_at).toLocaleDateString() : '' }}
            </span>
          </div>
          <p class="mt-0.5 text-xs text-[var(--color-text-secondary)] leading-relaxed">
            {{ notif.message || notif.data?.message }}
          </p>
        </div>
      </div>
      
      <div v-if="notifications.length === 0" class="rounded-xl border border-dashed border-[var(--color-border)] p-8 text-center bg-[var(--color-bg-tertiary)]/30">
        <BellIcon class="h-6 w-6 text-[var(--color-text-muted)] mx-auto mb-2 opacity-50" />
        <p class="text-xs text-[var(--color-text-secondary)]">No new alerts at this time.</p>
      </div>
    </div>
  </article>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { BellIcon } from '@heroicons/vue/24/outline';

defineProps({
  notifications: {
    type: Array,
    default: () => [],
  },
});
</script>

