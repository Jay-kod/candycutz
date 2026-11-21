<template>
  <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 shadow-sm dark:shadow-2xl space-y-5">
    <!-- Header banner -->
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-white/[0.06] pb-4">
      <div class="flex items-center gap-2">
        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gold/10 text-gold">
          <EyeIcon class="h-4 w-4" />
        </div>
        <div>
          <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Client Live Preview</h3>
          <p class="text-[10px] text-slate-500 dark:text-slate-400">Real-time salon card display</p>
        </div>
      </div>
      <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-700 dark:text-slate-300">
        <span class="h-1.5 w-1.5 rounded-full bg-gold animate-pulse"></span>
        Live Sync
      </span>
    </div>

    <!-- The Public Client Blog Card Preview -->
    <div class="group rounded-2xl border border-slate-200 dark:border-white/[0.1] bg-slate-50 dark:bg-[#0B0F13] overflow-hidden shadow-sm dark:shadow-xl hover:border-gold/30 transition-all duration-300">
      <!-- Media Header -->
      <div class="relative aspect-video w-full overflow-hidden bg-slate-200 dark:bg-slate-900/60">
        <template v-if="previewImage">
          <img
            :src="previewImage"
            :alt="title || 'Article cover preview'"
            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 dark:from-[#0B0F13] via-transparent to-black/30"></div>
        </template>
        <template v-else>
          <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-slate-100 via-slate-200 to-slate-100 dark:from-[#161B22] dark:via-[#0E1217] dark:to-black p-6 text-center">
            <div class="h-12 w-12 rounded-2xl bg-gold/10 border border-gold/20 flex items-center justify-center mb-2">
              <SparklesIcon class="h-6 w-6 text-gold" />
            </div>
            <p class="font-display text-xs font-semibold text-slate-700 dark:text-slate-300 tracking-wide">CandyCutz Editorial</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Upload a cover image to personalize</p>
          </div>
        </template>

        <!-- Floating Category & Read Time -->
        <div class="absolute top-3 left-3 right-3 flex items-center justify-between z-10 pointer-events-none">
          <span class="px-2.5 py-1 rounded-full bg-black/70 backdrop-blur-md border border-white/10 text-[9px] font-bold uppercase tracking-widest text-gold shadow-md">
            Grooming Insight
          </span>
          <span class="px-2 py-0.5 rounded-full bg-black/70 backdrop-blur-md border border-white/10 text-[9px] font-semibold text-slate-200 shadow-md">
            {{ readingTime }}
          </span>
        </div>
      </div>

      <!-- Card Body -->
      <div class="p-5 space-y-3">
        <!-- Status & Date -->
        <div class="flex items-center justify-between text-xs">
          <span
            class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider"
            :class="
              isPublished
                ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20'
                : 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20'
            "
          >
            <span
              class="h-1 w-1 rounded-full"
              :class="isPublished ? 'bg-emerald-500' : 'bg-amber-500'"
            ></span>
            {{ isPublished ? 'Live' : 'Draft' }}
          </span>

          <span class="text-[11px] text-slate-500 font-medium">
            {{ formattedDate }}
          </span>
        </div>

        <!-- Title -->
        <h4 class="font-display text-base font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug group-hover:text-gold transition-colors">
          {{ title || 'Article Title Preview...' }}
        </h4>

        <!-- Excerpt -->
        <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-3 leading-relaxed">
          {{
            excerpt ||
            (content
              ? content.replace(/<[^>]*>?/gm, '').replace(/[#*`_>]/g, '').substring(0, 140) + '...'
              : 'Add an excerpt or body paragraphs to preview how your summary appears on the public website and client mobile app...')
          }}
        </p>

        <!-- Author & Reaction Footer -->
        <div class="pt-3 border-t border-slate-200 dark:border-white/[0.06] flex items-center justify-between text-xs">
          <div class="flex items-center gap-2 min-w-0">
            <div class="h-6 w-6 rounded-full bg-gradient-to-br from-gold to-amber-500 text-obsidian font-bold text-[10px] flex items-center justify-center shrink-0">
              {{ (authorDisplay || 'C').charAt(0).toUpperCase() }}
            </div>
            <div class="min-w-0">
              <p class="text-xs font-semibold text-slate-900 dark:text-white truncate">
                {{ authorDisplay || 'CandyCutz Stylist' }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-3 text-slate-500 dark:text-slate-400 text-xs shrink-0">
            <span class="flex items-center gap-1 hover:text-rose-500 dark:hover:text-rose-400 transition-colors">
              <HeartIcon class="h-3.5 w-3.5 text-rose-500 dark:text-rose-400" />
              <span class="text-[11px] font-mono">0</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Editorial Checklist info -->
    <div class="rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/[0.06] p-4 space-y-2 text-xs">
      <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-medium">
        <span>Editorial Checklist</span>
        <span class="text-amber-800 dark:text-gold font-bold">{{ checklistProgress }}% Complete</span>
      </div>
      <div class="h-1.5 w-full bg-slate-200 dark:bg-white/[0.06] rounded-full overflow-hidden">
        <div
          class="h-full bg-gradient-to-r from-gold to-amber-400 transition-all duration-300"
          :style="{ width: `${checklistProgress}%` }"
        ></div>
      </div>
      <ul class="pt-1 space-y-1.5 text-[11px]">
        <li class="flex items-center gap-2" :class="title ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'">
          <CheckCircleIcon class="h-3.5 w-3.5 shrink-0" />
          <span>Compelling Headline</span>
        </li>
        <li class="flex items-center gap-2" :class="previewImage ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'">
          <CheckCircleIcon class="h-3.5 w-3.5 shrink-0" />
          <span>Cover Image Uploaded</span>
        </li>
        <li class="flex items-center gap-2" :class="excerpt ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'">
          <CheckCircleIcon class="h-3.5 w-3.5 shrink-0" />
          <span>Summary Excerpt Written</span>
        </li>
        <li class="flex items-center gap-2" :class="content ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'">
          <CheckCircleIcon class="h-3.5 w-3.5 shrink-0" />
          <span>Article Body Formatted</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  EyeIcon,
  SparklesIcon,
  HeartIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  excerpt: {
    type: String,
    default: '',
  },
  content: {
    type: String,
    default: '',
  },
  previewImage: {
    type: String,
    default: '',
  },
  authorDisplay: {
    type: String,
    default: '',
  },
  isPublished: {
    type: Boolean,
    default: false,
  },
  readingTime: {
    type: String,
    default: '1 min read',
  },
  formattedDate: {
    type: String,
    default: '',
  },
});

const checklistProgress = computed(() => {
  let score = 0;
  if (props.title?.trim()) score += 25;
  if (props.previewImage) score += 25;
  if (props.excerpt?.trim()) score += 25;
  if (props.content?.trim()) score += 25;
  return score;
});
</script>
