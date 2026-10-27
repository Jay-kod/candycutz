<template>
  <div class="space-y-6 animate-pulse">
    <!-- Header Skeleton -->
    <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/60 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="space-y-2">
        <div class="h-4 w-32 rounded bg-white/[0.05]"></div>
        <div class="h-8 w-64 rounded bg-white/[0.08]"></div>
        <div class="h-3 w-80 rounded bg-white/[0.04]"></div>
      </div>
      <div class="flex gap-2">
        <div class="h-10 w-48 rounded-xl bg-white/[0.05]"></div>
        <div class="h-10 w-32 rounded-xl bg-white/[0.05]"></div>
      </div>
    </div>

    <!-- 5 KPI Cards Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 sm:gap-5">
      <div
        v-for="i in 5"
        :key="'kpi-' + i"
        class="h-32 rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-5 flex flex-col justify-between"
      >
        <div class="flex items-center justify-between">
          <div class="h-3 w-20 rounded bg-white/[0.05]"></div>
          <div class="h-9 w-9 rounded-xl bg-white/[0.08]"></div>
        </div>
        <div class="space-y-2">
          <div class="h-7 w-32 rounded bg-white/[0.08]"></div>
          <div class="h-3 w-24 rounded bg-white/[0.04]"></div>
        </div>
      </div>
    </div>

    <!-- Top Performers 2-Col Skeleton -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div
        v-for="i in 2"
        :key="'perf-' + i"
        class="h-80 rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-6 space-y-4"
      >
        <div class="flex items-center justify-between mb-2">
          <div class="h-5 w-40 rounded bg-white/[0.06]"></div>
          <div class="h-4 w-16 rounded bg-white/[0.04]"></div>
        </div>
        <div v-for="j in 3" :key="j" class="h-14 w-full rounded-xl bg-white/[0.03]"></div>
      </div>
    </div>

    <!-- Status Breakdown Skeleton -->
    <div class="rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-6 space-y-4">
      <div class="h-5 w-48 rounded bg-white/[0.06]"></div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
        <div v-for="i in 5" :key="'status-' + i" class="h-24 rounded-xl bg-white/[0.03]"></div>
      </div>
    </div>
  </div>
</template>
