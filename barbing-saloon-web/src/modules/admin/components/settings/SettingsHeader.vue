<script setup>
import {
  AdjustmentsHorizontalIcon,
  CheckIcon,
  ArrowUturnLeftIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  saving: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  isDirty: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['save', 'revert', 'refresh'])
</script>

<template>
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <!-- Title & Brand Accent -->
    <div class="flex items-center gap-3">
      <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#FF6700]/20 via-amber-500/10 to-transparent border border-[#FF6700]/30 flex items-center justify-center shadow-lg shadow-[#FF6700]/10">
        <AdjustmentsHorizontalIcon class="w-6 h-6 text-[#FF6700]" />
      </div>
      <div>
        <div class="flex items-center gap-2.5">
          <h1 class="text-2xl font-bold tracking-tight text-white">System Configuration</h1>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-[#FF6700]/10 text-[#FF6700] border border-[#FF6700]/25">
            Master Registry
          </span>
          <span
            v-if="isDirty"
            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/30 animate-pulse"
          >
            Unsaved Edits
          </span>
        </div>
        <p class="text-xs text-zinc-400 mt-0.5">Platform parameters, appointment rules, localized contact & brand metadata</p>
      </div>
    </div>

    <!-- Actions Bar -->
    <div class="flex items-center flex-wrap gap-2.5">
      <button
        type="button"
        @click="$emit('refresh')"
        :disabled="loading || saving"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#12161C] border border-white/[0.08] text-zinc-300 hover:text-white hover:bg-white/[0.06] hover:border-white/[0.16] transition-all text-xs font-semibold disabled:opacity-50 shadow-sm"
        title="Reload Configuration from Server"
      >
        <ArrowPathIcon class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
        <span class="hidden sm:inline">Synchronize</span>
      </button>

      <button
        v-if="isDirty"
        type="button"
        @click="$emit('revert')"
        :disabled="saving"
        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/[0.03] border border-white/[0.08] text-zinc-400 hover:text-white hover:bg-white/[0.06] transition-all text-xs font-semibold"
      >
        <ArrowUturnLeftIcon class="w-3.5 h-3.5" />
        <span>Discard Changes</span>
      </button>

      <button
        type="button"
        @click="$emit('save')"
        :disabled="saving || loading || !isDirty"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-600 text-white text-xs font-semibold hover:brightness-110 transition-all shadow-lg shadow-[#FF6700]/20 disabled:opacity-40 disabled:cursor-not-allowed"
      >
        <ArrowPathIcon v-if="saving" class="w-3.5 h-3.5 animate-spin" />
        <CheckIcon v-else class="w-4 h-4" />
        <span>{{ saving ? 'Persisting Changes…' : 'Save Configuration' }}</span>
      </button>
    </div>
  </div>
</template>

