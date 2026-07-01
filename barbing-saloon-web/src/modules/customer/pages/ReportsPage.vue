<template>
  <CustomerLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-3xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8 shadow-2xl">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-48 w-48 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-bold">Account Reports</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-white">
              Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-white">Reports</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-white/60 leading-relaxed">
              View a summary of your booking history, spending, and activity on CandyCutz.
            </p>
          </div>

          <!-- Time Range Selector -->
          <div class="flex items-center gap-1.5 bg-theme-bg p-1.5 rounded-2xl border border-theme-border shadow-inner w-max">
            <button v-for="r in ranges" :key="r.value"
                    @click="setRange(r.value)"
                    :class="[
                      'px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all duration-300',
                      range === r.value 
                        ? 'bg-gradient-to-r from-gold to-gold-dark text-obsidian shadow-[0_0_15px_rgba(212,175,55,0.3)]' 
                        : 'text-theme-muted hover:text-theme-text hover:bg-white/5'
                    ]">
              {{ r.label }}
            </button>
          </div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="space-y-6">
        <div class="grid gap-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
          <div v-for="i in 4" :key="i" class="h-32 rounded-2xl bg-theme-border/30 animate-pulse border border-theme-border"></div>
        </div>
        <div class="h-80 rounded-2xl bg-theme-border/30 animate-pulse border border-theme-border"></div>
      </div>

      <template v-else>
        <!-- Summary Cards -->
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <div v-for="(stat, idx) in statCards" :key="stat.label"
               class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 p-5 backdrop-blur-sm transition-all duration-500 hover:-translate-y-1 hover:shadow-[0_10px_40px_-10px_rgba(212,175,55,0.15)]">
            <div class="absolute -right-3 -top-3 opacity-[0.06] group-hover:opacity-[0.12] transition-opacity duration-500 pointer-events-none">
              <component :is="stat.icon" class="w-24 h-24" />
            </div>
            <div class="relative z-10">
              <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-muted">{{ stat.label }}</p>
              <p class="mt-2 font-display text-3xl text-theme-text group-hover:text-gold transition-colors duration-300 leading-none">{{ stat.value }}</p>
              <p v-if="stat.sub" class="mt-1.5 text-[11px] text-white/40">{{ stat.sub }}</p>
            </div>
          </div>
        </div>

        <!-- Booking History Table -->
        <div class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm overflow-hidden">
          <div class="border-b border-white/[0.04] px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold/10 border border-gold/15">
                <ClipboardDocumentCheckIcon class="h-4.5 w-4.5 text-gold" />
              </div>
              <div>
                <h2 class="text-sm font-bold text-theme-text">Booking History</h2>
                <p class="text-[10px] text-white/35 mt-0.5">Your recent appointments</p>
              </div>
            </div>
            <span class="text-[10px] text-white/30 font-mono">{{ rangeLabel }}</span>
          </div>

          <div v-if="bookings.length > 0" class="overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
              <thead>
                <tr class="border-b border-white/[0.04] text-[10px] uppercase tracking-widest text-white/30 font-bold">
                  <th class="text-left px-6 py-3">Service</th>
                  <th class="text-left px-4 py-3">Barber</th>
                  <th class="text-left px-4 py-3">Date</th>
                  <th class="text-left px-4 py-3">Amount</th>
                  <th class="text-left px-4 py-3">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="b in bookings" :key="b.id" class="border-b border-white/[0.03] hover:bg-white/[0.02] transition-colors">
                  <td class="px-6 py-3.5 text-white/80 font-medium">{{ b.service?.name || 'Service' }}</td>
                  <td class="px-4 py-3.5 text-white/60">{{ b.barber?.name || 'Any Barber' }}</td>
                  <td class="px-4 py-3.5 text-white/50 text-xs font-mono whitespace-nowrap">{{ formatDate(b.appointment_date) }}</td>
                  <td class="px-4 py-3.5 text-emerald-400 font-bold">₦{{ Number(b.service?.price || 0).toLocaleString() }}</td>
                  <td class="px-4 py-3.5">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" :class="statusClass(b.status)">
                      <span class="h-1.5 w-1.5 rounded-full" :class="statusDot(b.status)"></span>
                      {{ b.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="flex flex-col items-center justify-center py-16 text-white/25">
            <CalendarDaysIcon class="h-12 w-12 mb-3 opacity-30" />
            <p class="text-sm font-medium">No bookings found for this period.</p>
          </div>
        </div>

        <!-- Spending Breakdown -->
        <div class="grid gap-6 lg:grid-cols-2">
          <!-- Top Services -->
          <div class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm p-6">
            <h3 class="text-sm font-bold text-theme-text mb-5 flex items-center gap-2">
              <SparklesIcon class="h-4 w-4 text-gold" /> Most Booked Services
            </h3>
            <div v-if="topServices.length > 0" class="space-y-3">
              <div v-for="s in topServices" :key="s.name" class="group">
                <div class="flex items-center justify-between mb-1.5">
                  <span class="text-xs text-theme-text font-medium truncate max-w-[180px]">{{ s.name }}</span>
                  <span class="text-[10px] text-white/40 font-bold tabular-nums">{{ s.count }} {{ s.count === 1 ? 'time' : 'times' }}</span>
                </div>
                <div class="h-1.5 w-full rounded-full bg-white/[0.04] overflow-hidden">
                  <div class="h-full rounded-full bg-gradient-to-r from-gold to-gold-dark transition-all duration-700 group-hover:opacity-90" :style="{ width: `${barWidth(s.count)}%` }"></div>
                </div>
              </div>
            </div>
            <div v-else class="flex flex-col items-center py-8 text-white/20">
              <SparklesIcon class="h-8 w-8 mb-2" />
              <p class="text-xs">No service data yet</p>
            </div>
          </div>

          <!-- Spending Summary -->
          <div class="rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-sm p-6">
            <h3 class="text-sm font-bold text-theme-text mb-5 flex items-center gap-2">
              <BanknotesIcon class="h-4 w-4 text-emerald-400" /> Spending Summary
            </h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between py-3 border-b border-white/[0.04]">
                <span class="text-xs text-white/50 uppercase tracking-wider font-bold">Total Spent</span>
                <span class="text-xl font-display text-emerald-400">₦{{ totalSpent.toLocaleString() }}</span>
              </div>
              <div class="flex items-center justify-between py-3 border-b border-white/[0.04]">
                <span class="text-xs text-white/50 uppercase tracking-wider font-bold">Completed Bookings</span>
                <span class="text-lg font-bold text-theme-text">{{ completedCount }}</span>
              </div>
              <div class="flex items-center justify-between py-3 border-b border-white/[0.04]">
                <span class="text-xs text-white/50 uppercase tracking-wider font-bold">Cancelled</span>
                <span class="text-lg font-bold text-red-400">{{ cancelledCount }}</span>
              </div>
              <div class="flex items-center justify-between py-3">
                <span class="text-xs text-white/50 uppercase tracking-wider font-bold">Avg. per Visit</span>
                <span class="text-lg font-bold text-gold">₦{{ avgPerVisit.toLocaleString() }}</span>
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
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { customerApi } from '../api/customer.api';
import {
  CalendarDaysIcon,
  BanknotesIcon,
  ClipboardDocumentCheckIcon,
  SparklesIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
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
      customerApi.bookings(),
      customerApi.analytics(range.value)
    ]);
    bookings.value = filterByRange(bookingsRes.data.data || []);
    analyticsData.value = analyticsRes.data.data || {};
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
  { label: 'Completed', value: completedCount.value, icon: CheckCircleIcon, sub: 'Successfully done' },
  { label: 'Total Spent', value: `₦${totalSpent.value.toLocaleString()}`, icon: CurrencyDollarIcon, sub: 'On completed bookings' },
  { label: 'Avg. per Visit', value: `₦${avgPerVisit.value.toLocaleString()}`, icon: BanknotesIcon, sub: 'Per completed booking' },
]);

function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

const statusClass = (status) => ({
  completed: 'bg-emerald-500/10 text-emerald-400',
  confirmed: 'bg-blue-500/10 text-blue-400',
  pending: 'bg-amber-500/10 text-amber-400',
  cancelled: 'bg-red-500/10 text-red-400',
}[status] || 'bg-white/5 text-white/40');

const statusDot = (status) => ({
  completed: 'bg-emerald-400',
  confirmed: 'bg-blue-400',
  pending: 'bg-amber-400',
  cancelled: 'bg-red-400',
}[status] || 'bg-white/30');

onMounted(fetchData);
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
