<template>
  <div class="lg:sticky lg:top-8 space-y-6">
    <div class="flex items-center gap-2 px-2">
      <EyeIcon class="h-4 w-4 text-white/30" />
      <h3 class="text-[10px] uppercase tracking-[0.2em] font-bold text-white/30">Live Service Preview</h3>
    </div>

    <div class="group relative overflow-hidden rounded-[2.5rem] border border-white/[0.08] bg-gradient-to-br from-[#1a1a1a] to-[#111] shadow-[0_20px_60px_rgba(0,0,0,0.6)]">
      <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10 mix-blend-overlay"></div>
      <!-- Glow accent -->
      <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-admin to-amber-400 z-20"></div>

      <!-- Image Gallery Preview -->
      <div v-if="hasAnyImage" class="relative w-full h-48 bg-black/40 overflow-hidden">
        <div class="absolute inset-0 flex transition-transform duration-500" :style="{ transform: `translateX(-${activePreviewIndex * 100}%)` }">
          <img v-for="(img, idx) in previewImages" :key="idx" :src="img" class="w-full h-full object-cover shrink-0" />
        </div>
        
        <div v-if="previewImages.length > 1" class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5 z-10">
          <button 
            v-for="(_, idx) in previewImages" 
            :key="idx" 
            @click="$emit('update:activePreviewIndex', idx)"
            class="h-1.5 rounded-full transition-all duration-300"
            :class="activePreviewIndex === idx ? 'w-4 bg-admin' : 'w-1.5 bg-white/40 hover:bg-white/70'"
          ></button>
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#1a1a1a] via-transparent to-transparent opacity-80"></div>
      </div>

      <div class="p-8 relative z-10 flex flex-col" :class="{'pt-4': hasAnyImage}">
        <!-- Category badge -->
        <div class="mb-5">
          <span class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest border border-admin/20 bg-admin/10 text-admin/90 shadow-lg">
            <TagIcon class="h-3 w-3" />
            {{ selectedCategoryName || 'General' }}
          </span>
        </div>

        <!-- Name -->
        <h2 class="font-display text-3xl text-white leading-tight mb-3 drop-shadow-md">
          {{ form.name || 'Service Name' }}
        </h2>

        <!-- Description -->
        <p class="text-sm text-white/50 leading-relaxed mb-6">
          {{ form.description || 'This service doesn\'t have a description yet.' }}
        </p>

        <!-- Availability -->
        <div class="mb-6">
          <span
            class="rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-wider border"
            :class="form.is_available
              ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
              : 'bg-red-500/10 text-red-400 border-red-500/20'"
          >
            {{ form.is_available ? '● Available for Booking' : '● Currently Unavailable' }}
          </span>
        </div>

        <!-- Price & Duration -->
        <div class="pt-6 flex items-center justify-between border-t border-white/[0.05]">
          <div class="flex items-baseline gap-1 text-emerald-400 font-display drop-shadow-md">
            <span class="text-sm opacity-60">₦</span>
            <span class="text-3xl font-bold">{{ form.price ? Number(form.price).toLocaleString() : '0' }}</span>
          </div>
          <div class="flex items-center gap-1.5 text-xs font-bold text-white/50 bg-white/[0.03] px-3 py-2 rounded-xl border border-white/[0.05]">
            <ClockIcon class="h-4 w-4 text-admin/60" />
            {{ form.duration_minutes || 0 }} mins
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { EyeIcon, TagIcon, ClockIcon } from '@heroicons/vue/24/outline';

defineProps({
  form: { type: Object, required: true },
  hasAnyImage: { type: Boolean, required: true },
  previewImages: { type: Array, required: true },
  activePreviewIndex: { type: Number, required: true },
  selectedCategoryName: { type: String, required: true }
});

defineEmits(['update:activePreviewIndex']);
</script>
