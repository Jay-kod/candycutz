<template>
  <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
    <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Appointments</p>
        <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
          Manage Your <span class="text-gold">Chair List</span>
        </h1>
      </div>
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
        <button 
          @click="$router.push('/barber/walk-in')"
          class="inline-flex items-center gap-2 rounded-xl bg-gold px-5 py-2.5 text-sm font-bold text-obsidian shadow-lg shadow-gold/20 transition-all hover:bg-gold-light hover:scale-105 hover:shadow-[0_0_20px_rgba(212,175,55,0.4)]"
        >
          <UserPlusIcon class="h-4 w-4" />
          New Walk-In
        </button>
        <button 
          @click="$emit('refresh')"
          class="flex items-center gap-2 rounded-xl bg-white/[0.04] border border-theme-border px-4 py-2.5 text-xs font-bold text-ivory/50 hover:text-gold hover:border-gold/30 hover:bg-gold/10 transition-all shadow-sm group"
          title="Refresh Data"
        >
          <ArrowPathIcon class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" :class="{ 'animate-spin': loading }" />
          <span class="hidden sm:inline">Refresh</span>
        </button>
        <div class="flex items-center gap-2 rounded-xl bg-white/[0.04] border border-theme-border px-4 py-2.5">
          <MagnifyingGlassIcon class="h-4 w-4 text-ivory/30" />
          <input
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            type="text"
            placeholder="Search client..."
            class="bg-transparent text-sm text-theme-text placeholder:text-ivory/25 outline-none w-32 sm:w-40"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  UserPlusIcon,
  ArrowPathIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';

defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  loading: {
    type: Boolean,
    default: false
  }
});

defineEmits(['update:modelValue', 'refresh']);
</script>
