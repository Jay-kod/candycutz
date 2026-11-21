<template>
  <div class="space-y-6 animate-pulse" aria-hidden="true">
    <!-- Header Banner Skeleton -->
    <div class="h-44 rounded-3xl bg-theme-surface border border-theme-border/50"></div>

    <!-- 4 KPI Cards Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="h-28 rounded-2xl bg-theme-surface border border-theme-border/50"></div>
    </div>

    <!-- Filters Bar Skeleton -->
    <div class="h-14 rounded-2xl bg-theme-surface border border-theme-border/50"></div>

    <!-- Review Cards Skeleton -->
    <div class="space-y-4">
      <div v-for="i in 3" :key="i" class="h-36 rounded-3xl bg-theme-surface border border-theme-border/50"></div>
    </div>
  </div>
</template>

