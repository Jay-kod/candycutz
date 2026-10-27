<script setup>
// Luxury Zero-CLS Skeleton for Health Checker & Diagnostics
</script>

<template>
  <div class="space-y-8 animate-pulse" aria-hidden="true">
    <!-- Header Skeleton -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-2xl bg-[#12161C] border border-white/[0.08]"></div>
        <div class="space-y-1.5">
          <div class="w-48 h-6 rounded bg-[#12161C] border border-white/[0.06]"></div>
          <div class="w-72 h-3.5 rounded bg-[#12161C] border border-white/[0.04]"></div>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-32 h-9 rounded-xl bg-[#12161C] border border-white/[0.06]"></div>
        <div class="w-36 h-9 rounded-xl bg-[#12161C] border border-white/[0.06]"></div>
      </div>
    </div>

    <!-- Status Banner Skeleton -->
    <div class="h-20 rounded-2xl bg-[#12161C] border border-white/[0.06]"></div>

    <!-- 8 KPI Cards Skeleton -->
    <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
      <div
        v-for="i in 8"
        :key="i"
        class="h-28 rounded-2xl bg-[#12161C] border border-white/[0.06] p-4 flex flex-col justify-between"
      >
        <div class="flex items-center justify-between">
          <div class="w-8 h-8 rounded-lg bg-white/[0.04]"></div>
          <div class="w-12 h-4 rounded-full bg-white/[0.04]"></div>
        </div>
        <div class="space-y-1.5">
          <div class="w-16 h-2.5 rounded bg-white/[0.04]"></div>
          <div class="w-24 h-5 rounded bg-white/[0.06]"></div>
        </div>
      </div>
    </div>

    <!-- Subsystems Grid Skeleton -->
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      <div
        v-for="i in 6"
        :key="i"
        class="h-56 rounded-2xl bg-[#12161C] border border-white/[0.06] p-5 flex flex-col justify-between"
      >
        <div class="flex items-center justify-between">
          <div class="w-36 h-5 rounded bg-white/[0.05]"></div>
          <div class="w-16 h-4 rounded-full bg-white/[0.05]"></div>
        </div>
        <div class="space-y-2 pt-4">
          <div v-for="j in 4" :key="j" class="flex justify-between">
            <div class="w-24 h-3 rounded bg-white/[0.04]"></div>
            <div class="w-20 h-3 rounded bg-white/[0.05]"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

