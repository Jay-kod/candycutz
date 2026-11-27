<template>
  <header
    class="flex h-14 sm:h-20 items-center justify-between border-b px-2 xs:px-3 sm:px-6 backdrop-blur-xl sticky top-0 z-30 shrink-0 transition-colors duration-300"
    :class="[themeClasses.headerBg || 'bg-[var(--color-bg-secondary)]/90', themeClasses.headerBorder || 'border-[var(--color-border)]']"
  >
    <div class="flex flex-1 items-center gap-1.5 xs:gap-2.5 sm:gap-4 min-w-0 pr-2">
      <!-- Mobile Hamburger -->
      <button
        @click="$emit('toggleMobile')"
        class="min-w-[44px] min-h-[44px] flex items-center justify-center rounded-lg p-2 text-[var(--color-text-secondary)] hover:bg-[var(--color-bg-tertiary)] hover:text-[var(--color-text-primary)] lg:hidden transition-colors shrink-0"
        aria-label="Open navigation menu"
      >
        <Bars3Icon class="h-6 w-6" />
      </button>

      <!-- Desktop Collapse Toggle -->
      <button
        @click="$emit('toggleCollapse')"
        :class="['hidden lg:flex items-center justify-center min-w-[44px] min-h-[44px] rounded-xl border border-[var(--color-border)] text-[var(--color-text-secondary)] transition-all bg-[var(--color-bg-secondary)] hover:bg-[var(--color-bg-tertiary)]', themeClasses.borderHover, themeClasses.hoverText]"
        aria-label="Toggle sidebar collapse"
      >
        <Bars3CenterLeftIcon v-if="!isSidebarCollapsed" class="h-5 w-5" />
        <Bars3Icon v-else class="h-5 w-5" />
      </button>

      <!-- Breadcrumb/Title -->
      <h2 class="font-display text-sm xs:text-base sm:text-xl truncate" :class="themeClasses.headerTitle || 'text-[var(--color-text-primary)]'">
        {{ currentRouteName }}
      </h2>
    </div>

    <div class="flex items-center gap-0.5 xs:gap-1 sm:gap-4 shrink-0">
      <div class="flex items-center gap-1 xs:gap-2">
        <!-- Notification Bell -->
        <NotificationBell />
      </div>

      <!-- User Profile Action -->
      <button
        type="button"
        @click="openProfile"
        :class="['group flex min-h-[44px] items-center gap-2 rounded-full border p-1 pr-1 sm:pr-3 transition-all active:scale-[0.97]', themeClasses.userPill || 'border-[var(--color-border)] bg-[var(--color-bg-secondary)]']"
        aria-label="Open profile settings"
      >
        <div v-if="userAvatarUrl" class="h-9 w-9 sm:h-10 sm:w-10 rounded-full overflow-hidden border-2 border-[var(--color-accent)]/50 shadow-sm ring-2 ring-white/10 shrink-0">
          <img :src="userAvatarUrl" alt="Open profile" class="h-full w-full object-cover" />
        </div>
        <div v-else :class="['flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full border-2 border-[var(--color-accent)]/50 text-xs sm:text-sm font-bold shadow-sm ring-2 ring-white/10 shrink-0', themeClasses.bg20, themeClasses.text]">
          {{ userInitials }}
        </div>
        <span class="text-xs xs:text-sm font-medium hidden md:block truncate max-w-[120px]" :class="themeClasses.headerTitle || 'text-[var(--color-text-primary)]'">{{ userName }}</span>
      </button>
    </div>
  </header>
</template>

<script setup>
import NotificationBell from '@/core/components/NotificationBell.vue';
import { useRouter } from 'vue-router';
import {
  Bars3Icon,
  Bars3CenterLeftIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  currentRouteName: { type: String, default: 'Dashboard' },
  isSidebarCollapsed: { type: Boolean, default: false },
  themeClasses: { type: Object, required: true },
  userAvatarUrl: { type: String, default: null },
  userInitials: { type: String, default: 'U' },
  userName: { type: String, default: 'User' },
  profileRoute: { type: String, default: '' },
});

defineEmits(['toggleMobile', 'toggleCollapse']);

const router = useRouter();

const openProfile = () => {
  if (props.profileRoute) router.push(props.profileRoute);
};
</script>
