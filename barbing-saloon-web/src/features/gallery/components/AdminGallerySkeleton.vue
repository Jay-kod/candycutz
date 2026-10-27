<template>
  <div class="space-y-6 animate-pulse pb-10" aria-busy="true" aria-label="Loading Gallery Portfolio">
    <!-- Header Skeleton -->
    <div class="rounded-[2rem] border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 flex flex-col lg:flex-row justify-between gap-6">
      <div class="space-y-3 flex-1">
        <div class="h-4 w-36 bg-white/[0.06] rounded-md"></div>
        <div class="h-8 w-64 bg-white/[0.08] rounded-xl"></div>
        <div class="h-3 w-96 bg-white/[0.04] rounded"></div>
      </div>
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <div class="h-10 w-full sm:w-64 bg-white/[0.04] rounded-xl"></div>
        <div class="h-10 w-32 bg-white/[0.08] rounded-xl"></div>
      </div>
    </div>

    <!-- 4 KPI Stat Cards Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 space-y-3">
        <div class="flex justify-between items-center">
          <div class="h-3 w-20 bg-white/[0.06] rounded"></div>
          <div class="h-7 w-7 bg-white/[0.06] rounded-lg"></div>
        </div>
        <div class="h-8 w-24 bg-white/[0.08] rounded-lg"></div>
        <div class="h-3 w-36 bg-white/[0.04] rounded pt-2 border-t border-white/[0.04]"></div>
      </div>
    </div>

    <!-- Filter Bar Skeleton -->
    <div class="flex flex-col sm:flex-row justify-between gap-4">
      <div class="h-10 w-64 bg-[#12161C] border border-white/[0.08] rounded-xl"></div>
      <div class="h-10 w-48 bg-[#12161C] border border-white/[0.08] rounded-xl"></div>
    </div>

    <!-- Grid Skeleton (8 Photo Cards) -->
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <div v-for="i in 8" :key="i" class="aspect-square rounded-3xl border border-white/[0.08] bg-[#12161C] overflow-hidden p-4 flex flex-col justify-between">
        <div class="flex justify-between">
          <div class="h-5 w-16 bg-white/[0.06] rounded-lg"></div>
          <div class="h-6 w-14 bg-white/[0.06] rounded-lg"></div>
        </div>
        <div class="space-y-2">
          <div class="h-5 w-3/4 bg-white/[0.08] rounded"></div>
          <div class="h-3 w-1/2 bg-white/[0.04] rounded"></div>
        </div>
      </div>
    </div>
  </div>
</template>

