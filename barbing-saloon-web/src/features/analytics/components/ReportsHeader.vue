<template>
  <header class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/90 p-5 sm:p-7 backdrop-blur-xl shadow-2xl">
    <!-- Ambient Glow Accent -->
    <div 
      class="pointer-events-none absolute -top-24 right-1/4 h-56 w-96 rounded-full blur-3xl"
      :class="theme === 'gold' ? 'bg-gradient-to-r from-gold/15 via-amber-500/10 to-transparent' : 'bg-gradient-to-r from-[#FF6700]/15 via-amber-500/10 to-transparent'"
    ></div>

    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
      <!-- Title & Live Meta -->
      <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-2.5">
          <span 
            class="inline-flex items-center gap-1.5 rounded-full px-3 py-0.5 text-[11px] font-bold uppercase tracking-widest border"
            :class="theme === 'gold' ? 'border-gold/30 bg-gold/10 text-gold' : 'border-[#FF6700]/30 bg-[#FF6700]/10 text-[#FF6700]'"
          >
            <span class="h-1.5 w-1.5 rounded-full" :class="theme === 'gold' ? 'bg-gold' : 'bg-[#FF6700]'"></span>
            Executive Dossier
          </span>
          <span class="inline-flex items-center gap-1 rounded-full border border-theme-border bg-theme-surface px-2.5 py-0.5 text-[11px] font-medium text-theme-text-muted">
            <ClockIcon class="h-3 w-3 text-theme-text-muted" />
            {{ currentTime }}
          </span>
          <span v-if="lastRefreshed" class="text-[11px] text-theme-text-muted">
            Updated {{ lastRefreshed }}
          </span>
        </div>

        <div>
          <h1 class="font-display text-2xl font-bold tracking-tight text-theme-text sm:text-3xl">
            {{ title }} <span class="bg-clip-text text-transparent" :class="theme === 'gold' ? 'bg-gradient-to-r from-gold via-amber-400 to-amber-200' : 'bg-gradient-to-r from-[#FF6700] via-amber-400 to-amber-200'">{{ titleHighlight }}</span>
          </h1>
          <p class="mt-1 text-xs sm:text-sm text-theme-text-muted max-w-xl">
            {{ subtitle }}
          </p>
        </div>
      </div>

      <!-- Controls & Actions -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Range Selector Pills -->
        <div class="flex items-center rounded-xl border border-theme-border bg-theme-bg/80 p-1 backdrop-blur-md">
          <button
            v-for="option in safeRangeOptions"
            :key="option.value"
            type="button"
            @click="$emit('update:selectedRange', option.value)"
            :class="[
              'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-200',
              selectedRange === option.value
                ? (theme === 'gold'
                    ? 'bg-gradient-to-r from-gold via-amber-400 to-amber-500 text-obsidian shadow-lg shadow-gold/25 font-black'
                    : 'bg-gradient-to-r from-[#FF6700] to-amber-500 text-white shadow-lg shadow-[#FF6700]/25')
                : 'text-theme-muted hover:text-theme-text hover:bg-theme-surface'
            ]"
          >
            {{ option.label }}
          </button>
        </div>

        <!-- Direct Link to Live Analytics Dashboard -->
        <router-link
          :to="analyticsRoute"
          class="inline-flex items-center gap-2 rounded-xl border border-theme-border bg-theme-surface px-3.5 py-2 text-xs font-semibold text-theme-text transition-all"
          :class="theme === 'gold' ? 'hover:border-gold/40 hover:bg-gold/10 hover:text-gold' : 'hover:border-sky-500/40 hover:bg-sky-500/10 hover:text-sky-400'"
        >
          <ChartBarSquareIcon class="h-4 w-4" />
          <span class="hidden sm:inline">Live Analytics</span>
        </router-link>

        <!-- Export CSV Button -->
        <button
          type="button"
          @click="$emit('export-csv')"
          :disabled="loading"
          class="inline-flex items-center gap-1.5 rounded-xl border border-theme-border bg-theme-surface px-3 py-2 text-xs font-semibold text-theme-text hover:border-emerald-500/40 hover:bg-emerald-500/10 hover:text-emerald-400 transition-all disabled:opacity-50 cursor-pointer"
          title="Export report dataset as CSV spreadsheet"
        >
          <TableCellsIcon class="h-4 w-4 text-emerald-400" />
          <span class="hidden sm:inline">Export</span> CSV
        </button>

        <!-- Print Button -->
        <button
          type="button"
          @click="$emit('print')"
          class="hidden md:inline-flex items-center gap-1.5 rounded-xl border border-theme-border bg-theme-surface px-3 py-2 text-xs font-semibold text-theme-text hover:border-sky-500/40 hover:bg-sky-500/10 hover:text-sky-400 transition-all cursor-pointer"
          title="Print official report document"
        >
          <PrinterIcon class="h-4 w-4 text-sky-400" />
          <span>Print</span>
        </button>

        <!-- Generate PDF Report Button -->
        <button
          type="button"
          @click="handleGenerate"
          :disabled="loading"
          class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
          :class="theme === 'gold' ? 'bg-gradient-to-r from-gold via-amber-400 to-amber-500 text-obsidian shadow-lg shadow-gold/25 hover:shadow-xl hover:shadow-gold/35' : 'bg-gradient-to-r from-[#FF6700] to-amber-500 text-white shadow-lg shadow-[#FF6700]/25 hover:shadow-xl hover:shadow-[#FF6700]/30'"
        >
          <DocumentArrowDownIcon class="h-4 w-4" />
          <span>Generate PDF</span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
  ClockIcon, 
  DocumentArrowDownIcon, 
  ChartBarSquareIcon,
  TableCellsIcon,
  PrinterIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  loading: { type: Boolean, default: false },
  selectedRange: { type: String, required: true },
  rangeOptions: { type: Array, default: () => [] },
  lastRefreshed: { type: String, default: null },
  theme: { type: String, default: 'admin' },
  title: { type: String, default: 'Operational' },
  titleHighlight: { type: String, default: 'Reports' },
  subtitle: { 
    type: String, 
    default: 'Official financial ledger, barber throughput, and client retention summaries ready for export.' 
  }
});

const emit = defineEmits(['update:selectedRange', 'generate', 'export', 'export-csv', 'print']);

const defaultRanges = [
  { label: '7 Days', value: '7d' },
  { label: '30 Days', value: '30d' },
  { label: 'This Month', value: 'month' },
  { label: 'All Time', value: 'all' },
];

const safeRangeOptions = computed(() => {
  return props.rangeOptions && props.rangeOptions.length > 0 ? props.rangeOptions : defaultRanges;
});

const analyticsRoute = computed(() => {
  return props.theme === 'gold' ? '/barber/analytics' : '/admin/analytics';
});

const handleGenerate = () => {
  emit('generate');
  emit('export');
};

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
