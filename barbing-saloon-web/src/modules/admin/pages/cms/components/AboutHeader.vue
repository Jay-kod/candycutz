<template>
  <header class="relative overflow-hidden rounded-[2rem] border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-2xl flex flex-col lg:flex-row lg:items-end justify-between gap-6 backdrop-blur-3xl">
    <!-- Ambient luxury background glows -->
    <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-admin/10 blur-[100px] pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-amber-500/10 blur-[80px] pointer-events-none"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,103,0,0.04),transparent_60%)] pointer-events-none"></div>

    <!-- Left: Identity, Clock & Operational Status -->
    <div class="relative z-10 flex-1 min-w-0">
      <div class="flex flex-wrap items-center gap-3 mb-2.5">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-admin/15 text-admin border border-admin/30 text-[10px] font-bold uppercase tracking-wider">
          <InformationCircleIcon class="h-3.5 w-3.5" />
          CMS & Brand Studio
        </span>
        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-0.5 text-[10px] font-semibold text-emerald-400">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Public About Page Live
        </div>
        <div 
          v-if="isDirty" 
          class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-[10px] font-semibold text-amber-400"
        >
          <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
          Unsaved Changes
        </div>
      </div>

      <h1 class="font-display text-2xl xs:text-3xl sm:text-4xl text-white tracking-tight drop-shadow leading-tight">
        About Us <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin via-amber-400 to-amber-200">Editor</span>
      </h1>

      <!-- Live Clock & Subtitle -->
      <div class="mt-2.5 flex flex-wrap items-center gap-3 text-xs text-white/50">
        <span class="font-mono text-white/80 font-medium">{{ currentTime }}</span>
        <span class="text-white/20">•</span>
        <span class="truncate">Salon history, interior showcase, craftsmanship story, and master stylist intro.</span>
      </div>
    </div>

    <!-- Right: Preview, Sync & Primary Save Button -->
    <div class="relative z-10 flex flex-wrap items-center gap-2.5 justify-end shrink-0 w-full lg:w-auto">
      <!-- Live Preview Public Link -->
      <a 
        href="/about" 
        target="_blank" 
        rel="noopener noreferrer"
        class="flex items-center justify-center gap-1.5 rounded-xl bg-white/[0.05] border border-white/10 px-3.5 py-2.5 text-xs font-bold text-white hover:bg-white/[0.1] hover:border-white/20 transition-all group"
        title="Open public About Us page in a new tab"
      >
        <ArrowTopRightOnSquareIcon class="h-4 w-4 text-white/60 group-hover:text-white transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
        <span>View Live Page</span>
      </a>

      <!-- Sync / Reload Button -->
      <button 
        @click="$emit('refresh')" 
        class="flex items-center justify-center gap-1.5 rounded-xl bg-white/[0.05] border border-white/10 px-3.5 py-2.5 text-xs font-bold text-white hover:bg-white/[0.1] hover:border-white/20 transition-all group"
        :title="loading ? 'Loading...' : 'Reload content from cloud'"
        :disabled="loading || saving"
      >
        <ArrowPathIcon 
          class="h-4 w-4 text-white/60 group-hover:text-white transition-transform duration-500" 
          :class="{ 'animate-spin text-admin': loading }" 
        />
        <span class="hidden sm:inline">Sync</span>
      </button>

      <!-- Save Changes Primary Button -->
      <button 
        @click="$emit('save')" 
        :disabled="saving || loading" 
        class="flex items-center justify-center min-h-[42px] gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-5 py-2.5 text-xs font-bold text-obsidian shadow-[0_4px_15px_rgba(255,103,0,0.25)] hover:shadow-[0_6px_20px_rgba(255,103,0,0.4)] active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-none"
      >
        <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
        <CheckIcon v-else class="h-4 w-4" />
        <span>{{ saving ? 'Publishing...' : 'Save Changes' }}</span>
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  InformationCircleIcon, 
  ArrowTopRightOnSquareIcon, 
  ArrowPathIcon, 
  CheckIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  saving: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  isDirty: {
    type: Boolean,
    default: false
  }
});

defineEmits(['save', 'refresh']);

const currentTime = ref('');
let clockTimer = null;

function updateClock() {
  const now = new Date();
  currentTime.value = now.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  }) + ' • ' + now.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true
  });
}

onMounted(() => {
  updateClock();
  clockTimer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  if (clockTimer) clearInterval(clockTimer);
});
</script>

