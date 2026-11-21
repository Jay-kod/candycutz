<script setup>
import {
  ServerStackIcon,
  CloudArrowUpIcon,
  PlusIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['refresh', 'openCreate', 'openUpload'])
</script>

<template>
  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <!-- Title & Brand Accent -->
    <div class="flex items-center gap-3">
      <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500/20 via-indigo-500/10 to-transparent border border-blue-500/30 flex items-center justify-center shadow-lg shadow-blue-500/10">
        <ServerStackIcon class="w-6 h-6 text-blue-400" />
      </div>
      <div>
        <div class="flex items-center gap-2.5">
          <h1 class="text-2xl font-bold tracking-tight text-white">Disaster Recovery & Snapshots</h1>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-blue-500/10 text-blue-400 border border-blue-500/25">
            Redundancy Core
          </span>
        </div>
        <p class="text-xs text-zinc-400 mt-0.5">Point-in-time database snapshots, verification hashes, and disaster recovery</p>
      </div>
    </div>

    <!-- Actions Bar -->
    <div class="flex items-center flex-wrap gap-2.5">
      <button
        type="button"
        @click="$emit('refresh')"
        :disabled="loading"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#12161C] border border-white/[0.08] text-zinc-300 hover:text-white hover:bg-white/[0.06] hover:border-white/[0.16] transition-all text-xs font-semibold disabled:opacity-50 shadow-sm"
      >
        <ArrowPathIcon class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" />
        <span class="hidden sm:inline">Sync Registry</span>
      </button>

      <button
        type="button"
        @click="$emit('openUpload')"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#12161C] border border-white/[0.08] text-zinc-200 hover:text-white hover:bg-white/[0.06] hover:border-white/[0.16] transition-all text-xs font-semibold shadow-sm"
      >
        <CloudArrowUpIcon class="w-4 h-4 text-indigo-400" />
        <span>Upload Archive</span>
      </button>

      <button
        type="button"
        @click="$emit('openCreate')"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-600 text-white text-xs font-semibold hover:brightness-110 transition-all shadow-lg shadow-[#FF6700]/20"
      >
        <PlusIcon class="w-4 h-4" />
        <span>Generate Snapshot</span>
      </button>
    </div>
  </div>
</template>

