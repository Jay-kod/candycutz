<template>
  <div class="relative overflow-hidden rounded-3xl border border-[var(--color-border)] bg-gradient-to-br from-amber-500/10 via-[var(--color-bg-secondary)] to-[var(--color-bg-secondary)] p-4 xs:p-6 sm:p-8 shadow-sm">
    <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/10 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 h-48 w-48 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <p class="text-xs uppercase tracking-[0.25em] text-[var(--color-accent)] font-bold">
          Customer Portal
        </p>
        <h1 class="mt-1 font-display text-2xl sm:text-3xl lg:text-4xl text-[var(--color-text-primary)]">
          Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-200">Booking Overview</span>
        </h1>
        <p class="mt-2 max-w-xl text-xs sm:text-sm text-[var(--color-text-secondary)] leading-relaxed">
          Track upcoming appointments, check-in verification codes, and real-time grooming schedules.
        </p>
      </div>

      <div class="flex items-center gap-3">
        <RouterLink 
          to="/customer/dashboard/services"
          class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-accent)] px-5 py-3 text-xs sm:text-sm font-bold text-white shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-[0.98] transition-all"
        >
          <ScissorsIcon class="w-4 h-4" />
          <span>Book New Service</span>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { ScissorsIcon } from '@heroicons/vue/24/outline';
</script>

