<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Testimonials & Feedback Metrics">
    <!-- Card 1: Total Customer Reviews -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Total Reviews</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <ChatBubbleLeftRightIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-theme-text tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-theme-text-muted">feedback</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
        <span>{{ stats.approvedCount }} approved reviews</span>
      </div>
    </div>

    <!-- Card 2: Pending Moderation Alert -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.pendingCount > 0 
        ? 'border-amber-500/40 bg-theme-surface hover:border-amber-400' 
        : 'border-theme-border bg-theme-surface hover:border-theme-border/80'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.pendingCount > 0 ? 'bg-amber-500/15 group-hover:bg-amber-500/25' : 'bg-theme-surface-subtle'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Pending Review</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.pendingCount > 0 
            ? 'bg-amber-500/20 text-amber-400 border-amber-500/40' 
            : 'bg-theme-surface-subtle text-theme-text-muted border-theme-border'"
        >
          <ExclamationTriangleIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.pendingCount > 0 ? 'text-amber-400' : 'text-theme-text-muted'"
        >
          {{ stats.pendingCount }}
        </span>
        <span class="text-xs text-theme-text-muted">awaiting</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] relative z-10 pt-2 border-t border-theme-border/50">
        <span 
          v-if="stats.pendingCount > 0" 
          class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"
        ></span>
        <span :class="stats.pendingCount > 0 ? 'text-amber-400/90 font-medium' : 'text-theme-text-muted'">
          {{ stats.pendingCount > 0 ? 'Requires admin review' : 'All testimonials approved' }}
        </span>
      </div>
    </div>

    <!-- Card 3: Website Featured Ribbon -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Homepage Ribbon</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <SparklesIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-emerald-400 tracking-tight">{{ stats.featuredCount }}</span>
        <span class="text-xs text-theme-text-muted">active</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span>Drifting on public marquee</span>
      </div>
    </div>

    <!-- Card 4: Average Satisfaction Rating -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-amber-400/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-400/5 blur-2xl group-hover:bg-amber-400/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Satisfaction Index</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-400/15 text-amber-400 border border-amber-400/30">
          <StarIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-amber-400 tracking-tight">★ {{ stats.avgRating }}</span>
        <span class="text-xs text-theme-text-muted">/ 5.0</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span>{{ stats.satisfactionPct }}% client satisfaction</span>
        <span class="font-mono text-theme-text-muted">({{ stats.fiveStarCount }} perfect 5★)</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { 
  ChatBubbleLeftRightIcon, 
  ExclamationTriangleIcon, 
  SparklesIcon, 
  StarIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      totalCount: 0,
      pendingCount: 0,
      approvedCount: 0,
      featuredCount: 0,
      avgRating: 5.0,
      fiveStarCount: 0,
      satisfactionPct: 100
    })
  }
});
</script>
