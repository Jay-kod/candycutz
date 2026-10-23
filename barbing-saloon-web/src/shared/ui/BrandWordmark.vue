<template>
  <span class="brand-wordmark" aria-label="Shop name">
    <span :class="goldFirst ? 'brand-wordmark__gold' : 'brand-wordmark__white'">{{ first }}</span>
    <span v-if="spaceBetween" aria-hidden="true">&nbsp;</span>
    <span :class="goldFirst ? 'brand-wordmark__white' : 'brand-wordmark__gold'">{{ second }}</span>
  </span>
</template>

<script setup>
defineProps({
  first: { type: String, default: 'Candy' },
  second: { type: String, default: 'Cutz' },
  spaceBetween: { type: Boolean, default: false },
  goldFirst: { type: Boolean, default: false },
});
</script>

<style scoped>
.brand-wordmark {
  display: inline-flex;
  align-items: baseline;
}

.brand-wordmark__white {
  color: #fff;
}

.brand-wordmark__gold {
  color: #ff9900;
}
</style>
