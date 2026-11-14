<template>
  <header class="border-b-2 border-gray-900 pb-5 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
      <!-- Left: Salon Corporate Letterhead -->
      <div class="space-y-1">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF6700] via-orange-600 to-amber-600 text-white shadow-md shadow-orange-500/20">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="6" cy="6" r="3" />
              <path d="M8.12 8.12 12 12" />
              <path d="M20 4 8.12 15.88" />
              <circle cx="6" cy="18" r="3" />
              <path d="M14.8 14.8 20 20" />
            </svg>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h1 class="text-xl sm:text-2xl font-black tracking-tight text-gray-900 uppercase">
                CANDYCUTZ
              </h1>
              <span class="rounded bg-black px-1.5 py-0.5 text-[9px] font-black uppercase tracking-widest text-white">
                FLAGSHIP
              </span>
            </div>
            <p class="text-[10px] font-extrabold uppercase tracking-widest text-[#FF6700]">
              Luxury Grooming & Barbing Saloon
            </p>
          </div>
        </div>
        <p class="text-[11px] text-gray-600 font-medium pt-1">
          Plot 14, Commercial District, Keffi, Nasarawa State, Nigeria
        </p>
        <p class="text-[10px] text-gray-400 font-mono">
          Enterprise Registry: RC-CCZ-49210 • Operating Terminal #01 • Central Journal
        </p>
      </div>

      <!-- Right: Document Reference & Audit Metadata -->
      <div class="sm:text-right space-y-1">
        <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-300 px-3 py-0.5 text-[10px] font-bold text-emerald-800 shadow-xs">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>AUDITED & RECONCILED</span>
        </div>
        <h2 class="text-xs sm:text-sm font-bold tracking-wide text-gray-800 uppercase">
          Executive Operations Dossier
        </h2>
        <div class="text-[11px] font-mono text-gray-600 space-y-0.5">
          <p class="flex sm:justify-end items-center gap-1">
            <span class="text-gray-400">Audit Ref:</span>
            <button
              type="button"
              @click="copyRefId"
              class="group font-bold text-gray-900 hover:text-[#FF6700] transition-colors inline-flex items-center gap-1 cursor-pointer"
              title="Click to copy Reference ID"
            >
              <span>{{ reportId }}</span>
              <svg class="h-3 w-3 text-gray-400 group-hover:text-[#FF6700]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
            </button>
          </p>
          <p><span class="text-gray-400">Audited:</span> {{ generatedAt }}</p>
          <p>
            <span class="text-gray-400">Timeframe:</span>
            <span class="ml-1 inline-block bg-orange-50 border border-orange-200 text-[#FF6700] font-black px-2 py-0.5 rounded text-[10px]">
              {{ activeRangeLabel }}
            </span>
          </p>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useToast } from '@/core/composables/useToast';

const props = defineProps({
  activeRangeLabel: { type: String, default: '7 Days' },
  reportId: { type: String, required: true },
  generatedAt: { type: String, required: true }
});

const toast = useToast();

const copyRefId = async () => {
  try {
    await navigator.clipboard.writeText(props.reportId);
    toast.success('Report Reference ID copied to clipboard');
  } catch {
    toast.info(`Ref ID: ${props.reportId}`);
  }
};
</script>
