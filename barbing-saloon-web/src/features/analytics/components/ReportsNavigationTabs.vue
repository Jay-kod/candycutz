<template>
  <div class="space-y-4 print:hidden">
    <!-- Executive Title & Subtitle Header -->
    <div class="relative overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/90 p-5 sm:p-6 backdrop-blur-xl shadow-xl">
      <div class="pointer-events-none absolute -top-24 right-1/4 h-48 w-80 rounded-full bg-gradient-to-r from-[#FF6700]/15 via-amber-500/10 to-transparent blur-3xl"></div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1.5">
          <div class="flex flex-wrap items-center gap-2.5">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-[#FF6700]/30 bg-[#FF6700]/10 px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider text-[#FF6700]">
              <span class="h-1.5 w-1.5 rounded-full bg-[#FF6700] animate-pulse"></span>
              Operational Reports & Audit Ledger
            </span>
            <span v-if="lastRefreshed" class="inline-flex items-center gap-1 text-[11px] text-theme-text-muted font-mono">
              <ClockIcon class="h-3 w-3" />
              Reconciled {{ lastRefreshed }}
            </span>
          </div>

          <h1 class="font-display text-2xl sm:text-3xl font-bold tracking-tight text-theme-text">
            CandyCutz <span class="bg-gradient-to-r from-[#FF6700] via-amber-400 to-amber-200 bg-clip-text text-transparent">Operations & Audit</span>
          </h1>
          <p class="text-xs sm:text-sm text-theme-text-muted max-w-2xl">
            Live business intelligence, printable executive dossier, and immutable audit logs of all report downloads and exports.
          </p>
        </div>

        <!-- Flagship Status Pill -->
        <div class="flex items-center gap-3">
          <div class="hidden lg:flex flex-col items-end text-right">
            <span class="text-xs font-semibold text-theme-text flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
              Flagship Branch • Keffi
            </span>
            <span class="text-[11px] font-mono text-theme-text-muted">
              {{ historyCount }} Export Events Recorded
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Prominent Segmented Primary Tab Bar -->
    <div class="rounded-2xl border-2 border-theme-border bg-theme-surface/95 p-1.5 shadow-lg backdrop-blur-xl">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <!-- Tab 1: Executive Dossier -->
        <button
          type="button"
          id="tab-btn-dossier"
          @click="$emit('update:activeTab', 'dossier')"
          :class="[
            'flex items-center justify-between rounded-xl px-4 py-3 text-left transition-all cursor-pointer',
            activeTab === 'dossier'
              ? 'bg-gradient-to-r from-[#FF6700] to-amber-500 text-white shadow-md shadow-[#FF6700]/25'
              : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-bg/80'
          ]"
        >
          <div class="flex items-center gap-3">
            <div
              :class="[
                'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm font-bold transition-colors',
                activeTab === 'dossier' ? 'bg-white/20 text-white' : 'bg-orange-500/10 text-[#FF6700]'
              ]"
            >
              <DocumentTextIcon class="h-5 w-5" />
            </div>
            <div>
              <p :class="['text-sm font-bold leading-tight', activeTab === 'dossier' ? 'text-white' : 'text-theme-text']">
                Executive Dossier
              </p>
              <p :class="['text-[11px] leading-tight mt-0.5', activeTab === 'dossier' ? 'text-white/80' : 'text-theme-text-muted']">
                Printable WYSIWYG report, daily ledger & KPIs
              </p>
            </div>
          </div>
          <span
            v-if="activeTab === 'dossier'"
            class="hidden md:inline-flex items-center gap-1 text-[11px] font-semibold bg-white/20 px-2 py-0.5 rounded-full"
          >
            Active View
          </span>
        </button>

        <!-- Tab 2: Export & Audit History -->
        <button
          type="button"
          id="tab-btn-history"
          @click="$emit('update:activeTab', 'history')"
          :class="[
            'flex items-center justify-between rounded-xl px-4 py-3 text-left transition-all cursor-pointer',
            activeTab === 'history'
              ? 'bg-gradient-to-r from-[#FF6700] to-amber-500 text-white shadow-md shadow-[#FF6700]/25'
              : 'text-theme-text-muted hover:text-theme-text hover:bg-theme-bg/80'
          ]"
        >
          <div class="flex items-center gap-3">
            <div
              :class="[
                'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-sm font-bold transition-colors',
                activeTab === 'history' ? 'bg-white/20 text-white' : 'bg-orange-500/10 text-[#FF6700]'
              ]"
            >
              <ClockIcon class="h-5 w-5" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <p :class="['text-sm font-bold leading-tight', activeTab === 'history' ? 'text-white' : 'text-theme-text']">
                  Export & Audit History
                </p>
                <span
                  :class="[
                    'px-2 py-0.5 rounded-full text-[10px] font-mono font-bold transition-colors',
                    activeTab === 'history'
                      ? 'bg-white text-[#FF6700]'
                      : 'bg-[#FF6700]/15 text-[#FF6700] border border-[#FF6700]/30'
                  ]"
                >
                  {{ historyCount }}
                </span>
              </div>
              <p :class="['text-[11px] leading-tight mt-0.5', activeTab === 'history' ? 'text-white/80' : 'text-theme-text-muted']">
                Audit logs, SHA-256 verifications & download history
              </p>
            </div>
          </div>
          <span
            v-if="activeTab === 'history'"
            class="hidden md:inline-flex items-center gap-1 text-[11px] font-semibold bg-white/20 px-2 py-0.5 rounded-full"
          >
            Active View
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { DocumentTextIcon, ClockIcon } from '@heroicons/vue/24/outline';

defineProps({
  activeTab: {
    type: String,
    required: true,
    validator: (v) => ['dossier', 'history'].includes(v)
  },
  historyCount: {
    type: Number,
    default: 0
  },
  lastRefreshed: {
    type: String,
    default: null
  }
});

defineEmits(['update:activeTab']);
</script>

