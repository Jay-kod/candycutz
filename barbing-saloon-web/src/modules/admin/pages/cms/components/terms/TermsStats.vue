<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: Terms Headline -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-admin/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-admin/5 blur-2xl group-hover:bg-admin/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Terms Title</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-admin/15 text-admin border border-admin/30">
          <DocumentTextIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-xl font-bold text-white tracking-tight truncate">
          {{ content?.terms_title || 'Terms of Service' }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">Public Legal Contract Active</span>
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
      </div>
    </div>

    <!-- Card 2: Clauses Count -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-emerald-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-emerald-500/5 blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Contract Clauses</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
          <ScaleIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-emerald-400 tracking-tight">{{ sectionsCount }}</span>
        <span class="text-xs text-white/40">articles</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">Enforceable Conditions</span>
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
      </div>
    </div>

    <!-- Card 3: Reading Volume -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-amber-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-amber-500/5 blur-2xl group-hover:bg-amber-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Document Length</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/15 text-amber-400 border border-amber-500/30">
          <ClockIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span class="font-display text-3xl font-bold text-amber-400 tracking-tight">{{ totalWords }}</span>
        <span class="text-xs text-white/40">words</span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">~{{ estimatedReadTime }} min reading time</span>
        <span 
          class="shrink-0 px-1.5 py-0.5 rounded text-[10px] font-mono"
          :class="totalWords > 0 ? 'bg-amber-500/10 text-amber-400' : 'bg-white/5 text-white/40'"
        >
          {{ totalWords > 400 ? 'Comprehensive' : 'Concise' }}
        </span>
      </div>
    </div>

    <!-- Card 4: Operations Rules -->
    <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-5 shadow-lg group hover:border-sky-500/40 transition-all duration-300">
      <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full bg-sky-500/5 blur-2xl group-hover:bg-sky-500/10 transition-all"></div>
      <div class="flex items-center justify-between relative z-10">
        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Operations Scope</span>
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/15 text-sky-400 border border-sky-500/30">
          <ShieldCheckIcon class="h-4 w-4" />
        </span>
      </div>
      <div class="mt-3 flex items-baseline gap-2 relative z-10">
        <span 
          class="font-display text-lg font-bold tracking-tight truncate"
          :class="hasBookingPolicy ? 'text-sky-400' : 'text-amber-400'"
        >
          {{ hasBookingPolicy && hasSafetyPolicy ? 'Full Operations Scope' : (hasBookingPolicy ? 'Booking Policy Active' : 'Basic Legal Scope') }}
        </span>
      </div>
      <div class="mt-3 flex items-center justify-between text-[11px] text-white/50 relative z-10 pt-2 border-t border-white/[0.04]">
        <span class="truncate">{{ hasBookingPolicy ? 'Cancellations & Studio Rules' : 'Add Booking Clauses' }}</span>
        <span class="h-1.5 w-1.5 rounded-full" :class="hasBookingPolicy ? 'bg-sky-400' : 'bg-amber-400'"></span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  DocumentTextIcon, 
  ScaleIcon, 
  ClockIcon, 
  ShieldCheckIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  content: {
    type: Object,
    default: () => ({})
  },
  sections: {
    type: Array,
    default: () => []
  },
  totalWords: {
    type: Number,
    default: 0
  },
  estimatedReadTime: {
    type: Number,
    default: 1
  },
  hasBookingPolicy: {
    type: Boolean,
    default: false
  },
  hasSafetyPolicy: {
    type: Boolean,
    default: false
  }
});

const sectionsCount = computed(() => props.sections?.length || 0);
</script>

