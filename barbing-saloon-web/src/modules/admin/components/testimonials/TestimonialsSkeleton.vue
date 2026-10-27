<template>
  <div class="space-y-6 animate-pulse pb-10" aria-busy="true" aria-label="Loading Testimonials Moderation">
    <!-- Header Skeleton -->
    <div class="rounded-[2rem] border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 flex flex-col lg:flex-row justify-between gap-6">
      <div class="space-y-3 flex-1">
        <div class="h-4 w-36 bg-white/[0.06] rounded-md"></div>
        <div class="h-8 w-64 bg-white/[0.08] rounded-xl"></div>
        <div class="h-3 w-96 bg-white/[0.04] rounded"></div>
      </div>
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <div class="h-10 w-full sm:w-64 bg-white/[0.04] rounded-xl"></div>
        <div class="h-10 w-28 bg-white/[0.08] rounded-xl"></div>
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

    <!-- Flow Settings Card Skeleton -->
    <div class="rounded-3xl border border-white/[0.08] bg-[#12161C] p-6 md:p-8 space-y-4">
      <div class="h-5 w-44 bg-white/[0.06] rounded"></div>
      <div class="h-3 w-80 bg-white/[0.04] rounded"></div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
        <div class="h-28 bg-white/[0.03] rounded-2xl"></div>
        <div class="h-28 bg-white/[0.03] rounded-2xl"></div>
      </div>
    </div>

    <!-- Filter Bar Skeleton -->
    <div class="flex flex-col sm:flex-row justify-between gap-4">
      <div class="h-10 w-80 bg-[#12161C] border border-white/[0.08] rounded-xl"></div>
      <div class="h-10 w-48 bg-[#12161C] border border-white/[0.08] rounded-xl"></div>
    </div>

    <!-- Reviews List Skeleton (3 Cards) -->
    <div class="space-y-4">
      <div v-for="i in 3" :key="i" class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 flex flex-col md:flex-row justify-between gap-6">
        <div class="flex-1 space-y-3">
          <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-full bg-white/[0.06]"></div>
            <div class="space-y-2">
              <div class="h-4 w-32 bg-white/[0.08] rounded"></div>
              <div class="h-3 w-24 bg-white/[0.04] rounded"></div>
            </div>
          </div>
          <div class="h-12 w-full bg-white/[0.03] rounded-xl"></div>
        </div>
        <div class="w-40 space-y-2 shrink-0">
          <div class="h-8 bg-white/[0.06] rounded-xl"></div>
          <div class="h-8 bg-white/[0.06] rounded-xl"></div>
        </div>
      </div>
    </div>
  </div>
</template>
