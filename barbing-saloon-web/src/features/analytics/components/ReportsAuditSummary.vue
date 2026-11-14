<template>
  <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      <!-- Left: Audit Certification & Compliance Notes -->
      <div class="space-y-3 flex-1 min-w-0">
        <div class="flex items-center gap-2">
          <ShieldCheckIcon class="h-5 w-5 text-emerald-400" />
          <h3 class="font-display text-base sm:text-lg font-bold text-theme-text">
            Audit Certification & Ledger Integrity
          </h3>
          <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            VERIFIED
          </span>
        </div>

        <p class="text-xs text-theme-text-muted leading-relaxed max-w-2xl">
          This document represents the consolidated operational and financial report generated for 
          <strong class="text-theme-text">CandyCutz Flagship Salon (Keffi, Nasarawa State)</strong>. 
          All monetary amounts are reconciled in Nigerian Naira (₦) against realized service settlements 
          and verified booking transactions.
        </p>

        <!-- Audit Metadata Badges -->
        <div class="flex flex-wrap items-center gap-2 text-[11px] font-mono text-theme-text-muted pt-1">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-theme-border bg-theme-bg">
            <BuildingStorefrontIcon class="h-3.5 w-3.5 text-theme-text-muted" />
            Branch: Keffi Flagship
          </span>
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-theme-border bg-theme-bg">
            <CalendarDaysIcon class="h-3.5 w-3.5 text-theme-text-muted" />
            Period: {{ activeRangeLabel }}
          </span>
          <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border border-theme-border bg-theme-bg">
            <ClockIcon class="h-3.5 w-3.5 text-theme-text-muted" />
            Audit Stamp: {{ reportId }}
          </span>
        </div>
      </div>

      <!-- Right: Quick Export Buttons -->
      <div class="flex flex-wrap sm:flex-nowrap items-center gap-3 shrink-0">
        <!-- Download CSV Button -->
        <button
          type="button"
          @click="$emit('export-csv')"
          class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl border border-theme-border bg-theme-bg hover:border-emerald-500/40 hover:bg-emerald-500/10 hover:text-emerald-400 px-4 py-2.5 text-xs font-bold text-theme-text transition-all active:scale-95 cursor-pointer"
        >
          <TableCellsIcon class="h-4 w-4 text-emerald-400" />
          <span>Export CSV</span>
        </button>

        <!-- Print Document Button -->
        <button
          type="button"
          @click="$emit('print')"
          class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-xl border border-theme-border bg-theme-bg hover:border-sky-500/40 hover:bg-sky-500/10 hover:text-sky-400 px-4 py-2.5 text-xs font-bold text-theme-text transition-all active:scale-95 cursor-pointer"
        >
          <PrinterIcon class="h-4 w-4 text-sky-400" />
          <span>Print Report</span>
        </button>

        <!-- Download PDF Dossier Button -->
        <button
          type="button"
          @click="$emit('generate')"
          class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin via-amber-500 to-[#FF6700] hover:from-admin-light hover:to-amber-400 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-[#FF6700]/25 transition-all active:scale-95 cursor-pointer"
        >
          <DocumentArrowDownIcon class="h-4 w-4" />
          <span>Download PDF</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  ShieldCheckIcon,
  BuildingStorefrontIcon,
  CalendarDaysIcon,
  ClockIcon,
  TableCellsIcon,
  PrinterIcon,
  DocumentArrowDownIcon
} from '@heroicons/vue/24/outline';

defineProps({
  activeRangeLabel: { type: String, default: '7 Days' }
});

defineEmits(['export-csv', 'print', 'generate']);

const reportId = computed(() => {
  const d = new Date();
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `CC-REP-${year}${month}${day}`;
});
</script>

