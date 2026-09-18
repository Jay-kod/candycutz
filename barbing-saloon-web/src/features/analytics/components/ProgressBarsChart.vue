<template>
  <div class="space-y-4">
    <div v-for="item in normalizedItems" :key="item.label" class="group">
      <div class="flex justify-between text-sm mb-1">
        <span class="text-ivory/80 font-medium">{{ item.label }}</span>
        <span class="font-bold" :class="valueClass">{{ item.value }} {{ unit }}</span>
      </div>
      <div class="h-2 w-full bg-theme-bg rounded-full overflow-hidden border border-theme-border">
        <div class="h-full transition-all duration-1000 ease-out"
             :class="barClass"
             :style="{ width: `${item.percentage}%` }"></div>
      </div>
    </div>
    <div v-if="normalizedItems.length === 0" class="p-8 text-center text-ivory/40 text-sm">
      {{ emptyMessage }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  labelKey: {
    type: String,
    required: true
  },
  valueKey: {
    type: String,
    required: true
  },
  unit: {
    type: String,
    default: 'appts'
  },
  emptyMessage: {
    type: String,
    default: 'No data available.'
  },
  valueClass: {
    type: String,
    default: 'text-gold'
  },
  barClass: {
    type: String,
    default: 'bg-gradient-to-r from-gold to-gold-light'
  }
});

const normalizedItems = computed(() => {
  if (props.items.length === 0) return [];
  
  const max = Math.max(...props.items.map(item => Number(item[props.valueKey])), 1);
  return props.items.map(item => ({
    label: item[props.labelKey],
    value: item[props.valueKey],
    percentage: (Number(item[props.valueKey]) / max) * 100
  }));
});
</script>
