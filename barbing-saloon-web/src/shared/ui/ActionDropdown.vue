<template>
  <div class="relative inline-block text-left" ref="dropdownRef">
    <!-- Trigger Button -->
    <button
      type="button"
      @click="toggle"
      :class="[
        'inline-flex items-center justify-center rounded-xl p-2 transition-all duration-200',
        'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)]',
        'hover:bg-[var(--color-bg-tertiary)] dark:hover:bg-white/[0.08]',
        'focus:outline-none focus:ring-2 focus:ring-amber-500/40',
        isOpen ? 'bg-[var(--color-bg-tertiary)] dark:bg-white/[0.08] text-[var(--color-text-primary)]' : ''
      ]"
      :aria-expanded="isOpen"
      aria-haspopup="true"
      aria-label="Actions"
    >
      <slot name="trigger">
        <EllipsisVerticalIcon class="h-5 w-5" />
      </slot>
    </button>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div
        v-if="isOpen"
        :class="[
          'absolute z-50 mt-2 min-w-[180px] rounded-2xl border shadow-2xl backdrop-blur-xl py-1.5 focus:outline-none',
          'bg-[var(--color-bg-secondary)] dark:bg-[#151C28]',
          'border-[var(--color-border)] dark:border-white/[0.12]',
          align === 'right' ? 'right-0' : 'left-0'
        ]"
      >
        <template v-for="(item, idx) in items" :key="idx">
          <!-- Divider -->
          <div
            v-if="item.divider"
            class="my-1.5 border-t border-[var(--color-border-subtle)] dark:border-white/[0.06]"
          ></div>

          <!-- Menu Item -->
          <button
            v-else
            type="button"
            @click="handleSelect(item)"
            :disabled="item.disabled"
            :class="[
              'w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-left transition-colors',
              item.variant === 'danger'
                ? 'text-rose-600 dark:text-rose-400 hover:bg-rose-500/10'
                : 'text-[var(--color-text-primary)] hover:bg-[var(--color-bg-tertiary)] dark:hover:bg-white/[0.06]',
              item.disabled ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
            ]"
          >
            <component
              v-if="item.icon"
              :is="item.icon"
              :class="[
                'h-4 w-4 shrink-0',
                item.variant === 'danger' ? 'text-rose-500' : 'text-[var(--color-text-secondary)]'
              ]"
            />
            <span class="truncate">{{ item.label }}</span>
            <span
              v-if="item.badge"
              class="ml-auto text-[10px] font-semibold px-1.5 py-0.2 rounded bg-[var(--color-bg-tertiary)] text-[var(--color-text-muted)]"
            >
              {{ item.badge }}
            </span>
          </button>
        </template>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { EllipsisVerticalIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  align: {
    type: String,
    default: 'right',
    validator: (v) => ['left', 'right'].includes(v),
  },
});

const emit = defineEmits(['select']);

const isOpen = ref(false);
const dropdownRef = ref(null);

const toggle = () => {
  isOpen.value = !isOpen.value;
};

const close = () => {
  isOpen.value = false;
};

const handleSelect = (item) => {
  if (item.disabled) return;
  close();
  if (typeof item.action === 'function') {
    item.action();
  }
  emit('select', item);
};

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    close();
  }
};

const handleKeyDown = (e) => {
  if (e.key === 'Escape' && isOpen.value) {
    close();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  document.removeEventListener('keydown', handleKeyDown);
});
</script>

