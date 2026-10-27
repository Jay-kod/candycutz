<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-xl space-y-5">
    <div class="border-b border-white/[0.06] pb-4">
      <h3 class="text-base font-bold text-white">Page Heading &amp; Introduction</h3>
      <p class="mt-0.5 text-xs text-white/50">
        Controls the title and introductory explanation shown to visitors before they customize categories.
      </p>
    </div>

    <!-- Page Title -->
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <label for="cookie-title" class="text-xs font-bold uppercase tracking-wider text-white/60">
          Page Title <span class="text-[#FF6700]">*</span>
        </label>
        <span class="text-[10px] font-mono text-white/30">{{ (content.cookie_title || '').length }}/150</span>
      </div>
      <input
        id="cookie-title"
        v-model="content.cookie_title"
        type="text"
        maxlength="150"
        required
        placeholder="e.g. Manage Cookies"
        class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 px-4 py-2.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30"
      />
    </div>

    <!-- Introduction Narrative -->
    <div class="space-y-2 pt-2 border-t border-white/[0.06]">
      <div class="flex items-center justify-between">
        <label for="cookie-intro" class="text-xs font-bold uppercase tracking-wider text-white/60">
          Introduction Narrative <span class="text-[#FF6700]">*</span>
        </label>
        <span class="text-[10px] font-mono text-white/30">{{ (content.cookie_intro || '').length }}/2000</span>
      </div>
      <textarea
        id="cookie-intro"
        v-model="content.cookie_intro"
        rows="3"
        maxlength="2000"
        required
        placeholder="Explain your cookie storage policy and client choices..."
        class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 p-3.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30 resize-none leading-relaxed"
      ></textarea>
    </div>
  </div>
</template>

<script setup>
defineProps({
  content: {
    type: Object,
    required: true
  }
});
</script>
