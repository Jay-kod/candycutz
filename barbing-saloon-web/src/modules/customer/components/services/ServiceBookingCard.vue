<template>
  <div class="lg:sticky lg:top-8 space-y-6">
    <div class="rounded-[2rem] border border-gold/20 bg-gradient-to-b from-charcoal to-obsidian p-8 shadow-[0_20px_40px_rgba(0,0,0,0.5)] relative overflow-hidden">
      <div class="absolute -right-20 -top-20 h-48 w-48 rounded-full bg-gold/10 blur-[60px]"></div>
      
      <div class="relative z-10">
        <div class="mb-6 flex items-start justify-between">
          <span class="inline-flex items-center gap-1.5 rounded-lg bg-gold/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-gold border border-gold/20">
            {{ service.category?.name || service.category_name || 'Service' }}
          </span>
        </div>

        <h1 class="font-display text-3xl font-bold text-white leading-tight mb-6">
          {{ service.name }}
        </h1>

        <div class="space-y-4 mb-8">
          <div class="flex items-center justify-between rounded-2xl bg-white/10 p-4 border border-white/10">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-gold/10 text-gold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              </div>
              <div>
                <p class="text-xs uppercase tracking-wider text-white/50 font-semibold mb-0.5">Duration</p>
                <p class="font-bold text-white">{{ service.duration_minutes }} Minutes</p>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between rounded-2xl bg-white/10 p-4 border border-white/10">
            <div class="flex items-center gap-3">
              <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              </div>
              <div>
                <p class="text-xs uppercase tracking-wider text-white/50 font-semibold mb-0.5">Price</p>
                <p class="font-display text-2xl font-bold text-emerald-400">₦{{ Number(service.price).toLocaleString() }}</p>
              </div>
            </div>
          </div>
        </div>

        <RouterLink 
          :to="`/customer/dashboard/book/${service.id}`" 
          class="flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-gold to-amber-500 py-4 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] hover:scale-[1.02] group"
        >
          Book Appointment Now
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 group-hover:translate-x-1 transition-transform"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
        </RouterLink>
      </div>
    </div>
    
    <!-- Guarantee Badge -->
    <div class="flex items-center gap-3 px-2 justify-center text-theme-muted text-sm">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gold"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" /></svg>
      Satisfaction Guaranteed
    </div>

    <!-- Barber Profile Card -->
    <div v-if="primaryBarber" class="rounded-[2rem] border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm">
      <h3 class="text-xs uppercase tracking-widest text-theme-muted font-bold mb-4">Service Provider</h3>
      <div class="flex items-center gap-4">
        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-full border-2 border-gold/40">
          <img v-if="primaryBarber.avatar" :src="getFullImageUrl(primaryBarber.avatar)" :alt="primaryBarber.name" class="h-full w-full object-cover" />
          <div v-else class="h-full w-full bg-gold/10 flex items-center justify-center text-gold font-bold text-xl">
            {{ primaryBarber.name?.charAt(0) || 'B' }}
          </div>
        </div>
        <div>
          <h4 class="font-display text-lg font-bold text-theme-text">{{ primaryBarber.name }}</h4>
          <RouterLink :to="`/customer/dashboard/barber/${primaryBarber.id}`" class="text-sm text-gold hover:underline mt-0.5 inline-block">
            View Provider
          </RouterLink>
        </div>
      </div>
      <p v-if="otherBarbers.length" class="mt-4 border-t border-theme-border/50 pt-3 text-xs text-theme-muted">
        Also available: <span class="font-semibold text-gold/80">{{ otherBarbers.map(b => b.name).join(', ') }}</span>
      </p>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'

defineProps({
  service: { type: Object, required: true },
  primaryBarber: { type: Object, default: null },
  otherBarbers: { type: Array, default: () => [] },
  getFullImageUrl: { type: Function, required: true }
})
</script>
