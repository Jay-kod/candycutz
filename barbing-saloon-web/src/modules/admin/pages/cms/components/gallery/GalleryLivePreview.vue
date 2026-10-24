<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] shadow-2xl">
    <!-- Preview Toolbar -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.08] bg-[#12161C]/90 backdrop-blur-md">
      <div class="flex items-center gap-3">
        <span class="flex h-3 w-3 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-xs font-mono font-bold uppercase tracking-wider text-white/80">Interactive Gallery Page Mockup Preview</span>
        <span class="hidden sm:inline-flex text-[10px] text-white/40 font-mono">(Live Simulation)</span>
      </div>
      <button 
        @click="$emit('exit')" 
        class="text-xs font-bold px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/10 text-white/70 hover:text-white hover:bg-white/10 transition-all"
      >
        Exit Preview Mode
      </button>
    </div>

    <div class="p-4 sm:p-8 space-y-12">
      <!-- 1. Simulated Page Hero -->
      <div class="relative overflow-hidden rounded-3xl border border-white/10 shadow-inner">
        <PageHero
          page-key="gallery"
          :fallback-title="content.gallery_title || 'Our Gallery'"
          :fallback-subtitle="content.gallery_subtitle || 'Browse our portfolio of premium cuts and styles.'"
          :background-image="previewImage"
          :overlay-opacity="heroOpacity"
          :load-settings="false"
        />
      </div>

      <!-- 2. Simulated Category Filter Tabs -->
      <div class="flex items-center justify-center flex-wrap gap-2.5">
        <button
          type="button"
          class="px-5 py-2 rounded-full text-xs font-semibold bg-amber-400 text-obsidian shadow-md cursor-default pointer-events-none"
        >
          All (3)
        </button>
        <button
          v-for="cat in ['Skin Fades', 'Beard Detailing', 'Designs']"
          :key="cat"
          type="button"
          class="px-5 py-2 rounded-full text-xs font-semibold bg-[#12161C] text-white/60 border border-white/10 cursor-default pointer-events-none"
        >
          {{ cat }}
        </button>
      </div>

      <!-- 3. Simulated Gallery Masonry Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <article
          v-for="(card, idx) in sampleCards"
          :key="idx"
          class="overflow-hidden rounded-2xl border border-gold/25 bg-[#12161C] relative group shadow-xl"
        >
          <div class="aspect-[4/5] bg-black/50 relative flex items-center justify-center">
            <span class="text-white/20 text-4xl font-display">✂</span>
            <div class="absolute inset-0 bg-gradient-to-t from-obsidian via-obsidian/40 to-transparent"></div>
          </div>

          <div class="absolute inset-0 p-4 flex flex-col justify-between text-white">
            <div class="flex items-center justify-between">
              <span class="text-[9px] uppercase tracking-wider text-amber-300 font-bold bg-black/60 px-2 py-0.5 rounded-full border border-white/10">
                {{ card.category }}
              </span>
              <span class="text-[9px] text-white/80 bg-black/60 px-2 py-0.5 rounded-full border border-white/10">
                {{ card.barber }}
              </span>
            </div>

            <div>
              <h4 class="font-display text-base font-bold text-white">{{ card.title }}</h4>
              <p class="text-[11px] text-white/60 line-clamp-1 mt-0.5">{{ card.desc }}</p>
            </div>
          </div>
        </article>
      </div>
    </div>
  </div>
</template>

<script setup>
import PageHero from '@/shared/ui/PageHero.vue';

defineProps({
  content: {
    type: Object,
    required: true
  },
  previewImage: {
    type: String,
    default: ''
  },
  heroOpacity: {
    type: Number,
    default: 76
  }
});

defineEmits(['exit']);

const sampleCards = [
  {
    title: 'High Skin Drop Fade',
    category: 'Skin Fades',
    barber: 'Master John',
    desc: 'Seamless bald fade with crisp razor edge finish.'
  },
  {
    title: 'Executive Beard Sculpt',
    category: 'Beard Detailing',
    barber: 'Stylist David',
    desc: 'Herbal steam moisture lock with straight razor outline.'
  },
  {
    title: 'Freestyle Hair Design',
    category: 'Designs',
    barber: 'Stylist Michael',
    desc: 'Custom artistic wave contour detailing on low taper.'
  }
];
</script>

