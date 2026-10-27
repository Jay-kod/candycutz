<template>
  <div class="space-y-6 animate-pulse">
    <!-- Header Skeleton -->
    <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/60 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="space-y-2">
        <div class="h-4 w-32 rounded bg-white/[0.05]"></div>
        <div class="h-8 w-64 rounded bg-white/[0.08]"></div>
        <div class="h-3 w-80 rounded bg-white/[0.04]"></div>
      </div>
      <div class="flex items-center gap-3">
        <div class="h-10 w-28 rounded-xl bg-white/[0.05]"></div>
        <div class="h-10 w-36 rounded-xl bg-white/[0.08]"></div>
      </div>
    </div>

    <!-- 4 KPI Cards Skeleton -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 sm:gap-5">
      <div
        v-for="i in 4"
        :key="'kpi-' + i"
        class="h-28 rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-5 flex flex-col justify-between"
      >
        <div class="flex items-center justify-between">
          <div class="h-3 w-28 rounded bg-white/[0.05]"></div>
          <div class="h-8 w-8 rounded-xl bg-white/[0.08]"></div>
        </div>
        <div class="space-y-1.5">
          <div class="h-7 w-20 rounded bg-white/[0.08]"></div>
          <div class="h-3 w-36 rounded bg-white/[0.04]"></div>
        </div>
      </div>
    </div>

    <!-- Filter Bar Skeleton -->
    <div class="rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-4 space-y-3">
      <div class="flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="flex gap-2 w-full md:w-auto">
          <div v-for="i in 5" :key="'cat-' + i" class="h-8 w-20 rounded-xl bg-white/[0.05]"></div>
        </div>
        <div class="h-9 w-full md:w-72 rounded-xl bg-white/[0.05]"></div>
      </div>
    </div>

    <!-- Notification List Skeleton -->
    <div class="rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-5 space-y-4">
      <div class="h-4 w-28 rounded bg-white/[0.06]"></div>
      <div
        v-for="i in 5"
        :key="'row-' + i"
        class="h-20 rounded-xl border border-white/[0.04] bg-white/[0.02] p-4 flex items-center justify-between"
      >
        <div class="flex items-center gap-4">
          <div class="h-10 w-10 rounded-xl bg-white/[0.06]"></div>
          <div class="space-y-2">
            <div class="h-4 w-48 rounded bg-white/[0.08]"></div>
            <div class="h-3 w-72 rounded bg-white/[0.04]"></div>
          </div>
        </div>
        <div class="h-3 w-20 rounded bg-white/[0.04]"></div>
      </div>
    </div>
  </div>
</template>
