<script setup>
import {
  ShieldExclamationIcon,
  ExclamationTriangleIcon,
  XMarkIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  restoring: {
    type: Boolean,
    default: false,
  },
  selectedBackup: {
    type: Object,
    default: null,
  },
  formatTime: {
    type: Function,
    required: true,
  },
})

defineEmits(['close', 'restore'])
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div
        class="fixed inset-0 bg-black/80 backdrop-blur-md transition-opacity"
        @click="$emit('close')"
      ></div>

      <!-- Modal Content -->
      <div class="relative w-full max-w-md bg-[#0E1217] border border-rose-500/30 rounded-2xl p-6 shadow-2xl z-10 space-y-5 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center">
              <ShieldExclamationIcon class="w-5 h-5 text-rose-400" />
            </div>
            <div>
              <h3 class="text-base font-bold text-white tracking-tight">Confirm Database Restore</h3>
              <p class="text-xs text-rose-400 font-semibold">Irreversible Destructive Operation</p>
            </div>
          </div>
          <button
            type="button"
            @click="$emit('close')"
            class="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-white/[0.06] transition-colors"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Warning Alert Body -->
        <div class="rounded-xl bg-rose-500/10 border border-rose-500/25 p-4">
          <div class="flex items-start gap-3">
            <ExclamationTriangleIcon class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" />
            <div class="text-xs text-zinc-300 space-y-2">
              <p>
                <strong class="text-rose-400">Critical Warning:</strong> Restoring this snapshot will <strong>completely overwrite all current tables and active data</strong> in the production relational database.
              </p>
              <div class="space-y-1 font-mono text-[11px] pt-1">
                <p>Target: <span class="text-white font-bold">{{ selectedBackup?.filename }}</span></p>
                <p>Size: <span class="text-zinc-200">{{ selectedBackup?.size_formatted }}</span></p>
                <p>Snapshot Date: <span class="text-zinc-200">{{ formatTime(selectedBackup?.created_at) }}</span></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-3 pt-1">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-zinc-400 text-xs font-semibold hover:text-white hover:bg-white/[0.06] transition-all"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="$emit('restore')"
            :disabled="restoring"
            class="flex-1 py-2.5 rounded-xl bg-rose-600/25 border border-rose-500/40 text-rose-300 text-xs font-bold hover:bg-rose-600/40 transition-all shadow-lg shadow-rose-600/20 disabled:opacity-50 flex items-center justify-center gap-2"
          >
            <ArrowPathIcon v-if="restoring" class="w-4 h-4 animate-spin" />
            <span>{{ restoring ? 'Restoring Database…' : 'Confirm & Restore' }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

