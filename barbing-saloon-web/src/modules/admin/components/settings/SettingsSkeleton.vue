<script setup>
// Luxury Zero-CLS Skeleton for System Settings
</script>

<template>
  <div class="space-y-6 animate-pulse" aria-hidden="true">
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

    <!-- Tabs Bar Skeleton -->
    <div class="flex gap-2">
      <div v-for="i in 5" :key="i" class="w-32 h-9 rounded-xl bg-[#12161C] border border-white/[0.06]"></div>
    </div>

    <!-- Form Grid Skeleton -->
    <div class="rounded-2xl bg-[#12161C] border border-white/[0.06] p-6 space-y-6">
      <div class="w-48 h-5 rounded bg-white/[0.05]"></div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div v-for="i in 6" :key="i" class="space-y-2">
          <div class="w-28 h-3.5 rounded bg-white/[0.04]"></div>
          <div class="w-full h-10 rounded-xl bg-white/[0.02] border border-white/[0.04]"></div>
        </div>
      </div>
    </div>
  </div>
</template>

