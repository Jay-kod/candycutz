<template>
  <div>
    <!-- KPI Row 1 - Primary Metrics -->
    <div class="grid gap-5 grid-cols-2 lg:grid-cols-4 mb-5">
      <article v-for="card in kpis" :key="card.label" @click="card.link && $router.push(card.link)" class="group relative overflow-hidden rounded-3xl border p-6 backdrop-blur-xl transition-all duration-500 hover:shadow-2xl hover:-translate-y-1" :class="[card.bgClass || 'bg-charcoal border-white/[0.08] hover:border-white/20', card.link ? 'cursor-pointer' : '']">
        
        <!-- Watermark icon top-right background -->
        <div class="absolute -right-4 -top-4 opacity-[0.06] transition-all duration-700 group-hover:opacity-[0.12] group-hover:scale-110 group-hover:rotate-6 pointer-events-none" :class="card.iconColor">
          <component :is="card.icon" class="w-28 h-28" />
        </div>

        <div class="relative z-10 flex flex-col h-full justify-between gap-5">
          <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border" :class="card.iconWrap">
              <component :is="card.icon" class="h-6 w-6" :class="card.iconColor" />
            </div>
            <div>
              <h3 class="text-[11px] font-bold uppercase tracking-[0.25em] mb-1" :class="card.labelColor || 'text-ivory/60'">{{ card.label }}</h3>
              <div class="flex items-baseline gap-2">
                <span class="font-display text-4xl font-bold" :class="card.valueColor || 'text-white'">
                  {{ card.value }}
                </span>
                <span v-if="card.suffix" class="text-sm font-medium" :class="card.labelColor || 'text-ivory/40'">{{ card.suffix }}</span>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between border-t border-white/5 pt-4">
            <div class="flex items-center gap-2">
              <div v-if="card.badge" class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold border shadow-sm transition-all duration-300" :class="card.badgeClass || 'border-current/10'">
                <component :is="card.badgeIcon" class="h-3 w-3" />
                {{ card.badge }}
              </div>
              <span class="text-[11px] font-medium" :class="card.subColor || 'text-white/40'">{{ card.sub }}</span>
            </div>
          </div>
        </div>
      </article>
    </div>

    <!-- Revenue Row -->
    <div v-if="revenueCards && revenueCards.length" class="grid gap-5 grid-cols-1 sm:grid-cols-3">
      <article v-for="rev in revenueCards" :key="rev.label" class="group relative overflow-hidden rounded-3xl border border-emerald-500/15 bg-gradient-to-br from-[#112419] to-[#0a120d] p-6 backdrop-blur-xl transition-all duration-500 hover:border-emerald-400/30 hover:shadow-[0_8px_40px_rgba(16,185,129,0.15)] hover:-translate-y-1">
        <!-- Background mesh -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(16,185,129,0.08),transparent_50%)] opacity-50 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-emerald-500/10 blur-[40px] group-hover:bg-emerald-500/20 transition-colors duration-500"></div>
        
        <div class="relative z-10 flex flex-col justify-between h-full gap-6">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-emerald-400/80">{{ rev.label }}</p>
            <BanknotesIcon class="h-8 w-8 text-emerald-400 group-hover:scale-110 transition-transform" />
          </div>
          <p class="text-4xl lg:text-5xl font-black tracking-tight font-sans text-white flex items-baseline gap-1.5 drop-shadow-md">
            <span class="text-2xl font-bold text-emerald-500/70">₦</span>
            {{ formatCurrency(rev.value) }}
          </p>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { BanknotesIcon } from '@heroicons/vue/24/outline';

defineProps({
  kpis: {
    type: Array,
    required: true
  },
  revenueCards: {
    type: Array,
    default: () => []
  }
});

const formatCurrency = (value) => {
  return Number(value || 0).toLocaleString('en-NG', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
};
</script>
