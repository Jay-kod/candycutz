<template>
  <div class="h-full flex shrink-0">
    <!-- Mobile Sidebar Backdrop -->
    <div
      v-if="isMobileSidebarOpen"
      class="fixed inset-0 z-40 bg-black/80 backdrop-blur-md lg:hidden transition-opacity duration-300"
      @click="$emit('closeMobile')"
    ></div>

    <!-- Sidebar Aside Container -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col h-full h-[100dvh] max-h-screen transition-all duration-300 ease-in-out',
        'lg:static lg:h-full lg:max-h-full lg:translate-x-0',
        'sidebar-panel backdrop-blur-xl',
        'bg-[var(--color-bg-secondary)]/95 dark:bg-[#0E131F]/95',
        'border-r border-[var(--color-border)] dark:border-white/[0.08]',
        isMobileSidebarOpen ? 'translate-x-0 shadow-2xl shadow-black/80' : '-translate-x-full',
        isSidebarCollapsed ? 'lg:w-[88px]' : 'w-72'
      ]"
    >
      <!-- Subtle Luxury Ambient Glow -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
        <div class="absolute -top-24 -right-24 h-56 w-56 rounded-full blur-[90px] opacity-[0.08] bg-amber-500"></div>
        <div class="absolute -bottom-24 -left-24 h-48 w-48 rounded-full blur-[80px] opacity-[0.05] bg-amber-500"></div>
      </div>

      <!-- 1. Brand & Emblem Header -->
      <SidebarBrand
        :home-route="homeRoute"
        :portal-config="portalTheme"
        :is-collapsed="isSidebarCollapsed"
        @close-mobile="$emit('closeMobile')"
      />

      <!-- 2. Scrollable Accordion Navigation -->
      <div
        ref="navScrollContainer"
        class="relative z-10 flex-1 min-h-0 overflow-y-auto overflow-x-hidden custom-scrollbar overscroll-contain"
      >
        <SidebarNavAccordion
          :groups="groupedNavItems"
          :portal-config="portalTheme"
          :is-collapsed="isSidebarCollapsed"
          :is-active-route="isActiveRoute"
          @group-toggled="ensureActiveItemVisible"
        />
      </div>

      <!-- 3. Pinned User Profile & Quick Actions Footer -->
      <SidebarUserProfile
        :portal-name="portalName"
        :profile-route="profileRoute"
        :is-collapsed="isSidebarCollapsed"
        :user-avatar-url="userAvatarUrl"
        :user-initials="userInitials"
        :user-name="userName"
        :user-email="userEmail"
        @logout="$emit('logout')"
      />
    </aside>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useTheme } from '@/core/composables/useTheme';
import { getPortalThemeConfig } from './sidebar/portalTheme';
import SidebarBrand from './sidebar/SidebarBrand.vue';
import SidebarNavAccordion from './sidebar/SidebarNavAccordion.vue';
import SidebarUserProfile from './sidebar/SidebarUserProfile.vue';

const props = defineProps({
  portalName: { type: String, default: 'Portal' },
  homeRoute: { type: String, required: true },
  profileRoute: { type: String, default: '' },
  themeClasses: { type: Object, default: () => ({}) },
  groupedNavItems: { type: Array, required: true },
  isSidebarCollapsed: { type: Boolean, default: false },
  isMobileSidebarOpen: { type: Boolean, default: false },
  isActiveRoute: { type: Function, required: true },
  userAvatarUrl: { type: String, default: null },
  userInitials: { type: String, default: 'U' },
  userName: { type: String, default: 'User' },
  userEmail: { type: String, default: '' },
});

defineEmits(['closeMobile', 'logout']);

const { isDark } = useTheme();

const portalTheme = computed(() => {
  return getPortalThemeConfig(props.portalName, isDark.value);
});

const navScrollContainer = ref(null);

const activeItemKey = computed(() => {
  for (const group of props.groupedNavItems) {
    const activeItem = group.items?.find((item) => props.isActiveRoute(item.to));
    if (activeItem) return `${group.label ?? ''}::${activeItem.to}`;
  }
  return null;
});

const ensureActiveItemVisible = async () => {
  await nextTick();
  const container = navScrollContainer.value;
  if (!container) return;

  const activeEl = container.querySelector('[data-active="true"]');
  if (!activeEl) return;

  const containerRect = container.getBoundingClientRect();
  const itemRect = activeEl.getBoundingClientRect();
  const padding = 12;

  if (containerRect.height > 0 && itemRect.height > 0) {
    const isOutOfView =
      itemRect.top < containerRect.top + padding ||
      itemRect.bottom > containerRect.bottom - padding;

    if (isOutOfView) {
      const itemOffsetInsideContainer = itemRect.top - containerRect.top;
      const targetTop =
        container.scrollTop +
        itemOffsetInsideContainer -
        (containerRect.height - itemRect.height) / 2;
      const maxScroll =
        container.scrollHeight > container.clientHeight
          ? container.scrollHeight - container.clientHeight
          : Infinity;
      container.scrollTop = Math.min(maxScroll, Math.max(0, targetTop));
    }
    return;
  }

  if (typeof activeEl.scrollIntoView === 'function') {
    activeEl.scrollIntoView({ block: 'nearest', inline: 'nearest' });
  }
};

onMounted(ensureActiveItemVisible);

watch(activeItemKey, ensureActiveItemVisible);

watch(() => props.isSidebarCollapsed, ensureActiveItemVisible);

watch(
  () => props.isMobileSidebarOpen,
  (isOpen) => {
    if (isOpen) ensureActiveItemVisible();
  }
);
</script>

<style scoped>
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(212, 175, 55, 0.45) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  border-radius: 9999px;
  background-color: rgba(212, 175, 55, 0.45);
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(212, 175, 55, 0.7);
}
</style>
