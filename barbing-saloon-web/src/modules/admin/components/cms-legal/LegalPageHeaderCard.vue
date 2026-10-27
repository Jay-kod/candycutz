<template>
  <div class="rounded-2xl border border-white/[0.08] bg-[#12161C]/90 p-5 sm:p-7 backdrop-blur-xl shadow-xl space-y-4">
    <div class="flex items-center justify-between border-b border-white/[0.06] pb-3">
      <div>
        <h3 class="text-base font-bold text-white">Document Heading</h3>
        <p class="mt-0.5 text-xs text-white/50">Primary document title displayed on the public storefront.</p>
      </div>

      <span class="px-2 py-0.5 text-[10px] font-mono text-white/40 rounded-md bg-white/[0.04] border border-white/10">
        {{ settingPrefix }}_title
      </span>
    </div>

    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <label :for="`${settingPrefix}-title`" class="text-xs font-bold uppercase tracking-wider text-white/60">
          Page Title <span class="text-[#FF6700]">*</span>
        </label>
        <span class="text-[10px] font-mono text-white/30">{{ (modelValue || '').length }}/150</span>
      </div>
      <input
        :id="`${settingPrefix}-title`"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        type="text"
        maxlength="150"
        required
        placeholder="e.g. Terms of Service, Legal Documentation"
        class="w-full rounded-xl border border-white/[0.08] bg-[#0B0F13]/80 px-4 py-2.5 text-xs text-white placeholder:text-white/30 outline-none transition-colors focus:border-[#FF6700]/50 focus:ring-1 focus:ring-[#FF6700]/30"
      />
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    required: true
  },
  settingPrefix: {
    type: String,
    required: true
  }
});

defineEmits(['update:modelValue']);
</script>

