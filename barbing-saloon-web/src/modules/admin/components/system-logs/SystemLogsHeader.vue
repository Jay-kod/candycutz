<template>
  <header class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-2xl">
    <!-- Ambient Glow Accent -->
    <div class="pointer-events-none absolute -top-24 right-1/4 h-56 w-96 rounded-full bg-gradient-to-r from-[#FF6700]/15 via-amber-500/10 to-transparent blur-3xl"></div>

    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
      <!-- Title & Live Status -->
      <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-2.5">
          <span class="inline-flex items-center gap-1.5 rounded-full border border-[#FF6700]/30 bg-[#FF6700]/10 px-3 py-0.5 text-[11px] font-bold uppercase tracking-widest text-[#FF6700]">
            <span class="h-1.5 w-1.5 rounded-full bg-[#FF6700] animate-ping"></span>
            Telemetry Center
          </span>
          <span
            v-if="gateStatus"
            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider"
            :class="gateStatus === 'optimal'
              ? 'border border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
              : 'border border-amber-500/20 bg-amber-500/10 text-amber-400'"
          >
            <span
              class="h-1.5 w-1.5 rounded-full"
              :class="gateStatus === 'optimal' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400 animate-ping'"
            ></span>
            Gate {{ gateStatus }}
          </span>
          <span class="inline-flex items-center gap-1 rounded-full border border-white/[0.08] bg-white/[0.03] px-2.5 py-0.5 text-[11px] font-medium text-white/50">
            <ClockIcon class="h-3 w-3 text-white/40" />
            {{ currentTime }}
          </span>
        </div>

        <div>
          <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
            System <span class="bg-gradient-to-r from-[#FF6700] via-amber-400 to-amber-200 bg-clip-text text-transparent">Logs & Gate</span>
          </h1>
          <p class="mt-1 text-xs sm:text-sm text-white/50 max-w-xl">
            Monitor ingress traffic, database query budgets, API latency, and administrative security audit trails.
          </p>
        </div>
      </div>

      <!-- Tab Switcher -->
      <div class="flex items-center rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 p-1.5 backdrop-blur-md self-start lg:self-auto">
        <button
          type="button"
          @click="$emit('update:tab', 'gate')"
          class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all duration-200"
          :class="[
            currentTab === 'gate'
              ? 'bg-gradient-to-r from-[#FF6700] to-amber-500 text-white shadow-lg shadow-[#FF6700]/25'
              : 'text-white/60 hover:text-white hover:bg-white/[0.04]'
          ]"
        >
          <CpuChipIcon class="h-4 w-4" />
          <span>API & DB Gate</span>
          <span
            v-if="gateTotalRequests"
            class="rounded-full px-1.5 py-0.2 text-[10px] font-mono"
            :class="currentTab === 'gate' ? 'bg-black/25 text-white' : 'bg-white/10 text-white/50'"
          >
            {{ gateTotalRequests }}
          </span>
        </button>

        <button
          type="button"
          @click="$emit('update:tab', 'audit')"
          class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-bold transition-all duration-200"
          :class="[
            currentTab === 'audit'
              ? 'bg-gradient-to-r from-[#FF6700] to-amber-500 text-white shadow-lg shadow-[#FF6700]/25'
              : 'text-white/60 hover:text-white hover:bg-white/[0.04]'
          ]"
        >
          <ClipboardDocumentListIcon class="h-4 w-4" />
          <span>Audit Logs</span>
          <span
            v-if="auditLogsCount"
            class="rounded-full px-1.5 py-0.2 text-[10px] font-mono"
            :class="currentTab === 'audit' ? 'bg-black/25 text-white' : 'bg-white/10 text-white/50'"
          >
            {{ auditLogsCount }}
          </span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import {
  ClockIcon,
  CpuChipIcon,
  ClipboardDocumentListIcon
} from '@heroicons/vue/24/outline';

defineProps({
  currentTab: {
    type: String,
    required: true
  },
  gateStatus: {
    type: String,
    default: null
  },
  gateTotalRequests: {
    type: [Number, String],
    default: 0
  },
  auditLogsCount: {
    type: [Number, String],
    default: 0
  }
});

defineEmits(['update:tab']);

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

