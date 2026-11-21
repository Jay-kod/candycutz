<template>
  <div class="relative z-10 flex h-20 shrink-0 items-center justify-between px-5 border-b border-[var(--color-border-subtle)] dark:border-white/[0.08]">
    <RouterLink :to="homeRoute" class="flex items-center gap-3 group min-w-0">
      <!-- Logo Emblem with Luxury Rim -->
      <div class="relative flex items-center justify-center shrink-0">
        <div :class="['h-11 w-11 rounded-2xl p-[1px] bg-gradient-to-br flex items-center justify-center group-hover:scale-105 transition-transform duration-300', portalConfig.rimGradient]">
          <div class="h-full w-full rounded-[15px] bg-[var(--color-bg-secondary)] dark:bg-[#121824] flex items-center justify-center overflow-hidden">
            <img :src="getStorageUrl('/images/logo-icon.png')" :alt="`${brandName} logo`" class="h-6 w-6 object-contain" />
          </div>
        </div>
      </div>

      <!-- Brand Typography & Portal Badge -->
      <div
        :class="[
          'min-w-0 transition-all duration-300',
          isCollapsed ? 'lg:opacity-0 lg:w-0 lg:hidden' : 'opacity-100'
        ]"
      >
        <div class="font-display text-xl font-bold tracking-tight leading-none text-[var(--color-text-primary)]">
          <BrandWordmark
            :first="brandNameFirst"
            :second="brandNameSecond"
            :gold-first="brandGoldPart === 'first'"
            :space-between="brandNameSeparator === 'space'"
          />
        </div>
        <div :class="['inline-flex items-center gap-1.5 mt-1.5 px-2 py-0.5 rounded-full text-[9px] font-semibold tracking-wider uppercase border', portalConfig.roleBadge]">
          <span :class="['h-1.5 w-1.5 rounded-full', portalConfig.dotClass]"></span>
          {{ portalConfig.title }}
        </div>
      </div>
    </RouterLink>

    <!-- Mobile Close Button -->
    <button
      @click="$emit('close-mobile')"
      class="lg:hidden p-2 transition-colors rounded-xl text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-bg-tertiary)] dark:hover:bg-white/[0.06]"
      aria-label="Close sidebar"
    >
      <XMarkIcon class="h-5 w-5" />
    </button>
  </div>
</template>

<script setup>
import { getStorageUrl } from '@/core/utils/url';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { useBrandIdentity } from '@/shared/composables/useBrandIdentity';
import BrandWordmark from '@/shared/ui/BrandWordmark.vue';

const { brandName, brandNameFirst, brandNameSecond, brandNameSeparator, brandGoldPart } = useBrandIdentity();

defineProps({
  homeRoute: { type: String, required: true },
  portalConfig: { type: Object, required: true },
  isCollapsed: { type: Boolean, default: false },
});

defineEmits(['close-mobile']);
</script>

