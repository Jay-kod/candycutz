<template>
  <CustomerLayout>
    <section class="space-y-8 animate-fade-in pb-12">
      <!-- Luxury Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-[#12161C] p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-48 w-48 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-bold">Financial & Appointment Records</p>
          <h1 class="mt-2 font-display text-3xl sm:text-4xl lg:text-5xl text-slate-900 dark:text-white">
            Customer <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-500 dark:to-amber-200">Reports</span>
          </h1>
          <p class="mt-2 max-w-xl text-xs sm:text-sm text-slate-600 dark:text-[var(--color-text-secondary)] leading-relaxed">
            Consolidated breakdown of your completed appointments, grooming expenditures, and service history.
          </p>
        </div>

        <!-- Range Selector -->
        <div class="relative z-10 flex items-center gap-1.5 bg-slate-100 dark:bg-black/40 p-1.5 rounded-2xl border border-slate-200 dark:border-white/[0.08] shadow-inner shrink-0">
          <button 
            v-for="r in ranges" 
            :key="r.value"
            @click="setRange(r.value)"
            class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all"
            :class="range === r.value 
              ? 'bg-[var(--color-accent)] text-white shadow-md shadow-[var(--color-accent)]/20' 
              : 'text-slate-600 dark:text-[var(--color-text-secondary)] hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-white/[0.04]'"
          >
            {{ r.label }}
          </button>
        </div>
      </div>

      <!-- Zero-CLS Skeleton Loader -->
      <div v-if="loading" class="space-y-6">
        <div class="grid gap-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
          <div v-for="i in 4" :key="i" class="h-28 rounded-2xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/5 animate-pulse"></div>
        </div>
        <div class="h-80 rounded-3xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/5 animate-pulse"></div>
      </div>

      <template v-else>
        <!-- Summary Stat Cards -->
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <div 
            v-for="stat in statCards" 
            :key="stat.label"
            class="group relative overflow-hidden rounded-2xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-5 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-gold/40 hover:shadow-xl"
          >
            <div class="absolute -right-3 -top-3 opacity-[0.06] group-hover:opacity-[0.12] transition-opacity duration-300 pointer-events-none">
              <component :is="stat.icon" class="w-24 h-24" />
            </div>
            <div class="relative z-10">
              <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-[var(--color-text-muted)]">{{ stat.label }}</p>
              <p class="mt-2 font-display text-3xl text-slate-900 dark:text-white group-hover:text-[var(--color-accent)] transition-colors leading-none">{{ stat.value }}</p>
              <p v-if="stat.sub" class="mt-1.5 text-[11px] text-slate-600 dark:text-[var(--color-text-secondary)]">{{ stat.sub }}</p>
            </div>
          </div>
        </div>

        <!-- Booking History Table -->
        <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] overflow-hidden shadow-xl">
          <div class="border-b border-slate-100 dark:border-white/[0.06] px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)]">
                <ClipboardDocumentCheckIcon class="h-4.5 w-4.5" />
              </div>
              <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Booking Statement</h2>
                <p class="text-[10px] text-slate-500 dark:text-[var(--color-text-secondary)]">Filtered by selected timeframe</p>
              </div>
            </div>
            <span class="text-xs text-[var(--color-accent)] font-semibold">{{ rangeLabel }}</span>
          </div>

          <div v-if="bookings.length > 0" class="overflow-x-auto">
            <table class="w-full text-sm min-w-[650px]">
              <thead>
                <tr class="border-b border-slate-100 dark:border-white/[0.06] text-[10px] uppercase tracking-wider text-slate-500 dark:text-[var(--color-text-muted)] font-bold bg-slate-50/50 dark:bg-white/[0.02]">
                  <th class="text-left px-6 py-3.5">Service</th>
                  <th class="text-left px-4 py-3.5">Specialist</th>
                  <th class="text-left px-4 py-3.5">Date</th>
                  <th class="text-left px-4 py-3.5">Price</th>
                  <th class="text-left px-4 py-3.5">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">
                <tr v-for="b in bookings" :key="b.id" class="hover:bg-slate-50 dark:hover:bg-white/[0.02] transition-colors">
                  <td class="px-6 py-3.5 text-slate-900 dark:text-white font-medium">{{ b.service?.name || 'Grooming Service' }}</td>
                  <td class="px-4 py-3.5 text-slate-600 dark:text-[var(--color-text-secondary)]">{{ b.barber?.name || 'Assigned Barber' }}</td>
                  <td class="px-4 py-3.5 text-slate-500 dark:text-white/50 text-xs font-mono whitespace-nowrap">{{ formatDate(b.appointment_date) }}</td>
                  <td class="px-4 py-3.5 text-emerald-600 dark:text-emerald-400 font-bold">₦{{ Number(b.service?.price || 0).toLocaleString() }}</td>
                  <td class="px-4 py-3.5">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border" :class="statusClass(b.status)">
                      <span class="h-1.5 w-1.5 rounded-full" :class="statusDot(b.status)"></span>
                      {{ b.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="flex flex-col items-center justify-center py-16 text-center">
            <CalendarDaysIcon class="h-10 w-10 text-slate-300 dark:text-white/20 mb-2" />
            <p class="text-xs text-slate-500 dark:text-[var(--color-text-secondary)]">No booking activity recorded for this date window.</p>
          </div>
        </div>

        <!-- Breakdown Section -->
        <div class="grid gap-6 lg:grid-cols-2">
          <!-- Top Services -->
          <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 shadow-xl">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
              <SparklesIcon class="h-4 w-4 text-[var(--color-accent)]" /> 
              Most Frequent Services
            </h3>
            <div v-if="topServices.length > 0" class="space-y-3.5">
              <div v-for="s in topServices" :key="s.name" class="group">
                <div class="flex items-center justify-between mb-1.5">
                  <span class="text-xs text-slate-900 dark:text-white font-medium truncate max-w-[200px]">{{ s.name }}</span>
                  <span class="text-[11px] text-slate-600 dark:text-[var(--color-text-secondary)] font-bold tabular-nums">{{ s.count }} {{ s.count === 1 ? 'visit' : 'visits' }}</span>
                </div>
                <div class="h-1.5 w-full rounded-full bg-slate-100 dark:bg-white/[0.04] overflow-hidden">
                  <div class="h-full rounded-full bg-[var(--color-accent)] transition-all duration-500" :style="{ width: `${barWidth(s.count)}%` }"></div>
                </div>
              </div>
            </div>
            <div v-else class="flex flex-col items-center py-8 text-slate-400 dark:text-white/30 text-xs">
              <SparklesIcon class="h-7 w-7 mb-2" />
              <span>No service frequency data yet</span>
            </div>
          </div>

          <!-- Spending Summary -->
          <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 shadow-xl">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
              <BanknotesIcon class="h-4 w-4 text-emerald-500 dark:text-emerald-400" /> 
              Spending Breakdown
            </h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-white/[0.06]">
                <span class="text-xs text-slate-600 dark:text-[var(--color-text-secondary)] uppercase tracking-wider font-bold">Total Spent</span>
                <span class="text-xl font-display text-emerald-600 dark:text-emerald-400 font-bold">₦{{ totalSpent.toLocaleString() }}</span>
              </div>
              <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-white/[0.06]">
                <span class="text-xs text-slate-600 dark:text-[var(--color-text-secondary)] uppercase tracking-wider font-bold">Completed Visits</span>
                <span class="text-base font-bold text-slate-900 dark:text-white">{{ completedCount }}</span>
              </div>
              <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-white/[0.06]">
                <span class="text-xs text-slate-600 dark:text-[var(--color-text-secondary)] uppercase tracking-wider font-bold">Cancelled Visits</span>
                <span class="text-base font-bold text-red-500 dark:text-red-400">{{ cancelledCount }}</span>
              </div>
              <div class="flex items-center justify-between py-2.5">
                <span class="text-xs text-slate-600 dark:text-[var(--color-text-secondary)] uppercase tracking-wider font-bold">Average Per Cut</span>
                <span class="text-base font-bold text-[var(--color-accent)]">₦{{ avgPerVisit.toLocaleString() }}</span>
              </div>
            </div>
          </div>
        </div>
      </template>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { client } from '@/shared/api/client';
import {
  CalendarDaysIcon,
  BanknotesIcon,
  ClipboardDocumentCheckIcon,
  SparklesIcon,
  CheckCircleIcon,
  CurrencyDollarIcon
} from '@heroicons/vue/24/outline';

const loading = ref(true);
const range = ref('30d');
const bookings = ref([]);
const analyticsData = ref({});

const ranges = [
  { label: '7 Days', value: '7d' },
  { label: '30 Days', value: '30d' },
  { label: 'This Month', value: 'month' },
];

const rangeLabel = computed(() => {
  const r = ranges.find(r => r.value === range.value);
  return r ? r.label : '30 Days';
});

async function setRange(val) {
  range.value = val;
  await fetchData();
}

async function fetchData() {
  loading.value = true;
  try {
    const [bookingsRes, analyticsRes] = await Promise.all([
      client.get('/customer/bookings').catch(() => ({ data: { data: [] } })),
      client.get('/customer/analytics', { params: { range: range.value } }).catch(() => ({ data: { data: {} } }))
    ]);
    bookings.value = filterByRange(bookingsRes.data?.data || bookingsRes.data || []);
    analyticsData.value = analyticsRes.data?.data || analyticsRes.data || {};
  } catch (e) {
    console.error('Failed to load reports', e);
  } finally {
    loading.value = false;
  }
}

function filterByRange(list) {
  const now = new Date();
  let cutoff;
  if (range.value === '7d') cutoff = new Date(now - 7 * 86400000);
  else if (range.value === '30d') cutoff = new Date(now - 30 * 86400000);
  else cutoff = new Date(now.getFullYear(), now.getMonth(), 1);

  return list.filter(b => new Date(b.appointment_date || b.created_at) >= cutoff);
}

const completedBookings = computed(() => bookings.value.filter(b => b.status === 'completed'));
const cancelledCount = computed(() => bookings.value.filter(b => b.status === 'cancelled').length);
const completedCount = computed(() => completedBookings.value.length);

const totalSpent = computed(() => {
  return completedBookings.value.reduce((sum, b) => sum + Number(b.service?.price || 0), 0);
});

const avgPerVisit = computed(() => {
  if (completedCount.value === 0) return 0;
  return Math.round(totalSpent.value / completedCount.value);
});

const topServices = computed(() => {
  const map = {};
  bookings.value.forEach(b => {
    const name = b.service?.name;
    if (name) map[name] = (map[name] || 0) + 1;
  });
  return Object.entries(map)
    .map(([name, count]) => ({ name, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 5);
});

function barWidth(count) {
  const max = Math.max(...topServices.value.map(s => s.count), 1);
  return Math.max((count / max) * 100, 5);
}

const statCards = computed(() => [
  { label: 'Total Bookings', value: bookings.value.length, icon: CalendarDaysIcon, sub: `In the last ${rangeLabel.value.toLowerCase()}` },
  { label: 'Completed', value: completedCount.value, icon: CheckCircleIcon, sub: 'Verified & delivered' },
  { label: 'Total Spent', value: `₦${totalSpent.value.toLocaleString()}`, icon: CurrencyDollarIcon, sub: 'On completed visits' },
  { label: 'Avg. per Visit', value: `₦${avgPerVisit.value.toLocaleString()}`, icon: BanknotesIcon, sub: 'Per visit average' },
]);

function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

const statusClass = (status) => ({
  completed: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
  confirmed: 'bg-blue-500/10 text-blue-400 border-blue-500/30',
  pending: 'bg-amber-500/10 text-amber-400 border-amber-500/30',
  cancelled: 'bg-red-500/10 text-red-400 border-red-500/30',
}[status] || 'bg-white/[0.04] text-white/50 border-white/10');

const statusDot = (status) => ({
  completed: 'bg-emerald-400',
  confirmed: 'bg-blue-400',
  pending: 'bg-amber-400',
  cancelled: 'bg-red-400',
}[status] || 'bg-white/40');

onMounted(fetchData);
</script>