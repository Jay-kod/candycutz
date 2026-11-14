<template>
  <div class="space-y-6 animate-pulse">
    <!-- Header banner skeleton -->
    <div class="h-44 rounded-3xl bg-slate-200 dark:bg-[#12161C] border border-slate-300 dark:border-white/[0.08] p-8 flex flex-col justify-between">
      <div class="space-y-2">
        <div class="h-6 w-32 rounded-xl bg-slate-300 dark:bg-white/[0.05]"></div>
        <div class="h-8 w-64 rounded-xl bg-slate-300 dark:bg-white/[0.05]"></div>
        <div class="h-4 w-96 rounded-lg bg-slate-300/80 dark:bg-white/[0.03]"></div>
      </div>
    </div>

    <!-- 4 KPI cards skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="h-28 rounded-3xl bg-slate-200 dark:bg-[#12161C] border border-slate-300 dark:border-white/[0.08] p-5 flex flex-col justify-between">
        <div class="flex justify-between items-center">
          <div class="h-4 w-24 rounded bg-slate-300 dark:bg-white/[0.05]"></div>
          <div class="h-8 w-8 rounded-xl bg-slate-300 dark:bg-white/[0.05]"></div>
        </div>
        <div class="h-7 w-16 rounded bg-slate-300 dark:bg-white/[0.05]"></div>
      </div>
    </div>

    <!-- Filter bar skeleton -->
    <div class="h-16 rounded-3xl bg-slate-200 dark:bg-[#12161C] border border-slate-300 dark:border-white/[0.08]"></div>

    <!-- Posts grid skeleton -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="h-80 rounded-3xl bg-slate-200 dark:bg-[#12161C] border border-slate-300 dark:border-white/[0.08] overflow-hidden">
        <div class="h-40 bg-slate-300 dark:bg-white/[0.03]"></div>
        <div class="p-6 space-y-3">
          <div class="h-4 w-24 rounded bg-slate-300 dark:bg-white/[0.05]"></div>
          <div class="h-5 w-3/4 rounded bg-slate-300 dark:bg-white/[0.05]"></div>
          <div class="h-3 w-full rounded bg-slate-300/80 dark:bg-white/[0.03]"></div>
          <div class="h-3 w-2/3 rounded bg-slate-300/80 dark:bg-white/[0.03]"></div>
        </div>
      </div>
    </div>
  </div>
</template>
