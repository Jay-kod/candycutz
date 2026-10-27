<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-xl space-y-6">
    <div class="border-b border-white/[0.06] pb-4">
      <h3 class="text-base font-bold text-white">Wordmark &amp; Typography Parameters</h3>
      <p class="mt-1 text-xs text-white/50">
        Adjust the two-part compound shop name, spacing behaviour, and gold highlight placement.
      </p>
    </div>

    <!-- First & Second Name Inputs -->
    <div class="grid gap-5 sm:grid-cols-2">
      <!-- First Part -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold uppercase tracking-wider text-white/60">
            First Name Part <span class="text-[#FF6700]">*</span>
          </label>
          <span class="text-[10px] font-mono text-white/30">{{ form.first.length }}/60</span>
        </div>
        <input
          v-model="form.first"
          type="text"
          maxlength="60"
          required
          autocomplete="off"
          placeholder="Candy"
          class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 px-4 py-2.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30"
        />
        <p class="text-[11px] text-white/40">Primary name stem (e.g. Candy, Royal, King)</p>
      </div>

      <!-- Second Part -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-xs font-bold uppercase tracking-wider text-white/60">
            Second Name Part <span class="text-[#FF6700]">*</span>
          </label>
          <span class="text-[10px] font-mono text-white/30">{{ form.second.length }}/60</span>
        </div>
        <input
          v-model="form.second"
          type="text"
          maxlength="60"
          required
          autocomplete="off"
          placeholder="Cutz"
          class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 px-4 py-2.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30"
        />
        <p class="text-[11px] text-white/40">Secondary modifier (e.g. Cutz, Barbers, Saloon)</p>
      </div>
    </div>

    <!-- Space Separator Setting -->
    <div class="space-y-3">
      <label class="text-xs font-bold uppercase tracking-wider text-white/60 block">
        Part Spacing Treatment
      </label>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button
          type="button"
          @click="form.separator = 'none'"
          class="flex items-start gap-3 rounded-xl border p-3.5 text-left transition-all"
          :class="[
            form.separator === 'none'
              ? 'border-[#FF6700]/40 bg-[#FF6700]/10 text-white shadow-md shadow-[#FF6700]/5'
              : 'border-white/[0.06] bg-white/[0.02] text-white/60 hover:bg-white/[0.04] hover:text-white'
          ]"
        >
          <div
            class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border"
            :class="form.separator === 'none' ? 'border-[#FF6700] bg-[#FF6700]' : 'border-white/30'"
          >
            <span v-if="form.separator === 'none'" class="h-1.5 w-1.5 rounded-full bg-black"></span>
          </div>
          <div>
            <span class="text-xs font-bold block">No Space (Continuous)</span>
            <span class="text-[11px] text-white/40 mt-0.5 block">
              Concatenates without space (e.g. <span class="text-white font-mono font-medium">{{ form.first }}{{ form.second }}</span>)
            </span>
          </div>
        </button>

        <button
          type="button"
          @click="form.separator = 'space'"
          class="flex items-start gap-3 rounded-xl border p-3.5 text-left transition-all"
          :class="[
            form.separator === 'space'
              ? 'border-[#FF6700]/40 bg-[#FF6700]/10 text-white shadow-md shadow-[#FF6700]/5'
              : 'border-white/[0.06] bg-white/[0.02] text-white/60 hover:bg-white/[0.04] hover:text-white'
          ]"
        >
          <div
            class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border"
            :class="form.separator === 'space' ? 'border-[#FF6700] bg-[#FF6700]' : 'border-white/30'"
          >
            <span v-if="form.separator === 'space'" class="h-1.5 w-1.5 rounded-full bg-black"></span>
          </div>
          <div>
            <span class="text-xs font-bold block">Word Space</span>
            <span class="text-[11px] text-white/40 mt-0.5 block">
              Inserts a single space (e.g. <span class="text-white font-mono font-medium">{{ form.first }} {{ form.second }}</span>)
            </span>
          </div>
        </button>
      </div>
    </div>

    <!-- Gold Accent Placement -->
    <div class="space-y-3">
      <label class="text-xs font-bold uppercase tracking-wider text-white/60 block">
        Gold / Signature Orange Highlight
      </label>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button
          type="button"
          @click="form.goldPart = 'second'"
          class="flex items-start gap-3 rounded-xl border p-3.5 text-left transition-all"
          :class="[
            form.goldPart === 'second'
              ? 'border-amber-500/40 bg-amber-500/10 text-white shadow-md shadow-amber-500/5'
              : 'border-white/[0.06] bg-white/[0.02] text-white/60 hover:bg-white/[0.04] hover:text-white'
          ]"
        >
          <div
            class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border"
            :class="form.goldPart === 'second' ? 'border-amber-400 bg-amber-400' : 'border-white/30'"
          >
            <span v-if="form.goldPart === 'second'" class="h-1.5 w-1.5 rounded-full bg-black"></span>
          </div>
          <div>
            <span class="text-xs font-bold block">Second Part Highlighted</span>
            <span class="text-[11px] text-white/40 mt-0.5 block">
              First part white, second part gold (Recommended)
            </span>
          </div>
        </button>

        <button
          type="button"
          @click="form.goldPart = 'first'"
          class="flex items-start gap-3 rounded-xl border p-3.5 text-left transition-all"
          :class="[
            form.goldPart === 'first'
              ? 'border-amber-500/40 bg-amber-500/10 text-white shadow-md shadow-amber-500/5'
              : 'border-white/[0.06] bg-white/[0.02] text-white/60 hover:bg-white/[0.04] hover:text-white'
          ]"
        >
          <div
            class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border"
            :class="form.goldPart === 'first' ? 'border-amber-400 bg-amber-400' : 'border-white/30'"
          >
            <span v-if="form.goldPart === 'first'" class="h-1.5 w-1.5 rounded-full bg-black"></span>
          </div>
          <div>
            <span class="text-xs font-bold block">First Part Highlighted</span>
            <span class="text-[11px] text-white/40 mt-0.5 block">
              First part gold, second part white
            </span>
          </div>
        </button>
      </div>
    </div>

    <!-- Brand Tagline / Short Description -->
    <div class="space-y-2 pt-2 border-t border-white/[0.06]">
      <div class="flex items-center justify-between">
        <label class="text-xs font-bold uppercase tracking-wider text-white/60">
          Executive Tagline &amp; Description
        </label>
        <span class="text-[10px] font-mono text-white/30">{{ (form.tagline || '').length }}/300</span>
      </div>
      <textarea
        v-model="form.tagline"
        rows="3"
        maxlength="300"
        placeholder="A sharper standard in executive grooming. Premium precision cuts, tailored beard artistry, and royal styling."
        class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 p-3.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30 resize-none leading-relaxed"
      ></textarea>
      <p class="text-[11px] text-white/40">
        Displayed across the website footer, booking confirmation receipts, and customer notifications.
      </p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  form: {
    type: Object,
    required: true
  }
});
</script>
