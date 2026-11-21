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
          <ClockIcon class="h-3.5 w-3.5" />
          Shift Management & Staff Availability
        </span>
        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-0.5 text-[10px] font-semibold text-emerald-400">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Booking Slots Live
        </div>
      </div>

      <h1 class="font-display text-2xl xs:text-3xl sm:text-4xl text-white tracking-tight drop-shadow leading-tight flex items-center gap-3 flex-wrap">
        <span>Working <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin via-amber-400 to-amber-200">Hours</span></span>
        <span v-if="totalBarbers > 0" class="inline-flex items-center justify-center h-8 px-3 rounded-full bg-white/5 border border-white/10 text-sm text-white/70 font-sans font-medium">
          {{ totalBarbers }} {{ totalBarbers === 1 ? 'barber' : 'barbers' }}
        </span>
      </h1>

      <!-- Live Clock & Operational Subtitle -->
      <div class="mt-2.5 flex flex-wrap items-center gap-3 text-xs text-white/50">
        <span class="font-mono text-white/80 font-medium">{{ currentTime }}</span>
        <span class="text-white/20">•</span>
        <span class="truncate">Configure weekly shifts and bookable time windows for each barber.</span>
      </div>
    </div>

    <!-- Right: Search Input & Action CTAs -->
    <div class="relative z-10 flex flex-col sm:flex-row lg:flex-col items-stretch lg:items-end gap-3 shrink-0 w-full lg:w-auto">
      <!-- Search Bar -->
      <div class="relative w-full lg:w-84">
        <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-white/30" />
        <input
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
          type="text"
          placeholder="Search by barber name..."
          class="w-full bg-white/[0.03] border border-white/[0.08] text-xs text-white rounded-xl py-2.5 pl-11 pr-8 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 placeholder:text-white/25"
        />
        <button 
          v-if="modelValue"
          @click="$emit('update:modelValue', '')"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white transition-colors"
          title="Clear search"
        >
          <XMarkIcon class="h-4 w-4" />
        </button>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center gap-2.5 justify-end">
        <!-- View Staff Directory -->
        <router-link
          to="/admin/barbers"
          class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white/80 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all"
          title="View Barbers Roster"
        >
          <UserGroupIcon class="h-4 w-4 text-white/60" />
          <span>Staff Directory</span>
        </router-link>

        <!-- Sync / Refresh -->
        <button 
          @click="$emit('refresh')"
          :disabled="isRefreshing"
          class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white/80 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all disabled:opacity-50"
          title="Refresh schedule list"
        >
          <ArrowPathIcon class="h-4 w-4 text-white/60" :class="{ 'animate-spin': isRefreshing }" />
          <span>{{ isRefreshing ? 'Syncing...' : 'Sync' }}</span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  ClockIcon, 
  MagnifyingGlassIcon, 
  XMarkIcon, 
  ArrowPathIcon, 
  UserGroupIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  totalBarbers: {
    type: Number,
    default: 0
  },
  isRefreshing: {
    type: Boolean,
    default: false
  }
});

defineEmits(['update:modelValue', 'refresh']);

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

