<script setup>
import {
  CircleStackIcon,
  ArchiveBoxIcon,
  DocumentIcon,
  ArrowDownTrayIcon,
  ArrowPathIcon,
  TrashIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'

defineProps({
  backups: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  searchQuery: {
    type: String,
    default: '',
  },
  extensionFilter: {
    type: String,
    default: '',
  },
  deleting: {
    type: Object,
    default: () => ({}),
  },
  formatTime: {
    type: Function,
    required: true,
  },
  extColor: {
    type: Function,
    required: true,
  },
})

defineEmits([
  'update:searchQuery',
  'update:extensionFilter',
  'download',
  'restore',
  'delete',
  'openCreate',
])
</script>

<template>
  <div class="rounded-2xl bg-[#12161C] border border-white/[0.08] overflow-hidden shadow-xl">
    <!-- Header & Search Toolbar -->
    <div class="px-5 py-4 border-b border-white/[0.08] flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white/[0.02]">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/25 flex items-center justify-center">
          <CircleStackIcon class="w-4 h-4 text-blue-400" />
        </div>
        <div>
          <h2 class="text-sm font-bold text-white tracking-tight">Database Snapshots & Archives</h2>
          <p class="text-xs text-zinc-400">Available physical snapshot files on server volume</p>
        </div>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="flex items-center flex-wrap gap-2.5">
        <div class="relative min-w-[180px]">
          <MagnifyingGlassIcon class="w-3.5 h-3.5 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            :value="searchQuery"
            @input="$emit('update:searchQuery', $event.target.value)"
            type="text"
            placeholder="Search filenames…"
            class="w-full pl-8 pr-7 py-1.5 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white text-xs placeholder-zinc-500 focus:outline-none focus:border-white/[0.18] transition-colors"
          />
          <button
            v-if="searchQuery"
            type="button"
            @click="$emit('update:searchQuery', '')"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-white"
          >
            <XMarkIcon class="w-3 h-3" />
          </button>
        </div>

        <select
          :value="extensionFilter"
          @change="$emit('update:extensionFilter', $event.target.value)"
          class="px-3 py-1.5 rounded-lg bg-white/[0.03] border border-white/[0.08] text-zinc-300 text-xs focus:outline-none cursor-pointer"
        >
          <option value="">All Formats</option>
          <option value="sql">SQL Dump (.sql)</option>
          <option value="sqlite">SQLite (.sqlite)</option>
          <option value="gz">Gzip (.gz)</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="p-12 flex flex-col items-center justify-center gap-3">
      <ArrowPathIcon class="w-7 h-7 text-blue-400 animate-spin" />
      <p class="text-xs text-zinc-400">Scanning local backup storage volume…</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!backups.length" class="p-12 text-center flex flex-col items-center justify-center">
      <div class="w-12 h-12 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center mb-3 text-zinc-400">
        <ArchiveBoxIcon class="w-6 h-6" />
      </div>
      <h3 class="text-sm font-bold text-white mb-1">Zero Database Snapshots</h3>
      <p class="text-xs text-zinc-400 max-w-sm mb-4">
        No backup archives are currently available on this node. Generate your first point-in-time snapshot to ensure business continuity.
      </p>
      <button
        type="button"
        @click="$emit('openCreate')"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#FF6700] to-amber-600 text-white text-xs font-semibold hover:brightness-110 transition-all shadow-lg shadow-[#FF6700]/20"
      >
        <PlusIcon class="w-4 h-4" />
        <span>Create First Snapshot</span>
      </button>
    </div>

    <!-- Backups List -->
    <div v-else class="divide-y divide-white/[0.06]">
      <div
        v-for="backup in backups"
        :key="backup.filename"
        class="px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-white/[0.02] transition-colors group"
      >
        <!-- File Info -->
        <div class="flex items-center gap-3.5 min-w-0 flex-1">
          <div class="w-10 h-10 rounded-xl bg-white/[0.03] border border-white/[0.06] flex items-center justify-center shrink-0">
            <DocumentIcon class="w-5 h-5 text-blue-400" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <span class="text-xs text-white font-mono font-semibold truncate" :title="backup.filename">
                {{ backup.filename }}
              </span>
              <span :class="['text-[10px] px-2 py-0.5 rounded-full border font-bold uppercase tracking-wider', extColor(backup.extension)]">
                {{ backup.extension }}
              </span>
              <span v-if="!backup.is_valid" class="text-[10px] px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 font-bold uppercase">
                Corrupt
              </span>
            </div>
            <div class="flex items-center gap-3 text-[11px] text-zinc-500 mt-1">
              <span class="font-mono text-zinc-300">{{ backup.size_formatted }}</span>
              <span class="text-zinc-600">·</span>
              <span>Created {{ formatTime(backup.created_at) }}</span>
            </div>
          </div>
        </div>

        <!-- Row Actions -->
        <div class="flex items-center gap-1.5 self-end sm:self-center shrink-0">
          <button
            type="button"
            @click="$emit('download', backup.filename)"
            class="p-2 rounded-lg text-zinc-400 hover:text-blue-400 hover:bg-blue-500/10 transition-colors"
            title="Download Snapshot"
          >
            <ArrowDownTrayIcon class="w-4 h-4" />
          </button>

          <button
            v-if="backup.is_valid"
            type="button"
            @click="$emit('restore', backup)"
            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-zinc-300 hover:text-emerald-400 hover:bg-emerald-500/10 border border-white/[0.06] transition-colors"
            title="Restore Snapshot to Database"
          >
            <ArrowPathIcon class="w-3.5 h-3.5 text-emerald-400" />
            <span>Restore</span>
          </button>

          <button
            type="button"
            @click="$emit('delete', backup.filename)"
            :disabled="deleting[backup.filename]"
            class="p-2 rounded-lg text-zinc-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors opacity-70 group-hover:opacity-100 disabled:opacity-40"
            title="Delete Snapshot"
          >
            <ArrowPathIcon v-if="deleting[backup.filename]" class="w-4 h-4 animate-spin text-rose-400" />
            <TrashIcon v-else class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
