<script setup>
import {
  CloudArrowUpIcon,
  XMarkIcon,
  ArrowPathIcon,
  DocumentIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  uploading: {
    type: Boolean,
    default: false,
  },
  uploadFile: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'fileSelect', 'upload'])

const onFileChange = (e) => {
  const file = e.target.files?.[0] || null
  emit('fileSelect', file)
}
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
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/25 flex items-center justify-center">
              <CloudArrowUpIcon class="w-5 h-5 text-indigo-400" />
            </div>
            <div>
              <h3 class="text-base font-bold text-white tracking-tight">Upload Snapshot Archive</h3>
              <p class="text-xs text-zinc-400">Import an existing external database backup file</p>
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
              Select Archive (.sql, .sqlite, .gz)
            </label>
            <div class="relative">
              <input
                type="file"
                accept=".sql,.sqlite,.gz"
                @change="onFileChange"
                class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] border-dashed text-zinc-400 text-xs file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border file:border-white/10 file:bg-white/[0.06] file:text-zinc-200 file:text-xs file:font-semibold focus:outline-none focus:border-indigo-500/40 transition-colors"
              />
            </div>
            <div class="flex items-center justify-between text-[10px] text-zinc-500 mt-1.5 font-mono">
              <span>Accepted: .sql, .sqlite, .gz</span>
              <span>Max size: 100 MB</span>
            </div>
          </div>

          <!-- Selected File Preview -->
          <div v-if="uploadFile" class="rounded-xl p-3 bg-white/[0.02] border border-white/[0.06] flex items-center gap-3">
            <DocumentIcon class="w-5 h-5 text-indigo-400 shrink-0" />
            <div class="min-w-0 flex-1 text-xs">
              <p class="text-white font-mono font-semibold truncate">{{ uploadFile.name }}</p>
              <p class="text-zinc-500 font-mono text-[10px]">{{ (uploadFile.size / (1024 * 1024)).toFixed(2) }} MB</p>
            </div>
          </div>

          <!-- Action Buttons -->
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
              @click="$emit('upload')"
              :disabled="uploading || !uploadFile"
              class="flex-1 py-2.5 rounded-xl bg-indigo-600/25 border border-indigo-500/35 text-indigo-200 text-xs font-semibold hover:bg-indigo-600/35 transition-all shadow-lg shadow-indigo-600/15 disabled:opacity-40 flex items-center justify-center gap-2"
            >
              <ArrowPathIcon v-if="uploading" class="w-4 h-4 animate-spin" />
              <span>{{ uploading ? 'Uploading…' : 'Upload Archive' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
