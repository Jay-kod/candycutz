<template>
  <header class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-2xl">
    <!-- Ambient Glow Accent -->
    <div class="pointer-events-none absolute -top-24 right-1/4 h-56 w-96 rounded-full bg-gradient-to-r from-[#FF6700]/15 via-amber-500/10 to-transparent blur-3xl"></div>

    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
      <!-- Title & Live Meta -->
      <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-2.5">
          <span class="inline-flex items-center gap-1.5 rounded-full border border-[#FF6700]/30 bg-[#FF6700]/10 px-3 py-0.5 text-[11px] font-bold uppercase tracking-widest text-[#FF6700]">
            <span class="h-1.5 w-1.5 rounded-full bg-[#FF6700] animate-ping"></span>
            Activity Stream
          </span>
          <span
            v-if="unreadCount > 0"
            class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-amber-400"
          >
            <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
            {{ unreadCount }} Unread
          </span>
          <span class="inline-flex items-center gap-1 rounded-full border border-white/[0.08] bg-white/[0.03] px-2.5 py-0.5 text-[11px] font-medium text-white/50">
            <ClockIcon class="h-3 w-3 text-white/40" />
            {{ currentTime }}
          </span>
          <span v-if="lastRefreshed" class="text-[11px] text-white/40">
            Updated {{ lastRefreshed }}
          </span>
        </div>

        <div>
          <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
            Executive <span class="bg-gradient-to-r from-[#FF6700] via-amber-400 to-amber-200 bg-clip-text text-transparent">Notifications</span>
          </h1>
          <p class="mt-1 text-xs sm:text-sm text-white/50 max-w-xl">
            Live operational dispatches, booking events, security alerts, and system activities.
          </p>
        </div>
      </div>

      <!-- Header Actions -->
      <div class="flex flex-wrap items-center gap-2.5">
        <!-- Mark All As Read -->
        <button
          v-if="unreadCount > 0"
          type="button"
          @click="$emit('mark-all-read')"
          class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-500 px-4 py-2 text-xs font-bold text-white shadow-lg shadow-[#FF6700]/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-[#FF6700]/30 active:scale-95"
        >
          <CheckCircleIcon class="h-4 w-4" />
          <span>Mark All Read</span>
        </button>

        <!-- Refresh Trigger -->
        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="loading"
          class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/[0.04] px-3.5 py-2 text-xs font-semibold text-white/70 hover:bg-white/[0.08] hover:text-white transition-colors disabled:opacity-50"
          title="Refresh notification stream"
        >
          <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin text-[#FF6700]': loading }" />
          <span class="hidden sm:inline">Refresh</span>
        </button>

        <!-- Notification Settings Link -->
        <router-link
          to="/admin/settings"
          class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/[0.04] px-3.5 py-2 text-xs font-semibold text-white/70 hover:border-white/20 hover:text-white transition-colors"
        >
          <Cog6ToothIcon class="h-4 w-4" />
          <span>Settings</span>
        </router-link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import {
  ClockIcon,
  CheckCircleIcon,
  ArrowPathIcon,
  Cog6ToothIcon
} from '@heroicons/vue/24/outline';

defineProps({
  unreadCount: {
    type: Number,
    default: 0
  },
  loading: {
    type: Boolean,
    default: false
  },
  lastRefreshed: {
    type: String,
    default: null
  }
});

defineEmits(['mark-all-read', 'refresh']);

const currentTime = ref('');

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

let clockInterval = null;

onMounted(() => {
  updateClock();
  clockInterval = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  if (clockInterval) {
    clearInterval(clockInterval);
  }
});
</script>

