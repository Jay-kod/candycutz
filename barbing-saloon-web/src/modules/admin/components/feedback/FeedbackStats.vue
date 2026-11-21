<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Customer Feedback Metrics">
    <!-- Card 1: Total Inquiries -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Inbox Total</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <InboxStackIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-theme-text tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-theme-text-muted">reports</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
        <span>{{ stats.completedCount }} resolved & completed</span>
      </div>
    </div>

    <!-- Card 2: New Submissions (Needs Attention) -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.newCount > 0 
        ? 'border-amber-500/40 bg-theme-surface hover:border-amber-400' 
        : 'border-theme-border bg-theme-surface hover:border-theme-border/80'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.newCount > 0 ? 'bg-amber-500/15 group-hover:bg-amber-500/25' : 'bg-theme-surface-subtle'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Needs Review</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.newCount > 0 
            ? 'bg-amber-500/20 text-amber-400 border-amber-500/40' 
            : 'bg-theme-surface-subtle text-theme-text-muted border-theme-border'"
        >
          <BellAlertIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.newCount > 0 ? 'text-amber-400' : 'text-theme-text-muted'"
        >
          {{ stats.newCount }}
        </span>
        <span class="text-xs text-theme-text-muted">unreviewed</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] relative z-10 pt-2 border-t border-theme-border/50">
        <span 
          v-if="stats.newCount > 0" 
          class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"
        ></span>
        <span :class="stats.newCount > 0 ? 'text-amber-400/90 font-medium' : 'text-theme-text-muted'">
          {{ stats.newCount > 0 ? 'Fresh feedback awaiting review' : 'Inbox up to date' }}
        </span>
      </div>
    </div>

    <!-- Card 3: In Progress & Planned Roadmap -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">In Progress</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 border border-sky-500/30">
          <ClockIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-sky-400 tracking-tight">{{ stats.inProgressCount }}</span>
        <span class="text-xs text-theme-text-muted">active</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span>Reviewing & scheduled roadmap</span>
      </div>
    </div>

    <!-- Card 4: Topic Breakdown -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Topics Spectrum</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <WrenchScrewdriverIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-1 relative z-10">
        <span class="font-display text-2xl font-bold text-theme-text tracking-tight">{{ stats.featureCount }}</span>
        <span class="text-[11px] text-theme-text-muted">ideas</span>
        <span class="text-theme-text-muted/40 mx-1">/</span>
        <span class="font-display text-2xl font-bold text-amber-400 tracking-tight">{{ stats.maintenanceCount }}</span>
        <span class="text-[11px] text-theme-text-muted">maint</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span>Suggestions vs maintenance</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { 
  InboxStackIcon, 
  BellAlertIcon, 
  ClockIcon, 
  WrenchScrewdriverIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      totalCount: 0,
      newCount: 0,
      inProgressCount: 0,
      completedCount: 0,
      maintenanceCount: 0,
      featureCount: 0
    })
  }
});
</script>
