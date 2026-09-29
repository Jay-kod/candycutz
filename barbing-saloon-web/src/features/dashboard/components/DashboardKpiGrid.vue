<template>
  <div class="space-y-6">
    <!-- Primary KPI Row -->
    <div v-if="kpis && kpis.length" class="grid gap-5 grid-cols-1 sm:grid-cols-2 xl:grid-cols-4">
      <article 
        v-for="card in kpis" 
        :key="card.label" 
        @click="card.link && $router.push(card.link)"
        class="group relative overflow-hidden rounded-[24px] bg-white/[0.02] border border-white/[0.05] p-6 backdrop-blur-2xl transition-all duration-500 hover:-translate-y-1 hover:bg-white/[0.04] hover:shadow-[0_12px_40px_-10px_rgba(0,0,0,0.6)] cursor-pointer"
      >
        <!-- Dynamic Glow Behind Card -->
        <div class="absolute -inset-1 rounded-[24px] bg-gradient-to-br from-white/10 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 pointer-events-none blur-sm" :class="card.glowClass || ''"></div>
        
        <!-- Watermark icon bottom-right -->
        <div class="absolute -right-8 -bottom-8 opacity-[0.03] transition-all duration-700 group-hover:opacity-[0.06] group-hover:scale-110 group-hover:rotate-6 pointer-events-none" :class="card.iconColor">
          <component :is="card.icon" class="w-40 h-40" />
        </div>
        
        <div class="relative z-10 flex flex-col h-full justify-between gap-6">
          <div class="flex items-start justify-between">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/[0.03] border border-white/[0.05] shadow-inner transition-colors duration-500 group-hover:border-white/20" :class="card.iconWrap">
              <component :is="card.icon" class="h-5 w-5" :class="card.iconColor" />
            </div>
            
            <!-- Badge Top Right -->
            <div v-if="card.badge" class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold border backdrop-blur-md shadow-sm transition-all duration-300" :class="card.badgeClass || 'border-current/10 bg-white/5'">
              <component :is="card.badgeIcon" class="h-3 w-3" />
              {{ card.badge }}
            </div>
          </div>

          <div class="pt-2">
            <h3 class="text-[10px] font-bold uppercase tracking-[0.2em] mb-2 text-white/50 group-hover:text-white/70 transition-colors">{{ card.label }}</h3>
            <div class="flex items-baseline gap-2">
              <span class="font-display text-4xl lg:text-5xl font-bold tracking-tight text-white drop-shadow-sm tabular-nums">
                {{ card.value }}
              </span>
              <span v-if="card.suffix" class="text-sm font-medium text-white/40">{{ card.suffix }}</span>
            </div>
            <div v-if="card.sub" class="mt-3 flex items-center gap-2 text-[11px] font-medium text-white/40">
              <span class="w-1.5 h-1.5 rounded-full bg-current opacity-50" :class="card.iconColor"></span>
              {{ card.sub }}
            </div>
          </div>
        </div>
      </article>
    </div>

    <!-- Revenue Cards Row -->
    <div v-if="revenueCards && revenueCards.length" class="grid gap-5 grid-cols-1 md:grid-cols-3">
      <article 
        v-for="(rev, idx) in revenueCards" 
        :key="rev.label" 
        class="group relative overflow-hidden rounded-[24px] border border-emerald-500/10 bg-[#020a05]/80 p-7 backdrop-blur-2xl transition-all duration-500 hover:border-emerald-400/40 hover:shadow-[0_16px_40px_-10px_rgba(16,185,129,0.15)] hover:-translate-y-1"
      >
        <!-- Sophisticated Gradient Mesh -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(16,185,129,0.15),transparent_60%)] opacity-60 group-hover:opacity-100 transition-opacity duration-700"></div>
        <div class="absolute -bottom-24 -left-24 h-48 w-48 rounded-full bg-emerald-500/10 blur-[60px] group-hover:bg-emerald-500/20 transition-colors duration-500 pointer-events-none"></div>
        
        <!-- Animated border line top -->
        <div class="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-emerald-500/30 to-transparent transform -translate-x-full group-hover:animate-[shimmer_2s_infinite]"></div>

        <div class="relative z-10 flex flex-col justify-between h-full gap-8">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 shadow-[inset_0_0_15px_rgba(16,185,129,0.1)] group-hover:scale-110 group-hover:bg-emerald-500/20 transition-all duration-300">
                <BanknotesIcon class="h-4 w-4" />
              </div>
              <h3 class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400/80">{{ rev.label }}</h3>
            </div>
            
            <!-- Index indicator -->
            <div class="text-[10px] font-bold text-emerald-500/30">0{{ idx + 1 }}</div>
          </div>
          
          <div class="pt-4">
            <p class="text-4xl lg:text-[42px] font-black tracking-tight font-display text-white flex items-baseline gap-1 drop-shadow-lg tabular-nums">
              <span class="text-2xl font-bold text-emerald-500/70">₦</span>
              {{ formatCurrency(rev.value) }}
            </p>
          </div>
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

<style scoped>
@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}
</style>
