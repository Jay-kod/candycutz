<template>
  <div class="space-y-6 animate-pulse" aria-busy="true" aria-label="Loading Gallery page content editor">
    <!-- Header Skeleton -->
    <div class="rounded-[2rem] border border-white/[0.08] bg-[#12161C] p-6 lg:p-8">
      <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div class="space-y-3 flex-1">
          <div class="flex items-center gap-2">
            <div class="h-5 w-28 rounded-md bg-white/[0.05]"></div>
            <div class="h-5 w-24 rounded-full bg-white/[0.05]"></div>
          </div>
          <div class="h-10 w-64 rounded-xl bg-white/[0.08]"></div>
          <div class="h-4 w-96 rounded-md bg-white/[0.04]"></div>
        </div>
        <div class="flex items-center gap-2.5">
          <div class="h-10 w-28 rounded-xl bg-white/[0.06]"></div>
          <div class="h-10 w-28 rounded-xl bg-white/[0.06]"></div>
          <div class="h-10 w-32 rounded-xl bg-admin/20"></div>
        </div>
      </div>
    </div>

    <!-- Stats Skeleton -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 space-y-4">
        <div class="flex items-center justify-between">
          <div class="h-3 w-20 rounded bg-white/[0.05]"></div>
          <div class="h-7 w-7 rounded-lg bg-white/[0.05]"></div>
        </div>
        <div class="h-8 w-24 rounded-lg bg-white/[0.08]"></div>
        <div class="h-3 w-full rounded bg-white/[0.04] pt-2 border-t border-white/[0.04]"></div>
      </div>
    </div>

    <!-- Section Tabs Skeleton -->
    <div class="flex items-center gap-2">
      <div v-for="i in 4" :key="i" class="h-9 w-28 rounded-xl bg-white/[0.04]"></div>
    </div>

    <!-- Main Content Panel Skeleton -->
    <div class="rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 space-y-6">
      <div class="flex items-center gap-4 pb-6 border-b border-white/[0.06]">
        <div class="h-12 w-12 rounded-xl bg-white/[0.06]"></div>
        <div class="space-y-2">
          <div class="h-6 w-48 rounded bg-white/[0.08]"></div>
          <div class="h-3 w-72 rounded bg-white/[0.04]"></div>
        </div>
      </div>
      <div class="grid lg:grid-cols-2 gap-8">
        <div class="space-y-4">
          <div class="h-12 rounded-xl bg-white/[0.04]"></div>
          <div class="h-28 rounded-xl bg-white/[0.04]"></div>
          <div class="h-24 rounded-xl bg-white/[0.04]"></div>
        </div>
        <div class="space-y-4">
          <div class="aspect-video rounded-2xl bg-white/[0.04]"></div>
          <div class="h-16 rounded-xl bg-white/[0.04]"></div>
        </div>
      </div>
    </div>
  </div>
</template>
