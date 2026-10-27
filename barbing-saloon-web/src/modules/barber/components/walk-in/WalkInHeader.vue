<template>
  <div class="relative rounded-3xl p-6 sm:p-8 md:p-10 mb-8 bg-[#12161C] border border-white/[0.08] overflow-hidden shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
    <div class="absolute -top-36 -right-36 w-80 h-80 bg-emerald-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-emerald-500/15 transition-colors duration-700"></div>
    <div class="absolute -bottom-36 -left-36 w-80 h-80 bg-[#FF6700]/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-[#FF6700]/15 transition-colors duration-700"></div>

    <div class="relative z-10">
      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 mb-4">
        <UserPlusIcon class="h-3.5 w-3.5 text-emerald-400" />
        <span class="text-[10px] font-bold text-emerald-400 tracking-widest uppercase">Immediate Intake</span>
      </div>
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-white tracking-tight mb-2">
        New <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-[#FF6700]">Walk-In</span>
      </h1>
      <p class="text-slate-400 text-xs sm:text-sm max-w-lg leading-relaxed">
        Quickly check in physical saloon guests, allocate service chairs, and log payments without advance booking friction.
      </p>
    </div>

    <div class="relative z-10 flex items-center gap-3 shrink-0">
      <div class="px-4 py-2 rounded-2xl bg-white/[0.03] border border-white/[0.08] text-right">
        <p class="text-[10px] uppercase font-bold tracking-widest text-slate-400">Saloon Desk</p>
        <p class="text-xs font-semibold text-emerald-400">Fast-Track Mode Active</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { UserPlusIcon } from '@heroicons/vue/24/outline';
</script>
