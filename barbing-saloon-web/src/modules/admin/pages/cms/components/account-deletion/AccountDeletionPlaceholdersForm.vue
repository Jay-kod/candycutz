<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl space-y-6">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-sky-500/15 border border-sky-500/30 flex items-center justify-center text-sky-400 shadow-[0_0_15px_rgba(14,165,233,0.15)]">
          <ChatBubbleBottomCenterTextIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Public Form Field Placeholders</h2>
          <p class="text-xs text-white/50 mt-0.5">Customize placeholder cues shown to clients filling out the online account erasure request form.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 03
      </span>
    </div>

    <!-- Inputs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Registered Email Placeholder
          </label>
        </div>
        <input 
          v-model="content.account_deletion_email_placeholder" 
          type="text" 
          placeholder="e.g. you@example.com" 
          class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
        />
        <p class="text-[11px] text-white/40">Shows in the registered email input field.</p>
      </div>

      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Username / Name Placeholder
          </label>
        </div>
        <input 
          v-model="content.account_deletion_identifier_placeholder" 
          type="text" 
          placeholder="e.g. @username or Full Name" 
          class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
        />
        <p class="text-[11px] text-white/40">Shows in the account identifier field.</p>
      </div>

      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Reason Placeholder
          </label>
        </div>
        <input 
          v-model="content.account_deletion_reason_placeholder" 
          type="text" 
          placeholder="e.g. Help us understand how we can improve..." 
          class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none" 
        />
        <p class="text-[11px] text-white/40">Shows in the optional feedback textarea.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ChatBubbleBottomCenterTextIcon } from '@heroicons/vue/24/outline';

defineProps({
  content: {
    type: Object,
    required: true
  }
});
</script>

