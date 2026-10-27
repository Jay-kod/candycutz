<template>
  <header class="relative overflow-hidden rounded-[2rem] border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-2xl flex flex-col lg:flex-row lg:items-end justify-between gap-6 backdrop-blur-3xl">
    <!-- Ambient luxury background glow -->
    <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-admin/10 blur-[100px] pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-amber-500/10 blur-[80px] pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,103,0,0.04),transparent_60%)] pointer-events-none"></div>

    <!-- Left: Identity, Clock & Operational Status -->
    <div class="relative z-10 flex-1 min-w-0">
      <div class="flex flex-wrap items-center gap-3 mb-2.5">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-admin/15 text-admin border border-admin/30 text-[10px] font-bold uppercase tracking-wider">
          <ServerIcon class="h-3.5 w-3.5" />
          External Gateways & Identity Providers
        </span>
        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-0.5 text-[10px] font-semibold text-emerald-400">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Gateways Active
        </div>
      </div>

      <h1 class="font-display text-2xl xs:text-3xl sm:text-4xl text-white tracking-tight drop-shadow leading-tight flex items-center gap-3 flex-wrap">
        <span>API & <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin via-amber-400 to-amber-200">Integrations</span></span>
        <span class="inline-flex items-center justify-center h-8 px-3 rounded-full bg-white/5 border border-white/10 text-sm text-white/70 font-sans font-medium">
          {{ configuredCount }} of {{ totalServices }} active
        </span>
      </h1>

      <!-- Live Clock & Operational Subtitle -->
      <div class="mt-2.5 flex flex-wrap items-center gap-3 text-xs text-white/50">
        <span class="font-mono text-white/80 font-medium">{{ currentTime }}</span>
        <span class="text-white/20">•</span>
        <span class="truncate">Connect transactional SMTP email relays and social OAuth authentication providers.</span>
      </div>
    </div>

    <!-- Right: Actions -->
    <div class="relative z-10 flex items-center gap-3 shrink-0">
      <button 
        @click="$emit('refresh')"
        :disabled="isRefreshing"
        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white/80 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all disabled:opacity-50"
        title="Sync settings from server"
      >
        <ArrowPathIcon class="h-4 w-4 text-white/60" :class="{ 'animate-spin': isRefreshing }" />
        <span>{{ isRefreshing ? 'Syncing...' : 'Sync Gateway Status' }}</span>
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  ServerIcon, 
  ArrowPathIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  configuredCount: {
    type: Number,
    default: 0
  },
  totalServices: {
    type: Number,
    default: 3
  },
  isRefreshing: {
    type: Boolean,
    default: false
  }
});

defineEmits(['refresh']);

const currentTime = ref('');
let timer = null;

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
  });
};

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
