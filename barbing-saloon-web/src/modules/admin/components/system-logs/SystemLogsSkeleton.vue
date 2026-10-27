<template>
  <div class="space-y-6 animate-pulse">
    <!-- Header Skeleton -->
    <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/60 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div class="space-y-2">
        <div class="h-4 w-32 rounded bg-white/[0.05]"></div>
        <div class="h-8 w-64 rounded bg-white/[0.08]"></div>
        <div class="h-3 w-80 rounded bg-white/[0.04]"></div>
      </div>
      <div class="h-10 w-48 rounded-xl bg-white/[0.05]"></div>
    </div>

    <!-- 4 KPI Cards Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
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

    <!-- Diagnostics Skeleton -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="h-72 rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-6 space-y-4">
        <div class="h-4 w-40 rounded bg-white/[0.06]"></div>
        <div class="h-10 w-full rounded-xl bg-white/[0.03]"></div>
        <div class="h-10 w-full rounded-xl bg-white/[0.03]"></div>
      </div>
      <div class="lg:col-span-2 h-72 rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-6 space-y-3">
        <div class="h-4 w-48 rounded bg-white/[0.06]"></div>
        <div v-for="i in 3" :key="i" class="h-12 w-full rounded-xl bg-white/[0.03]"></div>
      </div>
    </div>

    <!-- Table Skeleton -->
    <div class="rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-6 space-y-4">
      <div class="h-8 w-full rounded-xl bg-white/[0.04]"></div>
      <div v-for="i in 4" :key="'row-' + i" class="h-12 w-full rounded-xl bg-white/[0.03]"></div>
    </div>
  </div>
</template>
