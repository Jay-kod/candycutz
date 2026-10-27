<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Total Staff Roster -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Staff Roster</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <ScissorsIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-theme-text tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-theme-text-muted">stylists</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
        <span>{{ stats.activeCount }} active staff on-duty</span>
      </div>
    </div>

    <!-- Card 2: Chair Readiness -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Chair Readiness</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <CheckBadgeIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-emerald-400 tracking-tight">{{ stats.freeChairs }}</span>
        <span class="text-xs text-theme-text-muted">chairs open</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
        <span>Ready for walk-in & online intake</span>
      </div>
    </div>

    <!-- Card 3: Quality & Rating Index -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-amber-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-500/5 blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Master Rating</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <StarIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-amber-400 tracking-tight">★ {{ stats.avgRating }}</span>
        <span class="text-xs text-theme-text-muted">/ 5.0</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span>Verified client satisfaction score</span>
      </div>
    </div>

    <!-- Card 4: Average Experience -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Experience Index</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 border border-sky-500/30">
          <AcademicCapIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-theme-text tracking-tight">{{ stats.avgExperience }}</span>
        <span class="text-xs text-theme-text-muted">yrs avg</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span>Master-grade precision styling</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  ScissorsIcon, 
  CheckBadgeIcon, 
  StarIcon, 
  AcademicCapIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  barbers: {
    type: Array,
    default: () => []
  }
});

const stats = computed(() => {
  const list = props.barbers || [];
  const totalCount = list.length;
  
  const activeCount = list.filter(b => b.status === 'active').length;
  const freeChairs = list.filter(b => b.chair_status === 'free' || (b.is_available && b.status === 'active')).length;

  const totalRating = list.reduce((sum, b) => sum + (Number(b.rating) || 5.0), 0);
  const avgRating = totalCount > 0 ? (totalRating / totalCount).toFixed(1) : '5.0';

  const totalExp = list.reduce((sum, b) => sum + (Number(b.experience_years) || 5), 0);
  const avgExperience = totalCount > 0 ? (totalExp / totalCount).toFixed(1) : '5.0';

  return {
    totalCount,
    activeCount,
    freeChairs,
    avgRating,
    avgExperience
  };
});
</script>
