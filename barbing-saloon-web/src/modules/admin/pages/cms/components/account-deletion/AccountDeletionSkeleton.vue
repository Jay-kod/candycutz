<template>
  <div class="space-y-6 animate-pulse">
    <!-- Stat Cards Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div 
        v-for="i in 4" 
        :key="i" 
        class="h-32 rounded-2xl border border-white/[0.06] bg-[#12161C] p-5 space-y-3"
      >
        <div class="flex justify-between items-center">
          <div class="h-3 w-24 bg-white/[0.06] rounded-md"></div>
          <div class="h-7 w-7 bg-white/[0.06] rounded-lg"></div>
        </div>
        <div class="h-6 w-36 bg-white/[0.08] rounded-md"></div>
        <div class="h-3 w-28 bg-white/[0.04] rounded-md pt-2 border-t border-white/[0.04]"></div>
      </div>
    </div>

    <!-- Navigation Tabs Skeleton -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2">
      <div v-for="i in 5" :key="i" class="h-9 w-28 bg-[#12161C] border border-white/[0.06] rounded-xl shrink-0"></div>
    </div>

    <!-- Form Container Skeleton -->
    <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] overflow-hidden p-6 sm:p-8 space-y-6 shadow-xl">
      <div class="space-y-2 border-b border-white/[0.06] pb-5">
        <div class="h-5 w-48 bg-white/[0.08] rounded-md"></div>
        <div class="h-3 w-72 bg-white/[0.04] rounded-md"></div>
      </div>
      <div class="space-y-4">
        <div class="h-10 w-full bg-white/[0.04] rounded-xl"></div>
        <div class="h-28 w-full bg-white/[0.03] rounded-xl"></div>
      </div>
    </div>
  </div>
</template>
