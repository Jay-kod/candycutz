<template>
  <div>
    <!-- Mobile Sidebar Backdrop -->
    <div
      v-if="isMobileSidebarOpen"
      class="fixed inset-0 z-40 bg-black/80 backdrop-blur-sm lg:hidden transition-opacity"
      @click="$emit('closeMobile')"
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
            <div class="relative h-11 w-11 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center overflow-hidden backdrop-blur-sm">
              <img src="/images/logo-icon.png" alt="Logo" class="h-7 w-7 object-contain" />
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
          @click="$emit('closeMobile')"
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
            @click="$emit('logout')"
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
  </div>
</template>

<script setup>
import { ref } from 'vue';
import {
  XMarkIcon,
  UserIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  portalName: { type: String, default: 'Portal' },
  homeRoute: { type: String, required: true },
  profileRoute: { type: String, default: '' },
  themeClasses: { type: Object, required: true },
  groupedNavItems: { type: Array, required: true },
  isSidebarCollapsed: { type: Boolean, default: false },
  isMobileSidebarOpen: { type: Boolean, default: false },
  isActiveRoute: { type: Function, required: true },
});

defineEmits(['closeMobile', 'logout']);

const navContainer = ref(null);
defineExpose({ navContainer });
</script>
