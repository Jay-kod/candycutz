<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <article
      v-for="(card, idx) in cards"
      :key="card.label || idx"
      class="group relative overflow-hidden rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 transition-all duration-300 hover:border-admin/40 hover:bg-[#0E141A]"
    >
      <!-- Technical Corner Ticks with CandyCutz Orange Accent -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

      <!-- Card Content -->
      <div class="flex flex-col justify-between h-full gap-3">
        <!-- Top Row: Icon + Label -->
        <div class="flex items-center gap-2 text-ivory/60 text-xs font-medium">
          <component :is="card.icon" class="h-4 w-4" :class="card.iconColor" />
          <span class="tracking-wide">{{ card.label }}</span>
        </div>

        <!-- Middle: Dynamic Main Value -->
        <div>
          <div class="text-2xl sm:text-[28px] font-bold text-white tracking-tight font-sans tabular-nums">
            {{ card.value }}
          </div>
        </div>

        <!-- Bottom Row: Dynamic Trend + Equalizer Sparkline -->
        <div class="flex items-center justify-between pt-1">
          <!-- Trend text -->
          <div class="flex items-center gap-1 text-[11px] font-medium" :class="card.trendPositive ? 'text-emerald-400' : 'text-amber-400'">
            <span class="font-bold">{{ card.trendPositive ? '↑' : '•' }}</span>
            <span class="truncate max-w-[140px]">{{ card.trendText }}</span>
          </div>

          <!-- Dynamic Equalizer Bars with System Colors -->
          <div class="flex items-end gap-[3px] h-4 shrink-0">
            <span
              v-for="(bar, bIdx) in card.equalizer"
              :key="bIdx"
              class="w-[3px] rounded-t-[1px] transition-all duration-300"
              :class="bar.highlight ? 'bg-admin' : 'bg-admin/30 group-hover:bg-admin/50'"
              :style="{ height: `${bar.height}%` }"
            ></span>
          </div>
        </div>
      </div>
    </article>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  BanknotesIcon, CalendarDaysIcon, CreditCardIcon, CheckBadgeIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  revenue: { type: Object, default: () => ({}) },
  revenueTrend: { type: Array, default: () => [] },
  bookingTrend: { type: Array, default: () => [] }
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};

const formatNumber = (val) => {
  return new Intl.NumberFormat('en-NG').format(Number(val || 0));
};

const cards = computed(() => {
  const s = props.stats || {};
  const r = props.revenue || {};

  // 1. Total Revenue from backend
  const totalRev = Number(r.total_revenue ?? s.total_revenue ?? (Number(s.month_revenue || 0) + Number(s.today_revenue || 0)));
  const todayRev = Number(s.today_revenue || 0);

  // 2. Bookings from backend
  const totalAppts = Number(s.total_appointments ?? s.today_appointments ?? 0);
  const yesterdayAppts = Number(s.appointments_yesterday || 0);
  const pendingAppts = Number(s.pending_appointments || 0);

  // 3. Average ticket from backend
  const avgTicket = totalAppts > 0 ? Math.round(totalRev / totalAppts) : 0;

  // 4. Completion rate from backend
  const completionRate = s.completion_rate !== undefined ? Number(s.completion_rate) : (totalAppts > 0 ? 100 : 0);

  // Build dynamic equalizer bars from real revenue trend
  const revTrendData = props.revenueTrend || [];
  const maxRev = Math.max(...revTrendData.map(d => Number(d.revenue || 0)), 1);
  const revEqualizer = revTrendData.slice(-6).map((d, i) => {
    const val = Number(d.revenue || 0);
    const height = Math.max(Math.round((val / maxRev) * 100), 20);
    return { height, highlight: i === revTrendData.slice(-6).length - 1 || height > 50 };
  });

  // Fallback equalizer if no trend data
  const defaultEqualizer = [
    { height: 25, highlight: false },
    { height: 45, highlight: false },
    { height: 35, highlight: false },
    { height: 75, highlight: true },
    { height: 100, highlight: true },
    { height: 60, highlight: false }
  ];

  // Booking trend equalizer
  const bookTrendData = props.bookingTrend || [];
  const maxBook = Math.max(...bookTrendData.map(d => Number(d.count || 0)), 1);
  const bookEqualizer = bookTrendData.slice(-6).map((d, i) => {
    const val = Number(d.count || 0);
    const height = Math.max(Math.round((val / maxBook) * 100), 25);
    return { height, highlight: val > 0 };
  });

  return [
    {
      label: 'Total Revenue',
      value: `₦${formatCurrency(totalRev)}`,
      icon: BanknotesIcon,
      iconColor: 'text-emerald-400',
      trendPositive: todayRev > 0,
      trendText: todayRev > 0 ? `₦${formatCurrency(todayRev)} today` : 'Current revenue',
      equalizer: revEqualizer.length ? revEqualizer : defaultEqualizer
    },
    {
      label: 'Total Bookings',
      value: formatNumber(totalAppts),
      icon: CalendarDaysIcon,
      iconColor: 'text-admin',
      trendPositive: totalAppts >= yesterdayAppts,
      trendText: yesterdayAppts > 0 ? `${yesterdayAppts} yesterday` : `${pendingAppts} pending`,
      equalizer: bookEqualizer.length ? bookEqualizer : defaultEqualizer
    },
    {
      label: 'Average Ticket Value',
      value: `₦${formatCurrency(avgTicket)}`,
      icon: CreditCardIcon,
      iconColor: 'text-amber-400',
      trendPositive: avgTicket > 0,
      trendText: 'Per appointment',
      equalizer: revEqualizer.length ? revEqualizer : defaultEqualizer
    },
    {
      label: 'Completion Rate',
      value: `${completionRate}%`,
      icon: CheckBadgeIcon,
      iconColor: 'text-admin',
      trendPositive: completionRate >= 80,
      trendText: `${pendingAppts} awaiting service`,
      equalizer: bookEqualizer.length ? bookEqualizer : defaultEqualizer
    }
  ];
});
</script>
