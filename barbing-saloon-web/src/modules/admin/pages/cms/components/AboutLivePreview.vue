<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] shadow-2xl">
    <!-- Preview Toolbar -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.08] bg-[#12161C]/90 backdrop-blur-md">
      <div class="flex items-center gap-3">
        <span class="flex h-3 w-3 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-xs font-mono font-bold uppercase tracking-wider text-white/80">Interactive About Page Mockup Preview</span>
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
      <div class="relative overflow-hidden rounded-3xl border border-white/10 p-8 sm:p-12 text-center bg-gradient-to-b from-[#181D24] to-[#0B0F13] shadow-inner">
        <p class="text-xs uppercase tracking-[0.3em] text-amber-400 font-bold mb-2">Our Story</p>
        <h1 class="font-display text-3xl sm:text-5xl text-white font-bold tracking-tight">About CandyCutz</h1>
        <p class="mt-3 text-xs sm:text-sm text-white/60 max-w-lg mx-auto">Our story, our craft, and the standard behind every cut.</p>
      </div>

      <!-- 2. Simulated Shop Section -->
      <div class="rounded-3xl border border-white/[0.06] bg-[#12161C] p-6 sm:p-10 grid lg:grid-cols-2 gap-8 items-center">
        <div class="space-y-4">
          <p class="text-xs uppercase tracking-[0.3em] text-amber-300 font-bold">
            {{ content.about_shop_title || 'About Us' }}
          </p>
          <h2 class="font-display text-2xl sm:text-4xl text-gold font-bold tracking-tight leading-tight">
            {{ content.about_shop_subtitle || 'A Sharper Standard' }}
          </h2>
          <p class="text-xs sm:text-sm text-white/70 leading-relaxed whitespace-pre-wrap">
            {{ content.about_shop_text_1 || "Welcome to CandyCutz, where precision meets style. We've been providing premium grooming services to those who appreciate a quality cut." }}
          </p>
          <p class="text-xs sm:text-sm text-white/70 leading-relaxed whitespace-pre-wrap">
            {{ content.about_shop_text_2 || "Whether you're here for a quick shape-up or a complete restyle, our expert barbers are dedicated to making you look and feel your best." }}
          </p>
        </div>

        <div class="relative overflow-hidden rounded-2xl aspect-[4/3] border border-white/10 shadow-xl bg-black/40">
          <img 
            :src="shopImageSrc" 
            alt="CandyCutz Shop Interior" 
            class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" 
          />
        </div>
      </div>

      <!-- 3. Simulated Barbers Team Section -->
      <div class="space-y-6 text-center">
        <div class="space-y-2">
          <h2 class="font-display text-2xl sm:text-4xl text-gold font-bold tracking-tight">
            {{ content.about_team_title || 'Meet the team' }}
          </h2>
          <p class="text-xs sm:text-sm text-white/60 max-w-xl mx-auto leading-relaxed whitespace-pre-wrap">
            {{ content.about_team_subtitle || 'Our barbers are masters of their craft, bringing years of experience and a passion for perfection to every cut.' }}
          </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-4xl mx-auto pt-4">
          <div v-for="i in 3" :key="i" class="overflow-hidden rounded-2xl border border-gold/30 bg-[#12161C] shadow-xl text-left">
            <div class="aspect-[0.9] bg-[#0B0F13] relative flex items-center justify-center border-b border-white/10">
              <span class="text-white/20 text-4xl font-display">★</span>
              <span class="absolute top-3 left-3 px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-obsidian/80 text-amber-300 border border-amber-400/30">
                Master Barber
              </span>
            </div>
            <div class="p-4 space-y-2">
              <p class="text-xs font-bold text-white">Stylist Specialist {{ i }}</p>
              <p class="text-[11px] text-white/40">Skin Fades • Beard Sculpting</p>
              <div class="pt-2">
                <span class="inline-flex w-full justify-center items-center rounded-lg border border-gold/40 bg-gold/10 px-3 py-1.5 text-xs font-bold text-gold pointer-events-none">
                  View Profile & Book
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  content: {
    type: Object,
    required: true
  },
  previewShopUrl: {
    type: String,
    default: null
  },
  currentShopImage: {
    type: String,
    default: null
  },
  getStorageUrl: {
    type: Function,
    required: true
  }
});

defineEmits(['exit']);

const shopImageSrc = computed(() => {
  if (props.previewShopUrl) return props.previewShopUrl;
  if (props.currentShopImage) return props.getStorageUrl(props.currentShopImage);
  return 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80';
});
</script>
