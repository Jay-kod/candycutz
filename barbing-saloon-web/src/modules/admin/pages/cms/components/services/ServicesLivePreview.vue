<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0B0F13] shadow-2xl">
    <!-- Preview Toolbar -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.08] bg-[#12161C]/90 backdrop-blur-md">
      <div class="flex items-center gap-3">
        <span class="flex h-3 w-3 rounded-full bg-emerald-400 animate-pulse"></span>
        <span class="text-xs font-mono font-bold uppercase tracking-wider text-white/80">Interactive Services Page Mockup Preview</span>
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
        <p class="text-xs uppercase tracking-[0.3em] text-amber-400 font-bold mb-2">Grooming Menu</p>
        <h1 class="font-display text-3xl sm:text-5xl text-white font-bold tracking-tight">
          {{ content.services_title || 'Our Premium Services' }}
        </h1>
        <p class="mt-3 text-xs sm:text-sm text-white/60 max-w-xl mx-auto leading-relaxed">
          {{ content.services_subtitle || 'Explore our full range of grooming services designed to keep you looking sharp and feeling confident.' }}
        </p>
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
          v-for="cat in ['Haircuts', 'Beard Care', 'VIP Combo']"
          :key="cat"
          type="button"
          class="px-5 py-2 rounded-full text-xs font-semibold bg-[#12161C] text-white/60 border border-white/10 cursor-default pointer-events-none"
        >
          {{ cat }}
        </button>
      </div>

      <!-- 3. Simulated Service Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <article
          v-for="(card, idx) in sampleCards"
          :key="idx"
          class="overflow-hidden rounded-[1.5rem] border border-gold/25 bg-[#160e08]/90 p-5 space-y-4 shadow-xl text-left"
        >
          <div class="flex items-start justify-between gap-2">
            <span class="rounded-full border border-gold/40 bg-black/45 px-2.5 py-0.5 text-[9px] font-bold uppercase text-gold">
              {{ card.category }}
            </span>
            <span class="rounded-full border border-white/10 bg-black/40 px-2 py-0.5 text-[10px] text-white/70 font-mono">
              {{ card.duration }}
            </span>
          </div>

          <div class="aspect-video rounded-xl bg-black/50 border border-white/5 relative overflow-hidden flex items-center justify-center">
            <span class="text-white/20 text-3xl font-display">✂</span>
          </div>

          <div class="space-y-1">
            <h4 class="font-display text-lg font-bold text-white">{{ card.name }}</h4>
            <p class="text-xs text-white/60 line-clamp-2">{{ card.desc }}</p>
          </div>

          <div class="flex items-center justify-between pt-3 border-t border-gold/20">
            <div>
              <span class="block text-[9px] uppercase tracking-wider text-gold/60 font-bold">Price</span>
              <span class="font-display text-xl font-bold text-gold">{{ card.price }}</span>
            </div>
            <span class="rounded-xl bg-gradient-to-r from-admin to-admin-light px-3.5 py-1.5 text-xs font-bold text-obsidian pointer-events-none">
              Book Cut
            </span>
          </div>
        </article>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  content: {
    type: Object,
    required: true
  }
});

defineEmits(['exit']);

const sampleCards = [
  {
    name: 'Executive Fade & Shape-Up',
    category: 'Haircuts',
    duration: '35 min',
    price: '₦8,500',
    desc: 'Precision skin fade with razor line definition, cold towel finish, and signature aftershave splash.'
  },
  {
    name: 'Royal Beard Sculpt & Steam',
    category: 'Beard Care',
    duration: '25 min',
    price: '₦5,000',
    desc: 'Hot towel herbal steam treatment, beard balm hydration, and foil razor edge alignment.'
  },
  {
    name: 'Full VIP Grooming Session',
    category: 'VIP Combo',
    duration: '60 min',
    price: '₦15,000',
    desc: 'Complete haircut, beard sculpting, refreshing facial cleanser scrub, and scalp massage.'
  }
];
</script>
