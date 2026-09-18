<template>
  <div>
    <div class="flex items-center justify-between mb-8">
      <h2 class="font-display text-2xl md:text-3xl text-theme-text flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold text-obsidian text-sm font-bold">1</span>
        Date &amp; <span class="text-gold">Time</span>
      </h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
      <div>
        <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50 ml-2 mb-2 block">Select Date</label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gold/50 group-hover:text-gold transition-colors">
            <CalendarDaysIcon class="h-6 w-6" />
          </div>
          <input 
            :value="appointmentDate" 
            type="date" 
            :min="todayStr"
            class="w-full rounded-2xl border border-theme-border bg-theme-bg/80 pl-14 pr-6 py-4 text-base text-theme-text outline-none transition-all focus:border-gold focus:bg-theme-bg focus:ring-4 focus:ring-gold/10 hover:border-gold/30 [color-scheme:dark]" 
            @input="$emit('update:appointmentDate', $event.target.value)"
            @change="$emit('dateChange')" 
          />
        </div>
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50 ml-2 mb-2 block flex justify-between">
          <span>Select Time</span>
          <span v-if="loadingSlots" class="text-gold italic normal-case flex items-center gap-1">
            <ArrowPathIcon class="w-3 h-3 animate-spin" /> checking...
          </span>
        </label>
        
        <div v-if="slots.length > 0" class="grid grid-cols-3 gap-2">
          <button 
            v-for="slot in slots" :key="slot"
            type="button"
            @click="$emit('update:appointmentTime', slot)"
            class="rounded-xl py-3 text-sm font-bold transition-all duration-200 border"
            :class="appointmentTime === slot ? 'bg-gold text-obsidian border-gold shadow-[0_0_15px_rgba(212,175,55,0.3)] scale-105 z-10' : 'bg-theme-bg/50 border-theme-border text-theme-text hover:border-gold/40 hover:bg-theme-surface'"
          >
            {{ slot }}
          </button>
        </div>
        
        <div v-else class="h-[58px] rounded-2xl border border-dashed border-theme-border flex items-center justify-center text-sm text-ivory/40 italic bg-theme-bg/30">
          <span v-if="appointmentDate">No slots available</span>
          <span v-else>Pick a date first</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { CalendarDaysIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'

defineProps({
  appointmentDate: { type: String, default: '' },
  appointmentTime: { type: String, default: '' },
  todayStr: { type: String, required: true },
  slots: { type: Array, default: () => [] },
  loadingSlots: { type: Boolean, default: false }
})

defineEmits(['update:appointmentDate', 'update:appointmentTime', 'dateChange'])
</script>
