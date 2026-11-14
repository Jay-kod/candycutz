<template>
  <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
      <div>
        <h2 class="font-display text-lg sm:text-xl font-bold text-theme-text flex items-center gap-2">
          Daily Financial & Operational <span class="bg-gradient-to-r from-emerald-400 via-sky-400 to-indigo-400 bg-clip-text text-transparent">Ledger</span>
        </h2>
        <p class="text-xs text-theme-text-muted">Day-by-day chronological breakdown of appointment throughput and realized gross receipts.</p>
      </div>

      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 rounded-full border border-theme-border bg-theme-bg px-3 py-1 text-xs font-mono text-theme-text-muted">
          {{ ledgerRows.length }} Entries
        </span>
      </div>
    </div>

    <!-- Tabular Ledger View (Desktop & Tablet) -->
    <div class="hidden sm:block overflow-x-auto rounded-xl border border-theme-border/60 bg-theme-bg/40">
      <table class="w-full text-left text-xs sm:text-sm">
        <thead>
          <tr class="border-b border-theme-border/60 bg-theme-surface text-[11px] font-bold uppercase tracking-wider text-theme-text-muted">
            <th scope="col" class="py-3.5 px-4 font-mono">Date / Period</th>
            <th scope="col" class="py-3.5 px-4 text-center">Appointments</th>
            <th scope="col" class="py-3.5 px-4 text-right">Realized Revenue</th>
            <th scope="col" class="py-3.5 px-4 text-right">Avg Ticket</th>
            <th scope="col" class="py-3.5 px-4">Relative Volume</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-theme-border/40 font-mono">
          <tr 
            v-for="row in ledgerRows" 
            :key="row.date || row.label"
            class="hover:bg-theme-surface/70 transition-colors"
          >
            <!-- Date/Period -->
            <td class="py-3 px-4 font-semibold text-theme-text flex items-center gap-2">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shrink-0"></span>
              <span>{{ row.label || row.date }}</span>
            </td>

            <!-- Appointments Count -->
            <td class="py-3 px-4 text-center tabular-nums text-theme-text">
              <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md bg-theme-surface border border-theme-border text-xs font-bold">
                {{ row.count }}
              </span>
            </td>

            <!-- Realized Revenue -->
            <td class="py-3 px-4 text-right tabular-nums font-bold text-emerald-400">
              ₦{{ formatCurrency(row.revenue) }}
            </td>

            <!-- Average Ticket -->
            <td class="py-3 px-4 text-right tabular-nums text-theme-text-muted">
              ₦{{ formatCurrency(row.avg_ticket) }}
            </td>

            <!-- Progress Bar Share -->
            <td class="py-3 px-4 min-w-[120px]">
              <div class="flex items-center gap-2">
                <div class="h-2 flex-1 rounded-full bg-theme-bg overflow-hidden">
                  <div
                    class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-sky-400 transition-all duration-500"
                    :style="{ width: `${computeRowBar(row.revenue)}%` }"
                  ></div>
                </div>
                <span class="text-[10px] text-theme-text-muted tabular-nums w-8 text-right">
                  {{ computeRowBar(row.revenue) }}%
                </span>
              </div>
            </td>
          </tr>

          <!-- Summary / Totals Row -->
          <tr class="bg-theme-surface font-bold text-theme-text border-t-2 border-theme-border">
            <td class="py-3.5 px-4 uppercase tracking-wider text-xs">
              Period Total ({{ ledgerRows.length }} Entries)
            </td>
            <td class="py-3.5 px-4 text-center tabular-nums text-sm text-sky-400">
              {{ totalCount.toLocaleString() }}
            </td>
            <td class="py-3.5 px-4 text-right tabular-nums text-sm text-emerald-400 font-bold">
              ₦{{ formatCurrency(totalRevenue) }}
            </td>
            <td class="py-3.5 px-4 text-right tabular-nums text-xs text-theme-text-muted">
              ₦{{ formatCurrency(overallAvgTicket) }}
            </td>
            <td class="py-3.5 px-4 text-right text-[11px] text-theme-text-muted">
              100% Realized
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Card Ledger View (Mobile) -->
    <div class="sm:hidden space-y-2.5">
      <div
        v-for="row in ledgerRows"
        :key="row.date || row.label"
        class="rounded-xl border border-theme-border bg-theme-bg/60 p-3.5 space-y-2"
      >
        <div class="flex items-center justify-between font-mono">
          <span class="font-bold text-xs text-theme-text">{{ row.label || row.date }}</span>
          <span class="text-xs font-bold text-emerald-400">₦{{ formatCurrency(row.revenue) }}</span>
        </div>
        <div class="flex items-center justify-between text-xs text-theme-text-muted font-mono">
          <span>{{ row.count }} completed appts</span>
          <span>Avg: ₦{{ formatCurrency(row.avg_ticket) }}</span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-theme-bg overflow-hidden">
          <div
            class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-sky-400"
            :style="{ width: `${computeRowBar(row.revenue)}%` }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="ledgerRows.length === 0" class="py-8 text-center text-xs text-theme-text-muted">
      No ledger entries recorded for this selected time window.
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  dailyLedger: { type: Array, default: () => [] }
});

const ledgerRows = computed(() => {
  return props.dailyLedger || [];
});

const maxRevenue = computed(() => {
  if (ledgerRows.value.length === 0) return 1;
  const revs = ledgerRows.value.map(r => Number(r.revenue || 0));
  return Math.max(...revs, 1);
});

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
  const max = maxRevenue.value;
  return Math.min(Math.round((val / max) * 100), 100);
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};
</script>

