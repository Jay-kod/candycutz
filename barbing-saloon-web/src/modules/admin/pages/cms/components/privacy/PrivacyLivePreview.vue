<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] shadow-2xl">
    <!-- Preview Toolbar -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.08] bg-[#12161C]/90 backdrop-blur-md">
      <div class="flex items-center gap-3">
        <span class="flex h-3 w-3 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-xs font-mono font-bold uppercase tracking-wider text-white/80">Interactive Privacy Policy Simulation</span>
        <span class="hidden sm:inline-flex text-[10px] text-white/40 font-mono">(Live Public Rendering)</span>
      </div>
      <button 
        @click="$emit('exit')" 
        class="text-xs font-bold px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/10 text-white/70 hover:text-white hover:bg-white/10 transition-all"
      >
        Exit Preview Mode
      </button>
    </div>

    <div class="p-4 sm:p-8 space-y-10">
      <!-- 1. Simulated Page Hero -->
      <div class="relative overflow-hidden rounded-3xl border border-white/10 p-8 sm:p-12 text-center bg-gradient-to-b from-[#181D24] to-[#0B0F13] shadow-inner">
        <div 
          v-if="previewHeroImage"
          class="absolute inset-0 bg-cover bg-center"
          :style="{ 
            backgroundImage: `url(${previewHeroImage})`,
            opacity: (100 - heroOpacity) / 100
          }"
        ></div>
        <div class="relative z-10 space-y-3">
          <p class="text-xs uppercase tracking-[0.3em] text-amber-400 font-bold">Legal Notice</p>
          <h1 class="font-display text-3xl sm:text-5xl text-white font-bold tracking-tight">
            {{ content.privacy_title || 'Privacy Policy' }}
          </h1>
          <p class="text-xs sm:text-sm text-white/60 max-w-xl mx-auto leading-relaxed">
            {{ content.privacy_subtitle || 'How we collect, use, and protect your information.' }}
          </p>
        </div>
      </div>

      <!-- 2. Two-Column Layout (Table of Contents + Policy Document) -->
      <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8 max-w-6xl mx-auto">
        <!-- Left: Table of Contents -->
        <aside class="space-y-4">
          <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 space-y-3 sticky top-6">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-2">
              <DocumentTextIcon class="h-4 w-4" />
              Table of Contents
            </span>
            <nav class="space-y-1 text-xs">
              <a 
                v-for="(section, idx) in sections" 
                :key="idx" 
                :href="`#section-${idx}`"
                class="block py-1.5 px-2 rounded-lg text-white/60 hover:text-white hover:bg-white/5 transition-all truncate"
              >
                {{ section.heading || `Clause ${idx + 1}` }}
              </a>
            </nav>
            <div class="pt-3 border-t border-white/[0.04] text-[11px] text-white/40">
              Last updated: {{ currentDate }}
            </div>
          </div>
        </aside>

        <!-- Right: Legal Document Body -->
        <main class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 sm:p-10 space-y-8 shadow-xl">
          <div class="border-b border-white/[0.06] pb-4 flex items-center justify-between text-xs text-white/40">
            <span>CandyCutz Privacy & Data Protection Codex</span>
            <span>{{ sections.length }} Clauses</span>
          </div>

          <div class="space-y-10">
            <article 
              v-for="(section, index) in sections" 
              :key="index"
              :id="`section-${index}`"
              class="space-y-3 scroll-mt-24 pb-8 border-b border-white/[0.04] last:border-b-0"
            >
              <h3 class="font-display text-xl sm:text-2xl text-amber-400 font-bold tracking-tight">
                {{ section.heading || `Clause ${index + 1}` }}
              </h3>
              <p class="text-xs sm:text-sm text-white/80 whitespace-pre-wrap leading-relaxed font-sans">
                {{ section.body || 'No terms provided for this clause.' }}
              </p>
            </article>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { DocumentTextIcon } from '@heroicons/vue/24/outline';

defineProps({
  content: {
    type: Object,
    required: true
  },
  sections: {
    type: Array,
    required: true
  },
  previewHeroImage: {
    type: String,
    default: ''
  },
  heroOpacity: {
    type: Number,
    default: 76
  }
});

defineEmits(['exit']);

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  });
});
</script>

