<template>
  <Teleport to="body">
    <div
      v-if="isBannerVisible"
      class="fixed top-0 left-0 right-0 z-[9990] pointer-events-none select-none"
    >
      <!-- Expanded Top Alert Banner -->
      <transition name="banner-slide">
        <div
          v-if="!isMinimized"
          role="alert"
          :aria-live="isDisconnected ? 'assertive' : 'polite'"
          class="pointer-events-auto w-full backdrop-blur-md transition-all duration-300 shadow-2xl border-b"
          :class="bannerBgClasses"
        >
          <div class="max-w-7xl mx-auto px-3.5 sm:px-6 py-2.5 sm:py-3 flex flex-wrap items-center justify-between gap-3">
            <!-- Left: Status Icon, Badges & Contextual Description -->
            <div class="flex items-center gap-3 min-w-0 flex-1">
              <!-- Pulsing Status Emblem -->
              <div class="relative flex h-3 w-3 shrink-0">
                <span
                  class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                  :class="isDisconnected ? (disconnectType === 'database' ? 'bg-rose-400' : 'bg-amber-400') : 'bg-emerald-400'"
                ></span>
                <span
                  class="relative inline-flex rounded-full h-3 w-3"
                  :class="isDisconnected ? (disconnectType === 'database' ? 'bg-rose-500' : 'bg-amber-500') : 'bg-emerald-500'"
                ></span>
              </div>

              <!-- Icon & Badge Tag -->
              <div
                class="hidden xs:inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[11px] font-mono font-bold tracking-wider uppercase shrink-0 border"
                :class="badgeClasses"
              >
                <CircleStackIcon v-if="disconnectType === 'database' || isRecovered" class="h-3.5 w-3.5" />
                <ServerStackIcon v-else class="h-3.5 w-3.5" />
                <span>{{ isRecovered ? 'DB ACTIVE' : (disconnectType === 'database' ? 'DB INACTIVE' : 'SERVER OFFLINE') }}</span>
              </div>

              <!-- Message -->
              <div class="min-w-0 flex-1 text-xs sm:text-sm">
                <span class="font-semibold text-white">
                  {{ isRecovered ? 'Database Active:' : (disconnectType === 'database' ? 'Database Offline:' : 'Server Unreachable:') }}
                </span>
                <span class="ml-1.5 opacity-90">
                  {{ isRecovered ? recoveryMessage : errorMessage }}
                </span>
                <span
                  v-if="isDisconnected && disconnectType === 'database'"
                  class="hidden md:inline ml-2 text-[11px] opacity-75 font-mono"
                >
                  (Start MySQL in XAMPP Control Panel)
                </span>
                <span
                  v-else-if="isDisconnected && disconnectType === 'server'"
                  class="hidden md:inline ml-2 text-[11px] opacity-75 font-mono"
                >
                  (Start API backend: npm run dev:api on port 8000)
                </span>
              </div>
            </div>

            <!-- Right: Interactive Controls -->
            <div class="flex items-center gap-2 shrink-0">
              <!-- Retry Connection Button (when disconnected) -->
              <button
                v-if="isDisconnected"
                type="button"
                @click="retry"
                :disabled="isProbing"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded text-xs font-semibold tracking-wide border transition-all active:scale-95 cursor-pointer disabled:opacity-50"
                :class="actionBtnClasses"
                title="Probe MySQL database connection now"
              >
                <ArrowPathIcon
                  class="h-3.5 w-3.5 transition-transform"
                  :class="{ 'animate-spin': isProbing }"
                />
                <span>{{ isProbing ? 'Checking...' : 'Retry Now' }}</span>
              </button>

              <!-- Minimize / Dismiss Button -->
              <button
                v-if="isDisconnected"
                type="button"
                @click="minimize"
                class="inline-flex items-center justify-center p-1.5 rounded opacity-70 hover:opacity-100 transition-opacity cursor-pointer"
                title="Minimize banner"
                aria-label="Minimize connection alert"
              >
                <ChevronUpIcon class="h-4 w-4" />
              </button>

              <button
                v-else
                type="button"
                @click="dismissRecovered"
                class="inline-flex items-center justify-center p-1.5 rounded opacity-70 hover:opacity-100 transition-opacity cursor-pointer"
                title="Dismiss banner"
                aria-label="Dismiss recovery banner"
              >
                <XMarkIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Minimized Floating Pill Indicator -->
      <transition name="pill-fade">
        <div
          v-if="isMinimized && isDisconnected"
          class="pointer-events-auto fixed top-3 right-4 z-[9990]"
        >
          <button
            type="button"
            @click="expand"
            class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold shadow-xl backdrop-blur-md border transition-all active:scale-95 cursor-pointer"
            :class="minimizedPillClasses"
            :title="disconnectType === 'database' ? 'Database is offline — Click to view details and retry' : 'Backend server is unreachable — Click to view details and retry'"
          >
            <span class="relative flex h-2 w-2">
              <span
                class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                :class="disconnectType === 'database' ? 'bg-rose-400' : 'bg-amber-400'"
              ></span>
              <span
                class="relative inline-flex rounded-full h-2 w-2"
                :class="disconnectType === 'database' ? 'bg-rose-500' : 'bg-amber-500'"
              ></span>
            </span>
            <span>{{ disconnectType === 'database' ? 'DB Inactive' : 'Server Offline' }}</span>
            <ChevronDownIcon class="h-3.5 w-3.5 opacity-80" />
          </button>
        </div>
      </transition>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';
import { useConnectionStatus } from '../composables/useConnectionStatus';
import {
  CircleStackIcon,
  ServerStackIcon,
  ArrowPathIcon,
  ChevronUpIcon,
  ChevronDownIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

const {
  isBannerVisible,
  isDisconnected,
  isRecovered,
  disconnectType,
  errorMessage,
  recoveryMessage,
  isProbing,
  isMinimized,
  retry,
  minimize,
  expand,
  markConnected,
} = useConnectionStatus();

const dismissRecovered = () => {
  markConnected();
};

const bannerBgClasses = computed(() => {
  if (isRecovered.value) {
    return 'bg-[#0a291b]/95 border-emerald-500/40 text-emerald-100';
  }
  if (disconnectType.value === 'database') {
    return 'bg-[#2d0f14]/95 border-rose-500/40 text-rose-100';
  }
  return 'bg-[#2d1b09]/95 border-amber-500/40 text-amber-100';
});

const badgeClasses = computed(() => {
  if (isRecovered.value) {
    return 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300';
  }
  if (disconnectType.value === 'database') {
    return 'bg-rose-500/20 border-rose-500/40 text-rose-300';
  }
  return 'bg-amber-500/20 border-amber-500/40 text-amber-300';
});

const actionBtnClasses = computed(() => {
  if (disconnectType.value === 'database') {
    return 'bg-rose-500/20 border-rose-500/40 text-rose-200 hover:bg-rose-500/30';
  }
  return 'bg-amber-500/20 border-amber-500/40 text-amber-200 hover:bg-amber-500/30';
});

const minimizedPillClasses = computed(() => {
  if (disconnectType.value === 'database') {
    return 'bg-[#2d0f14]/95 border-rose-500/50 text-rose-200 hover:bg-[#3d141b]';
  }
  return 'bg-[#2d1b09]/95 border-amber-500/50 text-amber-200 hover:bg-[#3d240d]';
});
</script>

<style scoped>
.banner-slide-enter-active,
.banner-slide-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.banner-slide-enter-from,
.banner-slide-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}

.pill-fade-enter-active,
.pill-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.pill-fade-enter-from,
.pill-fade-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>

