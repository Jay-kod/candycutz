<template>
  <header class="relative flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/[0.08] pb-6">
    <!-- Close Button (Top Right) -->
    <button
      type="button"
      @click="$emit('close')"
      class="absolute right-0 -top-2 md:top-0 flex h-8 w-8 items-center justify-center rounded-full text-white/40 hover:text-white hover:bg-white/[0.08] transition-colors cursor-pointer"
      title="Close payment screen"
      aria-label="Close"
    >
      <span class="text-xl leading-none">&times;</span>
    </button>

    <!-- Left: Brand / Entity Logo & Registration Codes -->
    <div class="flex items-center gap-4 pr-10">
      <!-- Circular White Badge / Logo -->
      <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white shadow-md overflow-hidden p-1">
        <img
          v-if="logoUrl"
          :src="logoUrl"
          alt="CandyCutz"
          class="h-full w-full object-contain"
        />
        <div v-else class="flex h-full w-full items-center justify-center rounded-full bg-amber-500/10 text-amber-600 font-black text-xl">
          ✂️
        </div>
      </div>

      <!-- Registration Meta -->
      <div class="space-y-0.5">
        <h1 class="text-base sm:text-lg font-bold text-white tracking-wide leading-tight">
          {{ title }}
        </h1>
        <p class="text-xs text-white/60 font-mono tracking-wide">
          Registration {{ reference }}
        </p>
        <p class="text-[11px] text-white/40 font-mono tracking-wider">
          {{ transactionCode }}
        </p>
      </div>
    </div>

    <!-- Right: Total Amount to Pay -->
    <div class="text-left md:text-right pr-10 md:pr-12">
      <span class="text-[11px] uppercase tracking-wider text-white/50 block font-medium">
        Total Amount to Pay
      </span>
      <span class="font-display text-2xl sm:text-3xl font-bold text-white tracking-tight">
        {{ formattedAmount }}
      </span>
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
  }
});

defineEmits(['close']);
</script>
