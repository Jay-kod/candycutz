<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 overflow-hidden backdrop-blur-xl shadow-xl">
    <!-- Preview Window Top Bar -->
    <div class="border-b border-white/[0.06] bg-[#0B0F13]/80 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="flex gap-1.5">
          <span class="h-2.5 w-2.5 rounded-full bg-rose-500/70"></span>
          <span class="h-2.5 w-2.5 rounded-full bg-amber-500/70"></span>
          <span class="h-2.5 w-2.5 rounded-full bg-emerald-500/70"></span>
        </div>
        <div class="h-4 w-px bg-white/10 mx-1"></div>
        <div class="flex items-center gap-2">
          <EyeIcon class="h-4 w-4 text-[#FF6700]" />
          <span class="text-xs font-bold text-white">Live Storefront Preview</span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-[10px] font-mono text-white/40 hidden sm:inline">Component Preview</span>
        <span class="px-2.5 py-0.5 text-[10px] font-mono uppercase tracking-wider rounded-md bg-[#FF6700]/10 text-[#FF6700] border border-[#FF6700]/20 font-bold">
          @efferd/features-1
        </span>
      </div>
    </div>

    <!-- Live Render Area -->
    <div class="bg-[#0B0F13] p-2 sm:p-4 overflow-x-hidden">
      <FeaturesSection
        :badge="content.features_badge"
        :title="content.features_title"
        :subtitle="content.features_subtitle"
        :features="items"
      />
    </div>
  </div>
</template>

<script setup>
import { EyeIcon } from '@heroicons/vue/24/outline';
import FeaturesSection from '@/components/FeaturesSection.vue';

defineProps({
  content: {
    type: Object,
    required: true
  },
  items: {
    type: Array,
    required: true
  }
});
</script>
