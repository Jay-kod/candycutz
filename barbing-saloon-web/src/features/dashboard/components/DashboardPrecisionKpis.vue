<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <article
      v-for="(card, idx) in cards"
      :key="card.label || idx"
      class="group relative overflow-hidden rounded-xl border border-[#1E2025] bg-[#0E0F12] p-5 transition-all duration-300 hover:border-[#2D3038] hover:bg-[#121317]"
    >
      <!-- Technical Corner Ticks -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-white/10 group-hover:border-white/25 transition-colors"></span>

      <!-- Card Content -->
      <div class="flex flex-col justify-between h-full gap-3">
        <!-- Top Row: Icon + Label -->
        <div class="flex items-center gap-2 text-white/50 text-xs font-medium">
          <component :is="card.icon" class="h-4 w-4 text-white/40 group-hover:text-white/70 transition-colors" />
          <span class="tracking-wide">{{ card.label }}</span>
        </div>

        <!-- Middle: Main Value -->
        <div>
          <div class="text-2xl sm:text-[26px] font-bold text-white tracking-tight font-sans tabular-nums">
            {{ card.value }}
          </div>
        </div>

        <!-- Bottom Row: Trend + Equalizer Sparkline -->
        <div class="flex items-center justify-between pt-1">
          <!-- Trend -->
          <div class="flex items-center gap-1 text-[11px] font-medium" :class="card.trendPositive ? 'text-emerald-400' : 'text-rose-400'">
            <span class="font-bold">{{ card.trendPositive ? '↑' : '↓' }}</span>
            <span>{{ card.trendText }}</span>
          </div>

          <!-- Mini Sparkline Equalizer Bars -->
          <div class="flex items-end gap-[3px] h-4">
            <span
              v-for="(barH, bIdx) in card.equalizer"
              :key="bIdx"
              class="w-[3px] rounded-t-[1px] transition-all duration-300 group-hover:opacity-100"
              :class="barH.highlight ? 'bg-white/80' : 'bg-white/20'"
              :style="{ height: `${barH.height}%` }"
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
  CurrencyDollarIcon, ShoppingCartIcon, CreditCardIcon, ChartBarIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  revenue: { type: Object, default: () => ({}) }
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

  const totalRev = Number(r.total_revenue || s.month_revenue || 284920);
  const totalBookings = Number(s.total_appointments || s.today_appointments || 1842);
  const avgOrderVal = totalBookings > 0 ? Math.round(totalRev / totalBookings) : 154;
  const convRate = s.completion_rate ? `${s.completion_rate}%` : '3.06%';

  return [
    {
      label: 'Total revenue',
      value: `₦${formatCurrency(totalRev)}`,
      icon: CurrencyDollarIcon,
      trendPositive: true,
      trendText: '8.2% vs prior 30 days',
      equalizer: [
        { height: 35, highlight: false },
        { height: 60, highlight: false },
        { height: 45, highlight: false },
        { height: 80, highlight: true },
        { height: 100, highlight: true },
        { height: 65, highlight: false }
      ]
    },
    {
      label: 'Orders',
      value: formatNumber(totalBookings),
      icon: ShoppingCartIcon,
      trendPositive: true,
      trendText: '4.1% vs prior 30 days',
      equalizer: [
        { height: 40, highlight: false },
        { height: 50, highlight: false },
        { height: 70, highlight: false },
        { height: 60, highlight: false },
        { height: 95, highlight: true },
        { height: 75, highlight: false }
      ]
    },
    {
      label: 'Average order value',
      value: `₦${formatCurrency(avgOrderVal)}`,
      icon: CreditCardIcon,
      trendPositive: false,
      trendText: '1.3% vs prior 30 days',
      equalizer: [
        { height: 65, highlight: false },
        { height: 80, highlight: false },
        { height: 50, highlight: false },
        { height: 75, highlight: false },
        { height: 60, highlight: true },
        { height: 45, highlight: false }
      ]
    },
    {
      label: 'Store conversion',
      value: convRate,
      icon: ChartBarIcon,
      trendPositive: true,
      trendText: '0.6% vs prior 30 days',
      equalizer: [
        { height: 30, highlight: false },
        { height: 55, highlight: false },
        { height: 45, highlight: false },
        { height: 70, highlight: false },
        { height: 85, highlight: true },
        { height: 100, highlight: true }
      ]
    }
  ];
});
</script>
