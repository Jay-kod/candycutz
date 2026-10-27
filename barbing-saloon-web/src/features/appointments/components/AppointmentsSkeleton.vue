<template>
  <div class="space-y-6 animate-pulse" aria-hidden="true">
    <!-- Header Skeleton -->
    <div class="relative overflow-hidden rounded-[2rem] border border-theme-border bg-theme-surface p-6 lg:p-8 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div class="space-y-3">
        <div class="h-5 w-40 rounded-full bg-theme-bg"></div>
        <div class="h-10 w-72 rounded-xl bg-theme-bg"></div>
        <div class="h-4 w-96 rounded bg-theme-bg"></div>
      </div>
      <div class="flex flex-col items-end gap-3 shrink-0 w-full lg:w-auto">
        <div class="h-11 w-full lg:w-80 rounded-2xl bg-theme-bg"></div>
        <div class="flex gap-2">
          <div class="h-9 w-28 rounded-xl bg-theme-bg"></div>
          <div class="h-9 w-28 rounded-xl bg-theme-bg"></div>
          <div class="h-9 w-24 rounded-xl bg-theme-bg"></div>
        </div>
      </div>
    </div>

    <!-- 4 Stats Cards Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="rounded-2xl border border-theme-border bg-theme-surface p-5 space-y-3">
        <div class="flex justify-between items-center">
          <div class="h-3 w-24 rounded bg-theme-bg"></div>
          <div class="h-7 w-7 rounded-lg bg-theme-bg"></div>
        </div>
        <div class="h-8 w-16 rounded bg-theme-bg"></div>
        <div class="h-3 w-32 rounded bg-theme-bg pt-2 border-t border-theme-border"></div>
      </div>
    </div>

    <!-- Filters Skeleton -->
    <div class="flex flex-col md:flex-row justify-between gap-4">
      <div class="flex gap-2 overflow-hidden">
        <div v-for="i in 6" :key="i" class="h-10 w-24 rounded-xl bg-theme-bg"></div>
      </div>
      <div class="flex gap-3">
        <div class="h-10 w-32 rounded-xl bg-theme-bg"></div>
        <div class="h-10 w-32 rounded-xl bg-theme-bg"></div>
      </div>
    </div>

    <!-- Table Skeleton with Segmented Shimmer -->
    <div class="rounded-2xl border border-theme-border bg-theme-surface overflow-hidden p-6 space-y-4">
      <div class="h-8 w-full rounded-lg bg-theme-bg border-b border-theme-border"></div>
      <div v-for="row in 6" :key="row" class="grid grid-cols-1 md:grid-cols-[1.5fr_1.5fr_1fr_1fr_1fr_auto] gap-4 items-center py-3 border-b border-theme-border">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-xl bg-theme-bg"></div>
          <div class="space-y-1.5">
            <div class="h-3.5 w-28 rounded bg-theme-bg"></div>
            <div class="h-2.5 w-16 rounded bg-theme-bg"></div>
          </div>
        </div>
        <div class="space-y-1.5 hidden md:block">
          <div class="h-3.5 w-32 rounded bg-theme-bg"></div>
          <div class="h-2.5 w-20 rounded bg-theme-bg"></div>
        </div>
        <div class="space-y-1.5 hidden md:block">
          <div class="h-3.5 w-24 rounded bg-theme-bg"></div>
          <div class="h-2.5 w-16 rounded bg-theme-bg"></div>
        </div>
        <div class="h-6 w-20 rounded-full bg-theme-bg hidden md:block"></div>
        <div class="h-6 w-24 rounded-full bg-theme-bg hidden md:block"></div>
        <div class="h-8 w-28 rounded-xl bg-theme-bg justify-self-end"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Pure presentation component for skeletal preloading
</script>
