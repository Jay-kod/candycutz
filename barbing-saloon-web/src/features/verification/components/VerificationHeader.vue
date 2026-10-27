<template>
  <header class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-2xl">
    <!-- Ambient Glow Accent -->
    <div class="pointer-events-none absolute -top-24 right-1/4 h-56 w-96 rounded-full bg-gradient-to-r from-[#FF6700]/15 via-emerald-500/10 to-transparent blur-3xl"></div>

    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
      <!-- Title & Live Meta -->
      <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-2.5">
          <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-0.5 text-[11px] font-bold uppercase tracking-widest text-emerald-400">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
            Security Gateway
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
            Code <span class="bg-gradient-to-r from-[#FF6700] via-amber-400 to-emerald-400 bg-clip-text text-transparent">Verification</span>
          </h1>
          <p class="mt-1 text-xs sm:text-sm text-white/50 max-w-xl">
            Authenticate, inspect, and manually verify appointment codes to guarantee smooth check-in execution.
          </p>
        </div>
      </div>

      <!-- Quick Verify Input -->
      <div class="w-full lg:w-96">
        <div class="relative flex items-center rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 p-1.5 shadow-inner backdrop-blur-md transition-colors focus-within:border-emerald-500/40">
          <QrCodeIcon class="ml-3 h-5 w-5 text-white/40 shrink-0" />
          <input
            :value="quickCode"
            @input="$emit('update:quickCode', $event.target.value)"
            @keydown.enter="$emit('quick-verify')"
            type="text"
            placeholder="Paste code to quick-verify..."
            class="w-full bg-transparent px-3 py-1.5 font-mono text-xs sm:text-sm text-white placeholder:text-white/30 outline-none"
          />
          <button
            type="button"
            @click="$emit('quick-verify')"
            class="shrink-0 rounded-lg bg-emerald-500/20 px-3.5 py-1.5 text-xs font-bold text-emerald-400 border border-emerald-500/30 transition-all hover:bg-emerald-500/30 hover:scale-105 active:scale-95"
          >
            Verify
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  ClockIcon, 
  QrCodeIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  quickCode: {
    type: String,
    default: ''
  },
  lastRefreshed: {
    type: String,
    default: null
  }
});

defineEmits(['update:quickCode', 'quick-verify']);

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
