<script setup>
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
  statusIcon: {
    type: Function,
    required: true,
  },
})
</script>

<template>
  <div v-if="loading" class="grid gap-4 grid-cols-2 lg:grid-cols-4">
    <div
      v-for="i in 8"
      :key="i"
      class="h-28 rounded-2xl bg-[#12161C] border border-white/[0.06] p-4 flex flex-col justify-between animate-pulse"
    >
      <div class="flex items-center justify-between">
        <div class="w-8 h-8 rounded-lg bg-white/[0.05]"></div>
        <div class="w-12 h-4 rounded-full bg-white/[0.05]"></div>
      </div>
      <div>
        <div class="w-16 h-2.5 rounded bg-white/[0.05] mb-2"></div>
        <div class="w-24 h-5 rounded bg-white/[0.08]"></div>
      </div>
    </div>
  </div>

  <div v-else class="grid gap-4 grid-cols-2 lg:grid-cols-4">
    <div
      v-for="card in cards"
      :key="card.label"
      :class="[
        'rounded-2xl p-4 border bg-[#12161C] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-black/40 group relative overflow-hidden',
        card.status === 'error' || card.status === 'disconnected'
          ? 'border-rose-500/30'
          : card.status === 'warning' || card.status === 'attention'
            ? 'border-amber-500/30'
            : 'border-white/[0.08] hover:border-white/[0.18]'
      ]"
    >
      <!-- Top Decorative Accent Line -->
      <div
        :class="[
          'absolute top-0 left-0 right-0 h-0.5 opacity-60 group-hover:opacity-100 transition-opacity',
          card.status === 'connected' || card.status === 'operational'
            ? 'bg-gradient-to-r from-emerald-500 via-teal-400 to-transparent'
            : card.status === 'warning' || card.status === 'attention'
              ? 'bg-gradient-to-r from-amber-500 to-transparent'
              : card.status === 'error' || card.status === 'disconnected'
                ? 'bg-gradient-to-r from-rose-500 to-transparent'
                : 'bg-gradient-to-r from-zinc-500 to-transparent'
        ]"
      ></div>

      <!-- Icon & Status Pill -->
      <div class="flex items-center justify-between mb-3">
        <div
          :class="[
            'w-9 h-9 rounded-xl flex items-center justify-center border transition-all duration-300',
            statusBg(card.status)
          ]"
        >
          <component :is="card.icon" :class="['w-4.5 h-4.5', statusColor(card.status)]" />
        </div>
        <span
          :class="[
            'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider border',
            statusBg(card.status)
          ]"
        >
          <component :is="statusIcon(card.status)" class="w-3 h-3" />
          <span>{{ card.status }}</span>
        </span>
      </div>

      <!-- Metric Label & Value -->
      <div>
        <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400 mb-0.5">
          {{ card.label }}
        </p>
        <p class="text-xl font-bold tracking-tight text-white group-hover:text-emerald-400 transition-colors truncate">
          {{ card.value }}
        </p>
      </div>
    </div>
  </div>
</template>
