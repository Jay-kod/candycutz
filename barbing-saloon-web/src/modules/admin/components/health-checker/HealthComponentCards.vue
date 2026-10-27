<script setup>
import { WrenchScrewdriverIcon } from '@heroicons/vue/24/outline'

defineProps({
  cards: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  statusColor: {
    type: Function,
    required: true,
  },
  statusBg: {
    type: Function,
    required: true,
  },
})
</script>

<template>
  <div class="space-y-4">
    <!-- Section Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-white/[0.04] border border-white/[0.08] flex items-center justify-center">
          <WrenchScrewdriverIcon class="w-4 h-4 text-emerald-400" />
        </div>
        <div>
          <h2 class="text-base font-bold text-white tracking-tight">Subsystem Component Matrix</h2>
          <p class="text-xs text-zinc-400">Deep telemetry checks across persistence, cache, queues & storage</p>
        </div>
      </div>
      <span class="text-xs font-mono text-zinc-500 hidden sm:inline-block">
        {{ cards.length }} Subsystems Monitored
      </span>
    </div>

    <!-- Shimmer Skeleton -->
    <div v-if="loading" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div
        v-for="i in 6"
        :key="i"
        class="h-56 rounded-2xl bg-[#12161C] border border-white/[0.06] p-5 flex flex-col justify-between animate-pulse"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-white/[0.05]"></div>
            <div class="w-28 h-4 rounded bg-white/[0.06]"></div>
          </div>
          <div class="w-16 h-5 rounded-full bg-white/[0.05]"></div>
        </div>
        <div class="space-y-2.5 pt-4">
          <div v-for="j in 4" :key="j" class="flex justify-between">
            <div class="w-20 h-3 rounded bg-white/[0.04]"></div>
            <div class="w-24 h-3 rounded bg-white/[0.06]"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Subsystems Grid -->
    <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div
        v-for="card in cards"
        :key="card.title"
        :class="[
          'rounded-2xl border bg-[#12161C] p-5 transition-all duration-300 hover:border-white/[0.18] hover:shadow-xl hover:shadow-black/30 flex flex-col justify-between group relative overflow-hidden',
          card.status === 'error' || card.status === 'disconnected'
            ? 'border-rose-500/25'
            : card.status === 'warning' || card.status === 'attention'
              ? 'border-amber-500/25'
              : 'border-white/[0.08]'
        ]"
      >
        <!-- Top Glow Strip -->
        <div
          :class="[
            'absolute top-0 left-0 right-0 h-0.5 opacity-40 group-hover:opacity-100 transition-opacity',
            card.status === 'optimal' || card.status === 'healthy' || card.status === 'operational' || card.status === 'configured' || card.status === 'connected'
              ? 'bg-gradient-to-r from-emerald-500 to-transparent'
              : card.status === 'warning' || card.status === 'attention'
                ? 'bg-gradient-to-r from-amber-500 to-transparent'
                : 'bg-gradient-to-r from-rose-500 to-transparent'
          ]"
        ></div>

        <!-- Card Top Bar -->
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div :class="['w-10 h-10 rounded-xl flex items-center justify-center border shadow-inner', statusBg(card.status)]">
              <component :is="card.icon" :class="['w-5 h-5', statusColor(card.status)]" />
            </div>
            <div>
              <h3 class="text-sm font-semibold text-white tracking-tight">{{ card.title }}</h3>
              <span class="text-[10px] text-zinc-500 uppercase tracking-wider font-semibold">Active Module</span>
            </div>
          </div>
          <span
            :class="[
              'text-[10px] px-2.5 py-0.5 rounded-full border font-semibold uppercase tracking-wider',
              statusBg(card.status),
              statusColor(card.status)
            ]"
          >
            {{ card.status }}
          </span>
        </div>

        <!-- Telemetry Items -->
        <div class="space-y-2 pt-2 border-t border-white/[0.06]">
          <div
            v-for="item in card.items"
            :key="item.label"
            class="flex items-center justify-between text-xs py-1 px-1.5 rounded hover:bg-white/[0.02] transition-colors"
          >
            <span class="text-zinc-400 font-medium">{{ item.label }}</span>
            <span
              v-if="item.warn"
              class="text-rose-400 font-mono text-[11px] bg-rose-500/10 px-2 py-0.5 rounded border border-rose-500/20 max-w-[200px] truncate"
              :title="item.value"
            >
              {{ item.value }}
            </span>
            <span v-else class="text-zinc-200 font-mono font-medium truncate max-w-[200px] text-right">
              {{ item.value }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
