<template>
  <div class="space-y-6 animate-pulse pb-10" aria-busy="true" aria-label="Loading Service Menu">
    <!-- Header Skeleton -->
    <div class="rounded-[2rem] border border-theme-border bg-theme-surface p-6 lg:p-8 flex flex-col lg:flex-row justify-between gap-6">
      <div class="space-y-3 flex-1">
        <div class="h-4 w-36 bg-theme-border rounded-md"></div>
        <div class="h-8 w-64 bg-theme-border rounded-xl"></div>
        <div class="h-3 w-96 bg-theme-surface-subtle rounded"></div>
      </div>
      <div class="flex flex-col sm:flex-row items-center gap-3">
        <div class="h-10 w-full sm:w-64 bg-theme-surface-subtle rounded-xl"></div>
        <div class="h-10 w-32 bg-theme-border rounded-xl"></div>
      </div>
    </div>

    <!-- 4 KPI Stat Cards Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="rounded-2xl border border-theme-border bg-theme-surface p-5 space-y-3">
        <div class="flex justify-between items-center">
          <div class="h-3 w-20 bg-theme-border rounded"></div>
          <div class="h-7 w-7 bg-theme-border rounded-lg"></div>
        </div>
        <div class="h-8 w-24 bg-theme-border rounded-lg"></div>
        <div class="h-3 w-36 bg-theme-surface-subtle rounded pt-2 border-t border-theme-border/50"></div>
      </div>
    </div>

    <!-- Filter Bar Skeleton -->
    <div class="flex flex-col sm:flex-row justify-between gap-4">
      <div class="h-11 w-96 bg-theme-surface border border-theme-border rounded-2xl"></div>
      <div class="h-11 w-44 bg-theme-surface border border-theme-border rounded-xl"></div>
    </div>

    <!-- Grid Skeleton (6 Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="rounded-3xl border border-theme-border bg-theme-surface overflow-hidden flex flex-col">
        <div class="h-44 w-full bg-theme-surface-subtle"></div>
        <div class="p-5 space-y-3 flex-1 flex flex-col">
          <div class="h-5 w-3/4 bg-theme-border rounded"></div>
          <div class="h-3 w-full bg-theme-surface-subtle rounded"></div>
          <div class="h-3 w-2/3 bg-theme-surface-subtle rounded"></div>
          <div class="flex gap-2 pt-2 mt-auto">
            <div class="h-5 w-16 bg-theme-border rounded-full"></div>
            <div class="h-5 w-14 bg-theme-border rounded-full"></div>
          </div>
          <div class="pt-3 border-t border-theme-border flex justify-between items-center">
            <div class="h-6 w-20 bg-theme-border rounded"></div>
            <div class="h-6 w-16 bg-theme-surface-subtle rounded"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
