<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 overflow-hidden backdrop-blur-xl shadow-xl space-y-6">
    <!-- Window Bar -->
    <div class="border-b border-white/[0.06] bg-[#0B0F13]/80 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="flex gap-1.5">
          <span class="h-2.5 w-2.5 rounded-full bg-rose-500/70"></span>
          <span class="h-2.5 w-2.5 rounded-full bg-amber-500/70"></span>
          <span class="h-2.5 w-2.5 rounded-full bg-emerald-500/70"></span>
        </div>
        <div class="h-4 w-px bg-white/10 mx-1"></div>
        <div class="flex items-center gap-2">
          <EyeIcon class="h-4 w-4 text-[#FF6700]" />
          <span class="text-xs font-bold text-white">Live Visitor Consent Simulator</span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          class="px-2.5 py-1 text-[11px] font-bold rounded-lg border transition-colors"
          :class="simulationMode === 'pill' ? 'bg-[#FF6700] text-white border-[#FF6700]' : 'bg-white/[0.04] text-white/60 border-white/10 hover:text-white'"
          @click="simulationMode = 'pill'"
        >
          Floating Pill
        </button>
        <button
          type="button"
          class="px-2.5 py-1 text-[11px] font-bold rounded-lg border transition-colors"
          :class="simulationMode === 'modal' ? 'bg-[#FF6700] text-white border-[#FF6700]' : 'bg-white/[0.04] text-white/60 border-white/10 hover:text-white'"
          @click="simulationMode = 'modal'"
        >
          Drawer / Modal
        </button>
      </div>
    </div>

    <!-- Pill Banner Simulation Mode -->
    <div v-if="simulationMode === 'pill'" class="p-8 sm:p-14 max-w-xl mx-auto flex flex-col items-center justify-center space-y-6">
      <p class="text-xs text-white/50 font-mono">Visitor Screen Preview (Bottom Center)</p>
      
      <div class="inline-flex items-center gap-4 sm:gap-5 rounded-full border border-white/10 bg-[#12161C] px-5 py-2.5 text-white shadow-2xl">
        <span class="text-sm font-medium tracking-tight text-white/95 whitespace-nowrap">
          {{ content.banner_text || 'We use cookies.' }}
        </span>

        <div class="flex items-center gap-3.5">
          <button
            type="button"
            class="text-white/70 hover:text-white hover:rotate-45 transition-all p-1"
            title="Open Drawer"
            @click="simulationMode = 'modal'"
          >
            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3" />
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
            </svg>
          </button>
          <button
            type="button"
            class="text-white/70 hover:text-rose-400 hover:scale-110 transition-all p-1"
            title="Reject Non-Essential"
          >
            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 6L6 18M6 6l12 12" />
            </svg>
          </button>
          <button
            type="button"
            class="text-white/90 hover:text-emerald-400 hover:scale-110 transition-all p-1"
            title="Accept All"
          >
            <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4.5 12.75l6 6 9-13.5" />
            </svg>
          </button>
        </div>
      </div>
      <p class="text-[11px] text-white/40">Compact pill displays upon first arrival; clicking the gear opens the detailed drawer.</p>
    </div>

    <!-- Modal Mock Canvas -->
    <div v-else class="p-6 sm:p-10 max-w-2xl mx-auto">
      <div class="rounded-2xl border border-white/10 bg-[#0B0F13] p-6 sm:p-8 shadow-2xl space-y-6">
        <!-- Modal Header -->
        <div class="space-y-2 border-b border-white/[0.06] pb-4">
          <div class="flex items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-[#FF6700]"></span>
            <span class="text-[10px] font-bold uppercase tracking-widest text-[#FF6700]">Privacy Preferences</span>
          </div>
          <h2 class="font-display text-xl sm:text-2xl font-bold text-white">
            {{ content.cookie_title || 'Manage Cookies' }}
          </h2>
          <p class="text-xs text-white/60 leading-relaxed">
            {{ content.cookie_intro || 'Choose which optional cookie categories you want to allow.' }}
          </p>
        </div>

        <!-- Simulated Categories Switches -->
        <div class="space-y-4 divide-y divide-white/[0.04]">
          <div
            v-for="cat in categories"
            :key="cat.key"
            class="pt-4 first:pt-0 flex items-start justify-between gap-4"
          >
            <div class="space-y-1 min-w-0 pr-2">
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-white">
                  {{ content[cat.titleKey] || cat.label }}
                </span>
                <span
                  v-if="cat.required"
                  class="rounded bg-emerald-500/10 border border-emerald-500/20 px-1.5 py-0.2 text-[9px] font-mono font-bold text-emerald-400 uppercase"
                >
                  Required
                </span>
              </div>
              <p class="text-[11px] text-white/50 leading-relaxed">
                {{ content[cat.descriptionKey] || 'Category description' }}
              </p>
            </div>

            <!-- Simulated Toggle Switch -->
            <div class="shrink-0 pt-0.5">
              <button
                type="button"
                @click="toggleCategory(cat.key)"
                :disabled="cat.required"
                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none disabled:cursor-not-allowed disabled:opacity-80"
                :class="simulatedState[cat.key] ? 'bg-[#FF6700]' : 'bg-white/20'"
              >
                <span
                  class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out"
                  :class="simulatedState[cat.key] ? 'translate-x-4' : 'translate-x-0'"
                />
              </button>
            </div>
          </div>
        </div>

        <!-- Simulated Modal Actions -->
        <div class="pt-4 border-t border-white/[0.06] flex flex-col sm:flex-row items-center gap-2.5">
          <button
            type="button"
            class="w-full sm:w-auto flex-1 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-500 py-2.5 px-4 text-xs font-bold text-white shadow-md shadow-[#FF6700]/20"
          >
            Accept All
          </button>
          <button
            type="button"
            class="w-full sm:w-auto rounded-xl border border-white/10 bg-white/[0.04] py-2.5 px-4 text-xs font-semibold text-white/70"
          >
            Reject Non-Essential
          </button>
          <button
            type="button"
            class="w-full sm:w-auto rounded-xl border border-white/10 bg-white/[0.04] py-2.5 px-4 text-xs font-semibold text-white/70"
          >
            Save Choices
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { EyeIcon } from '@heroicons/vue/24/outline';

const simulationMode = ref('pill');

defineProps({
  categories: {
    type: Array,
    required: true
  },
  content: {
    type: Object,
    required: true
  }
});

const simulatedState = reactive({
  necessary: true,
  preferences: true,
  analytics: true,
  marketing: false
});

const toggleCategory = (key) => {
  if (key === 'necessary') return;
  simulatedState[key] = !simulatedState[key];
};
</script>

