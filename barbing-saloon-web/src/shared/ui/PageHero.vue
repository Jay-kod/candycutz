<template>
  <section class="page-hero" :class="{ 'page-hero--has-image': Boolean(backgroundImage), 'page-hero--home': pageKey === 'home' }">
    <div
      v-if="backgroundImage"
      class="page-hero__image"
      :style="{
        backgroundImage: `url('${backgroundImage}')`,
      }"
      aria-hidden="true"
    ></div>
    <div v-if="backgroundImage" class="page-hero__overlay" :style="{ opacity: overlayOpacity / 100, background: overlayBackground }" aria-hidden="true"></div>
    <div class="page-hero__gradient" aria-hidden="true"></div>
    <div class="page-hero__content text-center">
      <h1 class="page-hero__title text-center">{{ title }}</h1>
      <p v-if="subtitle" class="page-hero__subtitle text-center">{{ subtitle }}</p>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { getActivePinia } from 'pinia';
import { buildOverlayGradient } from '@/core/utils/heroOverlay';
import { usePublicContentStore } from '@/core/stores/publicContent.store';
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
  overlayColor: { type: String, default: undefined },
  loadSettings: { type: Boolean, default: true },
});

const publicStore = getActivePinia() ? usePublicContentStore() : null;

const resolveInitialSettings = () => {
  if (publicStore) {
    try {
      return publicStore.settings || {};
    } catch {
      return {};
    }
  }
  return {};
};
const settings = ref(resolveInitialSettings());
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
  const fallback = props.pageKey === 'home' ? 45 : 76;
  const raw = props.overlayOpacity ??
    (props.pageKey === 'home' ? (settings.value.hero_overlay_opacity ?? settings.value.page_hero_home_overlay_opacity) : settings.value[`page_hero_${props.pageKey}_overlay_opacity`]) ??
    fallback;
  const value = Number(raw);
  return Number.isFinite(value) ? Math.max(0, Math.min(100, value)) : fallback;
});


const overlayBackground = computed(() => {
  const color = props.overlayColor ??
    (props.pageKey === 'home'
      ? (settings.value.hero_overlay_color ?? settings.value.page_hero_home_overlay_color ?? '#D4AF37')
      : (settings.value[`page_hero_${props.pageKey}_overlay_color`] ?? '#111827'));
  return buildOverlayGradient(color);
});

onMounted(async () => {
  if (!props.loadSettings) return;
  try {
    if (publicStore) {
      const data = await publicStore.getSettings();
      if (data) settings.value = data;
    } else {
      const res = await publicApi.settings();
      if (res.data?.data) settings.value = res.data.data;
    }
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
  min-height: 40vh;
  min-height: 40svh;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 5rem 1.25rem 1rem;
  background:
    radial-gradient(ellipse at 50% 45%, rgba(255, 153, 0, 0.08), transparent 58%),
    var(--color-bg-primary);
  text-align: center;
  transition: background-color 300ms ease, color 300ms ease;
}

.page-hero--has-image {
  background: #090909;
}

.page-hero__image,
.page-hero__overlay,
.page-hero__gradient {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.page-hero__image {
  z-index: -3;
  background-position: center;
  background-size: cover;
}

.page-hero__overlay {
  z-index: -2;
  background: linear-gradient(180deg, rgba(9, 9, 9, 0.62), rgba(9, 9, 9, 0.78));
}

.page-hero__gradient {
  z-index: -1;
  background-image:
    radial-gradient(rgba(255, 255, 255, 0.16) 1px, transparent 1px),
    url('/images/hero-pattern.png');
  background-size: 16px 16px, 16px 16px;
  background-repeat: repeat;
  background-position: center;
  opacity: 0.28;
}

:root[data-theme="light"] .page-hero .page-hero__gradient,
.light .page-hero .page-hero__gradient {
  background-image:
    radial-gradient(rgba(0, 0, 0, 0.14) 1px, transparent 1px),
    url('/images/hero-pattern.png');
  filter: invert(1);
  opacity: 0.22;
}

.page-hero__content {
  width: min(100%, 54rem);
  margin: 0 auto;
  text-align: center;
}

.page-hero__title {
  margin: 0 auto;
  color: var(--color-text-primary);
  font-family: var(--font-display, inherit);
  font-size: clamp(2.25rem, 6vw, 4.75rem);
  font-weight: 700;
  line-height: 1.05;
  letter-spacing: -0.035em;
  text-wrap: balance;
  text-align: center;
}

.page-hero--has-image .page-hero__title {
  color: #f5f5f5;
}

.page-hero__subtitle {
  max-width: 44rem;
  margin: 0.8rem auto 0;
  color: var(--color-text-secondary);
  font-size: clamp(0.95rem, 1.5vw, 1.15rem);
  line-height: 1.7;
  text-wrap: balance;
  text-align: center;
}

.page-hero--has-image .page-hero__subtitle {
  color: rgba(245, 245, 245, 0.76);
}

@media (max-width: 640px) {
  .page-hero {
    padding-top: 5rem;
    padding-bottom: 1rem;
  }
}
</style>
