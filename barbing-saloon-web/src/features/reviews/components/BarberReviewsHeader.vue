<template>
  <header class="relative overflow-hidden rounded-3xl border border-theme-border bg-gradient-to-br from-[#12161C] via-[#0E1217] to-[#12161C] p-6 lg:p-8 shadow-2xl">
    <!-- Luxury ambient background glow -->
    <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -left-20 -bottom-20 h-48 w-48 rounded-full bg-amber-500/5 blur-2xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)] text-[11px] font-bold uppercase tracking-widest">
            <SparklesIcon class="h-3.5 w-3.5" />
            Client Reputation
          </span>
          <span class="text-xs text-theme-text-muted">•</span>
          <span class="text-xs text-theme-text-muted font-medium">{{ totalItems }} review{{ totalItems !== 1 ? 's' : '' }} recorded</span>
        </div>
        <h1 class="mt-2 font-display text-2xl sm:text-3xl lg:text-4xl text-theme-text">
          Service <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-200">Reviews</span> & Testimonials
        </h1>
        <p class="mt-2 max-w-xl text-xs sm:text-sm text-theme-text-muted leading-relaxed">
          Monitor direct customer feedback, service satisfaction, and testimonials submitted for your cuts and styling.
        </p>
      </div>

      <!-- Search & Controls -->
      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="relative flex-1 md:w-72">
          <MagnifyingGlassIcon class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-theme-text-muted" />
          <input
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            type="text"
            placeholder="Search by client, service or review..."
            class="w-full rounded-xl border border-theme-border bg-theme-surface/80 pl-10 pr-9 py-2.5 text-xs text-theme-text placeholder-theme-text-muted outline-none transition-all focus:border-[var(--color-accent)] focus:bg-theme-surface shadow-inner"
          />
          <button
            v-if="modelValue"
            @click="$emit('update:modelValue', '')"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-theme-text-muted hover:text-theme-text transition-colors"
            title="Clear search"
          >
            <XMarkIcon class="h-3.5 w-3.5" />
          </button>
        </div>

        <button
          @click="$emit('refresh')"
          class="flex items-center justify-center rounded-xl border border-theme-border bg-theme-surface p-2.5 text-theme-text-muted hover:text-theme-text hover:bg-theme-surface-subtle transition-all shadow-sm shrink-0 active:scale-95 disabled:opacity-50"
          :disabled="isRefreshing"
          title="Refresh reviews"
        >
          <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin': isRefreshing }" />
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import {
  SparklesIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  modelValue: { type: String, default: '' },
  totalItems: { type: Number, default: 0 },
  isRefreshing: { type: Boolean, default: false },
});

defineEmits(['update:modelValue', 'refresh']);
</script>

