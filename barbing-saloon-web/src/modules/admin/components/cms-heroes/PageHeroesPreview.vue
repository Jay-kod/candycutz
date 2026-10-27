<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 overflow-hidden backdrop-blur-xl shadow-xl flex flex-col justify-between">
    <!-- Window Bar -->
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
          <span class="text-xs font-bold text-white">40vh Hero Simulation</span>
        </div>
      </div>

      <span class="px-2.5 py-0.5 text-[10px] font-mono uppercase tracking-wider rounded-md bg-white/[0.05] text-white/60 border border-white/10 font-bold">
        {{ pageLabel }}
      </span>
    </div>

    <!-- Live Page Hero Viewport -->
    <div class="relative overflow-hidden rounded-b-2xl border-t border-white/[0.04]">
      <PageHero
        :key="selectedPage"
        :page-key="selectedPage"
        :eyebrow="pageLabel"
        :fallback-title="form.title || defaultTitle"
        :fallback-subtitle="form.subtitle || defaultSubtitle"
        :background-image="previewImage"
        :overlay-opacity="form.opacity"
        :load-settings="false"
      />
    </div>

    <!-- Footnote -->
    <div class="p-4 border-t border-white/[0.06] bg-[#0B0F13]/40 text-[11px] text-white/40 flex items-center justify-between">
      <span>Native 40vh Hero Section View</span>
      <span class="font-mono text-[#FF6700]">{{ form.opacity }}% Contrast Overlay</span>
    </div>
  </div>
</template>

<script setup>
import { EyeIcon } from '@heroicons/vue/24/outline';
import PageHero from '@/shared/ui/PageHero.vue';

defineProps({
  selectedPage: {
    type: String,
    required: true
  },
  pageLabel: {
    type: String,
    required: true
  },
  defaultTitle: {
    type: String,
    default: 'Precision Cuts'
  },
  defaultSubtitle: {
    type: String,
    default: ''
  },
  form: {
    type: Object,
    required: true
  },
  previewImage: {
    type: String,
    default: ''
  }
});
</script>

