<script setup>
// Luxury Zero-CLS Skeleton for Backup & Recovery
</script>

<template>
  <div class="space-y-8 animate-pulse" aria-hidden="true">
    <!-- Header Skeleton -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-2xl bg-[#12161C] border border-white/[0.08]"></div>
        <div class="space-y-1.5">
          <div class="w-56 h-6 rounded bg-[#12161C] border border-white/[0.06]"></div>
          <div class="w-72 h-3.5 rounded bg-[#12161C] border border-white/[0.04]"></div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-32 h-9 rounded-xl bg-[#12161C] border border-white/[0.06]"></div>
        <div class="w-36 h-9 rounded-xl bg-[#12161C] border border-white/[0.06]"></div>
      </div>
    </div>

    <!-- 4 Summary Metric Cards Skeleton -->
    <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
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

    <!-- Table Skeleton -->
    <div class="rounded-2xl bg-[#12161C] border border-white/[0.06] p-5 space-y-4">
      <div class="flex justify-between items-center pb-3 border-b border-white/[0.04]">
        <div class="w-36 h-5 rounded bg-white/[0.05]"></div>
        <div class="w-28 h-6 rounded bg-white/[0.04]"></div>
      </div>
      <div v-for="i in 4" :key="i" class="h-14 rounded-xl bg-white/[0.02] border border-white/[0.04]"></div>
    </div>
  </div>
</template>
