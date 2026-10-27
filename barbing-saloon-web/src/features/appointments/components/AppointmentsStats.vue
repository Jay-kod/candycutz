<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Today's Bookings -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Today's Schedule</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <CalendarDaysIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-theme-text tracking-tight">{{ stats.todayCount }}</span>
        <span class="text-xs text-theme-text-muted">scheduled</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border">
        <span class="h-1.5 w-1.5 rounded-full bg-admin animate-pulse"></span>
        <span>{{ stats.todayConfirmed }} confirmed ready</span>
      </div>
    </div>

    <!-- Card 2: Pending Approvals -->
    <div 
      class="relative overflow-hidden rounded-2xl border bg-theme-surface p-5 shadow-lg group transition-all duration-300"
      :class="stats.pendingCount > 0 ? 'border-amber-500/30 hover:border-amber-500/60' : 'border-theme-border hover:border-theme-border'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.pendingCount > 0 ? 'bg-amber-500/10' : 'bg-theme-text/[0.03]'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Pending Intake</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.pendingCount > 0 ? 'bg-amber-500/15 text-amber-400 border-amber-500/30' : 'bg-theme-bg text-theme-text-muted border-theme-border'"
        >
          <ClockIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.pendingCount > 0 ? 'text-amber-400' : 'text-theme-text'"
        >
          {{ stats.pendingCount }}
        </span>
        <span class="text-xs text-theme-text-muted">awaiting action</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border">
        <span v-if="stats.awaitingReceipts > 0" class="text-amber-400 font-medium flex items-center gap-1">
          <DocumentTextIcon class="h-3.5 w-3.5" /> {{ stats.awaitingReceipts }} receipts to verify
        </span>
        <span v-else class="text-emerald-400 font-medium flex items-center gap-1">
          <CheckCircleIcon class="h-3.5 w-3.5" /> Queue is up to date
        </span>
      </div>
    </div>

    <!-- Card 3: Confirmed & Completed -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Confirmed / Active</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <CheckBadgeIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-theme-text tracking-tight">{{ stats.confirmedCount }}</span>
        <span class="text-xs text-emerald-400 font-medium">+{{ stats.completedCount }} done</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
        <span>Chairs allocated and operational</span>
      </div>
    </div>

    <!-- Card 4: Total Volume & Channels -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Total Roster</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 border border-sky-500/30">
          <ChartBarIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-theme-text tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-theme-text-muted">all time</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border">
        <span class="flex items-center gap-1">📱 {{ stats.appCount }} App</span>
        <span class="flex items-center gap-1">🚶 {{ stats.walkInCount }} Walk-In</span>
        <span class="flex items-center gap-1">🌐 {{ stats.webCount }} Web</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  CalendarDaysIcon, 
  ClockIcon, 
  CheckBadgeIcon, 
  ChartBarIcon,
  DocumentTextIcon,
  CheckCircleIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  appointments: {
    type: Array,
    default: () => []
  }
});

const stats = computed(() => {
  const list = props.appointments || [];
  const todayStr = new Date().toISOString().split('T')[0];

  const todayList = list.filter(a => a.appointment_date === todayStr);
  const todayCount = todayList.length;
  const todayConfirmed = todayList.filter(a => a.status === 'confirmed').length;

  const pendingCount = list.filter(a => a.status === 'pending').length;
  const awaitingReceipts = list.filter(a => a.receipt_image && a.payment_status === 'awaiting_verification').length;

  const confirmedCount = list.filter(a => a.status === 'confirmed').length;
  const completedCount = list.filter(a => a.status === 'completed').length;

  const appCount = list.filter(a => a.source === 'app').length;
  const walkInCount = list.filter(a => a.source === 'walk_in' || a.booking_type === 'walk_in').length;
  const webCount = list.filter(a => !a.source || a.source === 'web').length;

  return {
    todayCount,
    todayConfirmed,
    pendingCount,
    awaitingReceipts,
    confirmedCount,
    completedCount,
    totalCount: list.length,
    appCount,
    walkInCount,
    webCount
  };
});
</script>
