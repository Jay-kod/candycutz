<template>
  <div>
    <div class="w-full h-px bg-gradient-to-r from-transparent via-[var(--color-border)] to-transparent my-8"></div>
    
    <div class="flex items-center justify-between mb-8">
      <h2 class="font-display text-2xl md:text-3xl text-[var(--color-text-primary)] flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-sm font-bold">2</span>
        Special <span class="text-[var(--color-accent)]">Requests</span>
      </h2>
    </div>
    
    <div class="space-y-6">
      <div>
        <label class="text-xs font-semibold uppercase tracking-widest text-[var(--color-text-secondary)] ml-2 mb-2 block">Notes (Optional)</label>
        <textarea 
          :value="notes" 
          rows="2" 
          class="w-full rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-input)] px-6 py-4 text-sm text-[var(--color-text-primary)] placeholder-[var(--color-text-muted)] outline-none transition-all focus:border-[var(--color-accent)] focus:ring-4 focus:ring-[var(--color-accent)]/10 hover:border-[var(--color-accent)]/40 resize-none" 
          placeholder="Any particular styling instructions?"
          @input="$emit('update:notes', $event.target.value)"
        ></textarea>
      </div>

      <!-- Booking Summary -->
      <div class="rounded-2xl border border-[var(--color-accent)]/20 bg-[var(--color-accent)]/5 p-5">
        <p class="text-xs uppercase tracking-widest text-[var(--color-accent)]/80 mb-3 font-bold">Booking Summary</p>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between"><span class="text-[var(--color-text-secondary)]">Service</span><span class="text-[var(--color-text-primary)] font-semibold">{{ service.name }}</span></div>
          <div class="flex justify-between"><span class="text-[var(--color-text-secondary)]">Barber</span><span class="text-[var(--color-text-primary)] font-semibold">{{ selectedBarberName }}</span></div>
          <div class="flex justify-between"><span class="text-[var(--color-text-secondary)]">Date</span><span class="text-[var(--color-text-primary)] font-semibold">{{ formatDate(appointmentDate) }}</span></div>
          <div class="flex justify-between"><span class="text-[var(--color-text-secondary)]">Time</span><span class="text-[var(--color-accent)] font-bold">{{ appointmentTime }}</span></div>
          <div class="flex justify-between border-t border-[var(--color-accent)]/20 pt-2 mt-2"><span class="text-[var(--color-text-secondary)]">Total</span><span class="text-[var(--color-accent)] font-display text-lg font-bold">{{ formatNairaAmount(service.price) }}</span></div>
        </div>
      </div>

      <button 
        type="submit"
        :disabled="isSubmitting"
        class="group w-full relative flex items-center justify-center gap-3 rounded-2xl bg-[var(--color-accent)] p-1 shadow-lg shadow-[var(--color-shadow)] transition-all duration-300 hover:scale-[1.01] disabled:opacity-70 disabled:cursor-not-allowed"
      >
        <div class="w-full bg-[var(--color-bg-primary)] rounded-xl px-8 py-4 transition-all duration-300 group-hover:bg-transparent flex items-center justify-center">
          <div class="flex items-center justify-center gap-3 text-[var(--color-accent)] group-hover:text-[var(--color-accent-foreground)] transition-colors font-display text-xl font-bold">
            <ArrowPathIcon v-if="isSubmitting" class="w-5 h-5 animate-spin" />
            <span>{{ isSubmitting ? 'Confirming...' : 'Confirm Booking' }}</span>
            <svg v-if="!isSubmitting" class="h-5 w-5 transition-transform group-hover:translate-x-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ArrowPathIcon } from '@heroicons/vue/24/outline'
import { formatNairaAmount } from '@/core/utils'

defineProps({
  notes: { type: String, default: '' },
  service: { type: Object, required: true },
  selectedBarberName: { type: String, default: '' },
  appointmentDate: { type: String, default: '' },
  appointmentTime: { type: String, default: '' },
  formatDate: { type: Function, required: true },
  isSubmitting: { type: Boolean, default: false }
})

defineEmits(['update:notes'])
</script>
