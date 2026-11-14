<template>
  <div class="space-y-4">
    <!-- Header Controls: Title, Search, Filter & Actions -->
    <div class="rounded-2xl border border-theme-border bg-theme-surface/90 p-4 sm:p-5 backdrop-blur-xl shadow-lg space-y-4">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-500/10 border border-orange-500/20 text-[#FF6700]">
            <ClockIcon class="h-5 w-5" />
          </div>
          <div>
            <h2 class="text-base sm:text-lg font-bold text-theme-text flex items-center gap-2">
              Report Audit & Export <span class="text-[#FF6700]">History</span>
            </h2>
            <p class="text-xs text-theme-text-muted">
              Traceability ledger of all generated PDF downloads, CSV exports, prints, and briefing snapshots.
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button
            type="button"
            @click="$emit('switch-to-dossier')"
            class="inline-flex items-center gap-1.5 rounded-xl border border-theme-border bg-theme-bg hover:border-orange-500/40 hover:text-[#FF6700] px-3.5 py-1.5 text-xs font-semibold text-theme-text transition-all cursor-pointer"
          >
            <DocumentTextIcon class="h-4 w-4 text-[#FF6700]" />
            <span>View Current Dossier</span>
          </button>

          <button
            type="button"
            @click="$emit('refresh')"
            :disabled="loading"
            class="inline-flex items-center gap-1.5 rounded-xl border border-theme-border bg-theme-bg hover:border-orange-500/40 px-3 py-1.5 text-xs font-semibold text-theme-text-muted hover:text-theme-text transition-all cursor-pointer disabled:opacity-50"
            title="Refresh history logs"
          >
            <ArrowPathIcon :class="['h-3.5 w-3.5', loading ? 'animate-spin text-[#FF6700]' : '']" />
            <span>Refresh</span>
          </button>
        </div>
      </div>

      <!-- Search & Filter Controls -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2 border-t border-theme-border/60">
        <div class="relative flex-1 max-w-md">
          <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-theme-text-muted" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by Audit Ref, Administrator, or Action..."
            class="w-full rounded-xl border border-theme-border bg-theme-bg py-2 pl-9 pr-3 text-xs text-theme-text placeholder-theme-text-muted focus:border-[#FF6700] focus:outline-none focus:ring-1 focus:ring-[#FF6700]"
          />
        </div>

        <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 no-scrollbar">
          <button
            v-for="filter in actionFilters"
            :key="filter.value"
            type="button"
            @click="selectedActionFilter = filter.value"
            :class="[
              'rounded-lg px-2.5 py-1 text-xs font-semibold transition-all cursor-pointer whitespace-nowrap',
              selectedActionFilter === filter.value
                ? 'bg-[#FF6700] text-white shadow-xs font-bold'
                : 'text-theme-text-muted hover:text-theme-text bg-theme-bg/60 border border-theme-border'
            ]"
          >
            {{ filter.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Quick Stats Cards Strip -->
    <ReportsHistoryStats
      :total-count="historyList.length"
      :pdf-count="pdfCount"
      :csv-count="csvCount"
      :latest-event-time="latestEventTime"
      :latest-event-user="latestEventUser"
    />

    <!-- Audit History Table -->
    <ReportsHistoryTable
      :records="filteredHistory"
      :is-filtered="Boolean(searchQuery || selectedActionFilter !== 'all')"
      @verify="openVerifyModal"
      @switch-to-dossier="$emit('switch-to-dossier')"
    />

    <!-- Verification Modal -->
    <ReportVerificationModal
      :show="showVerifyModal"
      :record="selectedRecord"
      @close="showVerifyModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import {
  ClockIcon,
  DocumentTextIcon,
  ArrowPathIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';
import ReportsHistoryStats from './ReportsHistoryStats.vue';
import ReportsHistoryTable from './ReportsHistoryTable.vue';
import ReportVerificationModal from './ReportVerificationModal.vue';

const props = defineProps({
  historyList: { type: Array, default: () => [] },
  loading: { type: Boolean, default: false }
});

defineEmits(['refresh', 'switch-to-dossier']);

const searchQuery = ref('');
const selectedActionFilter = ref('all');
const showVerifyModal = ref(false);
const selectedRecord = ref(null);

const actionFilters = [
  { label: 'All Exports', value: 'all' },
  { label: 'PDF Downloads', value: 'report_pdf_download' },
  { label: 'CSV Spreadsheets', value: 'report_csv_export' },
  { label: 'Prints', value: 'report_print' },
  { label: 'Briefings', value: 'report_brief_copied' }
];

const filteredHistory = computed(() => {
  let list = props.historyList || [];

  if (selectedActionFilter.value !== 'all') {
    list = list.filter(r => r.action === selectedActionFilter.value || r.action_label?.toLowerCase().includes(selectedActionFilter.value.replace(/report_|_/g, '')));
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(r => {
      const matchId = (r.report_id || '').toLowerCase().includes(q);
      const matchUser = (r.user_name || '').toLowerCase().includes(q);
      const matchAction = (r.action_label || r.action || '').toLowerCase().includes(q);
      const matchRange = (r.range_label || r.range || '').toLowerCase().includes(q);
      return matchId || matchUser || matchAction || matchRange;
    });
  }

  return list;
});

const pdfCount = computed(() => {
  return (props.historyList || []).filter(r => r.action === 'report_pdf_download' || (r.action_label || '').toLowerCase().includes('pdf')).length;
});

const csvCount = computed(() => {
  return (props.historyList || []).filter(r => r.action === 'report_csv_export' || (r.action_label || '').toLowerCase().includes('csv')).length;
});

const latestEventTime = computed(() => {
  const first = props.historyList?.[0];
  return first?.formatted_date || (first?.created_at ? new Date(first.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : 'No exports yet');
});

const latestEventUser = computed(() => {
  const first = props.historyList?.[0];
  return first ? `by ${first.user_name || 'Admin'}` : 'System ready';
});

const openVerifyModal = (record) => {
  selectedRecord.value = record;
  showVerifyModal.value = true;
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
