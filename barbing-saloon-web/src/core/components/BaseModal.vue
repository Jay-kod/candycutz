<template>
  <div v-if="open" class="fixed inset-0 z-50 grid place-items-center bg-black/70 p-3 sm:p-4 backdrop-blur-xs" @click.self="$emit('close')">
    <div class="w-full max-w-lg rounded-2xl sm:rounded-3xl bg-[var(--color-bg)] p-4 sm:p-6 shadow-2xl max-h-[90dvh] overflow-y-auto custom-scrollbar border border-[var(--color-border)]">
      <h2 class="mb-4 font-display text-2xl sm:text-3xl text-gold">{{ title }}</h2>
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({ open: Boolean, title: String });
</script>