<template>
  <div class="h-full flex shrink-0">
    <!-- Mobile Sidebar Backdrop -->
    <div
      v-if="isMobileSidebarOpen"
      class="fixed inset-0 z-40 bg-black/80 backdrop-blur-md lg:hidden transition-opacity duration-300"
      @click="$emit('closeMobile')"
    ></div>

    <!-- Sidebar Container -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 flex flex-col h-full h-[100dvh] max-h-screen transition-all duration-300 ease-in-out',
        'lg:static lg:h-full lg:max-h-full lg:translate-x-0',
        'sidebar-panel bg-[#090a0d]/95 backdrop-blur-xl',
        'border-r border-white/[0.07]',
        isMobileSidebarOpen ? 'translate-x-0 shadow-2xl shadow-black/80' : '-translate-x-full',
        isSidebarCollapsed ? 'lg:w-[88px]' : 'w-72'
      ]"
    >
      <!-- Ambient Decorative Lighting -->
      <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
        <div :class="['absolute -top-28 -right-28 h-60 w-60 rounded-full blur-[100px] opacity-[0.12]', themeClasses.bgRaw || 'bg-amber-500']"></div>
        <div :class="['absolute -bottom-24 -left-24 h-48 w-48 rounded-full blur-[90px] opacity-[0.08]', themeClasses.bgRaw || 'bg-amber-500']"></div>
      </div>

      <!-- Sidebar Header / Brand Emblem -->
      <div class="relative z-10 flex h-20 shrink-0 items-center justify-between px-5 border-b border-white/[0.06]">
        <RouterLink :to="homeRoute" class="flex items-center gap-3 group min-w-0">
          <!-- Logo Emblem with Luxury Rim -->
          <div class="relative flex items-center justify-center shrink-0">
            <div class="h-11 w-11 rounded-2xl p-[1px] bg-gradient-to-br from-amber-400/50 via-amber-500/20 to-transparent shadow-[0_0_20px_rgba(255,153,0,0.18)] flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
              <div class="h-full w-full rounded-[15px] bg-[#14151b] flex items-center justify-center overflow-hidden">
                <img src="/images/logo-icon.png" alt="CandyCutz Logo" class="h-6 w-6 object-contain" />
              </div>
            </div>
          </div>

          <!-- Brand Typography & Portal Badge -->
          <div
            :class="[
              'min-w-0 transition-all duration-300',
              isSidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100'
            ]"
          >
            <div class="font-display text-xl font-bold tracking-tight text-white leading-none">
              Candy<span :class="themeClasses.text || 'text-amber-400'">Cutz</span>
            </div>
            <div class="inline-flex items-center gap-1.5 mt-1 px-2 py-0.5 rounded-full text-[9px] font-semibold tracking-wider uppercase bg-amber-500/10 text-amber-300 border border-amber-500/20">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
              {{ portalName }} Portal
            </div>
          </div>
        </RouterLink>

        <!-- Mobile Close Button -->
        <button
          @click="$emit('closeMobile')"
          class="lg:hidden p-2 text-white/50 hover:text-white transition-colors rounded-xl hover:bg-white/5"
          aria-label="Close sidebar"
        >
          <XMarkIcon class="h-5 w-5" />
        </button>
      </div>


      <!-- Navigation Links Container (Scrollable) -->
      <div
        ref="navContainer"
        class="relative z-10 flex-1 min-h-0 overflow-y-auto overflow-x-hidden py-3 px-3 custom-scrollbar space-y-4 overscroll-contain"
      >
        <div v-for="(group, gIdx) in groupedNavItems" :key="gIdx" class="space-y-1">
          <!-- Section Label -->
          <div v-if="group.label" class="px-3 pt-2 pb-1.5 flex items-center justify-between">
            <p
              :class="[
                'text-[10px] uppercase tracking-[0.2em] font-bold transition-all duration-300',
                themeClasses.textLight70 || 'text-amber-400/80',
                isSidebarCollapsed ? 'lg:opacity-0 lg:hidden' : 'opacity-80'
              ]"
            >
              {{ group.label }}
            </p>
            <div
              v-if="!isSidebarCollapsed"
              class="h-px flex-1 ml-3 bg-gradient-to-r from-white/[0.08] to-transparent"
            ></div>
          </div>

          <!-- Nav Items -->
          <nav class="space-y-1">
            <RouterLink
              v-for="item in group.items"
              :key="item.name"
              :to="item.to"
              :id="(isActiveRoute(item.to)) ? 'active-nav-item' : undefined"
              :class="[
                'group relative flex items-center rounded-xl transition-all duration-200',
                isSidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2',
                isActiveRoute(item.to)
                  ? [
                      'bg-gradient-to-r from-amber-500/15 via-amber-500/[0.08] to-transparent',
                      'border border-amber-500/25',
                      'shadow-[inset_0_1px_1px_rgba(255,255,255,0.06)]'
                    ]
                  : 'text-white/60 hover:text-white hover:bg-white/[0.04] border border-transparent hover:border-white/[0.06]'
              ]"
              :title="isSidebarCollapsed ? item.name : ''"
            >
              <!-- Active Indicator Bar (Left Edge) -->
              <div
                v-if="isActiveRoute(item.to)"
                class="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-1 rounded-r-full bg-gradient-to-b from-amber-300 to-amber-500 shadow-[0_0_12px_rgba(255,153,0,0.8)]"
              ></div>

              <!-- Icon Container -->
              <div
                :class="[
                  'relative flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-all duration-200',
                  isActiveRoute(item.to)
                    ? 'bg-amber-500/20 border border-amber-500/30 text-amber-300 shadow-[0_0_12px_rgba(255,153,0,0.25)]'
                    : 'bg-transparent text-white/40 group-hover:text-amber-300 group-hover:bg-white/[0.05]'
                ]"
              >
                <component
                  :is="item.icon"
                  class="h-4 w-4 transition-transform duration-200 group-hover:scale-110"
                />
              </div>

              <!-- Item Label -->
              <span
                :class="[
                  'text-[13px] tracking-wide whitespace-nowrap transition-all duration-200',
                  isSidebarCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100 w-auto',
                  isActiveRoute(item.to) ? 'font-semibold text-white' : 'font-medium group-hover:text-white'
                ]"
              >
                {{ item.name }}
              </span>

              <!-- Badge (if applicable) -->
              <span
                v-if="item.badge && !isSidebarCollapsed"
                class="ml-auto text-[9px] font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30"
              >
                {{ item.badge }}
              </span>

              <!-- Subtle Chevron for Active Item -->
              <ChevronRightIcon
                v-if="isActiveRoute(item.to) && !isSidebarCollapsed"
                class="ml-auto h-3.5 w-3.5 text-amber-400/60 shrink-0"
              />
            </RouterLink>
          </nav>
        </div>
      </div>

      <!-- Customer Profile Card & Sign Out Footer (Pinned) -->
      <div class="relative z-10 shrink-0 mt-auto border-t border-white/[0.07] p-3">
        <!-- Expanded Profile Card -->
        <div
          v-if="!isSidebarCollapsed"
          class="rounded-2xl border border-white/[0.08] bg-white/[0.03] p-2.5 transition-all duration-200 hover:border-white/[0.14] hover:bg-white/[0.05]"
        >
          <div class="flex items-center gap-3">
            <!-- User Avatar with Status Indicator -->
            <RouterLink
              v-if="profileRoute"
              :to="profileRoute"
              class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full overflow-hidden border border-amber-500/30 shadow-[0_0_10px_rgba(255,153,0,0.15)] group"
              title="View Profile"
            >
              <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Avatar" class="h-full w-full object-cover" />
              <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-amber-500/20 to-amber-600/30 text-amber-300 text-xs font-bold">
                {{ userInitials }}
              </div>
              <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-[#090a0d]"></span>
            </RouterLink>
            <div
              v-else
              class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full overflow-hidden border border-amber-500/30"
            >
              <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Avatar" class="h-full w-full object-cover" />
              <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-amber-500/20 to-amber-600/30 text-amber-300 text-xs font-bold">
                {{ userInitials }}
              </div>
              <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-[#090a0d]"></span>
            </div>

            <!-- User Name and Role/Email -->
            <div class="min-w-0 flex-1">
              <RouterLink
                v-if="profileRoute"
                :to="profileRoute"
                class="block truncate text-[13px] font-semibold text-white hover:text-amber-300 transition-colors"
              >
                {{ userName }}
              </RouterLink>
              <span v-else class="block truncate text-[13px] font-semibold text-white">
                {{ userName }}
              </span>
              <p class="truncate text-[11px] text-white/40">
                {{ userEmail || (portalName + ' Member') }}
              </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex items-center gap-1 shrink-0">
              <RouterLink
                v-if="profileRoute"
                :to="profileRoute"
                class="flex h-7 w-7 items-center justify-center rounded-lg text-white/40 hover:bg-white/10 hover:text-amber-300 transition-colors"
                title="Profile Settings"
              >
                <Cog6ToothIcon class="h-4 w-4" />
              </RouterLink>
              <button
                @click="$emit('logout')"
                class="flex h-7 w-7 items-center justify-center rounded-lg text-red-400/60 hover:bg-red-500/10 hover:text-red-400 transition-colors"
                title="Sign Out"
              >
                <ArrowRightOnRectangleIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Collapsed Profile & Sign Out -->
        <div v-else class="flex flex-col items-center gap-2">
          <RouterLink
            v-if="profileRoute"
            :to="profileRoute"
            class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full overflow-hidden border border-amber-500/30 shadow-[0_0_10px_rgba(255,153,0,0.15)] group"
            :title="userName + ' - Profile'"
          >
            <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Avatar" class="h-full w-full object-cover" />
            <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-amber-500/20 to-amber-600/30 text-amber-300 text-xs font-bold">
              {{ userInitials }}
            </div>
            <span class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-[#090a0d]"></span>
          </RouterLink>

          <button
            @click="$emit('logout')"
            class="flex h-9 w-9 items-center justify-center rounded-xl text-red-400/60 hover:bg-red-500/10 hover:text-red-400 transition-colors"
            title="Sign Out"
          >
            <ArrowRightOnRectangleIcon class="h-4 w-4" />
          </button>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import {
  XMarkIcon,
  ArrowRightOnRectangleIcon,
  ChevronRightIcon,
  Cog6ToothIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  portalName: { type: String, default: 'Portal' },
  homeRoute: { type: String, required: true },
  profileRoute: { type: String, default: '' },
  themeClasses: { type: Object, required: true },
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


const navContainer = ref(null);
defineExpose({ navContainer });
</script>

<style scoped>
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 153, 0, 0.4) rgba(0, 0, 0, 0.2);
  -webkit-overflow-scrolling: touch;
}
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 153, 0, 0.3);
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: rgba(255, 153, 0, 0.6);
}
</style>
