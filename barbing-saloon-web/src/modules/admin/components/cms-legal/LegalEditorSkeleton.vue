<template>
  <div class="space-y-6 animate-pulse">
    <!-- Header Skeleton -->
    <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/60 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="space-y-2">
        <div class="h-4 w-36 rounded bg-white/[0.05]"></div>
        <div class="h-8 w-64 rounded bg-white/[0.08]"></div>
        <div class="h-3 w-80 rounded bg-white/[0.04]"></div>
      </div>
      <div class="flex items-center gap-3">
        <div class="h-10 w-28 rounded-xl bg-white/[0.05]"></div>
        <div class="h-10 w-36 rounded-xl bg-white/[0.08]"></div>
      </div>
    </div>

    <!-- Title Card Skeleton -->
    <div class="rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-6 space-y-4">
      <div class="h-5 w-40 rounded bg-white/[0.06]"></div>
      <div class="h-10 rounded-xl bg-white/[0.04]"></div>
    </div>

    <!-- Section Cards Skeleton -->
    <div class="rounded-2xl border border-white/[0.06] bg-[#12161C]/50 p-6 space-y-5">
      <div class="flex items-center justify-between pb-3 border-b border-white/[0.05]">
        <div class="h-5 w-48 rounded bg-white/[0.06]"></div>
        <div class="h-8 w-24 rounded-xl bg-white/[0.05]"></div>
      </div>
      <div v-for="i in 2" :key="i" class="h-44 rounded-xl bg-white/[0.03]"></div>
    </div>
  </div>
</template>

