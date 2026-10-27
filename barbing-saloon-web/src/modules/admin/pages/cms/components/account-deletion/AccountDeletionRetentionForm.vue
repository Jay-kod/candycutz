<template>
  <div class="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#12161C] p-6 lg:p-8 shadow-xl space-y-6">
    <!-- Section Header -->
    <div class="flex items-center justify-between pb-6 border-b border-white/[0.06]">
      <div class="flex items-center gap-3.5">
        <div class="h-10 w-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400 shadow-[0_0_15px_rgba(245,158,11,0.15)]">
          <DocumentTextIcon class="h-5 w-5" />
        </div>
        <div>
          <h2 class="font-display text-xl text-white tracking-tight">Statutory Data Retention & Legal Disclosure</h2>
          <p class="text-xs text-white/50 mt-0.5">Define instructions and statutory retention rules regarding personal data vs. tax/accounting logs.</p>
        </div>
      </div>
      <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-mono uppercase tracking-wider bg-white/[0.03] border border-white/[0.08] text-white/40">
        Section 02
      </span>
    </div>

    <!-- Instructions & Retention Notice Form -->
    <div class="space-y-4">
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="block text-xs font-bold uppercase tracking-wider text-white/70">
            Retention Notice Text & Guidelines
          </label>
          <span class="text-[10px] font-mono text-white/40">
            {{ (content.account_deletion_body || '').length }} chars • {{ wordCount }} words
          </span>
        </div>
        <textarea 
          v-model="content.account_deletion_body" 
          rows="8" 
          placeholder="Enter the statutory data retention notice, audit guidelines, and personal data deletion terms..." 
          class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-xs sm:text-sm text-white/90 placeholder-white/20 focus:border-admin/60 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 transition-all outline-none resize-y leading-relaxed font-sans"
        ></textarea>
        <p class="text-[11px] text-white/40">
          Displayed on the public Account Deletion page below the request form. Explains legal timelines and non-erasable accounting receipts.
        </p>
      </div>

      <!-- Compliance Tips Card -->
      <div class="rounded-xl border border-white/[0.06] bg-[#0B0F13] p-5 space-y-3">
        <span class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-2">
          <ShieldCheckIcon class="h-4 w-4" />
          Statutory Standards Checklist
        </span>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-white/60">
          <div class="flex items-start gap-2">
            <span class="text-emerald-400 font-bold">✓</span>
            <span>Immediate invalidation of active session tokens & push notifications.</span>
          </div>
          <div class="flex items-start gap-2">
            <span class="text-emerald-400 font-bold">✓</span>
            <span>Permanent scrubbing of marketing profiles and customer preferences.</span>
          </div>
          <div class="flex items-start gap-2">
            <span class="text-emerald-400 font-bold">✓</span>
            <span>Mandatory statutory retention of completed tax & billing invoices.</span>
          </div>
          <div class="flex items-start gap-2">
            <span class="text-emerald-400 font-bold">✓</span>
            <span>Compliance with Apple App Store Guideline 5.1.1(v) & Google Play.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  DocumentTextIcon, 
  ShieldCheckIcon 
} from '@heroicons/vue/24/outline';

const props = defineProps({
  content: {
    type: Object,
    required: true
  }
});

const wordCount = computed(() => {
  return (props.content?.account_deletion_body || '').trim().split(/\s+/).filter(Boolean).length;
});
</script>

