<template>
  <div>
    <div class="w-full h-px bg-gradient-to-r from-transparent via-theme-border to-transparent my-8"></div>
    
    <div class="flex items-center justify-between mb-8">
      <h2 class="font-display text-2xl md:text-3xl text-theme-text flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold text-obsidian text-sm font-bold">2</span>
        Special <span class="text-gold">Requests</span>
      </h2>
    </div>
    
    <div class="space-y-6">
      <div>
        <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50 ml-2 mb-2 block">Notes (Optional)</label>
        <textarea 
          :value="notes" 
          rows="2" 
          class="w-full rounded-2xl border border-theme-border bg-theme-bg/80 px-6 py-4 text-sm text-theme-text placeholder-theme-muted/50 outline-none transition-all focus:border-gold focus:bg-theme-bg focus:ring-4 focus:ring-gold/10 hover:border-gold/30 resize-none" 
          placeholder="Any particular styling instructions?"
          @input="$emit('update:notes', $event.target.value)"
        ></textarea>
      </div>

      <!-- Booking Summary -->
      <div class="rounded-2xl border border-gold/20 bg-gold/5 p-5">
        <p class="text-xs uppercase tracking-widest text-gold/70 mb-3 font-bold">Booking Summary</p>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between"><span class="text-ivory/50">Service</span><span class="text-theme-text font-semibold">{{ service.name }}</span></div>
          <div class="flex justify-between"><span class="text-ivory/50">Barber</span><span class="text-theme-text font-semibold">{{ selectedBarberName }}</span></div>
          <div class="flex justify-between"><span class="text-ivory/50">Date</span><span class="text-theme-text font-semibold">{{ formatDate(appointmentDate) }}</span></div>
          <div class="flex justify-between"><span class="text-ivory/50">Time</span><span class="text-gold font-bold">{{ appointmentTime }}</span></div>
          <div class="flex justify-between border-t border-gold/20 pt-2 mt-2"><span class="text-ivory/50">Total</span><span class="text-gold font-display text-lg font-bold">₦{{ Number(service.price).toLocaleString() }}</span></div>
        </div>
      </div>

      <button 
        type="submit"
        :disabled="isSubmitting"
        class="group w-full relative flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-gold via-gold-light to-gold p-1 shadow-[0_0_30px_rgba(212,175,55,0.2)] transition-all duration-300 hover:shadow-[0_0_40px_rgba(212,175,55,0.4)] hover:scale-[1.01] disabled:opacity-70 disabled:cursor-not-allowed"
      >
        <div class="w-full bg-obsidian rounded-xl px-8 py-4 transition-all duration-300 group-hover:bg-transparent flex items-center justify-center">
          <div class="flex items-center justify-center gap-3 text-gold group-hover:text-obsidian transition-colors font-display text-xl">
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
