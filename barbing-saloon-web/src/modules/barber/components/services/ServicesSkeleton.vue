<template>
  <div class="space-y-6 animate-pulse">
    <!-- Header skeleton -->
    <div class="h-44 rounded-3xl bg-slate-200 dark:bg-[#12161C] border border-slate-300 dark:border-white/[0.08]"></div>

    <!-- Tabs skeleton -->
    <div class="flex items-center gap-2">
      <div v-for="i in 4" :key="i" class="h-10 w-28 rounded-xl bg-slate-200 dark:bg-[#12161C] border border-slate-300 dark:border-white/[0.08]"></div>
    </div>

    <!-- Grid skeleton -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="h-56 rounded-3xl bg-slate-200 dark:bg-[#12161C] border border-slate-300 dark:border-white/[0.08]"></div>
    </div>
  </div>
</template>
