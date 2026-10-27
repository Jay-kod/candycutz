<template>
  <div class="space-y-6 animate-pulse pb-10" aria-busy="true" aria-label="Loading Gallery Portfolio">
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
      <div class="h-10 w-64 bg-theme-surface border border-theme-border rounded-xl"></div>
      <div class="h-10 w-48 bg-theme-surface border border-theme-border rounded-xl"></div>
    </div>

    <!-- Grid Skeleton (8 Photo Cards) -->
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      <div v-for="i in 8" :key="i" class="aspect-square rounded-3xl border border-theme-border bg-theme-surface overflow-hidden p-4 flex flex-col justify-between">
        <div class="flex justify-between">
          <div class="h-5 w-16 bg-theme-border rounded-lg"></div>
          <div class="h-6 w-14 bg-theme-border rounded-lg"></div>
        </div>
        <div class="space-y-2">
          <div class="h-5 w-3/4 bg-theme-border rounded"></div>
          <div class="h-3 w-1/2 bg-theme-surface-subtle rounded"></div>
        </div>
      </div>
    </div>
  </div>
</template>
