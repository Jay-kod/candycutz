<template>
  <div class="flex min-h-screen flex-col items-center justify-center bg-[var(--color-bg)] px-6 py-20 text-center text-[var(--color-text)]">
    <!-- Decorative background -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
      <div class="absolute left-1/2 top-1/3 -translate-x-1/2 -translate-y-1/2 h-[400px] w-[400px] rounded-full bg-gold/5 blur-3xl animate-pulse-slow"></div>
    </div>

    <!-- Offline / WiFi-off SVG -->
    <div class="relative mb-8 animate-float">
      <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="drop-shadow-2xl">
        <!-- WiFi arcs -->
        <path d="M28 52 C40 38 55 30 60 28 C65 30 80 38 92 52" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none" class="text-gold/20"/>
        <path d="M36 62 C45 50 55 44 60 42 C65 44 75 50 84 62" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none" class="text-gold/30"/>
        <path d="M44 72 C50 64 55 58 60 56 C65 58 70 64 76 72" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none" class="text-gold/40"/>
        <!-- Center dot -->
        <circle cx="60" cy="82" r="5" fill="currentColor" class="text-gold"/>
        <!-- Slash line -->
        <line x1="25" y1="95" x2="95" y2="25" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" class="text-danger animate-slash"/>
      </svg>
    </div>

    <!-- Brand -->
    <p class="font-display text-3xl font-bold text-gold tracking-wide mb-6">CandyCutz</p>

    <!-- Message -->
    <div class="max-w-md">
      <h1 class="font-display text-4xl font-bold text-theme-text md:text-5xl">
        You're Offline
      </h1>
      <p class="mt-4 text-lg text-[var(--color-text-muted)]">
        It seems your connection dropped. Check your internet and try again — your fresh cut awaits.
      </p>
    </div>

    <!-- Retry CTA -->
    <button
      @click="retry"
      class="mt-10 group inline-flex items-center gap-2 rounded-xl bg-gold px-8 py-4 font-semibold text-obsidian shadow-lg shadow-gold/20 transition-all hover:bg-gold-light hover:-translate-y-0.5 hover:shadow-xl hover:shadow-gold/30"
    >
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 transition-transform group-hover:rotate-180 duration-500">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/>
      </svg>
      Try Again
    </button>

    <!-- Status indicator -->
    <div class="mt-12 flex items-center gap-3 text-sm text-[var(--color-text-muted)]">
      <span class="relative flex h-2.5 w-2.5">
        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-danger opacity-75"></span>
        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-danger"></span>
      </span>
      <span class="uppercase tracking-[0.2em] text-xs">No Connection</span>
    </div>
  </div>
</template>

<script setup>
function retry() {
  window.location.reload();
}
</script>

<style scoped>
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}
@keyframes slash {
  0%, 80% { opacity: 1; }
  90% { opacity: 0.3; }
  100% { opacity: 1; }
}
@keyframes pulse-slow {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(1.1); }
}

.animate-float { animation: float 4s ease-in-out infinite; }
.animate-slash { animation: slash 3s ease-in-out infinite; }
.animate-pulse-slow { animation: pulse-slow 6s ease-in-out infinite; }
</style>
