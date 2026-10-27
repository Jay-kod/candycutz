<template>
  <div class="bg-[#12161C] rounded-3xl border border-white/[0.08] p-6 sm:p-8 relative overflow-hidden group hover:border-[#FF6700]/30 transition-all duration-300">
    <div class="flex items-center gap-4 mb-6 relative z-10">
      <div class="h-11 w-11 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center text-slate-300 group-hover:text-[#FF6700] group-hover:bg-[#FF6700]/10 group-hover:border-[#FF6700]/20 transition-all duration-300">
        <ScissorsIcon class="h-5 w-5" />
      </div>
      <div>
        <h2 class="text-lg font-display text-white font-bold">Service & Payment Method</h2>
        <p class="text-xs text-slate-400">Select treatment and venue settlement instrument.</p>
      </div>
    </div>

    <div class="space-y-5 relative z-10">
      <!-- Service Selection -->
      <div>
        <label class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2 ml-1">
          Select Service <span class="text-[#FF6700]">*</span>
        </label>
        <div class="relative">
          <select
            :value="form.service_id"
            @change="$emit('update:service_id', Number($event.target.value))"
            class="w-full appearance-none bg-[#0B0F13] border border-white/[0.08] rounded-2xl py-3.5 pl-11 pr-10 text-white text-sm focus:outline-none focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30 transition-all cursor-pointer"
          >
            <option value="" disabled class="bg-[#12161C] text-slate-400">Choose a service from catalogue...</option>
            <option
              v-for="svc in servicesList"
              :key="svc.id"
              :value="svc.id"
              class="bg-[#12161C] text-white"
            >
              {{ svc.name }} — ₦{{ Number(svc.price || 0).toLocaleString() }} ({{ svc.duration_minutes || 30 }}m)
            </option>
          </select>
          <SparklesIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500 pointer-events-none" />
          <ChevronDownIcon class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500 pointer-events-none" />
        </div>
      </div>

      <!-- Schedule & Payment Row -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2 ml-1">
            Date
          </label>
          <div class="relative">
            <input
              :value="form.appointment_date"
              @input="$emit('update:appointment_date', $event.target.value)"
              type="date"
              class="w-full bg-[#0B0F13] border border-white/[0.08] rounded-2xl py-3.5 px-4 text-white text-sm focus:outline-none focus:border-[#FF6700]/50 transition-all"
            />
          </div>
        </div>

        <div>
          <label class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2 ml-1">
            Time
          </label>
          <div class="relative">
            <input
              :value="form.appointment_time"
              @input="$emit('update:appointment_time', $event.target.value)"
              type="time"
              class="w-full bg-[#0B0F13] border border-white/[0.08] rounded-2xl py-3.5 px-4 text-white text-sm focus:outline-none focus:border-[#FF6700]/50 transition-all"
            />
          </div>
        </div>

        <div>
          <label class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2 ml-1">
            Payment Mode <span class="text-[#FF6700]">*</span>
          </label>
          <div class="relative">
            <select
              :value="form.payment_method"
              @change="$emit('update:payment_method', $event.target.value)"
              class="w-full appearance-none bg-[#0B0F13] border border-white/[0.08] rounded-2xl py-3.5 pl-11 pr-10 text-white text-sm focus:outline-none focus:border-[#FF6700]/50 transition-all cursor-pointer"
            >
              <option value="cash" class="bg-[#12161C] text-white">Cash at Counter</option>
              <option value="pos" class="bg-[#12161C] text-white">POS Terminal</option>
              <option value="transfer" class="bg-[#12161C] text-white">Direct Transfer</option>
              <option value="pending" class="bg-[#12161C] text-white">Unpaid / Deferred</option>
            </select>
            <BanknotesIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500 pointer-events-none" />
            <ChevronDownIcon class="absolute right-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500 pointer-events-none" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ScissorsIcon,
  SparklesIcon,
  ChevronDownIcon,
  BanknotesIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  form: {
    type: Object,
    required: true,
  },
  servicesList: {
    type: Array,
    default: () => [],
  },
});

defineEmits([
  'update:service_id',
  'update:appointment_date',
  'update:appointment_time',
  'update:payment_method',
]);
</script>
