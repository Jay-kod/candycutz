<template>
  <div class="relative rounded-2xl p-6 sm:p-8 mb-8 bg-[#0B0F13]/90 border border-white/[0.08] overflow-hidden group shadow-2xl">
    <!-- Ambient Orange Brand Glow -->
    <div class="absolute -top-24 -right-24 w-72 h-72 bg-admin/10 rounded-full blur-[90px] pointer-events-none group-hover:bg-admin/15 transition-all duration-700"></div>
    <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-amber-500/5 rounded-full blur-[90px] pointer-events-none"></div>

    <!-- Technical Corner Ticks -->
    <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
    <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-admin/10 border border-admin/20 mb-3">
          <span class="h-2 w-2 rounded-full bg-admin animate-pulse"></span>
          <span class="text-[10px] font-bold text-admin tracking-widest uppercase">Front Desk Concierge · Live Intake</span>
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-display font-bold text-white tracking-tight">
          Walk-In <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin via-amber-400 to-amber-200 font-serif italic">Check-In</span>
        </h1>
        <p class="text-ivory/50 text-xs sm:text-sm mt-1 max-w-lg">
          Instantly intake walk-in clients, allocate stylist chairs, issue digital tickets, and log realtime revenue.
        </p>
      </div>

      <!-- Live Clock & Salon Status Pill -->
      <div class="flex items-center gap-3 shrink-0 self-start md:self-auto">
        <div class="px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.06] text-right">
          <div class="text-sm font-mono font-bold text-white tracking-wider">{{ liveTime }}</div>
          <div class="text-[10px] font-mono text-ivory/40 uppercase tracking-wider">{{ liveDate }}</div>
        </div>

        <div class="px-3 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center gap-2 text-emerald-400 text-xs font-semibold">
          <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
          <span>Chairs Ready</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const liveTime = ref('');
const liveDate = ref('');
let timer = null;

const updateClock = () => {
  const now = new Date();
  liveTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  liveDate.value = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
};

onMounted(() => {
  updateClock();
  timer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>

