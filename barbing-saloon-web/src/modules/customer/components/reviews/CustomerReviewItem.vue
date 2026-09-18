<template>
  <article 
    class="group relative rounded-xl border border-theme-border bg-theme-bg/50 p-5 transition-all duration-300 hover:border-gold/30 hover:bg-theme-surface/50 hover:shadow-[0_8px_30px_-8px_rgba(255,153,0,0.08)]"
    :style="{ animationDelay: `${index * 80}ms` }"
  >
    <!-- Decorative quote mark -->
    <div class="absolute right-4 top-3 font-display text-5xl text-gold/[0.06] leading-none select-none pointer-events-none">"</div>
    
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
      <div class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-gold to-amber-600 flex items-center justify-center text-obsidian font-bold text-sm shrink-0 shadow-[0_0_12px_rgba(255,153,0,0.2)] group-hover:shadow-[0_0_18px_rgba(255,153,0,0.3)] transition-shadow">
          {{ item.barber_name ? item.barber_name.charAt(0).toUpperCase() : '?' }}
        </div>
        <div>
          <p class="text-theme-text font-semibold text-sm group-hover:text-gold transition-colors">{{ item.barber_name || 'Unknown Barber' }}</p>
          <div class="flex items-center gap-2 mt-0.5">
            <p v-if="item.service_name" class="text-gold/70 text-xs font-medium flex items-center gap-1">
              <ScissorsIcon class="h-3 w-3" />
              {{ item.service_name }}
            </p>
            <span v-if="item.service_name" class="text-theme-border text-xs">•</span>
            <p class="text-theme-muted text-xs flex items-center gap-1">
              <CalendarIcon class="h-3 w-3" />
              {{ formatDate(item.created_at) }}
            </p>
          </div>
        </div>
      </div>
      <span 
        class="text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full border" 
        :class="item.is_approved 
          ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' 
          : 'bg-amber-500/10 text-amber-400 border-amber-500/20'"
      >
        <span class="flex items-center gap-1">
          <span class="h-1.5 w-1.5 rounded-full" :class="item.is_approved ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse'"></span>
          {{ item.is_approved ? 'Approved' : 'Pending' }}
        </span>
      </span>
    </div>

    <!-- Star rating display -->
    <div class="flex items-center gap-1 mb-3 pl-[52px]">
      <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 transition-colors" :class="i <= item.rating ? 'text-gold drop-shadow-[0_0_3px_rgba(255,153,0,0.4)]' : 'text-white/10'">
        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
      </svg>
      <span class="ml-1.5 text-xs text-theme-muted font-medium">{{ item.rating }}/5</span>
    </div>

    <!-- Review text -->
    <div class="pl-[52px]">
      <p class="text-sm text-theme-muted leading-relaxed italic border-l-2 border-gold/20 pl-3 py-1">
        "{{ item.comment }}"
      </p>
    </div>
  </article>
</template>

<script setup>
import { CalendarIcon } from '@heroicons/vue/24/outline'
import { ScissorsIcon } from '@heroicons/vue/24/solid'

defineProps({
  item: { type: Object, required: true },
  index: { type: Number, default: 0 },
  formatDate: { type: Function, required: true }
})
</script>
