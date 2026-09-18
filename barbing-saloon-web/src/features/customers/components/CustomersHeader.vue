<template>
  <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.05] bg-[#111111]/90 p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 backdrop-blur-3xl">
    <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-admin/10 blur-[100px]"></div>
    <div class="absolute -bottom-24 -left-24 h-56 w-56 rounded-full bg-blue-500/10 blur-[80px]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,103,0,0.05),transparent_60%)]"></div>
    
    <div class="relative z-10">
      <div class="flex items-center gap-3 mb-2">
        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-admin/20 text-admin border border-admin/30">
          <UserGroupIcon class="h-3.5 w-3.5" />
        </span>
        <p class="text-[10px] uppercase tracking-[0.3em] text-admin/80 font-bold">User Management</p>
      </div>
      <h1 class="font-display text-4xl lg:text-5xl text-white drop-shadow-md leading-tight">
        Customer <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-amber-400">Directory</span>
      </h1>
      <p class="mt-3 text-sm text-white/40 max-w-lg leading-relaxed">
        Manage your client base, view booking histories, track lifetime value, and identify top spenders.
      </p>
    </div>
    
    <div class="relative z-10 flex flex-col sm:flex-row items-center gap-4 shrink-0 w-full md:w-auto">
      <div class="relative w-full sm:w-72">
        <MagnifyingGlassIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-white/30" />
        <input
          :value="modelValue"
          @input="$emit('update:modelValue', $event.target.value)"
          type="text"
          placeholder="Search by name, email, or phone..."
          class="w-full bg-white/[0.03] border border-white/[0.08] text-sm text-white rounded-2xl py-3.5 pl-11 pr-4 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.05] focus:ring-4 focus:ring-admin/10 placeholder:text-white/20"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { UserGroupIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

defineProps({
  modelValue: { type: String, required: true }
});

defineEmits(['update:modelValue']);
</script>
