<template>
  <div class="relative mb-6 mx-auto" :class="containerClass">
    <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
      <circle cx="50" cy="50" r="40" fill="transparent" stroke="rgba(255,255,255,0.05)" :stroke-width="strokeWidth" />
      <circle v-for="(segment, idx) in donutSegments" :key="idx"
              cx="50" cy="50" r="40" fill="transparent"
              :stroke="segment.color"
              :stroke-width="strokeWidth"
              stroke-linecap="round"
              :stroke-dasharray="`${segment.length} ${segment.gap}`"
              :stroke-dashoffset="segment.offset"
              class="transition-all duration-1000 ease-out"
              :style="{ transitionDelay: `${idx * 150}ms` }" />
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center">
      <span class="font-display text-theme-text" :class="valueClass">{{ total }}</span>
      <span class="uppercase tracking-widest text-ivory/40" :class="labelClass">{{ label }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  donutSegments: {
    type: Array,
    required: true
  },
  total: {
    type: [Number, String],
    required: true
  },
  label: {
    type: String,
    default: 'Total'
  },
  containerClass: {
    type: String,
    default: 'w-48 h-48'
  },
  strokeWidth: {
    type: Number,
    default: 20
  },
  valueClass: {
    type: String,
    default: 'text-3xl'
  },
  labelClass: {
    type: String,
    default: 'text-[10px]'
  }
});
</script>
