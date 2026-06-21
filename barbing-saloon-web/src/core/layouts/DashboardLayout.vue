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
        'fixed inset-y-0 left-0 z-50 flex flex-col border-r border-theme-border transition-all duration-300 ease-in-out lg:static lg:translate-x-0',
        'bg-theme-surface',
        isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full',
        isSidebarCollapsed ? 'lg:w-[88px]' : 'w-72'
      ]"
    >
      <!-- Sidebar Header -->
      <div class="flex h-20 items-center justify-between px-6 border-b border-white/[0.04]">
        <RouterLink :to="homeRoute" class="flex items-center gap-3 group">
          <div :class="['relative flex items-center justify-center h-10 w-10 shrink-0 rounded-xl transition-transform group-hover:scale-105 bg-gradient-to-br', themeClasses.gradient, themeClasses.shadowLogo]">
            <img src="/images/logo-icon.png" alt="Logo" class="h-6 w-6 object-contain" />
          </div>
          <span
            :class="[
              'font-display text-xl font-bold text-white whitespace-nowrap transition-all duration-300',
              isSidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100 w-auto'
            ]"
          >
            Candy<span :class="themeClasses.text">Cutz</span>
            <span :class="['block text-[10px] uppercase tracking-widest -mt-1', themeClasses.textLight70]">{{ portalName }}</span>
          </span>
        </RouterLink>

        <!-- Mobile Close Button -->
        <button
          @click="isMobileSidebarOpen = false"
          class="lg:hidden p-2 text-theme-muted hover:text-theme-text transition-colors"
        >
          <XMarkIcon class="h-6 w-6" />
        </button>
      </div>

      <!-- Navigation Links -->
      <div class="flex-1 overflow-y-auto overflow-x-hidden py-6 custom-scrollbar">
        <nav class="space-y-2 px-4">
          <RouterLink
            v-for="item in navItems"
            :key="item.name"
            :to="item.to"
            class="group relative flex items-center gap-3 rounded-xl px-3 py-3 transition-all duration-200"
            :class="[
              $route.path.startsWith(item.to) && item.to !== homeRoute || $route.path === item.to
                ? [themeClasses.bg10, themeClasses.text, themeClasses.shadowNavBox]
                : 'text-theme-muted hover:bg-theme-surface hover:text-theme-text'
            ]"
            :title="isSidebarCollapsed ? item.name : ''"
          >
            <!-- Active Indicator Line -->
            <div
              v-if="$route.path.startsWith(item.to) && item.to !== homeRoute || $route.path === item.to"
              :class="['absolute left-0 top-1/2 h-8 w-1 -translate-y-1/2 rounded-r-full', themeClasses.bg, themeClasses.shadowNavLine]"
            ></div>

            <component
              :is="item.icon"
              class="h-6 w-6 shrink-0 transition-transform duration-200 group-hover:scale-110"
              :class="[
                $route.path.startsWith(item.to) && item.to !== homeRoute || $route.path === item.to ? themeClasses.text : ['text-theme-muted', themeClasses.hoverTextLight]
              ]"
            />
            
            <span
              :class="[
                'font-medium whitespace-nowrap transition-all duration-300',
                isSidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100 w-auto'
              ]"
            >
              {{ item.name }}
            </span>
          </RouterLink>
        </nav>
      </div>

      <!-- User/Logout Section -->
      <div class="border-t border-theme-border p-4 space-y-1">
        <RouterLink
          v-if="profileRoute"
          :to="profileRoute"
          class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-theme-muted hover:bg-theme-surface hover:text-theme-text transition-all duration-200"
          :title="isSidebarCollapsed ? 'Profile' : ''"
        >
          <UserIcon class="h-6 w-6 shrink-0 transition-transform duration-200 group-hover:scale-110" />
          <span
            :class="[
              'font-medium whitespace-nowrap transition-all duration-300',
              isSidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100 w-auto'
            ]"
          >
            My Profile
          </span>
        </RouterLink>

        <button
          @click="handleLogout"
          class="group flex w-full items-center gap-3 rounded-xl px-3 py-3 text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-200"
          :title="isSidebarCollapsed ? 'Logout' : ''"
        >
          <ArrowRightOnRectangleIcon class="h-6 w-6 shrink-0 transition-transform duration-200 group-hover:-translate-x-1" />
          <span
            :class="[
              'font-medium whitespace-nowrap transition-all duration-300',
              isSidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100 w-auto'
            ]"
          >
            Sign Out
          </span>
        </button>
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
          <!-- Theme Toggle -->
          <button @click="toggleDark()" class="p-2 text-theme-muted hover:text-theme-text transition-colors" aria-label="Toggle theme">
            <SunIcon v-if="isDark" class="h-6 w-6" />
            <MoonIcon v-else class="h-6 w-6" />
          </button>

          <!-- Optional Header Actions / User Profile Snippet -->
          <div class="flex items-center gap-3 rounded-full border border-theme-border py-1.5 pl-1.5 pr-4 bg-theme-surface">
            <div :class="['flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold', themeClasses.bg20, themeClasses.text]">
              {{ userInitials }}
            </div>
            <span class="text-sm font-medium text-theme-text hidden md:block">{{ authStore.user?.name || 'User' }}</span>
          </div>
        </div>
      </header>

      <!-- Scrollable Main Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-10 custom-scrollbar bg-theme-bg">
        <div class="mx-auto max-w-7xl">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../modules/auth/store/auth.store';
import { useToast } from 'vue-toastification';
import { useConfirm } from '../composables/useConfirm';
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

const themeClasses = computed(() => {
  if (props.theme === 'admin') {
    return {
      text: 'text-admin',
      textLight: 'text-admin-light',
      textLight70: 'text-admin-light/70',
      bg: 'bg-admin',
      bg10: 'bg-admin/10',
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
      bg10: 'bg-blue-500/10',
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
      bg10: 'bg-gold/10',
      bg20: 'bg-gold/20',
      gradient: 'from-gold to-gold-dark',
      shadowLogo: 'shadow-[0_0_15px_rgba(212,175,55,0.3)]',
      shadowNavLine: 'shadow-[0_0_10px_rgba(212,175,55,0.5)]',
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
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background-color: rgba(212, 175, 55, 0.3);
}
</style>
