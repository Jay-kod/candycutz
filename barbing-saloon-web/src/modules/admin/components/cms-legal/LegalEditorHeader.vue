<template>
  <header class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-2xl">
    <!-- Ambient Glow Accent -->
    <div class="pointer-events-none absolute -top-24 right-1/4 h-56 w-96 rounded-full bg-gradient-to-r from-[#FF6700]/15 via-amber-500/10 to-transparent blur-3xl"></div>

    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
      <!-- Title & Live Meta -->
      <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-2.5">
          <span class="inline-flex items-center gap-1.5 rounded-full border border-[#FF6700]/30 bg-[#FF6700]/10 px-3 py-0.5 text-[11px] font-bold uppercase tracking-widest text-[#FF6700]">
            <span class="h-1.5 w-1.5 rounded-full bg-[#FF6700] animate-ping"></span>
            Legal &amp; Compliance CMS
          </span>

          <span
            v-if="hasChanges"
            class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-amber-400"
          >
            <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
            Unsaved Changes
          </span>

          <span class="inline-flex items-center gap-1 rounded-full border border-white/[0.08] bg-white/[0.03] px-2.5 py-0.5 text-[11px] font-medium text-white/60">
            <DocumentTextIcon class="h-3 w-3 text-[#FF6700]" />
            {{ sectionCount }} {{ sectionCount === 1 ? 'Legal Section' : 'Legal Sections' }}
          </span>
        </div>

        <div>
          <h1 class="font-display text-2xl font-bold tracking-tight text-white sm:text-3xl">
            {{ pageTitle }} <span class="bg-gradient-to-r from-[#FF6700] via-amber-400 to-amber-200 bg-clip-text text-transparent">Editor</span>
          </h1>
          <p class="mt-1 text-xs sm:text-sm text-white/50 max-w-xl">
            {{ description }}
          </p>
        </div>
      </div>

      <!-- Header Actions & View Mode Toggle -->
      <div class="flex flex-wrap items-center gap-2.5">
        <!-- View Mode Switcher -->
        <div class="flex items-center rounded-xl border border-white/10 bg-white/[0.04] p-0.5 mr-1">
          <button
            type="button"
            @click="$emit('update:activeView', 'editor')"
            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors flex items-center gap-1.5"
            :class="activeView === 'editor' ? 'bg-[#FF6700] text-white shadow-sm' : 'text-white/50 hover:text-white'"
          >
            <PencilSquareIcon class="h-3.5 w-3.5" />
            <span>Editor</span>
          </button>
          <button
            type="button"
            @click="$emit('update:activeView', 'preview')"
            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors flex items-center gap-1.5"
            :class="activeView === 'preview' ? 'bg-[#FF6700] text-white shadow-sm' : 'text-white/50 hover:text-white'"
          >
            <EyeIcon class="h-3.5 w-3.5" />
            <span>Live Preview</span>
          </button>
        </div>

        <!-- Reset Defaults -->
        <button
          type="button"
          @click="$emit('reset-defaults')"
          :disabled="saving"
          class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/[0.04] px-3.5 py-2 text-xs font-semibold text-white/70 hover:bg-white/[0.08] hover:text-white transition-colors disabled:opacity-50"
          title="Reset to factory legal template"
        >
          <ArrowPathIcon class="h-4 w-4" />
          <span>Reset</span>
        </button>

        <!-- Reload -->
        <button
          type="button"
          @click="$emit('refresh')"
          :disabled="loading || saving"
          class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/[0.04] px-3.5 py-2 text-xs font-semibold text-white/70 hover:bg-white/[0.08] hover:text-white transition-colors disabled:opacity-50"
          title="Reload document from server"
        >
          <ArrowPathIcon class="h-4 w-4" :class="{ 'animate-spin text-[#FF6700]': loading }" />
          <span class="hidden sm:inline">Reload</span>
        </button>

        <!-- Save Button -->
        <button
          type="button"
          @click="$emit('save')"
          :disabled="loading || saving || !hasChanges"
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-500 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-[#FF6700]/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-[#FF6700]/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
          <CheckIcon v-else class="h-4 w-4" />
          <span>{{ saving ? 'Saving Document...' : 'Save Document' }}</span>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import {
  DocumentTextIcon,
  CheckIcon,
  ArrowPathIcon,
  PencilSquareIcon,
  EyeIcon
} from '@heroicons/vue/24/outline';

defineProps({
  pageTitle: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  sectionCount: {
    type: Number,
    required: true
  },
  activeView: {
    type: String,
    default: 'editor'
  },
  hasChanges: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  saving: {
    type: Boolean,
    default: false
  }
});

defineEmits(['save', 'refresh', 'reset-defaults', 'update:activeView']);
</script>
