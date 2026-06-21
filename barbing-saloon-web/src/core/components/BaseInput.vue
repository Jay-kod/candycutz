<template>
  <label class="block space-y-2">
    <span class="text-sm text-[var(--color-text-muted)]">{{ label }}</span>
    <input class="w-full rounded-xl border border-[var(--color-border)] bg-transparent px-4 py-3" />
  </label>
</template>

<script setup>
defineProps({ label: { type: String, default: '' } });
</script>