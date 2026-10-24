<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl space-y-6">
    <!-- Section Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.15)]">
          <ListBulletIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Contract Clauses & Articles Editor</h2>
          <p class="text-xs text-white/50 mt-0.5">Author, reorder, and structure the operational service agreements and terms shown to customers.</p>
        </div>
      </div>
      <div class="flex items-center gap-2 self-start sm:self-auto">
        <button
          v-if="!hasBookingPolicy"
          @click="addBookingClause"
          type="button"
          class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 hover:bg-amber-500/20 transition-all"
          title="Add Appointment & Cancellation Policy clause"
        >
          <ScaleIcon class="h-4 w-4" />
          <span>+ Add Booking Rules</span>
        </button>

        <button 
          @click="$emit('add-section')" 
          type="button"
          class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-obsidian bg-gradient-to-r from-admin to-admin-light shadow-md hover:shadow-lg active:scale-95 transition-all"
        >
          <PlusIcon class="h-4 w-4" />
          <span>Add Clause</span>
        </button>
      </div>
    </div>

    <!-- Clauses List -->
    <div class="space-y-4">
      <div 
        v-for="(section, index) in sections" 
        :key="index"
        class="rounded-xl border border-white/[0.08] bg-[#0B0F13] overflow-hidden p-5 sm:p-6 space-y-4 shadow-md transition-all hover:border-white/[0.15]"
      >
        <!-- Clause Top Bar -->
        <div class="flex items-center justify-between pb-3 border-b border-white/[0.04]">
          <div class="flex items-center gap-2.5">
            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-white/5 text-amber-400 font-mono text-xs font-bold border border-white/10">
              {{ index + 1 }}
            </span>
            <span class="text-xs font-bold text-white uppercase tracking-wider">
              Clause {{ index + 1 }}
            </span>
          </div>

          <!-- Reorder & Action Controls -->
          <div class="flex items-center gap-1">
            <button 
              type="button"
              :disabled="index === 0"
              @click="$emit('move-section', index, -1)"
              class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors disabled:opacity-20 disabled:cursor-not-allowed"
              title="Move clause up"
            >
              <ChevronUpIcon class="h-4 w-4" />
            </button>
            <button 
              type="button"
              :disabled="index === sections.length - 1"
              @click="$emit('move-section', index, 1)"
              class="p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors disabled:opacity-20 disabled:cursor-not-allowed"
              title="Move clause down"
            >
              <ChevronDownIcon class="h-4 w-4" />
            </button>
            <div class="h-4 w-px bg-white/10 mx-1"></div>
            <button 
              type="button"
              :disabled="sections.length <= 1"
              @click="$emit('remove-section', index)"
              class="flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 transition-colors disabled:opacity-20 disabled:cursor-not-allowed"
              title="Remove clause"
            >
              <TrashIcon class="h-3.5 w-3.5" />
              <span class="hidden sm:inline">Delete</span>
            </button>
          </div>
        </div>

        <!-- Inputs: Heading & Body -->
        <div class="space-y-3">
          <div class="space-y-1.5">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-white/60">
              Clause Heading / Title
            </label>
            <input 
              v-model="section.heading" 
              type="text" 
              placeholder="e.g. 2. Booking and Appointments" 
              class="w-full rounded-xl border border-white/[0.08] bg-[#12161C] px-4 py-2.5 text-sm font-semibold text-white placeholder-white/20 focus:border-admin/60 focus:outline-none transition-all" 
            />
          </div>

          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label class="block text-[11px] font-bold uppercase tracking-wider text-white/60">
                Legal Text Content
              </label>
              <span class="text-[10px] font-mono text-white/40">
                {{ (section.body || '').length }} chars
              </span>
            </div>
            <textarea 
              v-model="section.body" 
              rows="5" 
              placeholder="Enter clause provisions, operational requirements, cancellation policies, or payment rules..." 
              class="w-full rounded-xl border border-white/[0.08] bg-[#12161C] px-4 py-3 text-xs sm:text-sm text-white/90 placeholder-white/20 focus:border-admin/60 focus:outline-none transition-all resize-y leading-relaxed"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Add Clause Area -->
    <div class="pt-2">
      <button 
        @click="$emit('add-section')" 
        type="button"
        class="w-full rounded-xl border-2 border-dashed border-white/10 hover:border-admin/40 bg-white/[0.02] hover:bg-white/[0.04] py-4 text-xs font-bold uppercase tracking-wider text-white/50 hover:text-white transition-all duration-200 flex items-center justify-center gap-2 group"
      >
        <PlusIcon class="h-4 w-4 text-admin group-hover:scale-110 transition-transform" />
        <span>Append New Terms Clause</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { 
  ListBulletIcon, 
  PlusIcon, 
  ChevronUpIcon, 
  ChevronDownIcon, 
  TrashIcon, 
  ScaleIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  sections: {
    type: Array,
    required: true
  },
  hasBookingPolicy: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['add-section', 'remove-section', 'move-section']);

const addBookingClause = () => {
  const nextNum = props.sections.length + 1;
  props.sections.push({
    heading: `${nextNum}. Booking and Appointment Terms`,
    body: "We strive to provide excellent service and value your time. Please note our policies regarding appointments:\n\n• Appointments can be booked up to 30 days in advance.\n• We require at least 24 hours notice for cancellations or rescheduling.\n• Late arrivals (more than 15 minutes) may result in a shortened service or cancellation.\n• No-shows may be subject to a fee equal to 50% of the scheduled service cost."
  });
};
</script>
