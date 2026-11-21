<template>
  <div class="space-y-6 animate-pulse">
    <!-- Banner skeleton -->
    <div class="h-44 rounded-3xl bg-slate-200 dark:bg-[#12161C] border border-slate-200 dark:border-white/[0.08]"></div>

    <!-- 4 KPI cards skeleton -->
    <div class="grid gap-5 grid-cols-2 lg:grid-cols-4">
      <div v-for="i in 4" :key="i" class="h-32 rounded-2xl bg-slate-200 dark:bg-[#12161C] border border-slate-200 dark:border-white/[0.08]"></div>
    </div>

    <!-- 2-column layout skeleton -->
    <div class="grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 space-y-6">
        <div class="h-64 rounded-2xl bg-slate-200 dark:bg-[#12161C] border border-slate-200 dark:border-white/[0.08]"></div>
        <div class="h-80 rounded-2xl bg-slate-200 dark:bg-[#12161C] border border-slate-200 dark:border-white/[0.08]"></div>
      </div>
      <div class="space-y-6">
        <div class="h-44 rounded-2xl bg-slate-200 dark:bg-[#12161C] border border-slate-200 dark:border-white/[0.08]"></div>
        <div class="h-72 rounded-2xl bg-slate-200 dark:bg-[#12161C] border border-slate-200 dark:border-white/[0.08]"></div>
      </div>
    </div>
  </div>
</template>
