<template>
  <div class="bg-[#12161C] rounded-3xl border border-white/[0.08] p-6 sm:p-8 relative overflow-hidden group hover:border-emerald-500/30 transition-all duration-300">
    <div class="flex items-center gap-4 mb-6 relative z-10">
      <div class="h-11 w-11 rounded-2xl bg-white/[0.04] border border-white/[0.08] flex items-center justify-center text-slate-300 group-hover:text-emerald-400 group-hover:bg-emerald-500/10 group-hover:border-emerald-500/20 transition-all duration-300">
        <UserIcon class="h-5 w-5" />
      </div>
      <div>
        <h2 class="text-lg font-display text-white font-bold">Customer Details</h2>
        <p class="text-xs text-slate-400">Basic contact profile for queuing and service records.</p>
      </div>
    </div>

    <div class="space-y-4 relative z-10">
      <div class="relative">
        <label class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2 ml-1">
          Full Name <span class="text-emerald-400">*</span>
        </label>
        <div class="relative">
          <input
            :value="form.customer_name"
            @input="$emit('update:customer_name', $event.target.value)"
            type="text"
            placeholder="e.g. Adeola Johnson"
            class="w-full bg-[#0B0F13] border border-white/[0.08] rounded-2xl py-3.5 pl-11 pr-4 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/30 transition-all"
            required
          />
          <UserIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500" />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2 ml-1">
            Phone Number <span class="text-emerald-400">*</span>
          </label>
          <div class="relative">
            <input
              :value="form.customer_phone"
              @input="$emit('update:customer_phone', $event.target.value)"
              type="tel"
              placeholder="0801 234 5678"
              class="w-full bg-[#0B0F13] border border-white/[0.08] rounded-2xl py-3.5 pl-11 pr-4 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/30 transition-all"
              required
            />
            <PhoneIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500" />
          </div>
        </div>

        <div>
          <label class="block text-[10px] uppercase tracking-widest text-slate-400 font-bold mb-2 ml-1">
            Email <span class="text-slate-500 font-normal lowercase">(optional)</span>
          </label>
          <div class="relative">
            <input
              :value="form.customer_email"
              @input="$emit('update:customer_email', $event.target.value)"
              type="email"
              placeholder="client@domain.com"
              class="w-full bg-[#0B0F13] border border-white/[0.08] rounded-2xl py-3.5 pl-11 pr-4 text-white text-sm focus:outline-none focus:border-emerald-500/50 focus:ring-1 focus:ring-emerald-500/30 transition-all"
            />
            <EnvelopeIcon class="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { UserIcon, PhoneIcon, EnvelopeIcon } from '@heroicons/vue/24/outline';

defineProps({
  form: {
    type: Object,
    required: true,
  },
});

defineEmits(['update:customer_name', 'update:customer_phone', 'update:customer_email']);
</script>
