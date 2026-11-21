<template>
  <div class="space-y-6">
    <!-- Header with Add Action -->
    <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-xl space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-white/[0.06] pb-4">
        <div>
          <h3 class="text-base font-bold text-white">
            Document Articles &amp; Clauses ({{ sections.length }})
          </h3>
          <p class="mt-0.5 text-xs text-white/50">
            Define structured headings, policy stipulations, and enforceable contract terms.
          </p>
        </div>

        <button
          type="button"
          @click="$emit('add-section')"
          class="inline-flex items-center gap-1.5 rounded-xl border border-[#FF6700]/30 bg-[#FF6700]/10 px-4 py-2 text-xs font-bold uppercase tracking-wider text-[#FF6700] hover:bg-[#FF6700]/20 transition-all self-start sm:self-auto"
        >
          <PlusIcon class="h-4 w-4" />
          <span>Add Section</span>
        </button>
      </div>

      <!-- Sections Cards List -->
      <div class="space-y-5 pt-2">
        <div
          v-for="(section, index) in sections"
          :key="index"
          class="group relative rounded-xl border border-white/[0.08] bg-[#0B0F13]/70 p-5 sm:p-6 transition-all hover:border-[#FF6700]/30 hover:shadow-lg space-y-4"
        >
          <!-- Section Card Header -->
          <div class="flex items-center justify-between pb-3 border-b border-white/[0.06]">
            <div class="flex items-center gap-2.5">
              <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#FF6700]/20 border border-[#FF6700]/40 text-[#FF6700] text-xs font-bold font-mono">
                {{ index + 1 }}
              </span>
              <span class="text-xs font-bold text-white truncate max-w-sm sm:max-w-md">
                {{ section.heading || `Article ${index + 1}` }}
              </span>
            </div>

            <button
              v-if="sections.length > 1"
              type="button"
              @click="$emit('remove-section', index)"
              class="text-white/40 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition-colors"
              title="Remove this legal clause"
            >
              <TrashIcon class="h-4 w-4" />
            </button>
          </div>

          <!-- Section Heading Input -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold uppercase tracking-wider text-white/60">
                Article Heading <span class="text-[#FF6700]">*</span>
              </label>
              <span class="text-[10px] font-mono text-white/30">{{ (section.heading || '').length }}/180</span>
            </div>
            <input
              v-model="section.heading"
              type="text"
              maxlength="180"
              required
              placeholder="e.g. 1. Appointment Cancellation & Deposit Policy"
              class="w-full rounded-xl border border-white/[0.08] bg-[#12161C] px-3.5 py-2 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50"
            />
          </div>

          <!-- Section Content Textarea -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold uppercase tracking-wider text-white/60">
                Stipulation Content &amp; Terms
              </label>
              <span class="text-[10px] font-mono text-white/30">{{ (section.body || '').length }}/12000</span>
            </div>
            <textarea
              v-model="section.body"
              rows="6"
              maxlength="12000"
              placeholder="Enter full legal copy, paragraphs, and policy clauses here..."
              class="w-full rounded-xl border border-white/[0.08] bg-[#12161C] p-3.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 resize-y leading-relaxed font-sans"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Bottom Add Button -->
      <div class="pt-4 border-t border-white/[0.06]">
        <button
          type="button"
          @click="$emit('add-section')"
          class="w-full rounded-xl border-2 border-dashed border-white/10 bg-white/[0.01] py-3.5 text-xs font-bold uppercase tracking-widest text-white/50 transition-colors hover:border-[#FF6700]/40 hover:bg-[#FF6700]/[0.02] hover:text-[#FF6700] flex items-center justify-center gap-2"
        >
          <PlusIcon class="h-4 w-4" />
          <span>Add Another Legal Clause</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

defineProps({
  sections: {
    type: Array,
    required: true
  }
});

defineEmits(['add-section', 'remove-section']);
</script>

