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
            Live Intelligence
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
            Platform <span class="bg-gradient-to-r from-white via-white/90 to-white/70 bg-clip-text text-transparent">Analytics</span>
          </h1>
          <p class="mt-1 text-xs sm:text-sm text-white/50 max-w-xl">
            Real-time business performance, revenue trends, service popularity, and client retention across CandyCutz.
          </p>
        </div>
      </div>

      <!-- Actions & Time Range Filters -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Range Selector Pills -->
        <div class="flex items-center rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 p-1 backdrop-blur-md">
          <button
            v-for="r in ranges"
            :key="r.value"
            type="button"
            @click="$emit('update:range', r.value)"
            :class="[
              'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-200',
              currentRange === r.value
                ? 'bg-gradient-to-r from-[#FF6700] to-amber-500 text-white shadow-lg shadow-[#FF6700]/25'
                : 'text-white/60 hover:text-white hover:bg-white/[0.04]'
            ]"
          >
            {{ r.label }}
          </button>
        </div>

        <!-- Manual Refresh Button -->
        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="refreshing"
          class="inline-flex items-center gap-2 rounded-xl border border-white/[0.08] bg-white/[0.03] px-3.5 py-2 text-xs font-semibold text-white/70 transition-colors hover:border-white/20 hover:bg-white/[0.06] hover:text-white disabled:opacity-50"
          title="Refresh metrics immediately"
        >
          <ArrowPathIcon
            class="h-4 w-4"
            :class="{ 'animate-spin text-[#FF6700]': refreshing }"
          />
          <span class="hidden sm:inline">{{ refreshing ? 'Updating...' : 'Refresh' }}</span>
        </button>

        <!-- PDF Reports Direct Link -->
        <router-link
          to="/admin/reports"
          class="inline-flex items-center gap-2 rounded-xl border border-white/[0.08] bg-white/[0.04] px-3.5 py-2 text-xs font-semibold text-white/80 transition-all hover:border-[#FF6700]/40 hover:bg-[#FF6700]/10 hover:text-[#FF6700]"
        >
          <DocumentChartBarIcon class="h-4 w-4" />
          <span>Executive Reports</span>
        </router-link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  ClockIcon, 
  ArrowPathIcon, 
  DocumentChartBarIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  currentRange: {
    type: String,
    required: true
  },
  ranges: {
    type: Array,
    required: true
  },
  refreshing: {
    type: Boolean,
    default: false
  },
  lastRefreshed: {
    type: String,
    default: null
  }
});

defineEmits(['update:range', 'refresh']);

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
