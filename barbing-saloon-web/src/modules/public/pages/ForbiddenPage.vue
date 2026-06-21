<template>
  <PublicLayout>
    <section class="relative flex min-h-[80vh] flex-col items-center justify-center overflow-hidden px-6 py-20 text-center">
      <!-- Decorative background -->
      <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -right-20 top-20 h-80 w-80 rounded-full bg-gold/5 blur-3xl animate-pulse-slow"></div>
        <div class="absolute -left-20 bottom-20 h-80 w-80 rounded-full bg-gold/5 blur-3xl animate-pulse-slow" style="animation-delay: 2s"></div>
      </div>

      <!-- Lock / Shield SVG -->
      <div class="relative mb-8 animate-lock-bounce" data-reveal>
        <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="drop-shadow-2xl">
          <!-- Shield body -->
          <path d="M60 15 L95 35 V65 C95 88 75 105 60 110 C45 105 25 88 25 65 V35 Z"
            stroke="currentColor" stroke-width="3" fill="none" class="text-gold"/>
          <!-- Lock body -->
          <rect x="45" y="55" width="30" height="25" rx="4" stroke="currentColor" stroke-width="2.5" class="text-gold"/>
          <!-- Lock shackle -->
          <path d="M50 55 V47 C50 40 54 36 60 36 C66 36 70 40 70 47 V55"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" fill="none" class="text-gold"/>
          <!-- Keyhole -->
          <circle cx="60" cy="65" r="3.5" fill="currentColor" class="text-gold"/>
          <line x1="60" y1="68" x2="60" y2="74" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" class="text-gold"/>
          <!-- Pulse rings -->
          <circle cx="60" cy="62" r="30" stroke="currentColor" stroke-width="1" fill="none" class="text-gold/20 animate-ring-pulse"/>
          <circle cx="60" cy="62" r="42" stroke="currentColor" stroke-width="0.5" fill="none" class="text-gold/10 animate-ring-pulse" style="animation-delay: 0.5s"/>
        </svg>
      </div>

      <!-- Error Code -->
      <h1
        class="font-display text-[8rem] font-extrabold leading-none tracking-tight text-gold/15 md:text-[12rem]"
        data-reveal
      >
        403
      </h1>

      <!-- Message -->
      <div data-reveal data-reveal-delay="150" class="relative -mt-8 max-w-lg">
        <h2 class="font-display text-3xl font-bold text-gold md:text-4xl">
          VIP Area Only
        </h2>
        <p class="mt-4 text-lg text-theme-muted">
          This section is reserved for authorized crew members. If you think you should have access, sign in with the right account.
        </p>
      </div>

      <!-- CTAs -->
      <div data-reveal data-reveal-delay="300" class="mt-10 flex flex-col gap-4 sm:flex-row">
        <RouterLink
          to="/customer/login"
          class="group inline-flex items-center gap-2 rounded-xl bg-gold px-8 py-4 font-semibold text-obsidian shadow-lg shadow-gold/20 transition-all hover:bg-gold-light hover:-translate-y-0.5 hover:shadow-xl hover:shadow-gold/30"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
          </svg>
          Sign In
        </RouterLink>
        <RouterLink
          to="/"
          class="inline-flex items-center gap-2 rounded-xl border border-gold/30 px-8 py-4 font-semibold text-gold transition-all hover:border-gold hover:bg-gold/10 hover:-translate-y-0.5"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
          </svg>
          Go Home
        </RouterLink>
      </div>

      <!-- Divider -->
      <div data-reveal data-reveal-delay="450" class="mt-16 flex items-center gap-3 text-sm text-theme-muted">
        <span class="h-px w-12 bg-gold/30"></span>
        <span class="uppercase tracking-[0.3em] text-xs">Access Denied</span>
        <span class="h-px w-12 bg-gold/30"></span>
      </div>
    </section>
  </PublicLayout>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import PublicLayout from '../../../core/layouts/PublicLayout.vue';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

useScrollReveal();
</script>

<style scoped>
@keyframes lock-bounce {
  0%, 100% { transform: translateY(0); }
  30% { transform: translateY(-8px); }
  60% { transform: translateY(3px); }
}
@keyframes ring-pulse {
  0% { transform: scale(0.8); opacity: 0.6; }
  100% { transform: scale(1.4); opacity: 0; }
}
@keyframes pulse-slow {
  0%, 100% { opacity: 0.4; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.1); }
}

.animate-lock-bounce { animation: lock-bounce 2s ease-in-out infinite; }
.animate-ring-pulse { animation: ring-pulse 2.5s ease-out infinite; }
.animate-pulse-slow { animation: pulse-slow 6s ease-in-out infinite; }
</style>
