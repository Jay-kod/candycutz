<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Traffic sources with Dashed Leader Lines -->
    <div class="rounded-xl border border-[#1E2025] bg-[#0E0F12] p-5 relative overflow-hidden group hover:border-[#2D3038] transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-white/10 group-hover:border-white/25 transition-colors"></span>

      <!-- Header -->
      <div class="flex items-center justify-between pb-4 border-b border-white/[0.04]">
        <div class="flex items-center gap-2 text-xs font-medium text-white/70">
          <ArrowPathRoundedSquareIcon class="h-3.5 w-3.5 text-white/40" />
          <span>Traffic sources</span>
        </div>
        <span class="text-[10px] text-white/40 font-mono">12 months</span>
      </div>

      <!-- Dashed Line Items -->
      <div class="py-3 space-y-3.5 flex-1 flex flex-col justify-center">
        <div 
          v-for="source in trafficSources" 
          :key="source.name"
          class="flex items-center justify-between text-xs group/line"
        >
          <span class="text-white/60 group-hover/line:text-white transition-colors shrink-0">
            {{ source.name }}
          </span>
          <span class="flex-1 mx-2 border-b border-dashed border-white/15"></span>
          <span class="text-white font-mono font-semibold tabular-nums shrink-0">
            {{ source.val }}
          </span>
        </div>
      </div>
    </div>

    <!-- Card 2: Team Card -->
    <div class="rounded-xl border border-[#1E2025] bg-[#0E0F12] p-5 relative overflow-hidden group hover:border-[#2D3038] transition-all flex flex-col justify-between text-center">
      <!-- Technical Corner Ticks -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-white/10 group-hover:border-white/25 transition-colors"></span>

      <!-- Header -->
      <div class="flex items-center justify-between pb-3 border-b border-white/[0.04] text-left">
        <div class="flex items-center gap-2 text-xs font-medium text-white/70">
          <UserGroupIcon class="h-3.5 w-3.5 text-white/40" />
          <span>Team</span>
        </div>
      </div>

      <!-- Center Content -->
      <div class="py-4 flex flex-col items-center justify-center flex-1">
        <!-- Overlapping Avatars -->
        <div class="flex items-center -space-x-2.5 mb-3">
          <div class="h-10 w-10 rounded-full bg-[#1C1F26] border-2 border-[#0E0F12] flex items-center justify-center text-xs font-bold text-white shadow">
            🧑
          </div>
          <div class="h-10 w-10 rounded-full bg-[#272B35] border-2 border-[#0E0F12] flex items-center justify-center text-xs font-bold text-white shadow">
            🐰
          </div>
          <div class="h-10 w-10 rounded-full bg-[#353A47] border-2 border-[#0E0F12] flex items-center justify-center text-xs font-bold text-white shadow">
            😎
          </div>
        </div>

        <p class="text-xs font-bold text-white">No Team Members</p>
        <p class="text-[11px] text-white/40 max-w-[180px] mt-1 leading-snug">
          Invite your team to collaborate on this project.
        </p>

        <!-- Action Button -->
        <RouterLink
          to="/admin/barbers"
          class="mt-3.5 inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-[#16181D] hover:bg-[#1E2128] hover:border-white/20 px-3 py-1.5 text-[11px] font-semibold text-white/90 transition-all shadow-sm"
        >
          <PlusIcon class="h-3 w-3 text-white/70" />
          <span>Invite Members</span>
        </RouterLink>
      </div>
    </div>

    <!-- Card 3: Sales by Hour (Hourly Activity Heatmap Matrix) -->
    <div class="rounded-xl border border-[#1E2025] bg-[#0E0F12] p-5 relative overflow-hidden group hover:border-[#2D3038] transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-white/10 group-hover:border-white/25 transition-colors"></span>

      <!-- Header -->
      <div class="flex items-center justify-between pb-3 border-b border-white/[0.04]">
        <div class="flex items-center gap-2 text-xs font-medium text-white/70">
          <ClockIcon class="h-3.5 w-3.5 text-white/40" />
          <span>Sales by hour</span>
        </div>
        <span class="text-[10px] text-white/40 font-mono">Last 7 days</span>
      </div>

      <!-- Stat info -->
      <div class="pt-2 flex items-baseline justify-between">
        <div>
          <span class="text-xl font-bold text-white tabular-nums tracking-tight font-sans">₦4.8K</span>
          <p class="text-[10px] text-white/40">Peak - Sat 9p–10p</p>
        </div>
        <div class="text-right">
          <span class="text-xs font-bold text-white/80 tabular-nums">₦1.5K</span>
          <p class="text-[10px] text-white/40">Avg / hour</p>
        </div>
      </div>

      <!-- 7x24 Matrix Canvas -->
      <div class="py-2.5">
        <div class="flex items-center gap-1.5">
          <!-- Day Labels -->
          <div class="flex flex-col justify-between h-[84px] text-[8px] font-mono text-white/30 shrink-0 leading-none">
            <span>Mon</span>
            <span>Tue</span>
            <span>Wed</span>
            <span>Thu</span>
            <span>Fri</span>
            <span>Sat</span>
            <span>Sun</span>
          </div>

          <!-- Grid of Micro-Squares -->
          <div class="flex-1 flex flex-col gap-[2px]">
            <div v-for="d in 7" :key="'hrow-' + d" class="flex justify-between gap-[2px]">
              <span
                v-for="h in 24"
                :key="'hcell-' + d + '-' + h"
                class="flex-1 h-[9px] rounded-[1px] transition-colors"
                :class="getCellColor(d, h)"
              ></span>
            </div>
          </div>
        </div>

        <!-- Time Axis Labels -->
        <div class="flex justify-between text-[8px] text-white/30 font-mono pl-5 pt-1.5">
          <span>12a</span>
          <span>6a</span>
          <span>12p</span>
          <span>6p</span>
          <span>12a</span>
        </div>
      </div>

      <!-- Legend -->
      <div class="flex items-center justify-start gap-1 text-[9px] text-white/40 font-mono pt-1 border-t border-white/[0.03]">
        <span>Lower</span>
        <span class="h-2 w-2 rounded-[1px] bg-[#16181F]"></span>
        <span class="h-2 w-2 rounded-[1px] bg-[#272B35]"></span>
        <span class="h-2 w-2 rounded-[1px] bg-[#434856]"></span>
        <span class="h-2 w-2 rounded-[1px] bg-[#8A8F9E]"></span>
        <span class="h-2 w-2 rounded-[1px] bg-white"></span>
        <span>Higher</span>
      </div>
    </div>

    <!-- Card 4: Quick actions with chevron -->
    <div class="rounded-xl border border-[#1E2025] bg-[#0E0F12] p-5 relative overflow-hidden group hover:border-[#2D3038] transition-all flex flex-col justify-between">
      <!-- Technical Corner Ticks -->
      <span class="absolute top-1.5 left-1.5 w-1.5 h-1.5 border-t border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 border-t border-r border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 left-1.5 w-1.5 h-1.5 border-b border-l border-white/10 group-hover:border-white/25 transition-colors"></span>
      <span class="absolute bottom-1.5 right-1.5 w-1.5 h-1.5 border-b border-r border-white/10 group-hover:border-white/25 transition-colors"></span>

      <!-- Header -->
      <div class="flex items-center justify-between pb-3 border-b border-white/[0.04]">
        <div class="flex items-center gap-2 text-xs font-medium text-white/70">
          <BoltIcon class="h-3.5 w-3.5 text-white/40" />
          <span>Quick actions</span>
        </div>
      </div>

      <!-- Action Items List -->
      <div class="py-2 space-y-2.5 flex-1 flex flex-col justify-center">
        <RouterLink
          v-for="action in quickActions"
          :key="action.title"
          :to="action.to"
          class="flex items-center justify-between p-1.5 rounded-lg hover:bg-white/[0.04] transition-colors group/item"
        >
          <div class="flex items-center gap-2.5 min-w-0">
            <component :is="action.icon" class="h-4 w-4 text-white/50 group-hover/item:text-white transition-colors shrink-0" />
            <div class="min-w-0">
              <p class="text-xs font-semibold text-white/90 group-hover/item:text-white transition-colors truncate leading-snug">
                {{ action.title }}
              </p>
              <p class="text-[10px] text-white/40 truncate">
                {{ action.desc }}
              </p>
            </div>
          </div>
          <ChevronRightIcon class="h-3.5 w-3.5 text-white/30 group-hover/item:text-white group-hover/item:translate-x-0.5 transition-all shrink-0 ml-2" />
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { 
  ArrowPathRoundedSquareIcon, UserGroupIcon, ClockIcon, BoltIcon, 
  PlusIcon, ChevronRightIcon,
  ArchiveBoxIcon, Cog6ToothIcon, ArrowDownTrayIcon
} from '@heroicons/vue/24/outline';

const trafficSources = [
  { name: 'Organic search', val: '4.12K' },
  { name: 'Direct', val: '2.89K' },
  { name: 'Referral', val: '1.64K' },
  { name: 'Paid social', val: '980' },
  { name: 'Email', val: '620' }
];

const quickActions = [
  {
    title: 'Add Campaign',
    desc: 'Create a Campaign.',
    icon: PlusIcon,
    to: '/admin/walk-in'
  },
  {
    title: 'Review unfulfilled',
    desc: 'Orders waiting to ship.',
    icon: ArchiveBoxIcon,
    to: '/admin/appointments'
  },
  {
    title: 'Workspace settings',
    desc: 'Team, roles, permissions.',
    icon: Cog6ToothIcon,
    to: '/admin/settings'
  },
  {
    title: 'Export sales',
    desc: 'CSV for accountings.',
    icon: ArrowDownTrayIcon,
    to: '/admin/reports'
  }
];

// Generates shading for the 7x24 heatmap matrix matching the reference visual
const getCellColor = (day, hour) => {
  // Peak times on Friday (5) and Saturday (6) evenings (hours 18 to 22)
  if ((day === 5 || day === 6) && hour >= 18 && hour <= 22) {
    if (day === 6 && (hour === 21 || hour === 22)) return 'bg-white';
    if (hour === 20 || hour === 21) return 'bg-[#8A8F9E]';
    return 'bg-[#434856]';
  }
  if (hour >= 11 && hour <= 19) {
    if ((day + hour) % 5 === 0) return 'bg-[#434856]';
    if ((day + hour) % 3 === 0) return 'bg-[#272B35]';
    return 'bg-[#1C1F28]';
  }
  if (hour < 6) return 'bg-[#121319]';
  return 'bg-[#16181F]';
};
</script>

