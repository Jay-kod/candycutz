<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 mb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.15)]">
          <ScissorsIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Catalog Integration Bridge</h2>
          <p class="text-xs text-white/50 mt-0.5">Individual services, pricing, duration, and cover photos are managed via the dedicated Services Manager.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 02
      </span>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 items-center">
      <!-- Left 2 Cols: Explanatory & Feature Cards -->
      <div class="lg:col-span-2 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 space-y-1">
            <span class="text-[10px] uppercase font-bold text-white/40 tracking-wider">Total Catalog</span>
            <p class="font-display text-2xl font-bold text-white">{{ services.length || '12+' }}</p>
            <p class="text-[11px] text-emerald-400">Services Active</p>
          </div>
          <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 space-y-1">
            <span class="text-[10px] uppercase font-bold text-white/40 tracking-wider">Categories</span>
            <p class="font-display text-2xl font-bold text-white">{{ categories.length || '3' }}</p>
            <p class="text-[11px] text-amber-400">Filter Groupings</p>
          </div>
          <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 space-y-1">
            <span class="text-[10px] uppercase font-bold text-white/40 tracking-wider">Home Service</span>
            <p class="font-display text-2xl font-bold text-white">Eligible</p>
            <p class="text-[11px] text-sky-400">Configured Per Service</p>
          </div>
        </div>

        <p class="text-xs text-white/50 leading-relaxed">
          The public Services page automatically renders the live service cards and filters using the items configured in your catalog. You can edit names, prices in Naira (₦), duration in minutes, and service images anytime.
        </p>
      </div>

      <!-- Right 1 Col: Big Action Box -->
      <div class="rounded-2xl border border-admin/30 bg-admin/5 p-6 text-center space-y-3">
        <div class="w-12 h-12 rounded-xl bg-admin/20 text-admin flex items-center justify-center mx-auto">
          <ScissorsIcon class="w-6 h-6" />
        </div>
        <div>
          <h4 class="font-display text-base font-bold text-white">Need to edit cuts or prices?</h4>
          <p class="text-[11px] text-white/50 mt-1">Jump to the full Services Catalog Manager.</p>
        </div>
        <router-link
          to="/admin/services"
          class="inline-flex items-center justify-center gap-2 w-full rounded-xl bg-gradient-to-r from-admin to-admin-light px-4 py-2.5 text-xs font-bold text-obsidian shadow-lg hover:shadow-[0_4px_20px_rgba(255,103,0,0.4)] transition-all"
        >
          <span>Open Catalog Manager</span>
          <ArrowTopRightOnSquareIcon class="h-4 w-4" />
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  ScissorsIcon, 
  ArrowTopRightOnSquareIcon 
} from '@heroicons/vue/24/outline';

defineProps({
  services: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  }
});
</script>
