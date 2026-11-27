<template>
  <component :is="resolvedComponent" :class="customClass" aria-hidden="true" />
</template>

<script setup>
import { computed } from 'vue';
import { resolveCategoryIcon } from './categoryIcons';

const props = defineProps({
  name: {
    type: String,
    default: ''
  },
  customClass: {
    type: [String, Object, Array],
    default: 'size-5'
  },
  fallback: {
    type: String,
    default: 'tag'
  }
});

const resolvedComponent = computed(() => resolveCategoryIcon(props.name, props.fallback));
</script>
