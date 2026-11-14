<template>
  <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    <div 
      v-for="n in 6" 
      :key="'skeleton-' + n" 
      class="relative flex flex-col overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)]/80"
    >
      <!-- Media Skeleton -->
      <div class="relative aspect-[4/3] w-full bg-[var(--color-bg-tertiary)] animate-pulse">
        <div class="absolute left-4 top-4 h-5 w-20 rounded-full bg-white/10 animate-pulse"></div>
      </div>
      <!-- Content Skeleton -->
      <div class="flex flex-1 flex-col p-6 gap-3">
        <div class="flex justify-between items-start gap-4">
          <div class="h-6 w-3/5 bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
          <div class="h-6 w-1/4 bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
        </div>
        <div class="space-y-2 mt-2">
          <div class="h-3.5 w-full bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
          <div class="h-3.5 w-4/5 bg-[var(--color-bg-tertiary)] animate-pulse rounded-md"></div>
        </div>
        <div class="mt-6 pt-4 border-t border-[var(--color-border)] flex gap-3">
          <div class="h-10 flex-1 rounded-xl bg-[var(--color-bg-tertiary)] animate-pulse"></div>
          <div class="h-10 flex-[1.6] rounded-xl bg-[var(--color-bg-tertiary)] animate-pulse"></div>
        </div>
      </div>
    </div>
  </div>
</template>

