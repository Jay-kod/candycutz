<template>
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" aria-label="Blog Performance Metrics">
    <!-- Card 1: Total Articles -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Total Articles</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <DocumentTextIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.totalCount }}</span>
        <span class="text-xs text-white/40">posts</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Publish rate</span>
        <span class="font-medium text-admin font-mono">{{ stats.publishRate }}% live</span>
      </div>
    </div>

    <!-- Card 2: Drafts Queue -->
    <div 
      class="relative overflow-hidden rounded-2xl border p-5 shadow-lg group transition-all duration-300"
      :class="stats.draftsCount > 0 
        ? 'border-amber-500/30 bg-[#12161C] hover:border-amber-400' 
        : 'border-white/[0.08] bg-[#12161C] hover:border-white/20'"
    >
      <div 
        class="absolute -right-12 -top-12 h-32 w-32 rounded-full blur-2xl transition-all"
        :class="stats.draftsCount > 0 ? 'bg-amber-500/15 group-hover:bg-amber-500/25' : 'bg-white/5'"
      ></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Drafts Queue</span>
        <span 
          class="flex h-7 w-7 items-center justify-center rounded-lg border"
          :class="stats.draftsCount > 0 
            ? 'bg-amber-500/20 text-amber-400 border-amber-500/40' 
            : 'bg-white/5 text-white/40 border-white/10'"
        >
          <ClockIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-3xl font-bold tracking-tight"
          :class="stats.draftsCount > 0 ? 'text-amber-400' : 'text-white/60'"
        >
          {{ stats.draftsCount }}
        </span>
        <span class="text-xs text-white/40">awaiting review</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span 
          class="h-1.5 w-1.5 rounded-full" 
          :class="stats.draftsCount > 0 ? 'bg-amber-400 animate-pulse' : 'bg-white/20'"
        ></span>
        <span>{{ stats.draftsCount > 0 ? 'Unpublished drafts pending' : 'Queue fully cleared' }}</span>
      </div>
    </div>

    <!-- Card 3: Published Live -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Published Live</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <GlobeAltIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.publishedCount }}</span>
        <span class="text-xs text-emerald-400/70 font-medium">visible online</span>
      </div>
      <div class="mt-3 flex items-center gap-2 text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
        <span>Accessible on public website</span>
      </div>
    </div>

    <!-- Card 4: Reader Engagement & Authors -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-rose-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-rose-500/5 blur-2xl group-hover:bg-rose-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Engagement</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-500/15 text-rose-400 border border-rose-500/30">
          <HeartIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-white tracking-tight">{{ stats.totalReactions }}</span>
        <span class="text-xs text-rose-400/80">reactions</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span>Contributing authors</span>
        <span class="font-medium text-white/80 font-mono">{{ stats.authorsCount }}</span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { 
  DocumentTextIcon, 
  ClockIcon, 
  GlobeAltIcon, 
  HeartIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      totalCount: 0,
      publishedCount: 0,
      draftsCount: 0,
      totalReactions: 0,
      authorsCount: 0,
      publishRate: 0,
    })
  }
});
</script>
