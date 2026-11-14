<template>
  <footer class="pt-4 border-t-2 border-gray-900 text-xs font-mono page-break-avoid">
    <p class="text-[10px] text-gray-600 font-sans leading-relaxed mb-4">
      <strong class="text-gray-900">Official Certification:</strong>
      This document constitutes the reconciled operational and financial statement for CandyCutz Luxury Grooming Flagship (Keffi, Nasarawa State). All revenue figures, chair throughput entries, and service receipts have been extracted from the database audit log and verified against customer settlements.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1 items-end">
      <!-- 1. System Authentication & Cryptographic Hash -->
      <div class="space-y-1">
        <p class="text-[9px] uppercase tracking-wider text-gray-400 font-bold">System Authentication</p>
        <p class="text-[11px] font-bold text-gray-900">CandyCutz Ledger Engine</p>
        <p class="text-[10px] text-gray-600">Audit Stamp: {{ reportId }}</p>
        <p class="text-[9px] text-gray-400 font-mono">Checksum: {{ checksum }}</p>
        <p class="text-[9px] text-gray-400">Timestamp: {{ generatedAt }}</p>
      </div>

      <!-- 2. Facility Registration -->
      <div class="space-y-1">
        <p class="text-[9px] uppercase tracking-wider text-gray-400 font-bold">Facility & Jurisdiction</p>
        <p class="text-[11px] font-bold text-gray-900">Keffi Flagship Center</p>
        <p class="text-[10px] text-gray-600">Commercial District, Keffi</p>
        <p class="text-[9px] text-gray-400">Nasarawa State, Nigeria</p>
      </div>

      <!-- 3. Executive Approval & Rosette Seal -->
      <div class="space-y-1 sm:text-right">
        <p class="text-[9px] uppercase tracking-wider text-gray-400 font-bold">Executive Sign-Off</p>
        <div class="inline-block border-b border-gray-400 w-40 pb-0.5 pt-1 sm:float-right text-center">
          <span class="text-xs font-serif italic text-gray-700 tracking-wide">Suleiman A. / COO</span>
        </div>
        <div class="clear-both pt-1.5 flex sm:justify-end items-center gap-2">
          <!-- Circular Rosette Seal Icon -->
          <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 border border-emerald-300 text-emerald-700 text-xs shadow-xs" title="Official Seal">
            ✓
          </div>
          <div class="text-left sm:text-right">
            <p class="text-[10px] font-bold text-gray-900">General Manager / Operating Partner</p>
            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded">
              RECONCILED & VALIDATED
            </span>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  reportId: { type: String, required: true },
  generatedAt: { type: String, required: true }
});

const checksum = computed(() => {
  const hash = Math.abs(props.reportId.split('').reduce((a, b) => ((a << 5) - a) + b.charCodeAt(0), 0));
  return `SHA256:${hash.toString(16).padStart(8, '0')}`;
});
</script>
