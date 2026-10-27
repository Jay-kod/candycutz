<template>
  <div class="space-y-6 animate-pulse pb-10" aria-busy="true" aria-label="Loading Feedback Inbox">
    <!-- Header Skeleton -->
    <div class="rounded-[2rem] border border-theme-border bg-theme-surface p-6 lg:p-8 flex flex-col lg:flex-row justify-between gap-6">
      <div class="space-y-3 flex-1">
        <div class="h-4 w-36 bg-theme-border/60 rounded-md"></div>
        <div class="h-8 w-64 bg-theme-border rounded-xl"></div>
        <div class="h-3 w-96 bg-theme-border/40 rounded"></div>
      </div>
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <div class="h-10 w-full sm:w-64 bg-theme-border/40 rounded-xl"></div>
        <div class="h-10 w-28 bg-theme-border rounded-xl"></div>
      </div>
    </div>

    <!-- 4 KPI Stat Cards Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="rounded-2xl border border-theme-border bg-theme-surface p-5 space-y-3">
        <div class="flex justify-between items-center">
          <div class="h-3 w-20 bg-theme-border/60 rounded"></div>
          <div class="h-7 w-7 bg-theme-border/60 rounded-lg"></div>
        </div>
        <div class="h-8 w-24 bg-theme-border rounded-lg"></div>
        <div class="h-3 w-36 bg-theme-border/40 rounded pt-2 border-t border-theme-border/40"></div>
      </div>
    </div>

    <!-- Filter Bar Skeleton -->
    <div class="flex flex-col sm:flex-row justify-between gap-4">
      <div class="h-11 w-96 bg-theme-surface border border-theme-border rounded-2xl"></div>
      <div class="h-11 w-56 bg-theme-surface border border-theme-border rounded-xl"></div>
    </div>

    <!-- Feedback Cards Skeleton (3 Cards) -->
    <div class="space-y-5">
      <div v-for="i in 3" :key="i" class="rounded-3xl border border-theme-border bg-theme-surface p-6 flex flex-col lg:flex-row justify-between gap-6">
        <div class="flex-1 space-y-3">
          <div class="flex gap-2">
            <div class="h-5 w-24 bg-theme-border/60 rounded-lg"></div>
            <div class="h-5 w-20 bg-theme-border/60 rounded-full"></div>
            <div class="h-4 w-28 bg-theme-border/40 rounded"></div>
          </div>
          <div class="h-6 w-3/4 bg-theme-border rounded-lg"></div>
          <div class="h-16 w-full bg-theme-surface-subtle rounded-2xl"></div>
          <div class="flex items-center gap-3 pt-2">
            <div class="h-8 w-8 rounded-full bg-theme-border/60"></div>
            <div class="h-4 w-40 bg-theme-border/40 rounded"></div>
          </div>
        </div>
        <div class="w-full lg:max-w-sm rounded-2xl bg-theme-surface-subtle border border-theme-border/50 p-4 space-y-3 shrink-0">
          <div class="h-4 w-28 bg-theme-border/60 rounded"></div>
          <div class="h-9 w-full bg-theme-border/40 rounded-xl"></div>
          <div class="h-16 w-full bg-theme-border/40 rounded-xl"></div>
          <div class="h-9 w-full bg-theme-border rounded-xl"></div>
        </div>
      </div>
    </div>
  </div>
</template>
