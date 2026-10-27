<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-xl space-y-6">
    <div class="border-b border-white/[0.06] pb-4">
      <h3 class="text-base font-bold text-white">Section Heading &amp; Copy Lead</h3>
      <p class="mt-0.5 text-xs text-white/50">
        Controls the uppercase eyebrow badge, primary hero heading, and contextual narrative lead.
      </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
      <!-- Badge Text -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold uppercase tracking-wider text-white/60">
            Eyebrow Badge Text
          </label>
          <span class="text-[10px] font-mono text-white/30">{{ (content.features_badge || '').length }}/50</span>
        </div>
        <input
          v-model="content.features_badge"
          type="text"
          maxlength="50"
          placeholder="e.g. WHY CHOOSE CANDYCUTZ"
          class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 px-4 py-2.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30"
        />
        <p class="text-[11px] text-white/40">Small uppercase badge rendered directly above the section title</p>
      </div>

      <!-- Main Heading -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold uppercase tracking-wider text-white/60">
            Main Heading <span class="text-[#FF6700]">*</span>
          </label>
          <span class="text-[10px] font-mono text-white/30">{{ (content.features_title || '').length }}/80</span>
        </div>
        <input
          v-model="content.features_title"
          type="text"
          maxlength="80"
          required
          placeholder="e.g. Engineered for Perfection"
          class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 px-4 py-2.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30"
        />
        <p class="text-[11px] text-white/40">High-impact section headline displayed with gradient typography</p>
      </div>
    </div>

    <!-- Subtitle / Supporting Description -->
    <div class="space-y-2 pt-2 border-t border-white/[0.06]">
      <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase tracking-wider text-white/60">
          Subtitle &amp; Supporting Narrative
        </label>
        <span class="text-[10px] font-mono text-white/30">{{ (content.features_subtitle || '').length }}/240</span>
      </div>
      <textarea
        v-model="content.features_subtitle"
        rows="3"
        maxlength="240"
        placeholder="e.g. Every detail of the CandyCutz experience is calibrated to deliver the pinnacle of men’s grooming."
        class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 p-3.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30 resize-none leading-relaxed"
      ></textarea>
      <p class="text-[11px] text-white/40">
        Lead paragraph clarifying your salon's value standards for visitors before they review individual cards.
      </p>
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
