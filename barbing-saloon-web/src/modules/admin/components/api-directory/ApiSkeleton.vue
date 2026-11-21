<template>
  <div class="space-y-6 animate-pulse" aria-hidden="true">
    <!-- Stats Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div 
        v-for="i in 4" 
        :key="i"
        class="h-32 rounded-2xl border border-white/[0.04] bg-[#12161C]/60 p-5 flex flex-col justify-between"
      >
        <div class="flex items-center justify-between">
          <div class="h-3 w-20 rounded bg-white/[0.06]"></div>
          <div class="h-7 w-7 rounded-lg bg-white/[0.06]"></div>
        </div>
        <div class="h-8 w-16 rounded bg-white/[0.08]"></div>
        <div class="h-3 w-28 rounded bg-white/[0.04]"></div>
      </div>
    </div>

    <!-- Filter Bar Skeleton -->
    <div class="h-16 rounded-2xl border border-white/[0.04] bg-[#12161C]/60 flex items-center justify-between px-4">
      <div class="flex gap-2">
        <div class="h-8 w-24 rounded-lg bg-white/[0.06]"></div>
        <div class="h-8 w-20 rounded-lg bg-white/[0.06]"></div>
        <div class="h-8 w-20 rounded-lg bg-white/[0.06]"></div>
      </div>
      <div class="h-8 w-32 rounded-lg bg-white/[0.06]"></div>
    </div>

    <!-- Group Cards Skeleton -->
    <div class="space-y-6">
      <div 
        v-for="i in 2" 
        :key="i"
        class="rounded-[2rem] border border-white/[0.04] bg-[#12161C]/60 overflow-hidden space-y-3"
      >
        <div class="h-16 bg-white/[0.02] border-b border-white/[0.04] px-6 flex items-center justify-between">
          <div class="h-4 w-36 rounded bg-white/[0.08]"></div>
          <div class="h-6 w-16 rounded bg-white/[0.04]"></div>
        </div>
        <div class="p-6 space-y-4">
          <div v-for="j in 4" :key="j" class="h-10 rounded-xl bg-white/[0.02]"></div>
        </div>
      </div>
    </div>
  </div>
</template>

