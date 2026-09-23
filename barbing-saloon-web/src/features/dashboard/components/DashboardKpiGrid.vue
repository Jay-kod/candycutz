<template>
  <div>
    <!-- KPI Row 1 - Primary Metrics -->
    <div class="grid gap-5 grid-cols-2 lg:grid-cols-4 mb-5">
      <article v-for="card in kpis" :key="card.label" @click="card.link && $router.push(card.link)" class="group relative overflow-hidden rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300 hover:border-admin/30" :class="[card.link ? 'cursor-pointer' : '']">

        <div class="relative z-10 flex flex-col h-full justify-between gap-5">
          <div class="flex items-start gap-4">
            <component :is="card.icon" class="h-5 w-5 shrink-0 mt-0.5" :class="card.iconColor" />
            <div>
              <h3 class="text-[11px] font-bold uppercase tracking-[0.25em] mb-1 text-ivory/60">{{ card.label }}</h3>
              <div class="flex items-baseline gap-2">
                <span class="font-display text-4xl font-bold tabular-nums" :class="card.valueColor || 'text-white'">
                  {{ card.value }}
                </span>
                <span v-if="card.suffix" class="text-sm font-medium text-ivory/40">{{ card.suffix }}</span>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between border-t border-white/5 pt-4">
            <div class="flex items-center gap-2">
              <div v-if="card.badge" class="flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[10px] font-bold border border-current/10" :class="card.badgeClass">
                <component :is="card.badgeIcon" class="h-3 w-3" />
                {{ card.badge }}
              </div>
              <span class="text-[11px] font-medium text-white/40">{{ card.sub }}</span>
            </div>
          </div>
        </div>
      </article>
    </div>

    <!-- Revenue Row -->
    <div v-if="revenueCards && revenueCards.length" class="grid gap-5 grid-cols-1 sm:grid-cols-3">
      <article v-for="rev in revenueCards" :key="rev.label" class="group relative overflow-hidden rounded-xl border border-emerald-500/20 bg-theme-surface p-6 transition-colors duration-300 hover:border-emerald-400/40">
        
        <div class="relative z-10 flex flex-col justify-between h-full gap-6">
          <div class="flex items-center justify-between">
            <p class="text-[11px] font-bold uppercase tracking-[0.25em] text-emerald-400/80">{{ rev.label }}</p>
            <BanknotesIcon class="h-5 w-5 text-emerald-400" />
          </div>
          <p class="text-4xl lg:text-5xl font-black tracking-tight font-sans text-white flex items-baseline gap-1.5 tabular-nums">
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
  return new Intl.NumberFormat('en-NG', { maximumFractionDigits: 0 }).format(Number(value || 0));
};
</script>
