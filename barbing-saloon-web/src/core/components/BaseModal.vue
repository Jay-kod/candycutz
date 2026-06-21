<template>
  <div v-if="open" class="fixed inset-0 grid place-items-center bg-black/70 p-4">
    <div class="w-full max-w-lg rounded-3xl bg-[var(--color-bg)] p-6">
      <h2 class="mb-4 font-display text-3xl text-gold">{{ title }}</h2>
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({ open: Boolean, title: String });
</script>