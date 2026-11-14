<template>
  <div class="relative overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6 sm:p-8 shadow-xl">
    <!-- Ambient Luxury Glow -->
    <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 h-48 w-48 rounded-full bg-emerald-500/5 blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <span class="inline-block h-1.5 w-1.5 rounded-full bg-[var(--color-accent)]"></span>
          <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-semibold">Service Catalogue</p>
        </div>
        <h1 class="font-display text-3xl sm:text-4xl text-[var(--color-text-primary)] tracking-tight">
          {{ title }}
          <span class="ml-2 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-semibold bg-[var(--color-accent)]/10 text-[var(--color-accent)] border border-[var(--color-accent)]/20 align-middle">
            {{ totalServices }} available
          </span>
        </h1>
        <p class="mt-2 max-w-2xl text-sm text-[var(--color-text-secondary)] leading-relaxed">
          {{ subtitle }}
        </p>
      </div>

      <!-- Live Sync / Refresh Action -->
      <div class="flex items-center gap-3">
        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="isRefreshing"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] text-xs font-semibold text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] hover:border-[var(--color-accent)]/30 hover:bg-[var(--color-accent)]/5 transition-all focus:outline-none disabled:opacity-50"
          title="Refresh Services"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.8"
            stroke="currentColor"
            class="h-4 w-4"
            :class="{ 'animate-spin text-[var(--color-accent)]': isRefreshing }"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
          </svg>
          <span>{{ isRefreshing ? 'Refreshing...' : 'Refresh Catalogue' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Our Services'
  },
  subtitle: {
    type: String,
    default: 'Explore our curated menu of master barbering and luxury grooming services.'
  },
  totalServices: {
    type: Number,
    default: 0
  },
  isRefreshing: {
    type: Boolean,
    default: false
  }
});

defineEmits(['refresh']);
</script>

