<template>
  <div
    ref="containerRef"
    class="relative inline-flex items-center p-1 bg-black/5 dark:bg-white/10 rounded-xl select-none font-sans"
    :class="size === 'sm' ? 'text-xs h-8' : 'text-sm h-10'"
    role="tablist"
    :aria-label="ariaLabel || 'Segmented control'"
  >
    <!-- Sliding spring pill indicator -->
    <div
      class="absolute top-1 bottom-1 rounded-lg bg-white dark:bg-[#2C2C2E] shadow-[0_2px_8px_rgba(0,0,0,0.12),0_1px_2px_rgba(0,0,0,0.06)] transition-all duration-300 pointer-events-none"
      :style="indicatorStyle"
    />

    <!-- Segment buttons -->
    <button
      v-for="(option, idx) in normalizedOptions"
      :key="option.value"
      :ref="(el) => { if (el) buttonRefs[idx] = el; }"
      type="button"
      role="tab"
      :aria-selected="modelValue === option.value"
      class="relative z-10 flex-1 px-3 py-1 font-medium text-center truncate transition-colors duration-200 cursor-pointer outline-none focus-visible:ring-2 focus-visible:ring-gold/50 rounded-lg flex items-center justify-center gap-1.5"
      :class="[
        modelValue === option.value
          ? 'text-theme-text font-semibold'
          : 'text-theme-muted hover:text-theme-text'
      ]"
      @click="selectOption(option.value, idx)"
    >
      <span>{{ option.label }}</span>
      <span
        v-if="option.count !== undefined"
        class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
        :class="modelValue === option.value ? 'bg-gold/20 text-gold-dark dark:text-gold' : 'bg-black/5 dark:bg-white/10 text-theme-muted'"
      >
        {{ option.count }}
      </span>
    </button>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    required: true,
  },
  options: {
    type: Array,
    required: true,
  },
  size: {
    type: String,
    default: 'md',
  },
  ariaLabel: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const containerRef = ref(null);
const buttonRefs = ref([]);
const indicatorStyle = ref({});

const normalizedOptions = computed(() => {
  return props.options.map((opt) => {
    if (typeof opt === 'object' && opt !== null) {
      return {
        value: opt.value,
        label: opt.label ?? String(opt.value),
        count: opt.count,
      };
    }
    return {
      value: opt,
      label: String(opt),
    };
  });
});

const updateIndicator = () => {
  const activeIdx = normalizedOptions.value.findIndex((opt) => opt.value === props.modelValue);
  if (activeIdx === -1 || !buttonRefs.value[activeIdx]) {
    indicatorStyle.value = { display: 'none' };
    return;
  }

  const activeBtn = buttonRefs.value[activeIdx];
  const container = containerRef.value;
  if (!container || !activeBtn) return;

  const left = activeBtn.offsetLeft;
  const width = activeBtn.offsetWidth;

  indicatorStyle.value = {
    left: `${left}px`,
    width: `${width}px`,
    transition: 'all 0.32s cubic-bezier(0.2, 0.8, 0.2, 1)',
  };
};

const selectOption = (value) => {
  emit('update:modelValue', value);
  emit('change', value);
  nextTick(updateIndicator);
};

watch(() => props.modelValue, () => {
  nextTick(updateIndicator);
});

watch(() => props.options, () => {
  nextTick(updateIndicator);
}, { deep: true });

onMounted(() => {
  nextTick(updateIndicator);
});
</script>
