<template>
  <header class="flex h-20 items-center justify-between border-b border-theme-border px-6 backdrop-blur-md sticky top-0 z-30 bg-theme-bg/90">
    <div class="flex items-center gap-4">
      <!-- Mobile Hamburger -->
      <button
        @click="$emit('toggleMobile')"
        class="rounded-lg p-2 text-theme-muted hover:bg-theme-surface hover:text-theme-text lg:hidden transition-colors"
      >
        <Bars3Icon class="h-6 w-6" />
      </button>

      <!-- Desktop Collapse Toggle -->
      <button
        @click="$emit('toggleCollapse')"
        :class="['hidden lg:flex items-center justify-center h-10 w-10 rounded-xl border border-theme-border text-theme-muted transition-all bg-theme-surface', themeClasses.borderHover, themeClasses.hoverText]"
      >
        <Bars3CenterLeftIcon v-if="!isSidebarCollapsed" class="h-5 w-5" />
        <Bars3Icon v-else class="h-5 w-5" />
      </button>

      <!-- Breadcrumb/Title -->
      <h2 class="font-display text-xl text-theme-text hidden sm:block">
        {{ currentRouteName }}
      </h2>
    </div>

    <div class="flex items-center gap-4">
      <div class="flex items-center gap-2">
        <!-- Notification Bell -->
        <NotificationBell />

        <!-- Theme Toggle -->
        <button @click="toggleDark()" class="p-2 text-theme-muted hover:text-theme-text transition-colors" aria-label="Toggle theme">
          <SunIcon v-if="isDark" class="h-6 w-6" />
          <MoonIcon v-else class="h-6 w-6" />
        </button>
      </div>

      <!-- User Profile Snippet -->
      <div class="flex items-center gap-3 rounded-full border border-theme-border py-1.5 pl-1.5 pr-4 bg-theme-surface">
        <div v-if="userAvatarUrl" class="h-8 w-8 rounded-full overflow-hidden border border-theme-border">
          <img :src="userAvatarUrl" alt="Avatar" class="h-full w-full object-cover" />
        </div>
        <div v-else :class="['flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold', themeClasses.bg20, themeClasses.text]">
          {{ userInitials }}
        </div>
        <span class="text-sm font-medium text-theme-text hidden md:block">{{ userName }}</span>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useDark, useToggle } from '@vueuse/core';
import NotificationBell from '@/core/components/NotificationBell.vue';
import {
  SunIcon,
  MoonIcon,
  Bars3Icon,
  Bars3CenterLeftIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  currentRouteName: { type: String, default: 'Dashboard' },
  isSidebarCollapsed: { type: Boolean, default: false },
  themeClasses: { type: Object, required: true },
  userAvatarUrl: { type: String, default: null },
  userInitials: { type: String, default: 'U' },
  userName: { type: String, default: 'User' },
});

defineEmits(['toggleMobile', 'toggleCollapse']);

const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
});
const toggleDark = useToggle(isDark);
</script>
