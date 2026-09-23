<template>
  <div class="group relative overflow-hidden rounded-xl border border-theme-border bg-theme-surface p-6 transition-colors duration-300" :class="hoverBorder">
    
    <div class="relative z-10 flex flex-col gap-4">
      <div class="flex items-center gap-3">
        <component :is="icon" class="w-5 h-5" :class="iconClass" />
        <p class="text-xs font-semibold uppercase tracking-widest text-ivory/50">{{ label }}</p>
      </div>
      <p class="font-display text-4xl text-theme-text tabular-nums transition-colors" :class="valueClass">
        <span v-if="prefix">{{ prefix }}</span>{{ formattedValue }}<span v-if="suffix">{{ suffix }}</span>
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: String,
  value: [String, Number],
  icon: Object,
  colorClass: {
    type: String,
    default: 'text-gold'
  },
  hoverBorder: {
    type: String,
    default: 'hover:border-gold/30'
  },
  prefix: String,
  suffix: String,
  isCurrency: Boolean,
  formatNumber: {
    type: Boolean,
    default: true
  }
});

const iconClass = computed(() => props.colorClass);
const valueClass = computed(() => `group-hover:${props.colorClass}`);

const formattedValue = computed(() => {
  if (!props.value && props.value !== 0) return 0;
  if (!props.formatNumber) return props.value;
  return new Intl.NumberFormat('en-NG').format(Number(props.value));
});
</script>
