<template>
  <div class="ios-grouped-section my-4 font-sans">
    <div v-if="header" class="px-4 pb-1.5 text-[12px] font-semibold uppercase tracking-wider text-theme-muted">
      {{ header }}
    </div>
    <div class="ios-grouped-card overflow-hidden divide-y divide-theme-border/30 bg-theme-surface border border-theme-border/50 rounded-2xl shadow-sm">
      <slot />
    </div>
    <div v-if="footer" class="px-4 pt-1.5 text-[12px] text-theme-muted">
      {{ footer }}
    </div>
  </div>
</template>

<script setup>
defineProps({
  header: {
    type: String,
    default: '',
  },
  footer: {
    type: String,
    default: '',
  },
});
</script>
