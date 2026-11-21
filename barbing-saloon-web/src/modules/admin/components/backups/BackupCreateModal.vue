<script setup>
import {
  XMarkIcon,
  PlusIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  creating: {
    type: Boolean,
    default: false,
  },
  modelValue: {
    type: String,
    default: '',
  },
})

defineEmits(['update:modelValue', 'close', 'create'])
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <!-- Backdrop -->
      <div
        class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity"
        @click="$emit('close')"
      ></div>

      <!-- Modal Content -->
      <div class="relative w-full max-w-md bg-[#0E1217] border border-white/[0.08] rounded-2xl p-6 shadow-2xl z-10 space-y-5 animate-fade-in">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#FF6700]/10 border border-[#FF6700]/25 flex items-center justify-center">
              <PlusIcon class="w-5 h-5 text-[#FF6700]" />
            </div>
            <div>
              <h3 class="text-base font-bold text-white tracking-tight">Generate Database Snapshot</h3>
              <p class="text-xs text-zinc-400">Trigger full mysqldump archive of all tables</p>
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

        <!-- Form Body -->
        <div class="space-y-4">
          <div>
            <label class="text-xs font-semibold text-zinc-300 block mb-1.5">
              Custom Snapshot Tag / Filename (optional)
            </label>
            <input
              :value="modelValue"
              @input="$emit('update:modelValue', $event.target.value)"
              type="text"
              placeholder="e.g. pre-migration-release-v2"
              class="w-full px-4 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white text-xs font-mono placeholder-zinc-500 focus:outline-none focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30 transition-all"
              @keyup.enter="$emit('create')"
            />
            <p class="text-[10px] text-zinc-500 mt-1.5 font-mono">
              Note: Appropriate timestamp and .sql suffix are appended automatically.
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-3 pt-2">
            <button
              type="button"
              @click="$emit('close')"
              class="flex-1 py-2.5 rounded-xl bg-white/[0.03] border border-white/[0.08] text-zinc-400 text-xs font-semibold hover:text-white hover:bg-white/[0.06] transition-all"
            >
              Cancel
            </button>
            <button
              type="button"
              @click="$emit('create')"
              :disabled="creating"
              class="flex-1 py-2.5 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-600 text-white text-xs font-semibold hover:brightness-110 transition-all shadow-lg shadow-[#FF6700]/20 disabled:opacity-50 flex items-center justify-center gap-2"
            >
              <ArrowPathIcon v-if="creating" class="w-4 h-4 animate-spin" />
              <span>{{ creating ? 'Generating Dump…' : 'Generate Snapshot' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

