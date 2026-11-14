<template>
  <div class="relative z-20 flex flex-col xl:flex-row xl:items-center justify-between gap-3.5 rounded-2xl border border-theme-border bg-theme-surface/90 p-3 sm:p-4 backdrop-blur-xl shadow-lg print:hidden">
    <!-- Left: Dossier Indicator & Range Selector -->
    <div class="flex flex-wrap items-center gap-3">
      <div class="flex items-center gap-2">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#FF6700]/15 text-[#FF6700] border border-[#FF6700]/30 font-bold text-sm">
          📄
        </span>
        <div>
          <h2 class="text-xs sm:text-sm font-bold text-theme-text flex items-center gap-1.5">
            Operational Report <span class="text-[#FF6700]">Dossier</span>
          </h2>
          <p class="text-[10px] text-theme-text-muted">
            WYSIWYG Executive Document • Auto-Reconciled
          </p>
        </div>
      </div>

      <!-- Range Selector Pills -->
      <div class="flex items-center rounded-xl border border-theme-border bg-theme-bg p-1 ml-auto sm:ml-2">
        <button
          v-for="option in safeRangeOptions"
          :key="option.value"
          type="button"
          @click="$emit('update:selectedRange', option.value)"
          :disabled="loading"
          :class="[
            'rounded-lg px-2.5 py-1 text-xs font-semibold transition-all cursor-pointer disabled:opacity-50',
            selectedRange === option.value
              ? 'bg-gradient-to-r from-[#FF6700] to-amber-500 text-white shadow-xs font-bold'
              : 'text-theme-text-muted hover:text-theme-text'
          ]"
        >
          {{ option.label }}
        </button>
      </div>

      <!-- Live Refresh Button -->
      <button
        type="button"
        @click="$emit('refresh')"
        :disabled="loading"
        class="inline-flex items-center justify-center h-8 w-8 rounded-xl border border-theme-border bg-theme-bg hover:border-orange-500/40 text-theme-text-muted hover:text-[#FF6700] transition-colors cursor-pointer disabled:opacity-50"
        title="Refresh live data"
      >
        <ArrowPathIcon :class="['h-3.5 w-3.5', loading ? 'animate-spin text-[#FF6700]' : '']" />
      </button>
    </div>

    <!-- Right: View Controls & Export Actions -->
    <div class="flex flex-wrap items-center gap-2 ml-auto xl:ml-0">
      <!-- Zoom Scale Controls -->
      <div class="hidden lg:flex items-center gap-1 rounded-xl border border-theme-border bg-theme-bg px-2 py-1 text-xs font-mono text-theme-text-muted">
        <span class="text-[10px] text-gray-500 mr-1">Zoom:</span>
        <button
          type="button"
          @click="decreaseZoom"
          :disabled="zoomLevel <= 70"
          class="px-1.5 py-0.5 rounded hover:bg-theme-surface hover:text-theme-text disabled:opacity-30 cursor-pointer"
          title="Zoom out"
        >
          -
        </button>
        <span class="min-w-[36px] text-center font-bold text-theme-text text-[11px]">{{ zoomLevel }}%</span>
        <button
          type="button"
          @click="increaseZoom"
          :disabled="zoomLevel >= 120"
          class="px-1.5 py-0.5 rounded hover:bg-theme-surface hover:text-theme-text disabled:opacity-30 cursor-pointer"
          title="Zoom in"
        >
          +
        </button>
        <button
          v-if="zoomLevel !== 100"
          type="button"
          @click="resetZoom"
          class="ml-1 text-[9px] text-[#FF6700] hover:underline cursor-pointer"
        >
          Reset
        </button>
      </div>

      <!-- Copy Summary Briefing -->
      <button
        type="button"
        @click="$emit('copy-summary')"
        class="inline-flex items-center gap-1.5 rounded-xl border border-theme-border bg-theme-bg hover:border-amber-500/40 hover:bg-amber-500/10 hover:text-amber-400 px-3 py-1.5 text-xs font-semibold text-theme-text transition-all cursor-pointer"
        title="Copy executive briefing to clipboard"
      >
        <ClipboardDocumentCheckIcon class="h-3.5 w-3.5 text-amber-400" />
        <span class="hidden sm:inline">Copy Brief</span>
      </button>

      <!-- Open History Tab Button -->
      <button
        type="button"
        @click="$emit('open-history')"
        class="inline-flex items-center gap-1.5 rounded-xl border border-theme-border bg-theme-bg hover:border-orange-500/40 hover:bg-orange-500/10 hover:text-[#FF6700] px-3 py-1.5 text-xs font-semibold text-theme-text transition-all cursor-pointer"
        title="View Export & Verification History"
      >
        <ClockIcon class="h-3.5 w-3.5 text-[#FF6700]" />
        <span class="hidden sm:inline">History</span>
      </button>

      <!-- Live Analytics Link -->
      <router-link
        to="/admin/analytics"
        class="inline-flex items-center gap-1.5 rounded-xl border border-theme-border bg-theme-bg hover:border-sky-500/40 hover:bg-sky-500/10 hover:text-sky-400 px-3 py-1.5 text-xs font-semibold text-theme-text transition-all"
        title="View Real-Time Interactive Analytics"
      >
        <ChartBarSquareIcon class="h-3.5 w-3.5 text-sky-400" />
        <span class="hidden sm:inline">Analytics</span>
      </router-link>

      <!-- Export CSV -->
      <button
        type="button"
        @click="$emit('export-csv')"
        :disabled="loading"
        class="inline-flex items-center gap-1.5 rounded-xl border border-theme-border bg-theme-bg hover:border-emerald-500/40 hover:bg-emerald-500/10 hover:text-emerald-400 px-3 py-1.5 text-xs font-semibold text-theme-text transition-all disabled:opacity-50 cursor-pointer"
        title="Export dataset to CSV"
      >
        <TableCellsIcon class="h-3.5 w-3.5 text-emerald-400" />
        <span class="hidden sm:inline">CSV</span>
      </button>

      <!-- Print Report -->
      <button
        type="button"
        @click="$emit('print')"
        class="inline-flex items-center gap-1.5 rounded-xl border border-theme-border bg-theme-bg hover:border-sky-500/40 hover:bg-sky-500/10 hover:text-sky-400 px-3 py-1.5 text-xs font-semibold text-theme-text transition-all cursor-pointer"
        title="Print document directly"
      >
        <PrinterIcon class="h-3.5 w-3.5 text-sky-400" />
        <span>Print</span>
      </button>

      <!-- Download PDF -->
      <button
        type="button"
        @click="$emit('download-pdf')"
        :disabled="loading || isGeneratingPdf"
        class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-500 hover:from-[#FF6700]/90 hover:to-amber-500/90 text-white px-3.5 py-1.5 text-xs font-bold shadow-md shadow-[#FF6700]/25 transition-all active:scale-95 disabled:opacity-50 cursor-pointer"
        title="Download this exact document as PDF"
      >
        <div v-if="isGeneratingPdf" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/30 border-t-white"></div>
        <ArrowDownTrayIcon v-else class="h-3.5 w-3.5" />
        <span>{{ isGeneratingPdf ? 'Generating...' : 'PDF' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  ChartBarSquareIcon,
  TableCellsIcon,
  PrinterIcon,
  ArrowDownTrayIcon,
  ArrowPathIcon,
  ClipboardDocumentCheckIcon,
  ClockIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  loading: { type: Boolean, default: false },
  selectedRange: { type: String, required: true },
  rangeOptions: { type: Array, default: () => [] },
  isGeneratingPdf: { type: Boolean, default: false },
  zoomLevel: { type: Number, default: 100 }
});

const emit = defineEmits([
  'update:selectedRange',
  'refresh',
  'export-csv',
  'print',
  'download-pdf',
  'copy-summary',
  'update:zoomLevel',
  'open-history'
]);

const defaultRanges = [
  { label: '7 Days', value: '7d' },
  { label: '30 Days', value: '30d' },
  { label: 'This Month', value: 'month' },
  { label: 'All Time', value: 'all' },
];

const safeRangeOptions = computed(() => {
  return props.rangeOptions && props.rangeOptions.length > 0 ? props.rangeOptions : defaultRanges;
});

const increaseZoom = () => {
  if (props.zoomLevel < 120) {
    emit('update:zoomLevel', props.zoomLevel + 10);
  }
};

const decreaseZoom = () => {
  if (props.zoomLevel > 70) {
    emit('update:zoomLevel', props.zoomLevel - 10);
  }
};

const resetZoom = () => {
  emit('update:zoomLevel', 100);
};
</script>
