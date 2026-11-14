<template>
  <aside class="space-y-5 rounded-3xl border border-white/[0.08] bg-[#12161C] p-5 shadow-2xl sm:p-6">
    <div class="flex items-center justify-between border-b border-white/[0.06] pb-4">
      <div class="flex items-center gap-2.5">
        <span class="flex h-8 w-8 items-center justify-center rounded-xl border border-admin/20 bg-admin/10 text-admin-light">
          <EyeIcon class="h-4 w-4" />
        </span>
        <div>
          <h2 class="text-xs font-bold uppercase tracking-wider text-white">Client Live Preview</h2>
          <p class="mt-0.5 text-[10px] text-slate-400">Article card preview</p>
        </div>
      </div>
      <span class="rounded-full border border-white/[0.08] bg-white/[0.04] px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-slate-300">
        Preview
      </span>
    </div>

    <article class="group overflow-hidden rounded-2xl border border-white/[0.1] bg-[#0B0F13] shadow-xl transition hover:border-admin/30">
      <div class="relative aspect-video overflow-hidden bg-gradient-to-br from-[#1a2028] to-black">
        <img
          v-if="previewImage"
          :src="previewImage"
          :alt="title || 'Article cover preview'"
          class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
        />
        <div v-else class="flex h-full flex-col items-center justify-center text-admin-light">
          <PhotoIcon class="mb-2 h-9 w-9 opacity-70" />
          <span class="text-xs font-semibold">CandyCutz Editorial</span>
          <span class="mt-1 text-[10px] text-slate-500">Add a cover image</span>
        </div>
        <div class="absolute left-3 right-3 top-3 flex items-center justify-between">
          <span class="rounded-full border border-white/10 bg-black/70 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-admin-light backdrop-blur">
            Grooming Insight
          </span>
          <span class="rounded-full border border-white/10 bg-black/70 px-2 py-1 text-[9px] font-semibold text-slate-200 backdrop-blur">
            {{ readingTime }}
          </span>
        </div>
      </div>

      <div class="space-y-3 p-5">
        <div class="flex items-center justify-between gap-2">
          <span
            class="rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider"
            :class="isPublished
              ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
              : 'border-amber-500/20 bg-amber-500/10 text-amber-400'"
          >
            {{ isPublished ? 'Live' : 'Draft' }}
          </span>
          <span class="text-[10px] text-slate-500">{{ formattedDate }}</span>
        </div>
        <h3 class="line-clamp-2 font-display text-base font-bold leading-snug text-white transition group-hover:text-admin-light">
          {{ title || 'Article title preview...' }}
        </h3>
        <p class="line-clamp-3 text-xs leading-relaxed text-slate-400">
          {{ excerpt || plainContent || 'Your article summary will appear here as you write.' }}
        </p>
        <div class="flex items-center gap-2 border-t border-white/[0.06] pt-3">
          <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-admin to-admin-light text-[10px] font-bold text-obsidian">
            {{ (authorDisplay || 'C').charAt(0).toUpperCase() }}
          </span>
          <span class="truncate text-xs font-semibold text-white">{{ authorDisplay || 'CandyCutz Team' }}</span>
        </div>
      </div>
    </article>

    <div class="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4">
      <div class="mb-3 flex items-center justify-between text-[11px] font-medium text-slate-400">
        <span>Editorial checklist</span>
        <span class="font-bold text-admin-light">{{ checklistProgress }}% complete</span>
      </div>
      <div class="h-1.5 overflow-hidden rounded-full bg-white/[0.06]">
        <div class="h-full bg-gradient-to-r from-admin to-admin-light transition-all" :style="{ width: `${checklistProgress}%` }"></div>
      </div>
      <ul class="mt-3 space-y-2 text-[11px]">
        <li v-for="item in checklist" :key="item.label" class="flex items-center gap-2" :class="item.complete ? 'text-emerald-400' : 'text-slate-500'">
          <CheckCircleIcon class="h-3.5 w-3.5 shrink-0" />
          {{ item.label }}
        </li>
      </ul>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { CheckCircleIcon, EyeIcon, PhotoIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  title: { type: String, default: '' },
  excerpt: { type: String, default: '' },
  content: { type: String, default: '' },
  previewImage: { type: String, default: '' },
  authorDisplay: { type: String, default: '' },
  isPublished: { type: Boolean, default: false },
  readingTime: { type: String, default: '1 min read' },
  formattedDate: { type: String, default: '' },
});

const plainContent = computed(() => props.content
  .replace(/<[^>]*>?/gm, '')
  .replace(/[#*`_>]/g, '')
  .trim()
  .slice(0, 140));

const checklist = computed(() => [
  { label: 'Article title added', complete: Boolean(props.title.trim()) },
  { label: 'Cover image selected', complete: Boolean(props.previewImage) },
  { label: 'Summary written', complete: Boolean(props.excerpt.trim()) },
  { label: 'Article content added', complete: Boolean(props.content.trim()) },
]);

const checklistProgress = computed(() => Math.round(
  (checklist.value.filter((item) => item.complete).length / checklist.value.length) * 100,
));
</script>
