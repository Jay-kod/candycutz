<template>
  <header class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3">
    <!-- Top row on mobile / Left group on desktop -->
    <div class="flex items-center justify-between sm:justify-start gap-2.5 w-full sm:w-auto">
      <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
        <!-- Circular Luxury Badge / Logo -->
        <div class="relative flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500/20 via-amber-500/10 to-[#12161E] border border-amber-500/30 text-amber-400 shadow-[0_0_12px_rgba(245,158,11,0.12)] p-1 overflow-hidden">
          <img
            v-if="logoUrl"
            :src="logoUrl"
            alt="CandyCutz"
            class="h-full w-full object-contain"
          />
          <div v-else class="flex h-full w-full items-center justify-center font-bold text-xs sm:text-sm select-none">
            ✂️
          </div>
        </div>

        <!-- Title & Badges -->
        <div class="space-y-0.5 min-w-0">
          <div class="flex flex-wrap items-center gap-1 sm:gap-1.5">
            <h1 class="text-xs sm:text-sm font-bold text-white tracking-wide leading-tight truncate">
              {{ title }}
            </h1>
            <span
              v-if="appointmentId || appointment?.id"
              class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/15 text-amber-300 border border-amber-500/30 font-mono tracking-wide"
            >
              #{{ appointmentId || appointment?.id }}
            </span>
            <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
              <span class="h-1 w-1 rounded-full bg-emerald-400 animate-pulse"></span>
              Direct Pay
            </span>
          </div>

          <!-- Registration metadata (visible on desktop) -->
          <div class="hidden sm:flex items-center gap-x-2 text-[10px] text-white/50">
            <p class="font-mono tracking-wide flex items-center gap-1">
              <span>Registration {{ reference }}</span>
              <button
                v-if="reference"
                type="button"
                @click="$emit('copy', reference, 'Booking Reference')"
                class="text-white/35 hover:text-amber-400 transition-colors cursor-pointer"
                title="Copy Reference"
              >
                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                </svg>
              </button>
            </p>
            <span class="text-white/20">•</span>
            <p class="text-[9px] text-white/40 font-mono tracking-wider">
              {{ transactionCode }}
            </p>
          </div>
        </div>
      </div>

      <!-- Close Button (Always positioned conveniently in top right) -->
      <button
        type="button"
        @click="$emit('close')"
        class="sm:hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/[0.04] border border-white/[0.08] text-white/40 hover:text-white hover:bg-white/[0.1] hover:border-white/20 transition-all cursor-pointer shadow-sm"
        title="Close payment screen"
        aria-label="Close"
      >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Mobile Subrow: Registration + Amount -->
    <div class="flex sm:hidden items-center justify-between text-[10px] text-white/60 bg-white/[0.02] px-2 py-1 rounded-md border border-white/[0.04]">
      <p class="font-mono tracking-wide flex items-center gap-1 text-[10px]">
        <span>Registration {{ reference }}</span>
        <button
          v-if="reference"
          type="button"
          @click="$emit('copy', reference, 'Booking Reference')"
          class="text-white/35 hover:text-amber-400 transition-colors cursor-pointer"
        >
          <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
          </svg>
        </button>
      </p>

      <div class="flex items-center gap-1.5">
        <span class="text-[9px] uppercase tracking-wider text-amber-400/80 font-bold">Total:</span>
        <span class="font-display text-xs font-black text-white tracking-tight">
          {{ formattedAmount }}
        </span>
      </div>
    </div>

    <!-- Desktop Right: Total Amount to Pay & Close Button -->
    <div class="hidden sm:flex items-center gap-3 shrink-0">
      <div class="text-right">
        <span class="text-[9px] uppercase tracking-wider text-amber-400/80 font-bold block">
          Total Amount to Pay
        </span>
        <span class="font-display text-lg sm:text-xl font-extrabold text-white tracking-tight">
          {{ formattedAmount }}
        </span>
      </div>

      <button
        type="button"
        @click="$emit('close')"
        class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/[0.04] border border-white/[0.08] text-white/40 hover:text-white hover:bg-white/[0.1] hover:border-white/20 transition-all cursor-pointer shadow-sm ml-1"
        title="Close payment screen"
        aria-label="Close"
      >
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
  </header>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'MTN FlyX Registration • CandyCutz'
  },
  reference: {
    type: String,
    required: true
  },
  transactionCode: {
    type: String,
    required: true
  },
  formattedAmount: {
    type: String,
    required: true
  },
  logoUrl: {
    type: String,
    default: ''
  },
  appointment: {
    type: Object,
    default: null
  },
  appointmentId: {
    type: [Number, String],
    default: null
  }
});

defineEmits(['close', 'copy']);
</script>
