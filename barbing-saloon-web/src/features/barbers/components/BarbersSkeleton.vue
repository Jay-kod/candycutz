<template>
  <div class="space-y-6 animate-pulse" aria-hidden="true">
    <!-- Header Skeleton -->
    <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.06] bg-[#12161C] p-6 lg:p-8 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div class="space-y-3">
        <div class="h-5 w-40 rounded-full bg-white/[0.04]"></div>
        <div class="h-10 w-64 rounded-xl bg-white/[0.06]"></div>
        <div class="h-4 w-96 rounded bg-white/[0.03]"></div>
      </div>
      <div class="flex flex-col items-end gap-3 shrink-0 w-full md:w-auto">
        <div class="h-11 w-full sm:w-80 rounded-2xl bg-white/[0.04]"></div>
        <div class="flex gap-2">
          <div class="h-9 w-32 rounded-xl bg-white/[0.05]"></div>
          <div class="h-9 w-24 rounded-xl bg-white/[0.05]"></div>
        </div>
      </div>
    </div>

    <!-- 4 Stats Cards Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="rounded-2xl border border-white/[0.06] bg-[#12161C] p-5 space-y-3">
        <div class="flex justify-between items-center">
          <div class="h-3 w-24 rounded bg-white/[0.04]"></div>
          <div class="h-7 w-7 rounded-lg bg-white/[0.04]"></div>
        </div>
        <div class="h-8 w-20 rounded bg-white/[0.06]"></div>
        <div class="h-3 w-32 rounded bg-white/[0.03] pt-2 border-t border-white/[0.04]"></div>
      </div>
    </div>

    <!-- Filters Skeleton -->
    <div class="flex flex-col md:flex-row justify-between gap-4">
      <div class="flex gap-2 overflow-hidden">
        <div v-for="i in 5" :key="i" class="h-10 w-28 rounded-xl bg-white/[0.04]"></div>
      </div>
      <div class="flex gap-3">
        <div class="h-10 w-36 rounded-xl bg-white/[0.04]"></div>
        <div class="h-10 w-36 rounded-xl bg-white/[0.04]"></div>
      </div>
    </div>

    <!-- Cards Grid Skeleton -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="rounded-3xl border border-white/[0.06] bg-[#12161C] p-6 space-y-5">
        <div class="flex items-center gap-4">
          <div class="h-14 w-14 rounded-2xl bg-white/[0.05]"></div>
          <div class="space-y-2 flex-1">
            <div class="h-4 w-32 rounded bg-white/[0.05]"></div>
            <div class="h-3 w-20 rounded bg-white/[0.03]"></div>
          </div>
        </div>
        <div class="space-y-2">
          <div class="h-3 w-full rounded bg-white/[0.03]"></div>
          <div class="h-3 w-4/5 rounded bg-white/[0.03]"></div>
        </div>
        <div class="flex gap-2">
          <div class="h-6 w-16 rounded-lg bg-white/[0.04]"></div>
          <div class="h-6 w-20 rounded-lg bg-white/[0.04]"></div>
        </div>
        <div class="h-9 w-full rounded-xl bg-white/[0.04]"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Pure presentation component for barbers skeletal loading
</script>