<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <!-- Left Card: Conversion Funnel (Sankey Flow) with System Colors -->
    <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 sm:p-6 relative overflow-hidden group hover:border-admin/40 transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks with CandyCutz Accent -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

      <!-- Header with Real Conversion Rate -->
      <div class="flex items-start justify-between pb-6 border-b border-white/[0.04]">
        <div>
          <div class="text-2xl sm:text-[28px] font-bold text-white tracking-tight font-sans tabular-nums">
            {{ funnelCompletionRate }}%
          </div>
          <p class="text-xs text-ivory/50 mt-0.5">Booking completion efficiency · {{ completedCount }} of {{ totalAppts }} appointments fulfilled</p>
        </div>

        <div class="text-[11px] font-medium text-emerald-400 flex items-center gap-1">
          <span>↑</span>
          <span>Salon Flow</span>
        </div>
      </div>

      <!-- Sankey Funnel Canvas -->
      <div class="relative pt-6 pb-2 h-56 flex flex-col justify-between">
        <!-- SVG Ribbon Funnel -->
        <div class="relative w-full h-36 flex items-center">
          <svg viewBox="0 0 400 120" preserveAspectRatio="none" class="w-full h-full overflow-visible">
            <defs>
              <linearGradient id="candycutzFunnelGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#FF6700" stop-opacity="0.8" />
                <stop offset="35%" stop-color="#CC5200" stop-opacity="0.65" />
                <stop offset="70%" stop-color="#803300" stop-opacity="0.5" />
                <stop offset="100%" stop-color="#3D1A00" stop-opacity="0.4" />
              </linearGradient>
            </defs>

            <!-- Upper Edge & Lower Edge Ribbon Path -->
            <path
              d="
                M 10,0
                C 80,0 90,22 140,22
                C 190,22 200,44 250,44
                C 300,44 315,53 390,53
                L 390,67
                C 315,67 300,76 250,76
                C 200,76 190,98 140,98
                C 90,98 80,120 10,120
                Z
              "
              fill="url(#candycutzFunnelGrad)"
              stroke="rgba(255,103,0,0.3)"
              stroke-width="1.5"
            />

            <!-- Vertical Stage Dividers with Glass Fill -->
            <line x1="70" y1="5" x2="70" y2="115" stroke="rgba(255,255,255,0.08)" stroke-dasharray="2 2" />
            <line x1="175" y1="26" x2="175" y2="94" stroke="rgba(255,255,255,0.08)" stroke-dasharray="2 2" />
            <line x1="285" y1="46" x2="285" y2="74" stroke="rgba(255,255,255,0.08)" stroke-dasharray="2 2" />
          </svg>

          <!-- 4 Overlay Nodes with Values & Percentage Pills -->
          <div class="absolute inset-0 flex justify-between items-center px-4 sm:px-6 pointer-events-none">
            <!-- Stage 1 -->
            <div class="flex flex-col items-center">
              <span class="text-xs font-bold text-white mb-2 tabular-nums">{{ funnelStages[0].count }}</span>
              <div class="rounded-full bg-admin/20 backdrop-blur-md border border-admin/40 px-2.5 py-0.5 text-[10px] font-bold text-white shadow">
                100%
              </div>
            </div>

            <!-- Stage 2 -->
            <div class="flex flex-col items-center">
              <span class="text-xs font-bold text-white mb-2 tabular-nums">{{ funnelStages[1].count }}</span>
              <div class="rounded-full bg-admin/20 backdrop-blur-md border border-admin/40 px-2.5 py-0.5 text-[10px] font-bold text-white shadow">
                {{ funnelStages[1].pct }}%
              </div>
            </div>

            <!-- Stage 3 -->
            <div class="flex flex-col items-center">
              <span class="text-xs font-bold text-white mb-2 tabular-nums">{{ funnelStages[2].count }}</span>
              <div class="rounded-full bg-admin/20 backdrop-blur-md border border-admin/40 px-2.5 py-0.5 text-[10px] font-bold text-white shadow">
                {{ funnelStages[2].pct }}%
              </div>
            </div>

            <!-- Stage 4 -->
            <div class="flex flex-col items-center">
              <span class="text-xs font-bold text-white mb-2 tabular-nums">{{ funnelStages[3].count }}</span>
              <div class="rounded-full bg-admin/20 backdrop-blur-md border border-admin/40 px-2.5 py-0.5 text-[10px] font-bold text-white shadow">
                {{ funnelStages[3].pct }}%
              </div>
            </div>
          </div>
        </div>

        <!-- Stage Bottom Labels -->
        <div class="flex justify-between text-[11px] text-ivory/60 px-2 sm:px-4 font-medium pt-2">
          <span>Total Clients</span>
          <span>Bookings</span>
          <span>Confirmed</span>
          <span>Fulfilled</span>
        </div>
      </div>
    </div>

    <!-- Right Card: Radial-Lined Sunburst & Real Status Breakdown -->
    <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 sm:p-6 relative overflow-hidden group hover:border-admin/40 transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks with CandyCutz Accent -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-admin/30 group-hover:border-admin transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-admin/30 group-hover:border-admin transition-colors"></span>

      <!-- Header with Real Total Orders -->
      <div class="flex items-start justify-between pb-6 border-b border-white/[0.04]">
        <div>
          <div class="text-2xl sm:text-[28px] font-bold text-white tracking-tight font-sans tabular-nums">
            {{ totalAppts }}
          </div>
          <p class="text-xs text-ivory/50 mt-0.5">Total salon bookings breakdown by status</p>
        </div>

        <div class="text-[11px] font-medium text-admin flex items-center gap-1">
          <span>●</span>
          <span>Live Breakdown</span>
        </div>
      </div>

      <!-- Sunburst & Breakdown Content -->
      <div class="pt-6 pb-2 flex flex-col sm:flex-row items-center justify-between gap-6 h-56">
        <!-- Radial-Lined Sunburst Chart with System Palette -->
        <div class="relative w-36 h-36 shrink-0 flex items-center justify-center">
          <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
            <!-- Ray Lines Circle Texture -->
            <circle cx="50" cy="50" r="44" fill="#141820" stroke="rgba(255,103,0,0.15)" stroke-width="1" />
            
            <!-- Radiating Texture Lines (48 rays) -->
            <line
              vfor="i in 48"
              :key="'ray-' + i"
              :x1="50 + 12 * Math.cos((i * 7.5 * Math.PI) / 180)"
              :y1="50 + 12 * Math.sin((i * 7.5 * Math.PI) / 180)"
              :x2="50 + 44 * Math.cos((i * 7.5 * Math.PI) / 180)"
              :y2="50 + 44 * Math.sin((i * 7.5 * Math.PI) / 180)"
              stroke="rgba(255,103,0,0.15)"
              stroke-width="1"
            />

            <!-- Dynamic Segment Rings -->
            <circle
              v-for="(seg, idx) in sunburstSegments"
              :key="idx"
              cx="50"
              cy="50"
              r="34"
              fill="transparent"
              :stroke="seg.color"
              stroke-width="12"
              :stroke-dasharray="`${seg.length} ${seg.gap}`"
              :stroke-dashoffset="seg.offset"
              stroke-linecap="round"
              class="transition-all duration-700"
            />
          </svg>

          <!-- Center Label -->
          <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
            <span class="text-xl font-bold text-white tabular-nums">{{ totalAppts }}</span>
            <span class="text-[8px] uppercase tracking-wider text-ivory/40 font-semibold">Total</span>
          </div>
        </div>

        <!-- Real Breakdown List from Backend -->
        <div class="flex-1 w-full max-w-[260px] space-y-2.5">
          <div
            v-for="item in statusItems"
            :key="item.label"
            class="flex items-center justify-between text-xs group/item"
          >
            <!-- Bullet + Label -->
            <div class="flex items-center gap-2.5 min-w-0">
              <span class="h-2 w-2 rounded-[2px] shrink-0" :style="{ backgroundColor: item.color }"></span>
              <span class="text-ivory/70 group-hover/item:text-white transition-colors truncate capitalize">
                {{ item.label.replace('_', ' ') }}
              </span>
            </div>

            <!-- Percentage + Count from Backend -->
            <div class="flex items-center gap-3 text-right shrink-0 font-mono">
              <span class="text-ivory/40 tabular-nums">{{ item.pct }}%</span>
              <span class="text-white font-semibold tabular-nums w-10 text-right">{{ item.count }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  statusBreakdown: { type: Object, default: () => ({}) }
});

const statusColors = {
  completed: '#10B981', // Emerald
  confirmed: '#38BDF8', // Sky
  pending: '#FF6700',   // CandyCutz Admin Orange
  cancelled: '#EF4444', // Red
  no_show: '#A855F7'    // Purple
};

const totalAppts = computed(() => {
  const bd = props.statusBreakdown || {};
  const sum = Object.values(bd).reduce((a, b) => a + Number(b || 0), 0);
  return sum > 0 ? sum : Number(props.stats?.total_appointments || 0);
});

const completedCount = computed(() => {
  return Number(props.statusBreakdown?.completed || 0);
});

const funnelCompletionRate = computed(() => {
  if (totalAppts.value === 0) return props.stats?.completion_rate || 100;
  return Math.round((completedCount.value / totalAppts.value) * 100);
});

const funnelStages = computed(() => {
  const tot = totalAppts.value;
  const customers = Number(props.stats?.total_customers || 0);
  const comp = completedCount.value;
  const conf = Number(props.statusBreakdown?.confirmed || 0);

  const s1Count = Math.max(customers, tot, 1);
  const s2Count = tot;
  const s3Count = conf + comp;
  const s4Count = comp;

  return [
    { count: s1Count, pct: 100 },
    { count: s2Count, pct: Math.min(Math.round((s2Count / s1Count) * 100), 100) },
    { count: s3Count, pct: Math.min(Math.round((s3Count / s1Count) * 100), 100) },
    { count: s4Count, pct: Math.min(Math.round((s4Count / s1Count) * 100), 100) }
  ];
});

// Dynamic status items from real statusBreakdown
const statusItems = computed(() => {
  const bd = props.statusBreakdown || {};
  const entries = [
    { label: 'completed', count: Number(bd.completed || 0), color: statusColors.completed },
    { label: 'confirmed', count: Number(bd.confirmed || 0), color: statusColors.confirmed },
    { label: 'pending', count: Number(bd.pending || 0), color: statusColors.pending },
    { label: 'cancelled', count: Number(bd.cancelled || 0), color: statusColors.cancelled },
    { label: 'no_show', count: Number(bd.no_show || 0), color: statusColors.no_show }
  ];

  const total = totalAppts.value || 1;

  return entries.map(item => ({
    ...item,
    pct: Math.round((item.count / total) * 100)
  }));
});

// Dynamic circular segments for SVG
const sunburstSegments = computed(() => {
  const activeItems = statusItems.value.filter(i => i.count > 0);
  const total = activeItems.reduce((acc, i) => acc + i.count, 0);
  if (total === 0) {
    // Default single ring segment
    return [{ color: '#FF6700', length: 213, gap: 0, offset: 0 }];
  }

  const circumference = 2 * Math.PI * 34; // ~213.6
  let offset = 0;

  return activeItems.map(item => {
    const pct = item.count / total;
    const length = pct * circumference;
    const gap = circumference - length;
    const seg = { color: item.color, length, gap, offset: -offset };
    offset += length;
    return seg;
  });
});
</script>
