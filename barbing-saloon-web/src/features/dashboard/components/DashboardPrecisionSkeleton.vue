<template>
  <div class="space-y-4 animate-fade-in">
    <!-- Header Skeleton -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-1">
      <div class="space-y-1.5">
        <div class="h-7 w-64 rounded-lg bg-white/[0.06] animate-pulse"></div>
        <div class="h-3 w-44 rounded bg-white/[0.03] animate-pulse"></div>
      </div>
      <div class="h-9 w-32 rounded-lg bg-admin/15 border border-admin/25 animate-pulse"></div>
    </div>

    <!-- Row 1: 4 KPI Card Skeletons with System Dark Palette -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="i in 4"
        :key="'sk-kpi-' + i"
        class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 space-y-4 relative overflow-hidden"
      >
        <div class="flex items-center gap-2">
          <div class="h-4 w-4 rounded bg-admin/20 animate-pulse"></div>
          <div class="h-3.5 w-24 rounded bg-white/[0.04] animate-pulse"></div>
        </div>
        <div class="h-7 w-32 rounded bg-white/[0.07] animate-pulse"></div>
        <div class="flex items-center justify-between pt-1">
          <div class="h-3 w-28 rounded bg-white/[0.04] animate-pulse"></div>
          <div class="flex items-end gap-[3px] h-4">
            <span v-for="b in 6" :key="b" class="w-[3px] rounded-t-[1px] bg-admin/25 animate-pulse" :style="{ height: `${b * 16}%` }"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Row 2: 2 Revenue Chart Skeletons with Wide & Closely Spaced Bars -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
      <!-- Left 8-col Skeleton: 28 Wide & Closely Spaced Columns -->
      <div class="lg:col-span-8 rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 sm:p-6 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between gap-4 pb-4 border-b border-white/[0.04]">
          <div class="space-y-2">
            <div class="h-7 w-28 rounded bg-white/[0.07] animate-pulse"></div>
            <div class="h-3.5 w-44 rounded bg-white/[0.04] animate-pulse"></div>
            <div class="flex gap-4 pt-1">
              <div class="h-3 w-16 rounded bg-admin/30 animate-pulse"></div>
              <div class="h-3 w-20 rounded bg-white/[0.03] animate-pulse"></div>
            </div>
          </div>
          <div class="h-7 w-36 rounded-lg bg-white/[0.04] animate-pulse"></div>
        </div>

        <!-- 28 Wide & Closely Spaced Columns Skeleton -->
        <div class="h-56 flex items-end justify-between gap-[2px] sm:gap-[3px] md:gap-1 px-1 pb-5 w-full">
          <div
            v-for="c in 28"
            :key="'sk-col-' + c"
            class="flex-1 flex flex-col-reverse gap-[2px] items-center"
          >
            <span
              v-for="b in ((c % 7) + 10)"
              :key="'sk-b-' + b"
              class="w-full max-w-[20px] sm:max-w-[22px] h-[5px] sm:h-[6px] rounded-[1px] bg-admin/20 animate-pulse"
            ></span>
          </div>
        </div>
      </div>

      <!-- Right 4-col Skeleton: Wide Monthly Bars -->
      <div class="lg:col-span-4 rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 sm:p-6 space-y-6">
        <div class="flex justify-between items-start pb-4 border-b border-white/[0.04]">
          <div class="space-y-2">
            <div class="h-7 w-24 rounded bg-white/[0.07] animate-pulse"></div>
            <div class="h-3.5 w-40 rounded bg-white/[0.04] animate-pulse"></div>
          </div>
          <div class="h-4 w-12 rounded bg-emerald-500/20 animate-pulse"></div>
        </div>

        <!-- Wide Monthly Bars Skeleton -->
        <div class="h-56 flex items-end justify-between gap-3 sm:gap-4 md:gap-5 px-3 pb-5">
          <div
            v-for="m in 6"
            :key="'sk-mbar-' + m"
            class="flex-1 flex flex-col-reverse gap-[2px] items-center"
          >
            <span
              v-for="seg in ((m % 4) * 3 + 10)"
              :key="'sk-mseg-' + seg"
              class="w-full max-w-[24px] sm:max-w-[28px] h-[5px] sm:h-[6px] rounded-[1px] bg-admin/25 animate-pulse"
            ></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Row 3: 2 50/50 Card Skeletons -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <!-- Funnel Skeleton -->
      <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 sm:p-6 space-y-6">
        <div class="flex justify-between items-start pb-4 border-b border-white/[0.04]">
          <div class="space-y-2">
            <div class="h-7 w-20 rounded bg-white/[0.07] animate-pulse"></div>
            <div class="h-3.5 w-60 rounded bg-white/[0.04] animate-pulse"></div>
          </div>
          <div class="h-4 w-20 rounded bg-emerald-500/20 animate-pulse"></div>
        </div>

        <!-- Funnel Nodes Placeholder -->
        <div class="h-56 flex items-center justify-between px-6">
          <div v-for="node in 4" :key="'sk-fnode-' + node" class="flex flex-col items-center gap-2">
            <div class="h-4 w-10 rounded bg-white/[0.05] animate-pulse"></div>
            <div class="h-6 w-12 rounded-full bg-admin/20 animate-pulse"></div>
            <div class="h-3 w-16 rounded bg-white/[0.04] animate-pulse mt-12"></div>
          </div>
        </div>
      </div>

      <!-- Donut Skeleton -->
      <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 sm:p-6 space-y-6">
        <div class="flex justify-between items-start pb-4 border-b border-white/[0.04]">
          <div class="space-y-2">
            <div class="h-7 w-24 rounded bg-white/[0.07] animate-pulse"></div>
            <div class="h-3.5 w-44 rounded bg-white/[0.04] animate-pulse"></div>
          </div>
          <div class="h-4 w-20 rounded bg-admin/20 animate-pulse"></div>
        </div>

        <!-- Donut & Legend -->
        <div class="h-56 flex items-center justify-between gap-6 px-4">
          <div class="w-36 h-36 rounded-full border-8 border-admin/15 animate-pulse shrink-0"></div>
          <div class="flex-1 space-y-3">
            <div v-for="row in 5" :key="'sk-drow-' + row" class="flex justify-between items-center">
              <div class="h-3.5 w-24 rounded bg-white/[0.04] animate-pulse"></div>
              <div class="h-3.5 w-16 rounded bg-white/[0.04] animate-pulse"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Row 4: 4 Card Skeletons -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Card 1 -->
      <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 space-y-4">
        <div class="h-4 w-28 rounded bg-white/[0.04] animate-pulse"></div>
        <div class="space-y-3 pt-2">
          <div v-for="r in 5" :key="'sk-trow-' + r" class="flex justify-between items-center">
            <div class="h-3 w-20 rounded bg-white/[0.04] animate-pulse"></div>
            <div class="h-3 w-12 rounded bg-white/[0.04] animate-pulse"></div>
          </div>
        </div>
      </div>

      <!-- Card 2 -->
      <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 flex flex-col items-center justify-center space-y-3 text-center">
        <div class="flex -space-x-2">
          <div class="h-10 w-10 rounded-full bg-admin/20 animate-pulse"></div>
          <div class="h-10 w-10 rounded-full bg-admin/30 animate-pulse"></div>
          <div class="h-10 w-10 rounded-full bg-admin/20 animate-pulse"></div>
        </div>
        <div class="h-4 w-28 rounded bg-white/[0.06] animate-pulse"></div>
        <div class="h-3 w-36 rounded bg-white/[0.03] animate-pulse"></div>
        <div class="h-7 w-28 rounded-lg bg-admin/20 animate-pulse pt-1"></div>
      </div>

      <!-- Card 3 -->
      <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 space-y-3">
        <div class="flex justify-between">
          <div class="h-4 w-24 rounded bg-white/[0.04] animate-pulse"></div>
          <div class="h-3 w-16 rounded bg-white/[0.03] animate-pulse"></div>
        </div>
        <div class="h-5 w-20 rounded bg-white/[0.06] animate-pulse"></div>
        <div class="h-20 w-full rounded bg-admin/10 animate-pulse"></div>
      </div>

      <!-- Card 4 -->
      <div class="rounded-xl border border-white/[0.08] bg-[#0B0F13]/90 p-5 space-y-3">
        <div class="h-4 w-24 rounded bg-white/[0.04] animate-pulse"></div>
        <div class="space-y-2.5 pt-1">
          <div v-for="q in 4" :key="'sk-qrow-' + q" class="flex items-center gap-2">
            <div class="h-7 w-7 rounded bg-admin/15 animate-pulse shrink-0"></div>
            <div class="flex-1 space-y-1">
              <div class="h-3 w-24 rounded bg-white/[0.05] animate-pulse"></div>
              <div class="h-2 w-32 rounded bg-white/[0.03] animate-pulse"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
