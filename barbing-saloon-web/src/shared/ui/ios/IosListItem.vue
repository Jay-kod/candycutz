<template>
  <component
    :is="to ? 'RouterLink' : (clickable ? 'button' : 'div')"
    :to="to"
    :type="to ? undefined : (clickable ? 'button' : undefined)"
    class="w-full flex items-center justify-between px-4 py-3 min-h-[48px] text-left transition-colors select-none group"
    :class="[
      clickable || to ? 'cursor-pointer hover:bg-black/[0.02] dark:hover:bg-white/[0.03] active:bg-black/[0.05] dark:active:bg-white/[0.06]' : ''
    ]"
    @click="$emit('click', $event)"
  >
    <div class="flex items-center gap-3.5 min-w-0 pr-2">
      <!-- Leading Icon with squircle badge -->
      <div
        v-if="icon"
        class="w-8 h-8 rounded-[9px] flex items-center justify-center shrink-0"
        :class="iconBg || 'bg-gold/15 text-gold'"
      >
        <component :is="icon" class="w-5 h-5" />
      </div>

      <div class="min-w-0">
        <div class="text-sm font-medium text-theme-text truncate leading-tight">
          {{ title }}
        </div>
        <div v-if="subtitle" class="text-xs text-theme-muted truncate mt-0.5">
          {{ subtitle }}
        </div>
      </div>
    </div>

    <div class="flex items-center gap-2 shrink-0">
      <div v-if="value" class="text-xs text-theme-muted font-normal">
        {{ value }}
      </div>
      <slot name="trailing" />
      <!-- iOS Chevron -->
      <svg
        v-if="chevron && (clickable || to)"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 20 20"
        fill="currentColor"
        class="w-4 h-4 text-theme-muted/50 group-hover:text-theme-muted transition-colors"
      >
        <path
          fill-rule="evenodd"
          d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
          clip-rule="evenodd"
        />
      </svg>
    </div>
  </component>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  icon: {
    type: [Object, Function],
    default: null,
  },
  iconBg: {
    type: String,
    default: '',
  },
  value: {
    type: [String, Number],
    default: '',
  },
  chevron: {
    type: Boolean,
    default: true,
  },
  to: {
    type: [String, Object],
    default: null,
  },
  clickable: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['click']);
</script>
