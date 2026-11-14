<template>
  <article 
    class="group relative rounded-2xl border border-slate-200/80 dark:border-white/[0.08] bg-slate-50 dark:bg-[#1E232B]/50 p-5 transition-all duration-300 hover:border-amber-400/40 dark:hover:border-white/20 hover:bg-slate-100 dark:hover:bg-[#1E232B] shadow-sm"
    :style="{ animationDelay: `${index * 80}ms` }"
  >
    <!-- Decorative quote mark -->
    <div class="absolute right-4 top-3 font-display text-5xl text-[var(--color-accent)]/[0.08] leading-none select-none pointer-events-none">"</div>
    
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
      <div class="flex items-center gap-3">
        <div class="h-9 w-9 rounded-full bg-[var(--color-accent)] flex items-center justify-center text-white font-bold text-xs shrink-0 shadow-sm">
          {{ item.barber_name ? item.barber_name.charAt(0).toUpperCase() : 'B' }}
        </div>
        <div>
          <p class="text-slate-900 dark:text-white font-semibold text-sm group-hover:text-[var(--color-accent)] transition-colors">{{ item.barber_name || item.barber?.name || 'Assigned Barber' }}</p>
          <div class="flex items-center gap-2 mt-0.5 flex-wrap">
            <span v-if="item.service?.name || item.service_name" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)] text-[11px] font-semibold">
              <ScissorsIcon class="h-3 w-3" />
              {{ item.service?.name || item.service_name }}
            </span>
            <span v-if="item.service?.name || item.service_name" class="text-slate-300 dark:text-white/20 text-xs">•</span>
            <p class="text-[var(--color-text-secondary)] text-xs flex items-center gap-1">
              <CalendarIcon class="h-3 w-3 text-[var(--color-accent)]" />
              {{ formatDate(item.created_at) }}
            </p>
          </div>
        </div>
      </div>
      <span 
        class="text-[10px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-full border" 
        :class="item.is_approved 
          ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30' 
          : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30'"
      >
        <span class="flex items-center gap-1.5">
          <span class="h-1.5 w-1.5 rounded-full" :class="item.is_approved ? 'bg-emerald-500 dark:bg-emerald-400' : 'bg-amber-500 dark:bg-amber-400 animate-pulse'"></span>
          {{ item.is_approved ? 'Published' : 'Pending Review' }}
        </span>
      </span>
    </div>

    <!-- Star rating display -->
    <div class="flex items-center gap-1 mb-2.5 pl-12">
      <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5 transition-colors" :class="i <= item.rating ? 'text-[var(--color-accent)] drop-shadow-[0_0_3px_rgba(255,103,0,0.4)]' : 'text-slate-300 dark:text-white/20'">
        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
      </svg>
      <span class="ml-1.5 text-xs text-slate-500 dark:text-white/50 font-medium">{{ item.rating }}/5</span>
    </div>

    <!-- Review text -->
    <div class="pl-12">
      <p class="text-xs text-[var(--color-text-secondary)] leading-relaxed italic border-l-2 border-[var(--color-accent)]/30 pl-3 py-1">
        "{{ item.review || item.comment }}"
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
