<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-theme-bg text-theme-text">
    <section class="relative flex min-h-[80vh] flex-col items-center justify-center overflow-hidden px-6 py-20 text-center">
      <!-- Decorative background elements -->
      <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-gold/5 blur-3xl animate-pulse-slow"></div>
        <div class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-gold/5 blur-3xl animate-pulse-slow" style="animation-delay: 1.5s"></div>
      </div>

      <!-- Animated Scissors SVG -->
      <div class="relative mb-8 animate-float" data-reveal>
        <svg width="140" height="140" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg" class="drop-shadow-2xl">
          <!-- Left blade -->
          <g class="origin-center animate-snip-left">
            <path d="M70 70 L20 20" stroke="currentColor" stroke-width="4" stroke-linecap="round" class="text-gold"/>
            <circle cx="15" cy="15" r="12" stroke="currentColor" stroke-width="3" fill="none" class="text-gold"/>
          </g>
          <!-- Right blade -->
          <g class="origin-center animate-snip-right">
            <path d="M70 70 L120 20" stroke="currentColor" stroke-width="4" stroke-linecap="round" class="text-gold"/>
            <circle cx="125" cy="15" r="12" stroke="currentColor" stroke-width="3" fill="none" class="text-gold"/>
          </g>
          <!-- Pivot -->
          <circle cx="70" cy="70" r="5" fill="currentColor" class="text-gold"/>
          <!-- Cut line (dashed) -->
          <line x1="40" y1="110" x2="100" y2="110" stroke="currentColor" stroke-width="2" stroke-dasharray="6 4" class="text-theme-muted opacity-40"/>
          <!-- Hair strands falling -->
          <g class="animate-hair-fall">
            <line x1="55" y1="95" x2="50" y2="115" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="text-gold/40"/>
            <line x1="70" y1="92" x2="68" y2="118" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="text-gold/30"/>
            <line x1="85" y1="95" x2="90" y2="115" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="text-gold/40"/>
          </g>
        </svg>
      </div>

      <!-- Error Code -->
      <h1
        class="font-display text-[8rem] font-extrabold leading-none tracking-tight text-gold/20 md:text-[12rem]"
        data-reveal
      >
        404
      </h1>

      <!-- Message -->
      <div data-reveal data-reveal-delay="150" class="relative -mt-8 max-w-lg">
        <h2 class="font-display text-3xl font-bold text-gold md:text-4xl">
          This Cut Doesn't Exist
        </h2>
        <p class="mt-4 text-lg text-theme-muted">
          Looks like the page you're looking for has been trimmed from our lineup. Let's get you back to a fresh start.
        </p>
      </div>

      <!-- CTAs -->
      <div data-reveal data-reveal-delay="300" class="mt-10 flex flex-col gap-4 sm:flex-row">
        <RouterLink
          to="/"
          class="group inline-flex items-center gap-2 rounded-xl bg-gold px-8 py-4 font-semibold text-obsidian shadow-lg shadow-gold/20 transition-all hover:bg-gold-light hover:-translate-y-0.5 hover:shadow-xl hover:shadow-gold/30"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 transition-transform group-hover:-translate-x-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
          </svg>
          Return Home
        </RouterLink>
        <RouterLink
          to="/services"
          class="inline-flex items-center gap-2 rounded-xl border border-gold/30 px-8 py-4 font-semibold text-gold transition-all hover:border-gold hover:bg-gold/10 hover:-translate-y-0.5"
        >
          Browse Services
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 transition-transform group-hover:translate-x-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
          </svg>
        </RouterLink>
      </div>

      <!-- Barber pole decorative stripe -->
      <div data-reveal data-reveal-delay="450" class="mt-16 flex items-center gap-3 text-sm text-theme-muted">
        <span class="h-px w-12 bg-gold/30"></span>
        <span class="uppercase tracking-[0.3em] text-xs">CandyCutz</span>
        <span class="h-px w-12 bg-gold/30"></span>
      </div>
    </section>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

useScrollReveal();
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-12px); }
}
@keyframes snip-left {
  0%, 100% { transform: rotate(0deg); }
  50% { transform: rotate(-12deg); }
}
@keyframes snip-right {
  0%, 100% { transform: rotate(0deg); }
  50% { transform: rotate(12deg); }
}
@keyframes hair-fall {
  0%, 40% { opacity: 0; transform: translateY(-8px); }
  60% { opacity: 1; transform: translateY(0); }
  100% { opacity: 0; transform: translateY(12px); }
}
@keyframes pulse-slow {
  0%, 100% { opacity: 0.4; transform: scale(1); }
  50% { opacity: 0.7; transform: scale(1.1); }
}

.animate-float { animation: float 4s ease-in-out infinite; }
.animate-snip-left { animation: snip-left 2.5s ease-in-out infinite; }
.animate-snip-right { animation: snip-right 2.5s ease-in-out infinite; }
.animate-hair-fall { animation: hair-fall 2.5s ease-in-out infinite 0.5s; }
.animate-pulse-slow { animation: pulse-slow 6s ease-in-out infinite; }
</style>
