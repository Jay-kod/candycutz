<template>
  <section class="mb-5 page-break-avoid">
    <!-- Header with Live Table Metrics & Sort Indicator -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
      <div class="flex items-center gap-2">
        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800 flex items-center gap-1.5">
          <span class="h-2 w-2 rounded-full bg-[#FF6700]"></span>
          Chronological Financial & Operational Ledger
        </h3>
        <span class="text-[10px] font-mono text-gray-600 font-semibold bg-gray-100 border border-gray-200 px-2 py-0.5 rounded">
          {{ sortedLedgerRows.length }} Entries
        </span>
      </div>

      <!-- Quick Sort / Active Controls (Screen-only helpers) -->
      <div class="flex items-center gap-2 text-[11px] font-mono print:hidden">
        <span class="text-gray-400 text-[10px]">Sort by:</span>
        <button
          type="button"
          @click="toggleSort('date')"
          :class="['px-1.5 py-0.5 rounded text-[10px] font-bold cursor-pointer transition-colors', sortField === 'date' ? 'bg-orange-100 text-[#FF6700]' : 'text-gray-500 hover:text-gray-900']"
        >
          Date {{ sortField === 'date' ? (sortAsc ? '↑' : '↓') : '' }}
        </button>
        <button
          type="button"
          @click="toggleSort('revenue')"
          :class="['px-1.5 py-0.5 rounded text-[10px] font-bold cursor-pointer transition-colors', sortField === 'revenue' ? 'bg-emerald-100 text-emerald-800' : 'text-gray-500 hover:text-gray-900']"
        >
          Revenue {{ sortField === 'revenue' ? (sortAsc ? '↑' : '↓') : '' }}
        </button>
        <button
          type="button"
          @click="toggleSort('count')"
          :class="['px-1.5 py-0.5 rounded text-[10px] font-bold cursor-pointer transition-colors', sortField === 'count' ? 'bg-sky-100 text-sky-800' : 'text-gray-500 hover:text-gray-900']"
        >
          Appts {{ sortField === 'count' ? (sortAsc ? '↑' : '↓') : '' }}
        </button>
      </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
      <table class="w-full text-left text-xs font-mono">
        <thead>
          <tr class="border-b border-gray-200 bg-gray-50 text-[10px] font-bold uppercase tracking-wider text-gray-600">
            <th class="py-2 px-3">Date & Day</th>
            <th class="py-2 px-3 text-center">Fulfilled Appts</th>
            <th class="py-2 px-3 text-right">Realized Gross (₦)</th>
            <th class="py-2 px-3 text-right">Avg Ticket (₦)</th>
            <th class="py-2 px-3 text-right">Relative Volume</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="row in sortedLedgerRows"
            :key="row.date || row.label"
            :class="['hover:bg-gray-50/50 transition-colors', isPeakDay(row) ? 'bg-orange-50/30' : '']"
          >
            <!-- Date & Day Tag -->
            <td class="py-1.5 px-3 font-semibold text-gray-900">
              <div class="flex items-center gap-1.5">
                <span :class="['inline-block w-1.5 h-1.5 rounded-full shrink-0', isWeekend(row) ? 'bg-[#FF6700]' : 'bg-emerald-500']"></span>
                <span>{{ row.label || row.date }}</span>
                <span v-if="isPeakDay(row)" class="ml-1 text-[9px] font-bold uppercase tracking-wider text-[#FF6700] bg-orange-100/80 border border-orange-200 px-1 py-0.2 rounded">
                  ★ Peak
                </span>
              </div>
            </td>

            <!-- Fulfilled Appts -->
            <td class="py-1.5 px-3 text-center text-gray-700 font-bold">
              <span class="inline-block min-w-[20px] px-1.5 py-0.2 rounded bg-gray-100/80 border border-gray-200 text-gray-800">
                {{ row.count }}
              </span>
            </td>

            <!-- Realized Gross Revenue -->
            <td class="py-1.5 px-3 text-right font-bold text-emerald-700">
              ₦{{ formatCurrency(row.revenue) }}
            </td>

            <!-- Avg Ticket -->
            <td class="py-1.5 px-3 text-right text-gray-600">
              ₦{{ formatCurrency(row.avg_ticket) }}
            </td>

            <!-- Progress Share Bar -->
            <td class="py-1.5 px-3 text-right">
              <div class="inline-flex items-center gap-1.5 justify-end w-full">
                <span class="text-[10px] font-semibold text-gray-500 tabular-nums">{{ computeRowBar(row.revenue) }}%</span>
                <div class="w-12 h-1.5 rounded-full bg-gray-100 overflow-hidden shrink-0">
                  <div
                    class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-sky-500"
                    :style="{ width: `${computeRowBar(row.revenue)}%` }"
                  ></div>
                </div>
              </div>
            </td>
          </tr>

          <!-- Summary / Reconciled Totals Row -->
          <tr class="bg-gray-100 font-bold text-gray-900 border-t-2 border-gray-300">
            <td class="py-2 px-3 uppercase tracking-wider text-[11px] font-sans">
              Reconciled Period Total ({{ sortedLedgerRows.length }} Entries)
            </td>
            <td class="py-2 px-3 text-center text-sky-800 text-xs">
              {{ totalCount.toLocaleString() }}
            </td>
            <td class="py-2 px-3 text-right text-emerald-800 text-xs font-black">
              ₦{{ formatCurrency(totalRevenue) }}
            </td>
            <td class="py-2 px-3 text-right text-gray-800 text-xs">
              ₦{{ formatCurrency(overallAvgTicket) }}
            </td>
            <td class="py-2 px-3 text-right text-[10px] font-bold text-emerald-700 uppercase">
              100% Reconciled
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="sortedLedgerRows.length === 0" class="py-6 text-center text-xs text-gray-400 font-sans">
        No daily ledger entries recorded for this timeframe.
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  dailyLedger: { type: Array, default: () => [] }
});

const sortField = ref('date');
const sortAsc = ref(true);

const toggleSort = (field) => {
  if (sortField.value === field) {
    sortAsc.value = !sortAsc.value;
  } else {
    sortField.value = field;
    sortAsc.value = false;
  }
};

const ledgerRows = computed(() => props.dailyLedger || []);

const sortedLedgerRows = computed(() => {
  const list = [...ledgerRows.value];
  if (sortField.value === 'revenue') {
    list.sort((a, b) => sortAsc.value ? Number(a.revenue || 0) - Number(b.revenue || 0) : Number(b.revenue || 0) - Number(a.revenue || 0));
  } else if (sortField.value === 'count') {
    list.sort((a, b) => sortAsc.value ? Number(a.count || 0) - Number(b.count || 0) : Number(b.count || 0) - Number(a.count || 0));
  }
  return list;
});

const maxRevenue = computed(() => {
  if (ledgerRows.value.length === 0) return 1;
  const revs = ledgerRows.value.map(r => Number(r.revenue || 0));
  return Math.max(...revs, 1);
});

const isPeakDay = (row) => {
  const rev = Number(row.revenue || 0);
  return rev > 0 && rev === maxRevenue.value;
};

const isWeekend = (row) => {
  const str = String(row.label || row.day || '').toLowerCase();
  return str.includes('fri') || str.includes('sat') || str.includes('sun');
};

const totalRevenue = computed(() => {
  return ledgerRows.value.reduce((sum, r) => sum + Number(r.revenue || 0), 0);
});

const totalCount = computed(() => {
  return ledgerRows.value.reduce((sum, r) => sum + Number(r.count || 0), 0);
});

const overallAvgTicket = computed(() => {
  if (totalCount.value === 0) return 0;
  return Math.round(totalRevenue.value / totalCount.value);
});

const computeRowBar = (rev) => {
  const val = Number(rev || 0);
  return Math.min(Math.round((val / maxRevenue.value) * 100), 100);
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};
</script>
