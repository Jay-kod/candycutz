<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <!-- Left Card: Conversion Funnel (Sankey Flow) -->
    <div class="rounded-xl border border-[#1E2025] bg-[#0E0F12] p-5 sm:p-6 relative overflow-hidden group hover:border-[#2D3038] transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-white/10 group-hover:border-white/25 transition-colors"></span>

      <!-- Header -->
      <div class="flex items-start justify-between pb-6 border-b border-white/[0.04]">
        <div>
          <div class="text-2xl sm:text-[28px] font-bold text-white tracking-tight font-sans tabular-nums">
            7.8%
          </div>
          <p class="text-xs text-white/50 mt-0.5">Overall purchase rate - 5.64K orders from 72K views - Last 30 days</p>
        </div>

        <div class="text-[11px] font-medium text-emerald-400 flex items-center gap-1">
          <span>↑</span>
          <span>0.4% vs prior 30 days</span>
        </div>
      </div>

      <!-- Sankey Funnel Canvas -->
      <div class="relative pt-6 pb-2 h-56 flex flex-col justify-between">
        <!-- SVG Ribbon Funnel -->
        <div class="relative w-full h-36 flex items-center">
          <svg viewBox="0 0 400 120" preserveAspectRatio="none" class="w-full h-full overflow-visible">
            <defs>
              <linearGradient id="funnelGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                <stop offset="0%" stop-color="#4B4E58" stop-opacity="0.9" />
                <stop offset="33%" stop-color="#3A3D46" stop-opacity="0.8" />
                <stop offset="66%" stop-color="#2D2F36" stop-opacity="0.7" />
                <stop offset="100%" stop-color="#1E2026" stop-opacity="0.6" />
              </linearGradient>
            </defs>

            <!-- Upper Edge & Lower Edge Ribbon Path -->
            <!-- 
              Stage 1 (x: 40): y from 0 to 120 (height: 120)
              Stage 2 (x: 140): y from 22 to 98 (height: 76)
              Stage 3 (x: 250): y from 44 to 76 (height: 32)
              Stage 4 (x: 360): y from 53 to 67 (height: 14)
            -->
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
              fill="url(#funnelGrad)"
              stroke="rgba(255,255,255,0.12)"
              stroke-width="1"
            />

            <!-- Vertical Stage Dividers with Glass Fill -->
            <line x1="70" y1="5" x2="70" y2="115" stroke="rgba(255,255,255,0.06)" stroke-dasharray="2 2" />
            <line x1="175" y1="26" x2="175" y2="94" stroke="rgba(255,255,255,0.06)" stroke-dasharray="2 2" />
            <line x1="285" y1="46" x2="285" y2="74" stroke="rgba(255,255,255,0.06)" stroke-dasharray="2 2" />
          </svg>

          <!-- 4 Overlay Nodes with Values & Percentage Pills -->
          <div class="absolute inset-0 flex justify-between items-center px-4 sm:px-6 pointer-events-none">
            <!-- Stage 1 -->
            <div class="flex flex-col items-center">
              <span class="text-xs font-bold text-white mb-2 tabular-nums">72K</span>
              <div class="rounded-full bg-white/10 backdrop-blur-md border border-white/20 px-2.5 py-0.5 text-[10px] font-bold text-white shadow">
                100%
              </div>
            </div>

            <!-- Stage 2 -->
            <div class="flex flex-col items-center">
              <span class="text-xs font-bold text-white mb-2 tabular-nums">38.2K</span>
              <div class="rounded-full bg-white/10 backdrop-blur-md border border-white/20 px-2.5 py-0.5 text-[10px] font-bold text-white shadow">
                53%
              </div>
            </div>

            <!-- Stage 3 -->
            <div class="flex flex-col items-center">
              <span class="text-xs font-bold text-white mb-2 tabular-nums">16.8K</span>
              <div class="rounded-full bg-white/10 backdrop-blur-md border border-white/20 px-2.5 py-0.5 text-[10px] font-bold text-white shadow">
                23%
              </div>
            </div>

            <!-- Stage 4 -->
            <div class="flex flex-col items-center">
              <span class="text-xs font-bold text-white mb-2 tabular-nums">5.64K</span>
              <div class="rounded-full bg-white/10 backdrop-blur-md border border-white/20 px-2.5 py-0.5 text-[10px] font-bold text-white shadow">
                8%
              </div>
            </div>
          </div>
        </div>

        <!-- Stage Bottom Labels -->
        <div class="flex justify-between text-[11px] text-white/50 px-2 sm:px-4 font-medium pt-2">
          <span>Product views</span>
          <span>Add to cart</span>
          <span>Checkout</span>
          <span>Purchase</span>
        </div>
      </div>
    </div>

    <!-- Right Card: Radial-Lined Sunburst & Breakdown Table -->
    <div class="rounded-xl border border-[#1E2025] bg-[#0E0F12] p-5 sm:p-6 relative overflow-hidden group hover:border-[#2D3038] transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-white/10 group-hover:border-white/25 transition-colors"></span>

      <!-- Header -->
      <div class="flex items-start justify-between pb-6 border-b border-white/[0.04]">
        <div>
          <div class="text-2xl sm:text-[28px] font-bold text-white tracking-tight font-sans tabular-nums">
            1,842
          </div>
          <p class="text-xs text-white/50 mt-0.5">Total orders in last 30 days</p>
        </div>

        <div class="text-[11px] font-medium text-emerald-400 flex items-center gap-1">
          <span>↑</span>
          <span>0.5% vs prior 30 days</span>
        </div>
      </div>

      <!-- Sunburst & Breakdown Content -->
      <div class="pt-6 pb-2 flex flex-col sm:flex-row items-center justify-between gap-6 h-56">
        <!-- Radial-Lined Sunburst Chart -->
        <div class="relative w-36 h-36 shrink-0 flex items-center justify-center">
          <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
            <!-- Ray Lines Circle Texture -->
            <circle cx="50" cy="50" r="44" fill="#14161C" stroke="rgba(255,255,255,0.08)" stroke-width="1" />
            
            <!-- Radiating Texture Lines (48 rays) -->
            <line
              v-for="i in 48"
              :key="'ray-' + i"
              :x1="50 + 12 * Math.cos((i * 7.5 * Math.PI) / 180)"
              :y1="50 + 12 * Math.sin((i * 7.5 * Math.PI) / 180)"
              :x2="50 + 44 * Math.cos((i * 7.5 * Math.PI) / 180)"
              :y2="50 + 44 * Math.sin((i * 7.5 * Math.PI) / 180)"
              stroke="rgba(255,255,255,0.12)"
              stroke-width="1"
            />

            <!-- Outer Segment Bands -->
            <circle
              cx="50"
              cy="50"
              r="34"
              fill="transparent"
              stroke="#D6D8E1"
              stroke-width="14"
              stroke-dasharray="81 133"
              stroke-dashoffset="0"
              class="transition-all"
            />
            <circle
              cx="50"
              cy="50"
              r="34"
              fill="transparent"
              stroke="#7A7E8C"
              stroke-width="14"
              stroke-dasharray="60 154"
              stroke-dashoffset="-81"
              class="transition-all"
            />
            <circle
              cx="50"
              cy="50"
              r="34"
              fill="transparent"
              stroke="#434752"
              stroke-width="14"
              stroke-dasharray="32 182"
              stroke-dashoffset="-141"
              class="transition-all"
            />
            <circle
              cx="50"
              cy="50"
              r="34"
              fill="transparent"
              stroke="#2B2D35"
              stroke-width="14"
              stroke-dasharray="26 188"
              stroke-dashoffset="-173"
              class="transition-all"
            />
            <circle
              cx="50"
              cy="50"
              r="34"
              fill="transparent"
              stroke="#1C1E24"
              stroke-width="14"
              stroke-dasharray="15 199"
              stroke-dashoffset="-199"
              class="transition-all"
            />
          </svg>
        </div>

        <!-- Breakdown List -->
        <div class="flex-1 w-full max-w-[260px] space-y-2.5">
          <div
            v-for="item in channelBreakdown"
            :key="item.label"
            class="flex items-center justify-between text-xs group/item"
          >
            <!-- Bullet + Label -->
            <div class="flex items-center gap-2.5 min-w-0">
              <span class="h-2 w-2 rounded-[2px] shrink-0" :class="item.bulletColor"></span>
              <span class="text-white/70 group-hover/item:text-white transition-colors truncate">
                {{ item.label }}
              </span>
            </div>

            <!-- Percentage + Count -->
            <div class="flex items-center gap-3 text-right shrink-0 font-mono">
              <span class="text-white/40 tabular-nums">{{ item.pct }}%</span>
              <span class="text-white font-semibold tabular-nums w-10 text-right">{{ item.count }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const channelBreakdown = [
  { label: 'Organic search', pct: '37.9', count: 698, bulletColor: 'bg-[#D6D8E1]' },
  { label: 'Direct', pct: '28.0', count: 516, bulletColor: 'bg-[#7A7E8C]' },
  { label: 'Referral', pct: '15.0', count: 276, bulletColor: 'bg-[#434752]' },
  { label: 'Paid social', pct: '12.0', count: 221, bulletColor: 'bg-[#2B2D35]' },
  { label: 'Others', pct: '7.1', count: 131, bulletColor: 'bg-[#1C1E24]' }
];
</script>

