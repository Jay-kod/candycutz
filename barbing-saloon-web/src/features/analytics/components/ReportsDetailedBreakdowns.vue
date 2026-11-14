<template>
  <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl">
    <!-- Header with View Toggle -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
      <div>
        <h2 class="font-display text-lg sm:text-xl font-bold text-theme-text flex items-center gap-2">
          Structured Performance & <span class="bg-gradient-to-r from-sky-400 via-indigo-400 to-amber-400 bg-clip-text text-transparent">Catalogue Audit</span>
        </h2>
        <p class="text-xs text-theme-text-muted">Detailed granular breakdown of individual chair output and service yield.</p>
      </div>

      <!-- Segmented View Switcher -->
      <div class="flex items-center rounded-xl border border-theme-border bg-theme-bg p-1 shrink-0">
        <button
          type="button"
          @click="activeSubTab = 'staff'"
          :class="[
            'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all cursor-pointer',
            activeSubTab === 'staff'
              ? 'bg-sky-500/20 text-sky-300 border border-sky-500/40 shadow-sm'
              : 'text-theme-text-muted hover:text-theme-text'
          ]"
        >
          Staff Productivity ({{ topBarbers.length }})
        </button>
        <button
          type="button"
          @click="activeSubTab = 'services'"
          :class="[
            'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all cursor-pointer',
            activeSubTab === 'services'
              ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-sm'
              : 'text-theme-text-muted hover:text-theme-text'
          ]"
        >
          Service Yield ({{ topServices.length }})
        </button>
      </div>
    </div>

    <!-- View 1: Staff Productivity Table -->
    <div v-if="activeSubTab === 'staff'" class="space-y-4">
      <div class="overflow-x-auto rounded-xl border border-theme-border/60 bg-theme-bg/40">
        <table class="w-full text-left text-xs sm:text-sm">
          <thead>
            <tr class="border-b border-theme-border/60 bg-theme-surface text-[11px] font-bold uppercase tracking-wider text-theme-text-muted">
              <th scope="col" class="py-3.5 px-4">Rank / Master Barber</th>
              <th scope="col" class="py-3.5 px-4 text-center">Satisfaction</th>
              <th scope="col" class="py-3.5 px-4 text-center">Fulfilled Appts</th>
              <th scope="col" class="py-3.5 px-4 text-right">Revenue Generated</th>
              <th scope="col" class="py-3.5 px-4 text-right">Avg / Client</th>
              <th scope="col" class="py-3.5 px-4 text-right">Turnover Share</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-theme-border/40 font-mono">
            <tr 
              v-for="(barber, idx) in topBarbers" 
              :key="barber.id || barber.name || idx"
              class="hover:bg-theme-surface/70 transition-colors"
            >
              <!-- Name & Rank -->
              <td class="py-3 px-4 font-semibold text-theme-text">
                <div class="flex items-center gap-2.5">
                  <span 
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-[11px] font-bold"
                    :class="[
                      idx === 0 ? 'bg-amber-400/20 text-amber-400 border border-amber-400/30' :
                      idx === 1 ? 'bg-slate-300/20 text-slate-300 border border-slate-300/30' :
                      idx === 2 ? 'bg-amber-700/20 text-amber-500 border border-amber-700/30' :
                      'bg-theme-surface text-theme-text-muted border border-theme-border'
                    ]"
                  >
                    #{{ idx + 1 }}
                  </span>
                  <span class="truncate max-w-[150px] sm:max-w-xs font-sans font-medium text-theme-text">
                    {{ barber.name }}
                  </span>
                </div>
              </td>

              <!-- Rating -->
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold text-amber-400 bg-amber-400/10">
                  ★ {{ Number(barber.rating || 5.0).toFixed(1) }}
                </span>
              </td>

              <!-- Completed Appts -->
              <td class="py-3 px-4 text-center tabular-nums text-theme-text">
                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded bg-theme-surface border border-theme-border font-bold">
                  {{ barber.completed_appointments || barber.bookings || barber.count || 0 }}
                </span>
              </td>

              <!-- Revenue Generated -->
              <td class="py-3 px-4 text-right tabular-nums font-bold text-sky-400">
                ₦{{ formatCurrency(barber.revenue) }}
              </td>

              <!-- Avg Per Client -->
              <td class="py-3 px-4 text-right tabular-nums text-theme-text-muted">
                ₦{{ formatCurrency(computeBarberAvg(barber)) }}
              </td>

              <!-- Turnover Share -->
              <td class="py-3 px-4 text-right">
                <div class="inline-flex items-center gap-1.5 justify-end">
                  <span class="font-bold text-theme-text tabular-nums text-xs">
                    {{ computeBarberShare(barber.revenue) }}%
                  </span>
                  <div class="hidden md:block w-14 h-1.5 rounded-full bg-theme-bg overflow-hidden">
                    <div 
                      class="h-full rounded-full bg-sky-400"
                      :style="{ width: `${computeBarberShare(barber.revenue)}%` }"
                    ></div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="topBarbers.length === 0" class="py-8 text-center text-xs text-theme-text-muted">
        No barber productivity data available for this selected range.
      </div>
    </div>

    <!-- View 2: Service Yield Table -->
    <div v-else class="space-y-4">
      <div class="overflow-x-auto rounded-xl border border-theme-border/60 bg-theme-bg/40">
        <table class="w-full text-left text-xs sm:text-sm">
          <thead>
            <tr class="border-b border-theme-border/60 bg-theme-surface text-[11px] font-bold uppercase tracking-wider text-theme-text-muted">
              <th scope="col" class="py-3.5 px-4">Rank / Service Name</th>
              <th scope="col" class="py-3.5 px-4 text-center">Category</th>
              <th scope="col" class="py-3.5 px-4 text-center">Bookings Performed</th>
              <th scope="col" class="py-3.5 px-4 text-right">Gross Yield</th>
              <th scope="col" class="py-3.5 px-4 text-right">Catalog Contribution</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-theme-border/40 font-mono">
            <tr 
              v-for="(service, idx) in topServices" 
              :key="service.id || service.name || idx"
              class="hover:bg-theme-surface/70 transition-colors"
            >
              <!-- Name & Rank -->
              <td class="py-3 px-4 font-semibold text-theme-text">
                <div class="flex items-center gap-2.5">
                  <span 
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-[11px] font-bold"
                    :class="[
                      idx === 0 ? 'bg-amber-400/20 text-amber-400 border border-amber-400/30' :
                      idx === 1 ? 'bg-slate-300/20 text-slate-300 border border-slate-300/30' :
                      idx === 2 ? 'bg-amber-700/20 text-amber-500 border border-amber-700/30' :
                      'bg-theme-surface text-theme-text-muted border border-theme-border'
                    ]"
                  >
                    #{{ idx + 1 }}
                  </span>
                  <span class="truncate max-w-[150px] sm:max-w-xs font-sans font-medium text-theme-text">
                    {{ service.name }}
                  </span>
                </div>
              </td>

              <!-- Category -->
              <td class="py-3 px-4 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium border border-theme-border bg-theme-surface text-theme-text-muted font-sans">
                  {{ service.category || 'General' }}
                </span>
              </td>

              <!-- Times Booked -->
              <td class="py-3 px-4 text-center tabular-nums text-theme-text">
                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded bg-theme-surface border border-theme-border font-bold">
                  {{ service.count || service.bookings || 0 }}
                </span>
              </td>

              <!-- Gross Yield -->
              <td class="py-3 px-4 text-right tabular-nums font-bold text-emerald-400">
                ₦{{ formatCurrency(service.revenue) }}
              </td>

              <!-- Catalog Contribution -->
              <td class="py-3 px-4 text-right">
                <div class="inline-flex items-center gap-1.5 justify-end">
                  <span class="font-bold text-theme-text tabular-nums text-xs">
                    {{ computeServiceShare(service.revenue) }}%
                  </span>
                  <div class="hidden md:block w-14 h-1.5 rounded-full bg-theme-bg overflow-hidden">
                    <div 
                      class="h-full rounded-full bg-emerald-400"
                      :style="{ width: `${computeServiceShare(service.revenue)}%` }"
                    ></div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="topServices.length === 0" class="py-8 text-center text-xs text-theme-text-muted">
        No service yield records reported for this period.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  topBarbers: { type: Array, default: () => [] },
  topServices: { type: Array, default: () => [] },
  totalRevenue: { type: [Number, String], default: 0 }
});

const activeSubTab = ref('staff');

const totalBarberRevenue = computed(() => {
  return props.topBarbers.reduce((sum, b) => sum + Number(b.revenue || 0), 0);
});

const totalServicesRevenue = computed(() => {
  return props.topServices.reduce((sum, s) => sum + Number(s.revenue || 0), 0);
});

const computeBarberAvg = (barber) => {
  const count = Number(barber.completed_appointments || barber.bookings || barber.count || 0);
  const rev = Number(barber.revenue || 0);
  if (count === 0) return 0;
  return Math.round(rev / count);
};

const computeBarberShare = (revenue) => {
  const tot = totalBarberRevenue.value || Number(props.totalRevenue || 0);
  if (!tot) return 0;
  return Math.min(Math.round((Number(revenue || 0) / tot) * 100), 100);
};

const computeServiceShare = (revenue) => {
  const tot = totalServicesRevenue.value || Number(props.totalRevenue || 0);
  if (!tot) return 0;
  return Math.min(Math.round((Number(revenue || 0) / tot) * 100), 100);
};

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};
</script>

