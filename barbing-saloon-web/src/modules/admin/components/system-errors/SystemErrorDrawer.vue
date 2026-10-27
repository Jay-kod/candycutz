<script setup>
import { ref, watch } from 'vue'
import {
  XMarkIcon,
  ArrowPathIcon,
  LightBulbIcon,
  InformationCircleIcon,
  CodeBracketIcon,
  GlobeAltIcon,
  FingerPrintIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
  drawerOpen: {
    type: Boolean,
    default: false,
  },
  drawerLoading: {
    type: Boolean,
    default: false,
  },
  selectedGroup: {
    type: Object,
    default: null,
  },
  selectedEvents: {
    type: Array,
    default: () => [],
  },
  statusUpdating: {
    type: Boolean,
    default: false,
  },
  severityColor: {
    type: Function,
    required: true,
  },
  statusColor: {
    type: Function,
    required: true,
  },
  formatTime: {
    type: Function,
    required: true,
  },
})

const emit = defineEmits(['closeDrawer', 'updateStatus'])

const noteInput = ref('')

watch(() => props.selectedGroup, (group) => {
  noteInput.value = group?.resolution_note || ''
}, { immediate: true })

const handleStatusChange = (newStatus) => {
  emit('updateStatus', newStatus, noteInput.value || null)
}
</script>

<template>
  <Teleport to="body">
    <div v-if="drawerOpen" class="fixed inset-0 z-50 flex justify-end">
      <!-- Backdrop -->
      <div
        class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity"
        @click="$emit('closeDrawer')"
      ></div>

      <!-- Drawer Content -->
      <div class="relative w-full max-w-xl bg-[#0E1217] border-l border-white/[0.08] overflow-y-auto shadow-2xl flex flex-col z-10">
        <!-- Drawer Header -->
        <div class="sticky top-0 z-20 bg-[#0E1217]/95 backdrop-blur-md px-6 py-4 border-b border-white/[0.08] flex items-center justify-between">
          <div>
            <h3 class="text-base font-bold text-white tracking-tight">Incident Deep-Dive</h3>
            <p class="text-xs text-zinc-400">Classified error occurrence trace & status triage</p>
          </div>
          <button
            type="button"
            @click="$emit('closeDrawer')"
            class="p-2 rounded-xl text-zinc-400 hover:text-white hover:bg-white/[0.06] transition-colors"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Drawer Loading -->
        <div v-if="drawerLoading" class="p-16 flex flex-col items-center justify-center gap-3">
          <ArrowPathIcon class="w-8 h-8 text-rose-400 animate-spin" />
          <p class="text-xs text-zinc-400">Loading incident events & telemetry…</p>
        </div>

        <!-- Drawer Body -->
        <div v-else-if="selectedGroup" class="p-6 space-y-6 flex-1">
          <!-- Identity Card -->
          <div class="rounded-2xl p-4 bg-[#141920] border border-white/[0.08] space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-mono font-bold text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20">
                {{ selectedGroup.error_code }}
              </span>
              <div class="flex items-center gap-2">
                <span :class="['text-[10px] px-2 py-0.5 rounded-full border font-bold uppercase tracking-wider', severityColor(selectedGroup.severity)]">
                  {{ selectedGroup.severity }}
                </span>
                <span :class="['text-[10px] px-2 py-0.5 rounded-full border font-bold uppercase tracking-wider', statusColor(selectedGroup.status)]">
                  {{ selectedGroup.status }}
                </span>
              </div>
            </div>
            <p class="text-xs text-zinc-200 font-mono break-words leading-relaxed">
              {{ selectedGroup.sample_message }}
            </p>
          </div>

          <!-- Metadata 4-Grid -->
          <div class="grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-[#141920] border border-white/[0.06] p-3">
              <span class="text-[10px] text-zinc-400 uppercase font-semibold block mb-1">Occurrences</span>
              <span class="text-lg font-bold font-mono text-white">{{ selectedGroup.occurrences }}</span>
            </div>
            <div class="rounded-xl bg-[#141920] border border-white/[0.06] p-3">
              <span class="text-[10px] text-zinc-400 uppercase font-semibold block mb-1">Category</span>
              <span class="text-xs font-mono text-zinc-200 capitalize">{{ selectedGroup.category || 'General' }}</span>
            </div>
            <div class="rounded-xl bg-[#141920] border border-white/[0.06] p-3">
              <span class="text-[10px] text-zinc-400 uppercase font-semibold block mb-1">First Detected</span>
              <span class="text-xs font-mono text-zinc-300">{{ formatTime(selectedGroup.first_seen_at) }}</span>
            </div>
            <div class="rounded-xl bg-[#141920] border border-white/[0.06] p-3">
              <span class="text-[10px] text-zinc-400 uppercase font-semibold block mb-1">Latest Detection</span>
              <span class="text-xs font-mono text-zinc-300">{{ formatTime(selectedGroup.last_seen_at) }}</span>
            </div>
          </div>

          <!-- Exception Class -->
          <div v-if="selectedGroup.exception_class" class="rounded-xl bg-[#141920] border border-white/[0.06] p-3">
            <span class="text-[10px] text-zinc-400 uppercase font-semibold block mb-1">Exception Class</span>
            <span class="text-xs font-mono text-violet-300 break-all">{{ selectedGroup.exception_class }}</span>
          </div>

          <!-- Operator Guidance Hint -->
          <div v-if="selectedGroup.operator_hint" class="rounded-2xl bg-amber-500/10 border border-amber-500/25 p-4 space-y-1.5">
            <div class="flex items-center gap-2 text-amber-400 text-xs font-bold">
              <LightBulbIcon class="w-4 h-4 shrink-0" />
              <span>Operator Diagnostic Hint</span>
            </div>
            <p class="text-xs text-amber-200/90 leading-relaxed">{{ selectedGroup.operator_hint }}</p>
          </div>

          <!-- Status & Resolution Note Controls -->
          <div class="rounded-2xl bg-[#141920] border border-white/[0.08] p-4 space-y-4">
            <div>
              <span class="text-xs font-bold text-white block mb-2">Update Incident Status</span>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <button
                  v-for="s in ['open', 'acknowledged', 'resolved', 'ignored']"
                  :key="s"
                  type="button"
                  @click="handleStatusChange(s)"
                  :disabled="statusUpdating || selectedGroup.status === s"
                  :class="[
                    'px-3 py-2 rounded-xl text-xs font-bold uppercase tracking-wider transition-all border text-center disabled:opacity-40',
                    selectedGroup.status === s
                      ? 'bg-white/[0.12] border-white/[0.25] text-white shadow-sm'
                      : 'bg-white/[0.03] border-white/[0.06] text-zinc-400 hover:text-white hover:bg-white/[0.06]'
                  ]"
                >
                  {{ s }}
                </button>
              </div>
            </div>

            <div>
              <label class="text-[11px] font-semibold text-zinc-400 block mb-1.5">Resolution Note</label>
              <textarea
                v-model="noteInput"
                rows="2"
                placeholder="Optional resolution remarks or post-mortem notes…"
                class="w-full p-3 rounded-xl bg-black/30 border border-white/[0.08] text-xs text-white placeholder-zinc-500 focus:outline-none focus:border-white/[0.2]"
              ></textarea>
            </div>
          </div>

          <!-- Captured Event Occurrences Timeline -->
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="text-xs font-bold text-white uppercase tracking-wider">
                Event Timeline Occurrences ({{ selectedEvents.length }})
              </h4>
              <span class="text-[10px] text-zinc-500 font-mono">Last 20 Events</span>
            </div>

            <div v-if="!selectedEvents.length" class="p-6 text-center text-xs text-zinc-500 bg-[#141920] rounded-xl border border-white/[0.04]">
              Zero specific event traces recorded in active table.
            </div>

            <div v-else class="space-y-2.5">
              <div
                v-for="ev in selectedEvents"
                :key="ev.id"
                class="rounded-xl bg-[#141920] border border-white/[0.06] p-3.5 space-y-2"
              >
                <div class="flex items-center justify-between text-xs">
                  <div class="flex items-center gap-2">
                    <span :class="['text-[10px] px-2 py-0.5 rounded-full border font-bold uppercase tracking-wider', severityColor(ev.severity)]">
                      {{ ev.severity }}
                    </span>
                    <span v-if="ev.http_status" class="text-[11px] font-mono text-zinc-400">
                      HTTP {{ ev.http_status }}
                    </span>
                  </div>
                  <span class="text-[10px] text-zinc-500 font-mono">{{ formatTime(ev.occurred_at) }}</span>
                </div>

                <p class="text-xs text-zinc-200 font-mono break-all">{{ ev.message }}</p>

                <div v-if="ev.url" class="text-[10px] text-zinc-400 font-mono flex items-center gap-1">
                  <GlobeAltIcon class="w-3 h-3 text-zinc-500 shrink-0" />
                  <span class="truncate">{{ ev.url }}</span>
                </div>

                <div v-if="ev.request_id" class="text-[10px] text-violet-300 font-mono flex items-center gap-1">
                  <FingerPrintIcon class="w-3 h-3 text-violet-400 shrink-0" />
                  <span>Request ID: {{ ev.request_id }}</span>
                </div>

                <!-- Expandable Stack Trace -->
                <details v-if="ev.stack_trace" class="pt-1">
                  <summary class="text-[10px] text-zinc-400 cursor-pointer hover:text-white transition-colors flex items-center gap-1 select-none">
                    <CodeBracketIcon class="w-3 h-3" />
                    <span>Expand Stack Trace</span>
                  </summary>
                  <pre class="mt-2 p-3 rounded-xl bg-black/50 text-[10px] text-zinc-300 font-mono overflow-x-auto max-h-[220px] overflow-y-auto whitespace-pre-wrap border border-white/[0.06] leading-relaxed">{{ ev.stack_trace }}</pre>
                </details>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>
