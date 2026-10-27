<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-xl space-y-6">
    <!-- Header with Theme Preview Toggle -->
    <div class="flex items-center justify-between border-b border-white/[0.06] pb-4">
      <div>
        <h3 class="text-base font-bold text-white">Live Experience Preview</h3>
        <p class="mt-0.5 text-xs text-white/50">Real-time simulation across visitor viewpoints.</p>
      </div>

      <!-- Dark / Light Viewport Toggle -->
      <div class="flex items-center rounded-xl border border-white/10 bg-white/[0.04] p-0.5">
        <button
          type="button"
          @click="previewTheme = 'dark'"
          class="rounded-lg px-2.5 py-1 text-[11px] font-semibold transition-colors flex items-center gap-1.5"
          :class="previewTheme === 'dark' ? 'bg-[#FF6700] text-white shadow-sm' : 'text-white/50 hover:text-white'"
        >
          <MoonIcon class="h-3 w-3" />
          <span>Obsidian</span>
        </button>
        <button
          type="button"
          @click="previewTheme = 'light'"
          class="rounded-lg px-2.5 py-1 text-[11px] font-semibold transition-colors flex items-center gap-1.5"
          :class="previewTheme === 'light' ? 'bg-white text-zinc-900 shadow-sm' : 'text-white/50 hover:text-white'"
        >
          <SunIcon class="h-3 w-3" />
          <span>Ivory</span>
        </button>
      </div>
    </div>

    <!-- Preview Container with Dynamic Dark/Light Canvas -->
    <div
      class="rounded-2xl border transition-all duration-300 p-5 sm:p-6 space-y-6"
      :class="[
        previewTheme === 'dark'
          ? 'border-white/10 bg-[#0B0F13] text-white'
          : 'border-zinc-200 bg-zinc-50 text-zinc-900 [data-theme=light]'
      ]"
    >
      <!-- Simulation 1: Main Navigation Header Mock -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <span class="text-[10px] font-bold uppercase tracking-wider" :class="previewTheme === 'dark' ? 'text-white/40' : 'text-zinc-400'">
            1. Desktop Navigation Header
          </span>
          <div class="flex gap-1">
            <span class="h-2 w-2 rounded-full bg-rose-500/70"></span>
            <span class="h-2 w-2 rounded-full bg-amber-500/70"></span>
            <span class="h-2 w-2 rounded-full bg-emerald-500/70"></span>
          </div>
        </div>

        <div
          class="rounded-xl border p-3 sm:p-4 flex items-center justify-between"
          :class="previewTheme === 'dark' ? 'border-white/[0.08] bg-[#12161C]/80' : 'border-zinc-200 bg-white shadow-sm'"
        >
          <div class="flex items-center gap-3">
            <div class="h-7 w-7 rounded-lg bg-gradient-to-br from-[#FF6700] to-amber-500 flex items-center justify-center font-bold text-white text-xs shadow-md">
              {{ (form.first || 'C')[0] }}
            </div>
            <div class="font-display text-lg font-bold">
              <BrandWordmark
                :first="displayFirst"
                :second="displaySecond"
                :gold-first="form.goldPart === 'first'"
                :space-between="form.separator === 'space'"
              />
            </div>
          </div>

          <div class="hidden sm:flex items-center gap-4 text-xs font-semibold" :class="previewTheme === 'dark' ? 'text-white/60' : 'text-zinc-600'">
            <span>Services</span>
            <span>Barbers</span>
            <span class="rounded-lg bg-[#FF6700] px-2.5 py-1 text-white text-[11px] font-bold">Book Cut</span>
          </div>
        </div>
      </div>

      <!-- Simulation 2: Executive Footer Mock -->
      <div class="space-y-2">
        <span class="text-[10px] font-bold uppercase tracking-wider" :class="previewTheme === 'dark' ? 'text-white/40' : 'text-zinc-400'">
          2. Website Footer &amp; Business Summary
        </span>

        <div
          class="rounded-xl border p-5 space-y-3"
          :class="previewTheme === 'dark' ? 'border-white/[0.08] bg-[#12161C]/80' : 'border-zinc-200 bg-white shadow-sm'"
        >
          <div class="font-display text-xl font-bold">
            <BrandWordmark
              :first="displayFirst"
              :second="displaySecond"
              :gold-first="form.goldPart === 'first'"
              :space-between="form.separator === 'space'"
            />
          </div>

          <p class="text-xs leading-relaxed max-w-md" :class="previewTheme === 'dark' ? 'text-white/60' : 'text-zinc-600'">
            {{ form.tagline.trim() || 'A sharper standard in executive grooming. Premium precision cuts, tailored beard artistry, and royal styling.' }}
          </p>

          <div class="pt-3 border-t flex items-center justify-between text-[10px]" :class="previewTheme === 'dark' ? 'border-white/[0.06] text-white/40' : 'border-zinc-100 text-zinc-400'">
            <span>&copy; {{ new Date().getFullYear() }} {{ displayFullName }}. All rights reserved.</span>
            <span class="font-mono">v1.0.0</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Preservation Notice -->
    <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4 text-[11px] text-white/50 leading-relaxed">
      <strong class="text-white/80 font-semibold">Brand Assets Notice:</strong>
      Uploaded logo vectors, icons, and hero photography are preserved intact. Changing the typography parameters above updates the dynamic wordmark and legal identity across public pages, booking confirmation modals, and system notifications.
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { MoonIcon, SunIcon } from '@heroicons/vue/24/outline';
import BrandWordmark from '@/shared/ui/BrandWordmark.vue';

const props = defineProps({
  form: {
    type: Object,
    required: true
  }
});

const previewTheme = ref('dark');

const displayFirst = computed(() => {
  return props.form.first?.trim() || 'Candy';
});

const displaySecond = computed(() => {
  return props.form.second?.trim() || 'Cutz';
});

const displayFullName = computed(() => {
  const sep = props.form.separator === 'space' ? ' ' : '';
  return `${displayFirst.value}${sep}${displaySecond.value}`;
});
</script>

