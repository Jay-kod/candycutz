<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Review KPI Metrics">
    <!-- Card 1: Average Rating -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-[var(--color-accent)]/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-[var(--color-accent)]/5 blur-2xl group-hover:bg-[var(--color-accent)]/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Average Rating</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <StarIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-theme-text tracking-tight">{{ stats.averageRating }}</span>
        <span class="text-xs text-amber-400/90 font-semibold">/ 5.0</span>
      </div>
      <div class="mt-3 flex items-center gap-1.5 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <div class="flex text-amber-400">
          <span v-for="i in 5" :key="i">
            <svg class="h-3 w-3 fill-current" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </span>
        </div>
        <span>Based on {{ stats.totalCount }} feedback</span>
      </div>
    </div>

    <!-- Card 2: Total Reviews -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-theme-border/80 transition-all duration-300">
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Total Reviews</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--color-accent)]/15 text-[var(--color-accent)] border border-[var(--color-accent)]/30">
          <ChatBubbleBottomCenterTextIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-theme-text tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-theme-text-muted">submissions</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
        <span>{{ stats.satisfactionRate }}% positive (4-5★)</span>
      </div>
    </div>

    <!-- Card 3: 5-Star Reviews -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-amber-400/40 transition-all duration-300">
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">5-Star Excellence</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <TrophyIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-amber-400 tracking-tight">{{ stats.fiveStarCount }}</span>
        <span class="text-xs text-theme-text-muted">perfect cuts</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-theme-text-muted relative z-10 pt-2 border-t border-theme-border/50">
        <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
        <span>{{ stats.totalCount ? Math.round((stats.fiveStarCount / stats.totalCount) * 100) : 0 }}% of total ratings</span>
      </div>
    </div>

    <!-- Card 4: Publication Status -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-theme-text-muted">Public Showcase</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <CheckCircleIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-emerald-400 tracking-tight">{{ stats.approvedCount }}</span>
        <span class="text-xs text-theme-text-muted">published</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] relative z-10 pt-2 border-t border-theme-border/50">
        <span class="h-1.5 w-1.5 rounded-full" :class="stats.pendingCount > 0 ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400'"></span>
        <span :class="stats.pendingCount > 0 ? 'text-amber-400/90 font-medium' : 'text-theme-text-muted'">
          {{ stats.pendingCount > 0 ? `${stats.pendingCount} pending review` : 'All reviews published' }}
        </span>
      </div>
    </div>
  </section>
</template>

<script setup>
import {
  StarIcon,
  ChatBubbleBottomCenterTextIcon,
  TrophyIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
  },
});
</script>

