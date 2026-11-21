<template>
  <div class="space-y-4">
    <!-- Unified KPI Cards Grid -->
    <div 
      v-if="allCards && allCards.length" 
      class="grid gap-4 sm:gap-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5"
    >
      <article 
        v-for="(card, idx) in allCards" 
        :key="card.label || idx" 
        @click="card.link && $router.push(card.link)"
        :class="[
          'group relative overflow-hidden rounded-2xl border border-white/[0.07] bg-[#0A0F12]/80 p-5 backdrop-blur-xl transition-all duration-300',
          'hover:-translate-y-1 hover:border-white/[0.15] hover:bg-[#0E151A] hover:shadow-[0_12px_30px_-10px_rgba(0,0,0,0.5)]',
          card.link ? 'cursor-pointer' : ''
        ]"
      >
        <!-- Subtle Top Accent Glow -->
        <div 
          class="absolute top-0 left-0 right-0 h-[2px] opacity-70 group-hover:opacity-100 transition-opacity" 
          :class="card.accentBarClass || 'bg-white/10'"
        ></div>

        <div class="relative z-10 flex flex-col justify-between h-full gap-4">
          <!-- Top Row: Icon + Badge -->
          <div class="flex items-center justify-between">
            <div 
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border transition-all duration-300 group-hover:scale-105"
              :class="card.iconWrap || 'bg-white/[0.04] border-white/[0.08] text-white/70'"
            >
              <component :is="card.icon" class="h-5 w-5" :class="card.iconColor || 'text-white/80'" />
            </div>

            <!-- Status / Trend Badge -->
            <div 
              v-if="card.badge" 
              class="flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-semibold border backdrop-blur-sm"
              :class="card.badgeClass || 'bg-white/[0.05] text-white/60 border-white/[0.08]'"
            >
              <component v-if="card.badgeIcon" :is="card.badgeIcon" class="h-3 w-3" />
              <span>{{ card.badge }}</span>
            </div>
          </div>

          <!-- Metric & Label -->
          <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-white/45 group-hover:text-white/60 transition-colors">
              {{ card.label }}
            </p>
            <div class="mt-1 flex items-baseline gap-1.5">
              <span class="text-2xl lg:text-[28px] font-bold tracking-tight text-white tabular-nums font-sans">
                {{ card.value }}
              </span>
              <span v-if="card.suffix" class="text-xs font-medium text-white/40">{{ card.suffix }}</span>
            </div>
            
            <!-- Secondary Context / Subtext -->
            <div v-if="card.sub" class="mt-2.5 flex items-center gap-1.5 text-xs text-white/40">
              <span class="h-1.5 w-1.5 rounded-full" :class="card.subDotColor || 'bg-white/30'"></span>
              <span class="truncate">{{ card.sub }}</span>
            </div>
          </div>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { BanknotesIcon, ArrowTrendingUpIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  kpis: {
    type: Array,
    default: () => []
  },
  revenueCards: {
    type: Array,
    default: () => []
  }
});

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(val || 0));
};

// Combine kpis and revenueCards into a unified cohesive list if not already merged
const allCards = computed(() => {
  // If kpis already contains 4 or more cards, just use kpis
  if (props.kpis && props.kpis.length >= 4) {
    return props.kpis;
  }

  // Otherwise, merge kpis with revenueCards styled uniformly
  const merged = [...(props.kpis || [])];

  if (props.revenueCards && props.revenueCards.length) {
    const revConfig = [
      {
        icon: BanknotesIcon,
        iconColor: 'text-emerald-400',
        iconWrap: 'bg-emerald-500/10 border-emerald-500/20',
        accentBarClass: 'bg-emerald-500/40',
        badge: 'Today',
        badgeClass: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        subDotColor: 'bg-emerald-400'
      },
      {
        icon: ArrowTrendingUpIcon,
        iconColor: 'text-teal-400',
        iconWrap: 'bg-teal-500/10 border-teal-500/20',
        accentBarClass: 'bg-teal-500/40',
        badge: '7 Days',
        badgeClass: 'bg-teal-500/10 text-teal-400 border-teal-500/20',
        subDotColor: 'bg-teal-400'
      },
      {
        icon: ChartBarIcon,
        iconColor: 'text-cyan-400',
        iconWrap: 'bg-cyan-500/10 border-cyan-500/20',
        accentBarClass: 'bg-cyan-500/40',
        badge: 'Monthly',
        badgeClass: 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
        subDotColor: 'bg-cyan-400'
      }
    ];

    props.revenueCards.forEach((rev, idx) => {
      const cfg = revConfig[idx] || revConfig[0];
      // Insert after Today's Bookings or at end
      merged.push({
        label: rev.label,
        value: `₦${formatCurrency(rev.value)}`,
        icon: cfg.icon,
        iconColor: cfg.iconColor,
        iconWrap: cfg.iconWrap,
        accentBarClass: cfg.accentBarClass,
        badge: cfg.badge,
        badgeClass: cfg.badgeClass,
        sub: 'Gross revenue',
        subDotColor: cfg.subDotColor
      });
    });
  }

  return merged;
});
</script>
