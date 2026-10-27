<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 overflow-hidden backdrop-blur-xl shadow-xl space-y-6">
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
          <span class="text-xs font-bold text-white">Live Storefront Document Preview</span>
        </div>
      </div>

      <span class="px-2.5 py-0.5 text-[10px] font-mono uppercase tracking-wider rounded-md bg-[#FF6700]/10 text-[#FF6700] border border-[#FF6700]/20 font-bold">
        Formatted Document
      </span>
    </div>

    <!-- Formal Document Sheet -->
    <div class="p-6 sm:p-10 max-w-4xl mx-auto space-y-8">
      <!-- Document Header -->
      <div class="border-b border-white/10 pb-6 text-center space-y-2">
        <span class="text-[11px] font-bold uppercase tracking-widest text-[#FF6700]">
          CandyCutz Official Document
        </span>
        <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-white">
          {{ title || 'Legal Document' }}
        </h1>
        <p class="text-xs text-white/40 font-mono">
          Effective as of: {{ currentDate }} • Version 1.0
        </p>
      </div>

      <!-- Document Sections -->
      <div class="space-y-8 divide-y divide-white/[0.06]">
        <div
          v-for="(section, idx) in sections"
          :key="idx"
          class="pt-6 first:pt-0 space-y-3"
        >
          <div class="flex items-baseline gap-2.5">
            <span class="text-xs font-mono font-bold text-[#FF6700]">
              {{ idx + 1 }}.0
            </span>
            <h2 class="text-sm sm:text-base font-bold text-white tracking-wide">
              {{ section.heading || 'Untitled Clause' }}
            </h2>
          </div>

          <div class="text-xs text-white/70 leading-relaxed font-sans pl-5 border-l-2 border-white/[0.08] whitespace-pre-line space-y-2">
            {{ section.body || 'Clause content will appear here once entered.' }}
          </div>
        </div>
      </div>

      <!-- Formal Seal / Disclosures -->
      <div class="pt-8 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-[11px] text-white/40">
        <p>&copy; {{ new Date().getFullYear() }} CandyCutz Saloon &amp; Grooming. All rights reserved.</p>
        <div class="flex items-center gap-2">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
          <span>Official Verified Storefront Policy</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { EyeIcon } from '@heroicons/vue/24/outline';

defineProps({
  title: {
    type: String,
    required: true
  },
  sections: {
    type: Array,
    required: true
  }
});

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
});
</script>

