<template>
  <svg
    aria-hidden="true"
    :class="computedClasses"
    fill="none"
    stroke="currentColor"
    stroke-linecap="round"
    stroke-linejoin="round"
    viewBox="0 0 24 24"
    xmlns="http://www.w3.org/2000/svg"
  >
    <path d="M5 12h14" />
    <path d="M12 5v14" />
  </svg>
</template>

<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
  position: {
    type: String,
    default: 'top-left',
    validator: (val) => ['top-left', 'top-right', 'bottom-right', 'bottom-left'].includes(val),
  },
  class: {
    type: String,
    default: '',
  },
});

const positionClasses = {
  'top-left': 'top-0 left-0 -translate-x-[calc(50%+0.5px)] -translate-y-[calc(50%+0.5px)]',
  'top-right': 'top-0 right-0 translate-x-[calc(50%+0.5px)] -translate-y-[calc(50%+0.5px)]',
  'bottom-right': 'right-0 bottom-0 translate-x-[calc(50%+0.5px)] translate-y-[calc(50%+0.5px)]',
  'bottom-left': 'bottom-0 left-0 -translate-x-[calc(50%+0.5px)] translate-y-[calc(50%+0.5px)]',
};

const computedClasses = computed(() =>
  cn(
    'pointer-events-none absolute z-10 size-5 shrink-0 stroke-1 stroke-gold/60 text-gold/60',
    positionClasses[props.position] || positionClasses['top-left'],
    props.class
  )
);
</script>
