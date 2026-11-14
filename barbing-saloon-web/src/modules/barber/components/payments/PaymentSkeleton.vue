<template>
  <div class="space-y-6 animate-pulse">
    <!-- Header banner placeholder -->
    <div class="h-44 rounded-3xl bg-slate-200 dark:bg-[#12161C] border border-slate-300 dark:border-white/[0.08]"></div>

    <!-- Table placeholder -->
    <div class="rounded-3xl border border-slate-300 dark:border-white/[0.08] bg-slate-200 dark:bg-[#12161C] p-6 space-y-4">
      <div v-for="i in 5" :key="i" class="h-16 rounded-2xl bg-slate-300 dark:bg-white/[0.03]"></div>
    </div>
  </div>
</template>
