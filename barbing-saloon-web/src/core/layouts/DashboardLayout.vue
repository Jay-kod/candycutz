<template>
  <div 
    class="flex h-screen h-[100dvh] text-theme-text font-sans overflow-hidden"
    :class="props.theme === 'admin' ? 'bg-[#010405]' : 'bg-theme-bg'"
  >
    <!-- Decomposed Sidebar -->
    <DashboardSidebar
      ref="sidebarRef"
      :portal-name="portalName"
      :home-route="homeRoute"
      :profile-route="profileRoute"
      :theme-classes="themeClasses"
      :grouped-nav-items="groupedNavItems"
      :is-sidebar-collapsed="isSidebarCollapsed"
      :is-mobile-sidebar-open="isMobileSidebarOpen"
      :is-active-route="isActiveRoute"
      :user-avatar-url="userAvatarUrl"
      :user-initials="userInitials"
      :user-name="authStore.user?.name || 'User'"
      :user-email="authStore.user?.email || ''"
      @close-mobile="isMobileSidebarOpen = false"
      @logout="handleLogout"
    />

    <!-- Main Content Area -->
    <div class="flex flex-1 flex-col h-full min-h-0 min-w-0 overflow-hidden">
      <!-- Decomposed Header -->
      <DashboardHeader
        class="shrink-0"
        :current-route-name="currentRouteName"
        :is-sidebar-collapsed="isSidebarCollapsed"
        :theme-classes="themeClasses"
        :user-avatar-url="userAvatarUrl"
        :user-initials="userInitials"
        :user-name="authStore.user?.name || 'User'"
        @toggle-mobile="isMobileSidebarOpen = true"
        @toggle-collapse="isSidebarCollapsed = !isSidebarCollapsed"
      />

      <!-- Scrollable Main Content -->
      <main 
        id="main-scroll-container" 
        tabindex="0"
        class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden p-6 lg:p-10 custom-scrollbar overscroll-contain focus:outline-none"
        :class="props.theme === 'admin' ? 'bg-[#010405]' : 'bg-theme-bg'"
      >
        <div class="mx-auto max-w-7xl min-h-full pb-24">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../modules/auth/store/auth.store';
import { useToast } from '../../core/composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import DashboardSidebar from './components/DashboardSidebar.vue';
import DashboardHeader from './components/DashboardHeader.vue';
import { getStorageUrl } from '@/core/utils/url';

const props = defineProps({
  portalName: { type: String, default: 'Portal' },
  homeRoute: { type: String, required: true },
  navItems: { type: Array, required: true },
  theme: { type: String, default: 'gold' },
  profileRoute: { type: String, default: '' },
});

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();
const { confirm } = useConfirm();

const isMobileSidebarOpen = ref(false);
const isSidebarCollapsed = ref(false);
const sidebarRef = ref(null);

const isActiveRoute = (to) => {
  if (to === props.homeRoute) return route.path === to;
  return route.path.startsWith(to);
};

const groupedNavItems = computed(() => {
  const items = props.navItems;
  if (!items || items.length === 0) return [];

  const customerSections = {
    'Menu': ['Dashboard', 'Browse Services', 'My Bookings', 'My Codes'],
    'Discover': ['Wishlist', 'Reviews', 'Gallery', 'Blog'],
    'Activity': ['Notifications', 'Notification Settings', 'Analytics', 'Reports'],
  };

  const barberSections = {
    'Overview': ['Dashboard'],
    'Clients': ['Walk-In', 'Payments', 'Appointments'],
    'Work': ['Schedule', 'Services', 'Gallery'],
    'Content': ['Blog', 'Notifications'],
    'Insights': ['Analytics', 'Reports'],
  };

  const adminSections = {
    'Overview': ['Dashboard', 'Website'],
    'Operations': ['Walk-In', 'Appointments', 'Customers', 'Barbers'],
    'Content': ['Services', 'Gallery', 'Testimonials', 'Blog'],
    'Management': ['Working Hours', 'Analytics', 'Reports'],
    'Security': ['Verifications', 'System Logs', 'Notifications'],
  };

  const sections = props.theme === 'admin' ? adminSections : 
                   props.theme === 'customer' ? customerSections : barberSections;
  const groups = [];
  const used = new Set();

  for (const [label, names] of Object.entries(sections)) {
    const groupItems = [];
    for (const name of names) {
      const item = items.find(i => i.name === name);
      if (item) {
        groupItems.push(item);
        used.add(item.name);
      }
    }
    if (groupItems.length > 0) {
      groups.push({ label, items: groupItems });
    }
  }

  const remaining = items.filter(i => !used.has(i.name));
  if (remaining.length > 0) {
    groups.push({ label: 'Other', items: remaining });
  }

  return groups;
});

const themeClasses = computed(() => {
  if (props.theme === 'customer') {
    return {
      text: 'text-gold',
      textLight: 'text-gold-light',
      textLight70: 'text-gold-light/70',
      bg: 'bg-gold',
      bgRaw: 'bg-gold',
      bg10: 'bg-gold/10',
      bg15: 'bg-gold/15',
      bg20: 'bg-gold/20',
      gradient: 'from-gold to-gold-dark',
      shadowLogo: 'shadow-[0_0_15px_rgba(212,175,55,0.3)]',
      shadowNavLine: 'shadow-[0_0_10px_rgba(255,153,0,0.5)]',
      shadowNavBox: 'shadow-[inset_0_0_0_1px_rgba(212,175,55,0.2)]',
      borderHover: 'hover:border-gold/30',
      hoverTextLight: 'group-hover:text-gold-light',
      hoverText: 'hover:text-gold',
      bodyBg: 'bg-charcoal',
      surfaceBg: 'bg-obsidian',
      headerBg: 'bg-charcoal/90',
      mainBg: 'bg-obsidian/90'
    };
  }

  if (props.theme === 'admin') {
    return {
      text: 'text-admin',
      textLight: 'text-admin-light',
      textLight70: 'text-admin-light/70',
      bg: 'bg-admin',
      bgRaw: 'bg-admin',
      bg10: 'bg-admin/10',
      bg15: 'bg-admin/15',
      bg20: 'bg-admin/20',
      gradient: 'from-admin to-admin-dark',
      shadowLogo: 'shadow-[0_0_15px_rgba(255,103,0,0.3)]',
      shadowNavLine: 'shadow-[0_0_10px_rgba(255,103,0,0.5)]',
      shadowNavBox: 'shadow-[inset_0_0_0_1px_rgba(255,103,0,0.2)]',
      borderHover: 'hover:border-admin/30',
      hoverTextLight: 'group-hover:text-admin-light',
      hoverText: 'hover:text-admin',
      bodyBg: 'bg-[#010405]',
      surfaceBg: 'bg-[#030608]',
      headerBg: 'bg-[#010405]/95',
      mainBg: 'bg-[#010405]'
    };
  }

  if (props.theme === 'blue') {
    return {
      text: 'text-blue-500',
      textLight: 'text-blue-400',
      textLight70: 'text-blue-400/70',
      bg: 'bg-blue-500',
      bgRaw: 'bg-blue-500',
      bg10: 'bg-blue-500/10',
      bg15: 'bg-blue-500/15',
      bg20: 'bg-blue-500/20',
      gradient: 'from-blue-500 to-blue-700',
      shadowLogo: 'shadow-[0_0_15px_rgba(59,130,246,0.3)]',
      shadowNavLine: 'shadow-[0_0_10px_rgba(59,130,246,0.5)]',
      shadowNavBox: 'shadow-[inset_0_0_0_1px_rgba(59,130,246,0.2)]',
      borderHover: 'hover:border-blue-500/30',
      hoverTextLight: 'group-hover:text-blue-400',
      hoverText: 'hover:text-blue-500',
      bodyBg: 'bg-charcoal',
      surfaceBg: 'bg-obsidian',
      headerBg: 'bg-charcoal/90',
      mainBg: 'bg-obsidian/90'
    };
  }

  return {
    text: 'text-gold',
    textLight: 'text-gold-light',
    textLight70: 'text-gold-light/70',
    bg: 'bg-gold',
    bgRaw: 'bg-gold',
    bg10: 'bg-gold/10',
    bg15: 'bg-gold/15',
    bg20: 'bg-gold/20',
    gradient: 'from-gold to-gold-dark',
    shadowLogo: 'shadow-[0_0_15px_rgba(212,175,55,0.3)]',
    shadowNavLine: 'shadow-[0_0_10px_rgba(255,153,0,0.5)]',
    shadowNavBox: 'shadow-[inset_0_0_0_1px_rgba(212,175,55,0.2)]',
    borderHover: 'hover:border-gold/30',
    hoverTextLight: 'group-hover:text-gold-light',
    hoverText: 'hover:text-gold',
    bodyBg: 'bg-charcoal',
    surfaceBg: 'bg-obsidian',
    headerBg: 'bg-charcoal/90',
    mainBg: 'bg-obsidian/90'
  };
});

const currentRouteName = computed(() => {
  return route.name ? route.name.toString().replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Dashboard';
});

const userInitials = computed(() => {
  const name = authStore.user?.name || 'User';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const userAvatarUrl = computed(() => {
  const avatar = authStore.user?.avatar;
  if (!avatar) return null;
  return getStorageUrl(avatar);
});

const scrollToActiveItem = async () => {
  await nextTick();
  const activeEl = document.getElementById('active-nav-item');
  const container = sidebarRef.value?.navContainer;
  if (activeEl && container) {
    const scrollPos = activeEl.offsetTop - (container.offsetHeight / 2) + (activeEl.offsetHeight / 2);
    if (activeEl.offsetTop < container.scrollTop || activeEl.offsetTop + activeEl.offsetHeight > container.scrollTop + container.offsetHeight) {
      container.scrollTo({
        top: scrollPos > 0 ? scrollPos : 0,
        behavior: 'smooth'
      });
    }
  }
};

onMounted(() => {
  setTimeout(scrollToActiveItem, 100);
});

watch(() => route.path, () => {
  scrollToActiveItem();
});

const handleLogout = async () => {
  const ok = await confirm({ title: 'Sign Out', message: 'Are you sure you want to sign out of your account?', confirmText: 'Sign Out' });
  if (!ok) return;

  try {
    const isAdmin = authStore.isAdmin || authStore.isSuperAdmin;
    const isCustomer = authStore.isCustomer;

    await authStore.logout();

    if (isAdmin) {
      router.push('/admin/login');
    } else if (isCustomer) {
      router.push('/customer/login');
    } else {
      router.push('/barber/login');
    }

    toast.success('Logged out successfully');
  } catch (error) {
    // Handled by interceptor
  }
};
</script>

<style scoped>
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 103, 0, 0.45) rgba(0, 0, 0, 0.4);
}
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.35);
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 103, 0, 0.4);
  border-radius: 9999px;
  border: 2px solid transparent;
  background-clip: padding-box;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 103, 0, 0.75);
}
</style>
