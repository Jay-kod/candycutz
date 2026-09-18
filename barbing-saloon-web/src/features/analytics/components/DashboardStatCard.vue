<template>
  <div class="group relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all duration-500 hover:-translate-y-1 hover:border-gold/30 hover:shadow-[0_10px_40px_-10px_rgba(212,175,55,0.15)]" :class="hoverBorder">
    <div class="absolute -right-6 -top-6 text-theme-muted/5 group-hover:text-theme-muted/10 transition-colors duration-500 pointer-events-none" :class="watermarkClass">
      <component :is="icon" class="w-32 h-32" />
    </div>
    
    <div class="relative z-10 flex flex-col gap-4">
      <div class="flex items-center gap-3">
        <component :is="icon" class="w-8 h-8" :class="iconClass" />
        <p class="text-xs font-semibold uppercase tracking-widest text-ivory/50">{{ label }}</p>
      </div>
      <p class="font-display text-4xl text-theme-text transition-colors" :class="valueClass">
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

const watermarkClass = computed(() => props.colorClass.replace('text-', 'text-').replace('-400', '-400/5').replace('-500', '-500/5') + ' group-hover:' + props.colorClass.replace('text-', 'text-').replace('-400', '-400/10').replace('-500', '-500/10'));
const iconClass = computed(() => props.colorClass);
const valueClass = computed(() => `group-hover:${props.colorClass}`);

const formattedValue = computed(() => {
  if (!props.value && props.value !== 0) return 0;
  if (!props.formatNumber) return props.value;
  return Number(props.value).toLocaleString();
});
</script>
