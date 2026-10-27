<template>
  <div class="space-y-6">
    <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-xl space-y-6">
      <div class="border-b border-white/[0.06] pb-4">
        <h3 class="text-base font-bold text-white">Consent Categories ({{ categories.length }})</h3>
        <p class="mt-0.5 text-xs text-white/50">
          Configure display titles and explanatory copy for each cookie storage category.
        </p>
      </div>

      <!-- Categories Grid -->
      <div class="space-y-5">
        <div
          v-for="cat in categories"
          :key="cat.key"
          class="group relative rounded-xl border border-white/[0.08] bg-[#0B0F13]/70 p-5 sm:p-6 transition-all hover:border-[#FF6700]/30 hover:shadow-lg space-y-4"
        >
          <!-- Category Top Bar -->
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 border-b border-white/[0.06]">
            <div class="flex items-center gap-2.5">
              <span
                class="flex h-6 w-6 items-center justify-center rounded-lg border text-xs font-bold"
                :class="[
                  cat.required
                    ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400'
                    : 'border-[#FF6700]/30 bg-[#FF6700]/10 text-[#FF6700]'
                ]"
              >
                {{ cat.label[0] }}
              </span>
              <h4 class="text-sm font-bold text-white">
                {{ cat.label }}
              </h4>
            </div>

            <!-- Status Pill Badge -->
            <span
              class="px-2.5 py-0.5 text-[10px] font-mono uppercase tracking-wider rounded-md font-bold self-start sm:self-auto"
              :class="[
                cat.required
                  ? 'border border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                  : 'border border-white/10 bg-white/[0.03] text-white/50'
              ]"
            >
              {{ cat.badgeText }}
            </span>
          </div>

          <!-- Display Name Input -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label :for="`${cat.key}-title`" class="text-xs font-bold uppercase tracking-wider text-white/60">
                Category Display Name <span class="text-[#FF6700]">*</span>
              </label>
              <span class="text-[10px] font-mono text-white/30">{{ (content[cat.titleKey] || '').length }}/120</span>
            </div>
            <input
              :id="`${cat.key}-title`"
              v-model="content[cat.titleKey]"
              type="text"
              maxlength="120"
              required
              class="w-full rounded-xl border border-white/[0.08] bg-[#12161C] px-3.5 py-2 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50"
            />
          </div>

          <!-- Description Textarea -->
          <div class="space-y-1.5">
            <div class="flex items-center justify-between">
              <label :for="`${cat.key}-desc`" class="text-xs font-bold uppercase tracking-wider text-white/60">
                Visitor Description <span class="text-[#FF6700]">*</span>
              </label>
              <span class="text-[10px] font-mono text-white/30">{{ (content[cat.descriptionKey] || '').length }}/1000</span>
            </div>
            <textarea
              :id="`${cat.key}-desc`"
              v-model="content[cat.descriptionKey]"
              rows="2"
              maxlength="1000"
              required
              class="w-full rounded-xl border border-white/[0.08] bg-[#12161C] p-3 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 resize-y leading-relaxed"
            ></textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  categories: {
    type: Array,
    required: true
  },
  content: {
    type: Object,
    required: true
  }
});
</script>
