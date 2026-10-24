<template>
  <header 
    class="flex h-16 sm:h-20 items-center justify-between border-b px-3 xs:px-4 sm:px-6 backdrop-blur-xl sticky top-0 z-30 shrink-0"
    :class="[themeClasses.headerBg || 'bg-theme-bg/90', themeClasses.headerBorder || 'border-white/[0.05]']"
  >
    <div class="flex items-center gap-2 xs:gap-4 min-w-0">
      <!-- Mobile Hamburger -->
      <button
        @click="$emit('toggleMobile')"
        class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-lg p-2 text-theme-muted hover:bg-theme-surface hover:text-theme-text lg:hidden transition-colors shrink-0"
        aria-label="Open navigation menu"
      >
        <Bars3Icon class="h-6 w-6" />
      </button>

      <!-- Desktop Collapse Toggle -->
      <button
        @click="$emit('toggleCollapse')"
        :class="['hidden lg:flex items-center justify-center min-w-[44px] min-h-[44px] rounded-xl border border-theme-border text-theme-muted transition-all bg-theme-surface', themeClasses.borderHover, themeClasses.hoverText]"
        aria-label="Toggle sidebar collapse"
      >
        <Bars3CenterLeftIcon v-if="!isSidebarCollapsed" class="h-5 w-5" />
        <Bars3Icon v-else class="h-5 w-5" />
      </button>

      <!-- Breadcrumb/Title -->
      <h2 class="font-display text-lg sm:text-xl truncate hidden xs:block" :class="themeClasses.headerTitle || 'text-theme-text'">
        {{ currentRouteName }}
      </h2>
    </div>

    <div class="flex items-center gap-2 sm:gap-4 shrink-0">
      <div class="flex items-center gap-1 xs:gap-2">
        <!-- Notification Bell -->
        <NotificationBell />

        <!-- Theme Toggle -->
        <button
          @click="toggleDark()"
          :class="['min-w-[44px] min-h-[44px] flex items-center justify-center p-2 transition-colors rounded-lg', themeClasses.iconBtn || 'text-theme-muted hover:text-theme-text']"
          aria-label="Toggle theme"
        >
          <SunIcon v-if="isDark" class="h-5 w-5 sm:h-6 sm:w-6" />
          <MoonIcon v-else class="h-5 w-5 sm:h-6 sm:w-6" />
        </button>
      </div>

      <!-- User Profile Snippet -->
      <div :class="['flex items-center gap-2 xs:gap-3 rounded-full border py-1 pl-1 pr-2 xs:py-1.5 xs:pl-1.5 xs:pr-4', themeClasses.userPill || 'border-theme-border bg-theme-surface']">
        <div v-if="userAvatarUrl" class="h-7 w-7 xs:h-8 xs:w-8 rounded-full overflow-hidden border border-theme-border shrink-0">
          <img :src="userAvatarUrl" alt="Avatar" class="h-full w-full object-cover" />
        </div>
        <div v-else :class="['flex h-7 w-7 xs:h-8 xs:w-8 items-center justify-center rounded-full text-xs xs:text-sm font-bold shrink-0', themeClasses.bg20, themeClasses.text]">
          {{ userInitials }}
        </div>
        <span class="text-xs xs:text-sm font-medium hidden md:block truncate max-w-[120px]" :class="themeClasses.headerTitle || 'text-theme-text'">{{ userName }}</span>
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
