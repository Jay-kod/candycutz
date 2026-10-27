<template>
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-2">
    <!-- Left: Greeting & Description -->
    <div>
      <h1 class="text-2xl sm:text-[26px] font-bold text-theme-text tracking-tight flex items-center gap-2 font-sans">
        <span>Welcome back, {{ userName }}!</span>
        <span class="inline-block hover:rotate-12 transition-transform cursor-default select-none">👋</span>
      </h1>
      <p class="text-xs text-theme-text-muted mt-0.5">CandyCutz Salon Overview & Real-Time Operations</p>
    </div>

    <!-- Right: Telemetry Strip (Clock + Health + DB + Refresh + Action) -->
    <div class="flex flex-wrap items-center gap-2.5 sm:gap-3">
      <!-- Live Clock Pill -->
      <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-theme-bg border border-theme-border text-xs text-theme-text-muted font-mono">
        <ClockIcon class="h-3.5 w-3.5 text-theme-text-muted" />
        <span class="text-theme-text font-semibold tabular-nums">{{ currentTime }}</span>
        <span class="text-theme-text-muted/50">|</span>
        <span class="uppercase text-[11px]">{{ currentDate }}</span>
      </div>

      <!-- System Health Status Badge -->
      <div 
        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-medium backdrop-blur-sm transition-all"
        :class="isSystemHealthy 
          ? 'bg-emerald-950/30 border-emerald-500/30 text-emerald-300' 
          : 'bg-rose-950/30 border-rose-500/30 text-rose-300'"
      >
        <span class="relative flex h-2 w-2">
          <span 
            class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
            :class="isSystemHealthy ? 'bg-emerald-400' : 'bg-rose-400'"
          ></span>
          <span 
            class="relative inline-flex rounded-full h-2 w-2"
            :class="isSystemHealthy ? 'bg-emerald-400' : 'bg-rose-400'"
          ></span>
        </span>
        <span class="font-semibold">{{ isSystemHealthy ? 'System Operational' : 'Degraded' }}</span>
      </div>

      <!-- Database Connection Status Badge -->
      <div 
        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-mono font-medium backdrop-blur-sm transition-all"
        :class="isDbConnected 
          ? 'bg-theme-surface border-admin/30 text-theme-text' 
          : 'bg-rose-950/30 border-rose-500/30 text-rose-300'"
      >
        <CircleStackIcon 
          class="h-3.5 w-3.5 transition-colors" 
          :class="isDbConnected ? 'text-admin' : 'text-rose-400'" 
        />
        <span>DB: <strong :class="isDbConnected ? 'text-admin font-bold' : 'text-rose-300'">{{ isDbConnected ? 'Connected' : 'Offline' }}</strong></span>
        <span v-if="isDbConnected && dbLatency" class="text-[10px] text-theme-text-muted tabular-nums">({{ dbLatency }}ms)</span>
      </div>

      <!-- Live Refresh Button -->
      <button
        type="button"
        @click="triggerRefresh"
        :disabled="isRefreshing"
        class="inline-flex items-center min-h-[40px] xs:min-h-[44px] gap-1.5 rounded-lg border border-theme-border bg-theme-bg hover:border-admin/40 hover:bg-theme-surface text-xs font-semibold text-theme-text-muted hover:text-theme-text px-3 py-2 transition-all active:scale-95 cursor-pointer disabled:opacity-50"
        title="Sync live dashboard and health status"
      >
        <ArrowPathIcon 
          class="h-3.5 w-3.5 text-admin transition-transform" 
          :class="{ 'animate-spin': isRefreshing }"
        />
        <span class="inline">Sync</span>
      </button>

      <!-- New Walk-In CTA -->
      <RouterLink
        to="/admin/walk-in"
        class="inline-flex items-center min-h-[40px] xs:min-h-[44px] gap-1.5 rounded-lg border border-admin/40 bg-admin/15 hover:bg-admin hover:text-white text-xs font-semibold text-admin px-3.5 py-2 transition-all active:scale-95 shadow-sm group"
      >
        <PlusIcon class="h-3.5 w-3.5 text-admin group-hover:text-white transition-colors" />
        <span>New Walk-In</span>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { RouterLink } from 'vue-router';
import { 
  PlusIcon, ArrowPathIcon, CircleStackIcon, ClockIcon 
} from '@heroicons/vue/24/outline';
import { useAuthStore } from '@/modules/auth/store/auth.store';

const props = defineProps({
  systemHealth: { type: Object, default: () => ({}) },
  isRefreshing: { type: Boolean, default: false }
});

const emit = defineEmits(['refresh']);

const authStore = useAuthStore();

const userName = computed(() => {
  const name = authStore.user?.name || 'Admin';
  return name.split(' ')[0] || 'Admin';
});

// Live Database connection status from API
const isDbConnected = computed(() => {
  const health = props.systemHealth || {};
  return health.database?.status === 'connected' || health.status === 'operational';
});

const dbLatency = computed(() => {
  return props.systemHealth?.database?.latency_ms || 0;
});

// Live System Health status from API
const isSystemHealthy = computed(() => {
  const health = props.systemHealth || {};
  return health.status === 'operational' || isDbConnected.value;
});

// Live Real-Time Clock
const currentTime = ref('');
const currentDate = ref('');
let clockTimer = null;

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
};

onMounted(() => {
  updateClock();
  clockTimer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  if (clockTimer) clearInterval(clockTimer);
});

const triggerRefresh = () => {
  emit('refresh');
};
</script>
