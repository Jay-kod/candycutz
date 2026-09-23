<template>
  <div class="relative overflow-hidden rounded-xl border-b border-theme-border bg-theme-surface p-8 lg:p-10">
    <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div>
        <p class="text-[11px] uppercase tracking-[0.35em] text-gold/70 font-bold">Barber Command Center</p>
        <h1 class="mt-2 font-display text-4xl lg:text-5xl text-theme-text leading-tight">
          Today's <span class="text-gold">Chair View</span>
        </h1>
        <p class="mt-3 max-w-xl text-sm text-theme-muted leading-relaxed">
          Review today's customers, manage your schedule, and close out appointments after the cut.
        </p>
      </div>

      <div class="flex items-center gap-5 shrink-0">
        <!-- Current Time -->
        <div class="hidden sm:flex flex-col items-end">
          <span class="text-2xl font-display text-theme-text tabular-nums">{{ currentTime }}</span>
          <span class="text-[10px] uppercase tracking-widest text-theme-muted font-bold mt-0.5">{{ currentDate }}</span>
        </div>

        <div class="h-10 w-px bg-theme-border hidden sm:block"></div>

        <!-- System Status -->
        <div class="flex items-center gap-3 bg-theme-bg px-4 py-2.5 rounded-lg border border-theme-border">
          <div class="flex flex-col items-end mr-2">
            <span class="text-xs font-bold uppercase tracking-wider text-theme-text">My Status</span>
            <span class="text-[10px]" :class="myStatus === 'suspended' ? 'text-red-400' : (myStatus === 'pending_approval' ? 'text-amber-400' : 'text-theme-muted')">
              {{ formatStatus(myStatus) }}
            </span>
          </div>
          <template v-if="myStatus === 'active' || myStatus === 'on_leave'">
            <button 
              @click="$emit('toggle-status', 'active')"
              class="px-3 py-1.5 rounded-md text-xs font-bold uppercase transition-colors"
              :class="myStatus === 'active' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-theme-surface text-theme-muted hover:bg-white/5 border border-transparent'"
            >Active</button>
            <button 
              @click="$emit('toggle-status', 'on_leave')"
              class="px-3 py-1.5 rounded-md text-xs font-bold uppercase transition-colors"
              :class="myStatus === 'on_leave' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-theme-surface text-theme-muted hover:bg-white/5 border border-transparent'"
            >Not Active</button>
          </template>
          <template v-else>
            <div class="px-4 py-1.5 rounded-md text-xs font-bold uppercase border"
                 :class="myStatus === 'suspended' ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20'">
              {{ myStatus === 'suspended' ? 'Suspended by Admin' : 'Pending Admin Approval' }}
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

defineProps({
  currentTime: { type: String, required: true },
  currentDate: { type: String, required: true },
  myStatus: { type: String, required: true },
  formatStatus: { type: Function, required: true }
});

defineEmits(['toggle-status']);
</script>
