<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Staff Schedule Metrics">
    <!-- Card 1: Total Scheduled Barbers -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Scheduled Staff</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <UsersIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.totalBarbers }}</span>
        <span class="text-xs text-white/40">barbers</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Today's availability</span>
        <span class="font-medium text-admin font-mono">{{ stats.coverageRate }}% on duty</span>
      </div>
    </div>

    <!-- Card 2: Today's Active Coverage -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.workingTodayCount > 0 
        ? 'border-emerald-500/30 bg-[#12161C] hover:border-emerald-400' 
        : 'border-white/[0.08] bg-[#12161C] hover:border-white/20'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.workingTodayCount > 0 ? 'bg-emerald-500/15 group-hover:bg-emerald-500/25' : 'bg-white/5'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Open Today</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.workingTodayCount > 0 
            ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40' 
            : 'bg-white/5 text-white/40 border-white/10'"
        >
          <CalendarDaysIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.workingTodayCount > 0 ? 'text-emerald-400' : 'text-white/60'"
        >
          {{ stats.workingTodayCount }}
        </span>
        <span class="text-xs text-emerald-400/70 font-medium">on {{ stats.todayName }}</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="h-1.5 w-1.5 rounded-full" :class="stats.workingTodayCount > 0 ? 'bg-emerald-400 animate-pulse' : 'bg-white/20'"></span>
        <span>Accepting client appointments</span>
      </div>
    </div>

    <!-- Card 3: Weekly Shifts -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-amber-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-500/5 blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Weekly Shifts</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <ClockIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.totalWeeklyShifts }}</span>
        <span class="text-xs text-amber-400/70 font-medium">open days</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Roster capacity</span>
        <span class="text-white/60">Across all chairs</span>
      </div>
    </div>

    <!-- Card 4: Average Days Per Barber -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-cyan-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-cyan-500/5 blur-2xl group-hover:bg-cyan-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Average Schedule</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-500/15 text-cyan-400 border border-cyan-500/30">
          <ChartBarIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.averageDaysPerBarber }}</span>
        <span class="text-xs text-cyan-400/80 font-medium">days / week</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Per active stylist</span>
        <span class="font-mono text-white/80 font-medium">Standard pace</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { 
  UsersIcon, 
  CalendarDaysIcon, 
  ClockIcon, 
  ChartBarIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      totalBarbers: 0,
      workingTodayCount: 0,
      totalWeeklyShifts: 0,
      averageDaysPerBarber: '0.0',
      coverageRate: 0,
      todayName: 'Today',
    })
  }
});
</script>

