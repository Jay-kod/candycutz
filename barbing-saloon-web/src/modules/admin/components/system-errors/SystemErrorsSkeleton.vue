<script setup>
// Luxury Zero-CLS Skeleton for System Errors & Incidents
</script>

<template>
  <div class="space-y-6 animate-pulse" aria-hidden="true">
    <!-- Header Title & Counter Skeleton -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-2xl bg-[#12161C] border border-white/[0.08]"></div>
        <div class="space-y-1.5">
          <div class="w-56 h-6 rounded bg-[#12161C] border border-white/[0.06]"></div>
          <div class="w-72 h-3.5 rounded bg-[#12161C] border border-white/[0.04]"></div>
        </div>
      </div>
      <div class="w-36 h-9 rounded-xl bg-[#12161C] border border-white/[0.06]"></div>
    </div>

    <!-- 4 Stats Cards Skeleton -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <div
        v-for="i in 4"
        :key="i"
        class="h-24 rounded-2xl bg-[#12161C] border border-white/[0.06] p-4 flex flex-col justify-between"
      >
        <div class="flex justify-between">
          <div class="w-20 h-3 rounded bg-white/[0.04]"></div>
          <div class="w-7 h-7 rounded-lg bg-white/[0.04]"></div>
        </div>
        <div class="w-16 h-6 rounded bg-white/[0.06]"></div>
      </div>
    </div>

    <!-- Trace Bar Skeleton -->
    <div class="h-28 rounded-2xl bg-[#12161C] border border-white/[0.06] p-5"></div>

    <!-- Filter Bar Skeleton -->
    <div class="h-11 rounded-xl bg-[#12161C] border border-white/[0.06]"></div>

    <!-- Error Rows Skeleton -->
    <div class="rounded-2xl bg-[#12161C] border border-white/[0.06] p-5 space-y-4">
      <div v-for="i in 5" :key="i" class="h-16 rounded-xl bg-white/[0.02] border border-white/[0.04]"></div>
    </div>
  </div>
</template>

