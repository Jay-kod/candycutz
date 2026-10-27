<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md"
  >
    <div class="relative w-full max-w-md rounded-2xl border border-white/10 bg-[#0B0F13] p-6 shadow-2xl space-y-4">
      <div class="flex items-center gap-3">
        <div class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400">
          <TrashIcon class="w-5 h-5" />
        </div>
        <div>
          <h3 class="text-base font-bold text-white">Prune Gate Telemetry</h3>
          <p class="text-xs text-white/50">Clean up historical API and DB conversation logs.</p>
        </div>
      </div>

      <p class="text-xs text-white/70 leading-relaxed">
        Select the retention period. Logs older than the chosen number of days will be permanently removed from the database to keep storage lean and telemetry fast.
      </p>

      <div class="space-y-1.5">
        <label class="text-xs font-semibold text-white/80">Retain Logs For:</label>
        <select
          :value="days"
          @change="$emit('update:days', Number($event.target.value))"
          class="w-full rounded-xl bg-white/[0.04] border border-white/10 p-2.5 text-xs text-white outline-none focus:border-[#FF6700]/50"
        >
          <option :value="1" class="bg-[#0B0F13]">Older than 24 hours</option>
          <option :value="7" class="bg-[#0B0F13]">Older than 7 days (Recommended)</option>
          <option :value="14" class="bg-[#0B0F13]">Older than 14 days</option>
          <option :value="30" class="bg-[#0B0F13]">Older than 30 days</option>
        </select>
      </div>

      <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-white/10">
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 rounded-xl text-xs font-semibold text-white/60 hover:text-white hover:bg-white/5 transition-colors"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="$emit('confirm')"
          :disabled="isPruning"
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-rose-500 hover:bg-rose-600 shadow-lg shadow-rose-500/20 transition-all disabled:opacity-50"
        >
          <ArrowPathIcon v-if="isPruning" class="w-3.5 h-3.5 animate-spin" />
          <TrashIcon v-else class="w-3.5 h-3.5" />
          <span>{{ isPruning ? 'Pruning...' : 'Confirm & Prune' }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { TrashIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  days: {
    type: Number,
    default: 7
  },
  isPruning: {
    type: Boolean,
    default: false
  }
});

defineEmits(['close', 'confirm', 'update:days']);
</script>
