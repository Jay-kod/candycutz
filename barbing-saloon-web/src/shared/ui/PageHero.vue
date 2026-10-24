<template>
  <section class="page-hero">
    <div
      v-if="backgroundImage"
      class="page-hero__image"
      :style="{
        backgroundImage: `url('${backgroundImage}')`,
      }"
      aria-hidden="true"
    ></div>
    <div class="page-hero__overlay" :style="{ opacity: overlayOpacity / 100 }" aria-hidden="true"></div>
    <div class="page-hero__content text-center">
      <p v-if="eyebrow" class="page-hero__eyebrow text-center">{{ eyebrow }}</p>
      <h1 class="page-hero__title text-center">{{ title }}</h1>
      <p v-if="subtitle" class="page-hero__subtitle text-center">{{ subtitle }}</p>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { publicApi } from '@/shared/api/old_publicApi';
import { getStorageUrl } from '@/core/utils/url';

const props = defineProps({
  pageKey: { type: String, required: true },
  eyebrow: { type: String, default: '' },
  fallbackTitle: { type: String, required: true },
  fallbackSubtitle: { type: String, default: '' },
  titleSettingKey: { type: String, default: '' },
  subtitleSettingKey: { type: String, default: '' },
  backgroundImage: { type: String, default: undefined },
  overlayOpacity: { type: Number, default: undefined },
  loadSettings: { type: Boolean, default: true },
});

const settings = ref({});
const savedValue = (key, fallback) => (
  settings.value[key] !== undefined && settings.value[key] !== null
    ? settings.value[key]
    : fallback
);

const title = computed(() => savedValue(
  `page_hero_${props.pageKey}_title`,
  props.titleSettingKey ? savedValue(props.titleSettingKey, props.fallbackTitle) : props.fallbackTitle,
));
const subtitle = computed(() => savedValue(
  `page_hero_${props.pageKey}_subtitle`,
  props.subtitleSettingKey ? savedValue(props.subtitleSettingKey, props.fallbackSubtitle) : props.fallbackSubtitle,
));
const imageKey = computed(() => (
  props.pageKey === 'home' ? 'hero_image' : `page_hero_${props.pageKey}_image`
));
const backgroundImage = computed(() => (
  props.backgroundImage !== undefined
    ? props.backgroundImage
    : settings.value[imageKey.value] ? getStorageUrl(settings.value[imageKey.value]) : ''
));
const overlayOpacity = computed(() => {
  const value = Number(props.overlayOpacity ?? settings.value[`page_hero_${props.pageKey}_overlay_opacity`] ?? 76);
  return Number.isFinite(value) ? Math.max(0, Math.min(100, value)) : 76;
});

onMounted(async () => {
  if (!props.loadSettings) return;
  try {
    const response = await publicApi.settings();
    settings.value = response.data?.data || {};
  } catch (error) {
    console.error(`Failed to load ${props.pageKey} hero settings:`, error);
  }
});
</script>

<style scoped>
.page-hero {
  position: relative;
  isolation: isolate;
  display: flex;
  min-height: 30vh;
  min-height: 30svh;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 7.5rem 1.25rem 3.5rem;
  background:
    radial-gradient(ellipse at 50% 45%, rgba(255, 153, 0, 0.1), transparent 58%),
    #090909;
  text-align: center;
}

.page-hero__image,
.page-hero__overlay {
  position: absolute;
  z-index: -2;
  inset: 0;
  pointer-events: none;
}

.page-hero__image {
  background-position: center;
  background-size: cover;
}

.page-hero__overlay {
  z-index: -1;
  background: linear-gradient(180deg, rgba(9, 9, 9, 0.62), rgba(9, 9, 9, 0.78));
}

.page-hero__content {
  width: min(100%, 54rem);
  margin: 0 auto;
  text-align: center;
}

.page-hero__eyebrow {
  margin: 0 auto;
  color: #ffb84d;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.24em;
  text-transform: uppercase;
  text-align: center;
}

.page-hero__title {
  margin: 0.85rem auto 0;
  color: #f5f5f5;
  font-family: var(--font-display, inherit);
  font-size: clamp(2.25rem, 6vw, 4.75rem);
  font-weight: 700;
  line-height: 1.05;
  letter-spacing: -0.035em;
  text-wrap: balance;
  text-align: center;
}

.page-hero__subtitle {
  max-width: 44rem;
  margin: 1.25rem auto 0;
  color: rgba(245, 245, 245, 0.76);
  font-size: clamp(0.95rem, 1.5vw, 1.15rem);
  line-height: 1.7;
  text-wrap: balance;
  text-align: center;
}

@media (max-width: 640px) {
  .page-hero {
    padding-top: 6.5rem;
    padding-bottom: 2.5rem;
  }
}
</style>
