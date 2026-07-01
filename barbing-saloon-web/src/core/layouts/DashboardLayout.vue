<template>
  <div class="flex h-screen text-theme-text font-sans overflow-hidden bg-theme-bg">
    <!-- Mobile Sidebar Backdrop -->
    <div
      v-if="isMobileSidebarOpen"
      class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm lg:hidden transition-opacity"
      @click="isMobileSidebarOpen = false"
    ></div>

    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col transition-all duration-300 ease-in-out lg:static lg:translate-x-0',
        'sidebar-panel',
        isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full',
        isSidebarCollapsed ? 'lg:w-[88px]' : 'w-72'
      ]"
    >
      <!-- Sidebar Decorative Elements -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
        <div :class="['absolute -top-32 -right-32 h-64 w-64 rounded-full blur-[100px] opacity-[0.07]', themeClasses.bgRaw]"></div>
        <div :class="['absolute -bottom-20 -left-20 h-40 w-40 rounded-full blur-[80px] opacity-[0.05]', themeClasses.bgRaw]"></div>
      </div>

      <!-- Sidebar Header -->
      <div class="relative z-10 flex h-20 items-center justify-between px-5 border-b border-white/[0.06]">
        <RouterLink :to="homeRoute" class="flex items-center gap-3 group">
          <div class="relative flex items-center justify-center h-11 w-11 shrink-0 transition-transform duration-300 group-hover:scale-105">
            <div :class="['absolute inset-0 rounded-2xl opacity-20 blur-md transition-opacity duration-500 group-hover:opacity-40', themeClasses.bgRaw]"></div>
            <div class="relative h-11 w-11 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center overflow-hidden backdrop-blur-sm">
              <img src="/images/logo-icon.png" alt="Logo" class="h-7 w-7 object-contain drop-shadow-sm" />
            </div>
          </div>
          <span
            :class="[
              'whitespace-nowrap transition-all duration-300',
              isSidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100 w-auto'
            ]"
          >
            <span class="font-display text-xl font-bold text-theme-text leading-none">Candy<span :class="themeClasses.text">Cutz</span></span>
            <span :class="['block text-[9px] uppercase tracking-[0.25em] mt-0.5 font-semibold', themeClasses.textLight70]">{{ portalName }} Portal</span>
          </span>
        </RouterLink>

        <!-- Mobile Close Button -->
        <button
          @click="isMobileSidebarOpen = false"
          class="lg:hidden p-2 text-theme-muted hover:text-theme-text transition-colors rounded-xl hover:bg-white/5"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>

      <!-- Navigation Links -->
      <div ref="navContainer" class="relative z-10 flex-1 overflow-y-auto overflow-x-hidden py-4 custom-scrollbar">
        <template v-for="(group, gIdx) in groupedNavItems" :key="gIdx">
          <!-- Section Label -->
          <div v-if="group.label" class="px-5 pt-5 pb-2 first:pt-2">
            <p
              :class="[
                'text-[9px] uppercase tracking-[0.25em] font-bold transition-all duration-300',
                themeClasses.textLight70,
                isSidebarCollapsed ? 'lg:opacity-0 lg:hidden' : 'opacity-60'
              ]"
            >
              {{ group.label }}
            </p>
          </div>

          <!-- Separator (except first group) -->
          <div v-if="gIdx > 0 && !group.label" class="mx-5 my-3 h-px bg-gradient-to-r from-transparent via-white/[0.06] to-transparent"></div>

          <nav class="space-y-1 px-3">
            <RouterLink
              v-for="item in group.items"
              :key="item.name"
              :to="item.to"
              :id="(isActiveRoute(item.to)) ? 'active-nav-item' : undefined"
              class="nav-item group relative flex items-center gap-2.5 rounded-lg px-3 py-1.5 transition-all duration-200"
              :class="[
                isActiveRoute(item.to)
                  ? ['nav-item-active', themeClasses.bg10, themeClasses.text, 'border border-white/[0.06]']
                  : 'text-white/50 hover:text-white/80 hover:bg-white/[0.03] border border-transparent'
              ]"
              :title="isSidebarCollapsed ? item.name : ''"
            >
              <!-- Active Indicator Line -->
              <div
                v-if="isActiveRoute(item.to)"
                :class="['absolute left-0 top-1/2 h-5 w-[3px] -translate-y-1/2 rounded-r-full transition-all duration-300', themeClasses.bg, themeClasses.shadowNavLine]"
              ></div>

              <!-- Active Glow Background -->
              <div
                v-if="isActiveRoute(item.to)"
                :class="['absolute inset-0 rounded-xl opacity-[0.04] pointer-events-none', themeClasses.bgRaw]"
                style="filter: blur(20px);"
              ></div>

              <!-- Icon Container -->
              <div
                :class="[
                  'relative flex h-7 w-7 shrink-0 items-center justify-center rounded-md transition-all duration-200',
                  isActiveRoute(item.to)
                    ? [themeClasses.bg15, 'border border-white/[0.08]']
                    : 'bg-transparent group-hover:bg-white/[0.04]'
                ]"
              >
                <component
                  :is="item.icon"
                  class="h-4 w-4 transition-all duration-200 group-hover:scale-110"
                  :class="[
                    isActiveRoute(item.to) ? themeClasses.text : ['text-white/40', themeClasses.hoverTextLight]
                  ]"
                />
              </div>

              <span
                :class="[
                  'text-[13px] font-medium whitespace-nowrap transition-all duration-300',
                  isSidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100 w-auto'
                ]"
              >
                {{ item.name }}
              </span>

              <!-- Badge for items with badges -->
              <span
                v-if="item.badge && !isSidebarCollapsed"
                :class="[
                  'ml-auto text-[9px] font-bold px-1.5 py-0.5 rounded-md',
                  themeClasses.bg15, themeClasses.text
                ]"
              >
                {{ item.badge }}
              </span>
            </RouterLink>
          </nav>
        </template>
      </div>

      <!-- Logout Section -->
      <div class="relative z-10 border-t border-white/[0.06] p-3">
        <div class="space-y-0.5">
          <RouterLink
            v-if="profileRoute"
            :to="profileRoute"
            class="group flex w-full items-center gap-2.5 rounded-lg px-3 py-1.5 text-white/40 hover:bg-white/[0.03] hover:text-white/70 transition-all duration-200 border border-transparent"
            :title="isSidebarCollapsed ? 'Profile' : ''"
          >
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-transparent group-hover:bg-white/[0.04] transition-all">
              <UserIcon class="h-4 w-4 shrink-0 transition-transform duration-200 group-hover:scale-110" />
            </div>
            <span
              :class="[
                'text-[13px] font-medium whitespace-nowrap transition-all duration-300',
                isSidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100 w-auto'
              ]"
            >
              My Profile
            </span>
          </RouterLink>

          <button
            @click="handleLogout"
            class="group flex w-full items-center gap-2.5 rounded-lg px-3 py-1.5 text-red-400/60 hover:bg-red-500/[0.06] hover:text-red-400 transition-all duration-200 border border-transparent"
            :title="isSidebarCollapsed ? 'Logout' : ''"
          >
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-transparent group-hover:bg-red-500/[0.08] transition-all">
              <ArrowRightOnRectangleIcon class="h-4 w-4 shrink-0 transition-transform duration-200 group-hover:-translate-x-0.5" />
            </div>
            <span
              :class="[
                'text-[13px] font-medium whitespace-nowrap transition-all duration-300',
                isSidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100 w-auto'
              ]"
            >
              Sign Out
            </span>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex flex-1 flex-col overflow-hidden">
      <!-- Header -->
      <header class="flex h-20 items-center justify-between border-b border-theme-border px-6 backdrop-blur-md sticky top-0 z-30 bg-theme-bg/90">
        <div class="flex items-center gap-4">
          <!-- Mobile Hamburger -->
          <button
            @click="isMobileSidebarOpen = true"
            class="rounded-lg p-2 text-theme-muted hover:bg-theme-surface hover:text-theme-text lg:hidden transition-colors"
          >
            <Bars3Icon class="h-6 w-6" />
          </button>
          
          <!-- Desktop Collapse Toggle -->
          <button
            @click="isSidebarCollapsed = !isSidebarCollapsed"
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

          <!-- Optional Header Actions / User Profile Snippet -->
          <div class="flex items-center gap-3 rounded-full border border-theme-border py-1.5 pl-1.5 pr-4 bg-theme-surface">
            <div v-if="userAvatarUrl" class="h-8 w-8 rounded-full overflow-hidden border border-theme-border">
              <img :src="userAvatarUrl" alt="Avatar" class="h-full w-full object-cover" />
            </div>
            <div v-else :class="['flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold', themeClasses.bg20, themeClasses.text]">
              {{ userInitials }}
            </div>
            <span class="text-sm font-medium text-theme-text hidden md:block">{{ authStore.user?.name || 'User' }}</span>
          </div>
        </div>
      </header>

      <!-- Scrollable Main Content -->
      <main id="main-scroll-container" class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-10 custom-scrollbar bg-theme-bg">
        <div class="mx-auto max-w-7xl min-h-full">
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
import NotificationBell from '../components/NotificationBell.vue';
import { useDark, useToggle } from '@vueuse/core';
import {
  SunIcon,
  MoonIcon,
  Bars3Icon,
  Bars3CenterLeftIcon,
  XMarkIcon,
  ArrowRightOnRectangleIcon,
  UserIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  portalName: {
    type: String,
    default: 'Portal',
  },
  homeRoute: {
    type: String,
    required: true,
  },
  navItems: {
    type: Array,
    required: true,
  },
  theme: {
    type: String,
    default: 'gold'
  },
  profileRoute: {
    type: String,
    default: ''
  }
});

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();
const { confirm } = useConfirm();

const isMobileSidebarOpen = ref(false);
const isSidebarCollapsed = ref(false);

const isDark = useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light',
});
const toggleDark = useToggle(isDark);

const isActiveRoute = (to) => {
  if (to === props.homeRoute) return route.path === to;
  return route.path.startsWith(to);
};

// Group nav items into logical sections
const groupedNavItems = computed(() => {
  const items = props.navItems;
  if (!items || items.length === 0) return [];

  // Define section groupings based on portal type
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

  const sections = props.theme === 'admin' ? adminSections : barberSections;

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

  // Add any remaining items not in a section
  const remaining = items.filter(i => !used.has(i.name));
  if (remaining.length > 0) {
    groups.push({ label: 'Other', items: remaining });
  }

  return groups;
});

const themeClasses = computed(() => {
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
      bodyBg: 'bg-admin-bg',
      surfaceBg: 'bg-admin-bg',
      headerBg: 'bg-admin-bg/90',
      mainBg: 'bg-admin-bg/90'
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

const API_ROOT = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000';

const userAvatarUrl = computed(() => {
  const avatar = authStore.user?.avatar;
  if (!avatar) return null;
  return avatar.startsWith('http') ? avatar : API_ROOT + avatar;
});

const navContainer = ref(null);

const scrollToActiveItem = async () => {
  await nextTick();
  const activeEl = document.getElementById('active-nav-item');
  if (activeEl && navContainer.value) {
    const container = navContainer.value;
    const scrollPos = activeEl.offsetTop - (container.offsetHeight / 2) + (activeEl.offsetHeight / 2);
    
    // Check if it actually needs scrolling
    if (activeEl.offsetTop < container.scrollTop || activeEl.offsetTop + activeEl.offsetHeight > container.scrollTop + container.offsetHeight) {
      container.scrollTo({
        top: scrollPos > 0 ? scrollPos : 0,
        behavior: 'smooth'
      });
    }
  }
};

onMounted(() => {
  // Add a slight delay for initial render to complete accurately
  setTimeout(scrollToActiveItem, 100);
});

watch(() => route.path, () => {
  scrollToActiveItem();
});

const handleLogout = async () => {
  const ok = await confirm({ title: 'Sign Out', message: 'Are you sure you want to sign out of your account?', confirmText: 'Sign Out' });
  if (!ok) return;

  try {
    const isBarber = authStore.isBarber;
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
    // Error is handled by interceptor
  }
};
</script>

<style scoped>
.sidebar-panel {
  background: linear-gradient(180deg, 
    rgba(13, 13, 13, 0.98) 0%, 
    rgba(10, 10, 10, 0.99) 50%, 
    rgba(8, 8, 8, 1) 100%
  );
  border-right: 1px solid rgba(255, 255, 255, 0.04);
  backdrop-filter: blur(20px);
}

.nav-item {
  position: relative;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.nav-item:hover {
  transform: translateX(2px);
}

.nav-item-active {
  background: linear-gradient(135deg, 
    rgba(255, 153, 0, 0.08) 0%, 
    rgba(255, 153, 0, 0.03) 100%
  );
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.06);
  border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.12);
}
</style>
