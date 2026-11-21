<template>
  <div 
    class="flex h-screen h-[100dvh] text-[var(--color-text-primary)] font-sans overflow-hidden transition-colors duration-300"
    :class="layoutBgClass"
  >
    <!-- Decomposed Responsive Sidebar -->
    <DashboardSidebar
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
      <!-- Top Navigation Header -->
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
        class="flex-1 min-h-0 overflow-y-auto p-3 xs:p-4 sm:p-6 lg:p-8 custom-scrollbar overscroll-contain focus:outline-none transition-colors duration-300"
        :class="mainBgClass"
      >
        <div class="mx-auto max-w-7xl min-h-full pb-28 sm:pb-20">
          <slot />
        </div>
      </main>

      <!-- Mobile iOS Bottom Tab Bar -->
      <IosTabBar :tabs="resolvedMobileTabs" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useTheme } from '@/core/composables/useTheme';
import { useAuthStore } from '../../modules/auth/store/auth.store';
import { useToast } from '../../core/composables/useToast';
import { useConfirm } from '../composables/useConfirm';
import DashboardSidebar from './components/DashboardSidebar.vue';
import DashboardHeader from './components/DashboardHeader.vue';
import IosTabBar from '@/shared/ui/ios/IosTabBar.vue';
import { getStorageUrl } from '@/core/utils/url';
import { getPortalThemeConfig } from './components/sidebar/portalTheme';

const props = defineProps({
  portalName: { type: String, default: 'Portal' },
  homeRoute: { type: String, required: true },
  navItems: { type: Array, required: true },
  mobileTabs: { type: Array, default: null },
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

const resolvedMobileTabs = computed(() => {
  if (props.mobileTabs && props.mobileTabs.length > 0) {
    return props.mobileTabs;
  }
  if (!props.navItems || props.navItems.length === 0) return [];
  return props.navItems.slice(0, 4);
});

const { isDark } = useTheme();

const layoutBgClass = computed(() => {
  return isDark.value ? 'bg-[#0B0F17] text-slate-100' : 'bg-[#FAF9F6] text-slate-900';
});

const mainBgClass = computed(() => {
  return isDark.value ? 'bg-[#0B0F17]' : 'bg-[#FAF9F6]';
});

const themeClasses = computed(() => {
  const cfg = getPortalThemeConfig(props.portalName, isDark.value);
  return {
    ...cfg,
    text: isDark.value ? 'text-white' : 'text-slate-900',
    headerBg: isDark.value ? 'bg-[#0E131F]/90' : 'bg-white/95',
    headerBorder: isDark.value ? 'border-white/10' : 'border-slate-200',
    headerTitle: isDark.value ? 'text-white' : 'text-slate-900',
    iconBtn: isDark.value ? 'text-slate-400 hover:text-white' : 'text-slate-600 hover:text-slate-900',
    userPill: isDark.value ? 'border-white/10 bg-white/5 text-white' : 'border-slate-200 bg-slate-100 text-slate-800',
    mainBg: mainBgClass.value,
  };
});

const isActiveRoute = (to) => {
  if (to === props.homeRoute || to === '/admin/websites') return route.path === to;
  return route.path === to || route.path.startsWith(to + '/');
};

const groupedNavItems = computed(() => {
  const items = props.navItems;
  if (!items || items.length === 0) return [];

  const customerSections = {
    'Menu': ['Dashboard', 'Browse Services', 'My Bookings', 'My Codes'],
    'Discover': ['Wishlist', 'Reviews', 'Feedback & Suggestions', 'Gallery', 'Blog'],
    'Activity': ['Notifications', 'Notification Settings', 'Analytics', 'Reports'],
  };

  const barberSections = {
    'Operations': ['Dashboard', 'Appointments', 'Walk-In', 'Schedule'],
    'Services & Portfolio': ['Services', 'Service Categories', 'Gallery', 'Payments', 'Bank & Payments', 'Blog'],
    'Activity & Insights': ['Notifications', 'Analytics', 'Reports'],
  };

  const adminSections = {
    'Operations': ['Dashboard', 'Walk-In', 'Appointments', 'Customers', 'Barbers'],
    'Financials & Revenue': ['Bank & Payments', 'Payments', 'Analytics', 'Reports'],
    'The Website': [
      'Website Overview',
      'Brand Identity',
      'Home Page',
      'Features Block',
      'Page Heroes',
      'About Us Page',
      'Services Page',
      'Gallery Page',
      'Contact Page',
      'Privacy Policy Page',
      'Terms of Service Page',
      'Legal Stuff Page',
      'Security Page',
      'Manage Cookies Page',
      'Account Deletion Page'
    ],
    'Content & Catalog': ['Services', 'Service Categories', 'Gallery', 'Testimonials', 'Feedback Inbox', 'Blog'],
    'Management & Security': ['Feature Flags', 'Working Hours', 'Integrations', 'API Directory', 'Verifications', 'System Logs', 'Health Checker', 'System Errors', 'Backup & Restore', 'Notifications'],
  };

  const superAdminSections = {
    'Overview': ['Dashboard', 'Users', 'Audit Logs'],
    'Platform Operations': ['Appointments', 'Walk-In Queue', 'Customers', 'Barbers', 'Services'],
    'Content Management': ['Gallery', 'Testimonials', 'Blog'],
    'System Governance': ['Feature Flags', 'Working Hours', 'Integrations', 'API Directory', 'System Logs', 'Verifications'],
    'Analytics & Reports': ['Analytics', 'Reports'],
    'Configuration': ['Settings'],
  };

  const sections = props.theme === 'superadmin' ? superAdminSections :
                   props.theme === 'admin' ? adminSections : 
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

const currentRouteName = computed(() => {
  return route.name ? route.name.toString().replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Dashboard';
});

const userInitials = computed(() => {
  const name = authStore.user?.name || 'User';
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});

const userAvatarUrl = computed(() => {
  const avatar = authStore.user?.avatar_url || authStore.user?.avatar;
  if (!avatar) return null;
  return getStorageUrl(avatar);
});

const handleLogout = async () => {
  const ok = await confirm({
    title: 'Sign Out',
    message: 'Are you sure you want to sign out of your account?',
    confirmText: 'Sign Out',
    resolutionType: 'confirmed',
    resolutionTitle: 'Signed Out Successfully',
    resolutionMessage: 'You have been signed out of your account.',
    resolutionBadgeText: 'SIGNED OUT',
  });
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
  scrollbar-color: var(--color-accent) var(--color-border-subtle);
}
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(212, 175, 55, 0.3);
  border-radius: 9999px;
}
</style>
