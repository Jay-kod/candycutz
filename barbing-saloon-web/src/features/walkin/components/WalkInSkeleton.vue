<template>
  <div class="space-y-8 animate-pulse">
    <!-- Header Skeleton -->
    <div class="h-36 rounded-2xl bg-theme-surface border border-theme-border p-8 flex flex-col justify-between">
      <div class="w-48 h-5 rounded-full bg-theme-bg"></div>
      <div class="w-80 h-10 rounded-xl bg-theme-bg"></div>
      <div class="w-96 h-4 rounded bg-theme-bg"></div>
    </div>

    <!-- Main Grid Skeleton -->
    <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-8">
      <!-- Left Column Skeletons -->
      <div class="space-y-6">
        <!-- Client Info Skeleton -->
        <div class="rounded-2xl border border-theme-border bg-theme-surface p-6 space-y-4">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-theme-bg"></div>
            <div class="space-y-1.5">
              <div class="h-4 w-32 rounded bg-theme-bg"></div>
              <div class="h-3 w-48 rounded bg-theme-bg"></div>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <div class="h-14 rounded-xl bg-theme-bg"></div>
            <div class="h-14 rounded-xl bg-theme-bg"></div>
          </div>
        </div>

        <!-- Service Picker Skeleton -->
        <div class="rounded-2xl border border-theme-border bg-theme-surface p-6 space-y-4">
          <div class="h-5 w-40 rounded bg-theme-bg"></div>
          <div class="flex gap-2">
            <div v-for="i in 4" :key="i" class="h-8 w-20 rounded-lg bg-theme-bg"></div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            <div v-for="i in 4" :key="i" class="h-24 rounded-xl bg-theme-bg"></div>
          </div>
        </div>

        <!-- Barber Picker Skeleton -->
        <div class="rounded-2xl border border-theme-border bg-theme-surface p-6 space-y-4">
          <div class="h-5 w-40 rounded bg-theme-bg"></div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            <div v-for="i in 2" :key="i" class="h-20 rounded-xl bg-theme-bg"></div>
          </div>
        </div>
      </div>

      <!-- Right Column Ticket Skeleton -->
      <div class="space-y-6">
        <div class="rounded-2xl border border-theme-border bg-theme-surface p-6 h-96 flex flex-col justify-between">
          <div class="space-y-3">
            <div class="h-12 w-12 mx-auto rounded-xl bg-theme-bg"></div>
            <div class="h-5 w-32 mx-auto rounded bg-theme-bg"></div>
            <div class="h-3 w-40 mx-auto rounded bg-theme-bg"></div>
          </div>
          <div class="space-y-4">
            <div class="h-10 rounded-lg bg-theme-bg"></div>
            <div class="h-10 rounded-lg bg-theme-bg"></div>
            <div class="h-16 rounded-xl bg-theme-bg"></div>
          </div>
          <div class="h-12 rounded-xl bg-theme-bg"></div>
        </div>
      </div>
    </div>
  </div>
</template>
