<template>
  <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 backdrop-blur-3xl">
    <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/10 blur-[100px]"></div>
    <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-purple-500/10 blur-[80px]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,103,0,0.05),transparent_60%)]"></div>
    
    <div class="relative z-10 flex-1 min-w-0">
      <div class="flex items-center gap-3 mb-2">
        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-admin/20 text-admin border border-admin/30">
          <CalendarDaysIcon class="h-3.5 w-3.5" />
        </span>
        <p class="text-[10px] uppercase tracking-[0.3em] text-admin/80 font-bold truncate">Booking Management</p>
      </div>
      <h1 class="font-display text-3xl lg:text-4xl text-white drop-shadow-md leading-tight whitespace-nowrap">
        Saloon <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-amber-400">Appointments</span>
      </h1>
      <p class="mt-3 text-sm text-white/40 max-w-md leading-relaxed hidden sm:block">
        Review, verify payments, and manage all customer bookings in real-time.
      </p>
    </div>
    
    <div class="relative z-10 flex flex-col items-end gap-3 shrink-0 w-full lg:w-auto">
      <!-- Search Bar -->
      <div class="relative w-full lg:w-80">
        <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-white/30" />
        <input
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
          type="text"
          placeholder="Search by client name..."
          class="w-full bg-white/[0.03] border border-white/[0.08] text-sm text-white rounded-2xl py-3 pl-11 pr-4 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 placeholder:text-white/20"
        />
      </div>
      
      <!-- Action Buttons -->
      <div class="flex flex-wrap justify-end gap-3 w-full">
        <button @click="$router.push('/admin/walk-in')" class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-400 px-4 py-2 text-xs font-bold text-obsidian transition-all hover:shadow-[0_4px_15px_rgba(16,185,129,0.2)] group">
          <UserPlusIcon class="h-4 w-4 group-hover:scale-110 transition-transform" />
          New Walk-In
        </button>
        <button @click="$emit('open-settings')" class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-4 py-2 text-xs font-bold text-obsidian transition-all hover:shadow-[0_4px_15px_rgba(255,103,0,0.2)] group">
          <BanknotesIcon class="h-4 w-4 group-hover:scale-110 transition-transform" />
          Bank Settings
        </button>
        <button @click="$emit('refresh')" class="flex items-center justify-center gap-2 rounded-xl bg-white/[0.05] border border-white/10 px-4 py-2 text-xs font-bold text-white transition-all hover:bg-white/10 group">
          <ArrowPathIcon class="h-4 w-4 group-hover:rotate-180 transition-transform duration-500" :class="{ 'animate-spin': isRefreshing }" />
          Refresh
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  CalendarDaysIcon,
  MagnifyingGlassIcon,
  UserPlusIcon,
  BanknotesIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline';

defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  isRefreshing: {
    type: Boolean,
    default: false
  }
});

defineEmits(['update:modelValue', 'refresh', 'open-settings']);
</script>
