<template>
  <section class="space-y-6 rounded-3xl border border-white/[0.08] bg-[#12161C] p-5 shadow-2xl sm:p-7">
    <div class="space-y-2">
      <div class="flex items-center justify-between gap-3">
        <label for="article-title" class="text-xs font-bold uppercase tracking-wider text-white">Article title <span class="text-admin-light">*</span></label>
        <span class="text-[11px] font-mono" :class="title.length > 70 ? 'text-amber-400' : 'text-slate-500'">{{ title.length }} / 70</span>
      </div>
      <input
        id="article-title"
        :value="title"
        type="text"
        required
        class="w-full rounded-2xl border border-white/[0.08] bg-[#0B0F13] px-4 py-3.5 text-base text-white outline-none transition placeholder:text-slate-500 focus:border-admin/50 focus:ring-1 focus:ring-admin/30"
        placeholder="Write a clear, engaging article title"
        @input="$emit('update:title', $event.target.value)"
      />
      <p class="flex items-center gap-2 truncate pt-1 text-[11px] text-slate-400">
        <LinkIcon class="h-3.5 w-3.5 shrink-0 text-admin-light" />
        <span class="shrink-0 text-slate-500">Preview slug:</span>
        <span class="truncate font-mono text-slate-300">{{ previewSlug }}</span>
      </p>
    </div>

    <div class="space-y-2">
      <div class="flex items-center justify-between gap-3">
        <label for="article-excerpt" class="text-xs font-bold uppercase tracking-wider text-white">Excerpt / brief summary</label>
        <span class="text-[11px] font-mono text-slate-500">{{ excerpt.length }} / 160</span>
      </div>
      <textarea
        id="article-excerpt"
        :value="excerpt"
        rows="3"
        class="w-full resize-none rounded-2xl border border-white/[0.08] bg-[#0B0F13] px-4 py-3 text-sm leading-relaxed text-white outline-none transition placeholder:text-slate-500 focus:border-admin/50 focus:ring-1 focus:ring-admin/30"
        placeholder="A short summary shown on public blog cards and social previews..."
        @input="$emit('update:excerpt', $event.target.value)"
      ></textarea>
    </div>
  </section>
</template>

<script setup>
import { LinkIcon } from '@heroicons/vue/24/outline';

defineProps({
  title: { type: String, default: '' },
  excerpt: { type: String, default: '' },
  previewSlug: { type: String, default: '' },
});

defineEmits(['update:title', 'update:excerpt']);
</script>
