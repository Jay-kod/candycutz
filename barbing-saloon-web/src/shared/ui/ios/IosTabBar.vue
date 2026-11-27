<template>
  <nav
    class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-[var(--color-surface)]/85 backdrop-blur-xl border-t border-theme-border/40 pb-safe transition-transform duration-300 shadow-[0_-2px_12px_rgba(0,0,0,0.06)]"
    role="navigation"
    aria-label="Mobile Navigation"
  >
    <div class="grid h-14" :style="{ gridTemplateColumns: `repeat(${tabs.length}, minmax(0, 1fr))` }">
      <RouterLink
        v-for="tab in tabs"
        :key="tab.to"
        :to="tab.to"
        class="relative flex flex-col items-center justify-center pt-1.5 pb-1 transition-all group"
        :class="isActive(tab.to) ? 'text-gold' : 'text-theme-muted hover:text-theme-text'"
      >
        <!-- Icon container with unread badge -->
        <div class="relative flex items-center justify-center">
          <img
            v-if="tab.name === 'Profile' && userAvatarUrl"
            :src="userAvatarUrl"
            alt=""
            aria-hidden="true"
            class="h-6 w-6 rounded-full object-cover"
          />
          <component
            v-else
            :is="tab.icon"
            class="w-6 h-6 transition-transform duration-200"
            :class="!isActive(tab.to) ? 'stroke-[1.7] group-active:scale-95' : ''"
          />
          <span
            v-if="tab.badge"
            class="absolute -top-1 -right-2 px-1.5 py-0.2 min-w-[16px] text-[10px] font-bold text-obsidian bg-gold rounded-full text-center leading-tight shadow-sm"
          >
            {{ tab.badge }}
          </span>
        </div>

        <span class="text-[10px] font-medium tracking-tight mt-0.5 leading-none">
          {{ tab.name }}
        </span>
      </RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { useRoute } from 'vue-router';

defineProps({
  tabs: {
    type: Array,
    required: true,
  },
  userAvatarUrl: {
    type: String,
    default: null,
  },
});

const route = useRoute();

const isActive = (to) => {
  const currentPath = route?.path || '';
  if (to === '/customer/dashboard' || to === '/barber/dashboard' || to === '/admin/dashboard') {
    return currentPath === to;
  }
  return currentPath.startsWith(to);
};
</script>
