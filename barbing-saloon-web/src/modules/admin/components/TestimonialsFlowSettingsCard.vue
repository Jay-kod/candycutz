<template>
  <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-theme-surface/90 p-6 md:p-8 backdrop-blur-md shadow-xl transition-all duration-300">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-theme-border">
      <div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-admin/20 border border-admin/30 text-admin-light text-[11px] font-bold uppercase tracking-wider">
            ⚡ Website Flow Motion
          </span>
        </div>
        <h2 class="mt-2 font-display text-2xl text-theme-text flex items-center gap-2">
          Marquee <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Speed & Direction</span>
        </h2>
        <p class="text-xs sm:text-sm text-theme-text-muted mt-1 max-w-xl">
          Control how customer reviews drift across the homepage ribbon. Adjust the speed and direction, preview live, and save.
        </p>
      </div>

      <div class="flex items-center gap-2.5 self-start sm:self-auto">
        <button
          type="button"
          @click="resetToDefaults"
          :disabled="isSaving"
          class="rounded-xl border border-theme-border bg-theme-surface-subtle px-4 py-2 text-xs font-semibold text-theme-text-muted hover:bg-theme-surface hover:text-theme-text transition-all disabled:opacity-50"
        >
          Reset Defaults
        </button>
        <button
          type="button"
          @click="saveFlowSettings"
          :disabled="isSaving"
          class="flex items-center gap-2 rounded-xl bg-admin px-5 py-2 text-xs font-bold text-obsidian shadow-lg shadow-admin/20 hover:bg-admin-light transition-all active:scale-[0.98] disabled:opacity-50"
        >
          <span v-if="isSaving" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-obsidian border-t-transparent"></span>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
          {{ isSaving ? 'Saving...' : 'Save Flow Settings' }}
        </button>
      </div>
    </div>

    <!-- Controls Grid -->
    <div class="grid gap-6 md:grid-cols-2 pt-6">
      <!-- 1. Direction Control -->
      <div class="space-y-3">
        <label class="text-xs font-bold uppercase tracking-wider text-admin-light block">
          Flow Direction
        </label>
        <div class="grid grid-cols-2 gap-3">
          <button
            type="button"
            @click="flowDirection = 'left'"
            class="flex flex-col items-center justify-center gap-2 rounded-2xl border p-4 text-center transition-all duration-200"
            :class="flowDirection === 'left'
              ? 'border-admin bg-admin/15 text-admin-light shadow-md shadow-admin/10 ring-1 ring-admin/50'
              : 'border-theme-border bg-theme-surface-subtle text-theme-text-muted hover:border-theme-border/80 hover:text-theme-text'"
          >
            <div class="flex items-center justify-center size-9 rounded-xl bg-theme-surface border border-theme-border text-lg">
              ←
            </div>
            <div>
              <p class="text-sm font-bold">To the Left</p>
              <p class="text-[11px] opacity-70 mt-0.5">Standard continuous drift</p>
            </div>
          </button>

          <button
            type="button"
            @click="flowDirection = 'right'"
            class="flex flex-col items-center justify-center gap-2 rounded-2xl border p-4 text-center transition-all duration-200"
            :class="flowDirection === 'right'
              ? 'border-admin bg-admin/15 text-admin-light shadow-md shadow-admin/10 ring-1 ring-admin/50'
              : 'border-theme-border bg-theme-surface-subtle text-theme-text-muted hover:border-theme-border/80 hover:text-theme-text'"
          >
            <div class="flex items-center justify-center size-9 rounded-xl bg-theme-surface border border-theme-border text-lg">
              →
            </div>
            <div>
              <p class="text-sm font-bold">To the Right</p>
              <p class="text-[11px] opacity-70 mt-0.5">Reverse continuous drift</p>
            </div>
          </button>
        </div>
      </div>

      <!-- 2. Speed Control -->
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold uppercase tracking-wider text-admin-light">
            Flow Speed
          </label>
          <span class="rounded-full bg-admin/20 border border-admin/30 px-2.5 py-0.5 text-xs font-mono font-bold text-admin-light">
            {{ flowSpeed }}s · {{ getSpeedLabel(flowSpeed) }}
          </span>
        </div>

        <!-- Slider -->
        <div class="pt-2">
          <input
            type="range"
            min="15"
            max="90"
            step="5"
            v-model.number="flowSpeed"
            class="w-full accent-[#FF6700] h-2 bg-theme-border rounded-lg cursor-pointer"
          />
          <div class="flex justify-between text-[11px] text-theme-text-muted/60 mt-1 font-mono">
            <span>Fast (15s)</span>
            <span>Balanced (45s)</span>
            <span>Gentle (90s)</span>
          </div>
        </div>

        <!-- Preset Pills -->
        <div class="flex items-center gap-2 flex-wrap pt-1">
          <button
            v-for="preset in speedPresets"
            :key="preset.value"
            type="button"
            @click="flowSpeed = preset.value"
            class="rounded-lg border px-2.5 py-1 text-xs font-medium transition-all"
            :class="flowSpeed === preset.value
              ? 'border-admin bg-admin text-obsidian font-bold'
              : 'border-theme-border bg-theme-surface-subtle text-theme-text-muted hover:border-theme-border/80 hover:text-theme-text'"
          >
            {{ preset.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- 3. Live Interactive Mini-Preview Strip -->
    <div class="mt-6 pt-5 border-t border-theme-border">
      <div class="flex items-center justify-between mb-2.5">
        <span class="text-xs font-bold uppercase tracking-wider text-theme-text-muted flex items-center gap-2">
          <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
          Live Admin Preview (Hover to pause)
        </span>
        <span class="text-[11px] text-theme-text-muted">
          Direction: <strong class="text-theme-text">{{ flowDirection === 'left' ? '← Left' : '→ Right' }}</strong> | Speed: <strong class="text-theme-text">{{ flowSpeed }}s</strong>
        </span>
      </div>

      <TestimonialsFlowPreview
        :cards="previewCards"
        :speed="flowSpeed"
        :direction="flowDirection"
      />
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { adminApi } from '@/shared/api/old_adminApi';
import { useToast } from '../../../core/composables/useToast';
import TestimonialsFlowPreview from './TestimonialsFlowPreview.vue';

const toast = useToast();

const flowSpeed = ref(45);
const flowDirection = ref('left');
const isSaving = ref(false);

const speedPresets = [
  { label: 'Fast (20s)', value: 20 },
  { label: 'Normal (40s)', value: 40 },
  { label: 'Slow (60s)', value: 60 },
  { label: 'Gentle (80s)', value: 80 },
];

const previewCards = [
  {
    author: 'Guillermo Rauch',
    role: 'CEO, Vercel',
    source: 'x.com',
    text: 'CandyCutz is why I still have a fresh look. Flawless fades and precision line-up every time.',
  },
  {
    author: 'Ibrahim Suleiman',
    role: 'Verified Client',
    source: 'x.com',
    text: 'The fade was clean, precise, and exactly the style I asked for. Best barbershop in Keffi.',
  },
  {
    author: 'Elon Musk',
    role: 'CEO, X.com',
    source: 'x.com',
    text: 'Ultra-clean cuts with incredible attention to detail. Fast booking and razor-sharp styling.',
  },
  {
    author: 'Tim Cook',
    role: 'CEO, Apple',
    source: 'x.com',
    text: 'Clean lines, immaculate craft, and consistent excellence from start to finish.',
  },
];

const getSpeedLabel = (seconds) => {
  if (seconds <= 25) return 'Fast Drift';
  if (seconds <= 50) return 'Normal Flow';
  if (seconds <= 70) return 'Relaxed Pace';
  return 'Slow Drift';
};

const resetToDefaults = () => {
  flowSpeed.value = 45;
  flowDirection.value = 'left';
};

const loadSettings = async () => {
  try {
    const res = await adminApi.settings();
    const data = res.data?.data || {};
    if (data.testimonials_flow_speed) {
      const parsed = Number(data.testimonials_flow_speed);
      if (Number.isFinite(parsed) && parsed >= 10 && parsed <= 120) {
        flowSpeed.value = parsed;
      }
    }
    if (data.testimonials_flow_direction) {
      const dir = String(data.testimonials_flow_direction).toLowerCase();
      if (dir === 'left' || dir === 'right') {
        flowDirection.value = dir;
      }
    }
  } catch (err) {
    console.error('Failed to load testimonial flow settings:', err);
  }
};

const saveFlowSettings = async () => {
  isSaving.value = true;
  try {
    await adminApi.updateSettings({
      settings: {
        testimonials_flow_speed: flowSpeed.value,
        testimonials_flow_direction: flowDirection.value,
      },
    });
    toast.success('Testimonial marquee flow settings saved!');
  } catch (err) {
    console.error('Failed to save testimonial flow settings:', err);
    toast.error('Failed to save settings. Please try again.');
  } finally {
    isSaving.value = false;
  }
};

onMounted(() => {
  loadSettings();
});
</script>
