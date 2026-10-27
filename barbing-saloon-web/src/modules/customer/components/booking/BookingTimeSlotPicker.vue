<template>
  <div>
    <div class="flex items-center justify-between mb-8">
      <h2 class="font-display text-2xl md:text-3xl text-[var(--color-text-primary)] flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-sm font-bold">1</span>
        Date &amp; <span class="text-[var(--color-accent)]">Time</span>
      </h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
      <div>
        <label class="text-xs font-semibold uppercase tracking-widest text-[var(--color-text-secondary)] ml-2 mb-2 block">Select Date</label>
        <div class="relative group">
          <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-[var(--color-accent)]/60 group-hover:text-[var(--color-accent)] transition-colors">
            <CalendarDaysIcon class="h-6 w-6" />
          </div>
          <input 
            :value="appointmentDate" 
            type="date" 
            :min="todayStr"
            class="w-full rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-input)] pl-14 pr-6 py-4 text-base text-[var(--color-text-primary)] outline-none transition-all focus:border-[var(--color-accent)] focus:ring-4 focus:ring-[var(--color-accent)]/10 hover:border-[var(--color-accent)]/40" 
            @input="$emit('update:appointmentDate', $event.target.value)"
            @change="$emit('dateChange')" 
          />
        </div>
      </div>

      <div>
        <label class="text-xs font-semibold uppercase tracking-widest text-[var(--color-text-secondary)] ml-2 mb-2 flex justify-between">
          <span>Select Time</span>
          <span v-if="loadingSlots" class="text-[var(--color-accent)] italic normal-case flex items-center gap-1">
            <ArrowPathIcon class="w-3 h-3 animate-spin" /> checking...
          </span>
        </label>
        
        <div v-if="slots.length > 0" class="grid grid-cols-3 gap-2">
          <button 
            v-for="slot in slots" :key="slot"
            type="button"
            @click="$emit('update:appointmentTime', slot)"
            class="rounded-xl py-3 text-sm font-bold transition-all duration-200 border"
            :class="appointmentTime === slot ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] border-[var(--color-accent)] shadow-lg shadow-[var(--color-shadow)] scale-105 z-10' : 'bg-[var(--color-bg-tertiary)]/50 border-[var(--color-border)] text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 hover:bg-[var(--color-bg-secondary)]'"
          >
            {{ slot }}
          </button>
        </div>
        
        <div v-else class="h-[58px] rounded-2xl border border-dashed border-[var(--color-border)] flex items-center justify-center text-sm text-[var(--color-text-muted)] italic bg-[var(--color-bg-tertiary)]/30">
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
