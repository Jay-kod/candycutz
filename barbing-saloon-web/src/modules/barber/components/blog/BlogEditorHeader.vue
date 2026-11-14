<template>
  <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 md:p-10 shadow-sm dark:shadow-2xl flex flex-col md:flex-row md:items-end justify-between gap-6 group">
    <!-- Ambient ambient glow spheres -->
    <div class="absolute -top-36 -right-36 w-80 h-80 bg-gold/15 rounded-full blur-[100px] pointer-events-none group-hover:bg-gold/20 transition-colors duration-700"></div>
    <div class="absolute -bottom-36 -left-36 w-80 h-80 bg-amber-500/10 rounded-full blur-[100px] pointer-events-none group-hover:bg-amber-500/15 transition-colors duration-700"></div>

    <div class="relative z-10 max-w-2xl">
      <!-- Breadcrumb / Back button & badges -->
      <div class="flex flex-wrap items-center gap-3 mb-4">
        <router-link
          to="/barber/blog"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.04] text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white dark:hover:bg-white/[0.08] hover:border-gold/30 transition-all group/btn"
        >
          <ArrowLeftIcon class="h-3.5 w-3.5 group-hover/btn:-translate-x-0.5 transition-transform" />
          <span>Articles List</span>
        </router-link>

        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gold/10 border border-gold/20 text-amber-800 dark:text-gold text-[10px] font-bold uppercase tracking-widest">
          <PencilSquareIcon class="h-3.5 w-3.5 text-gold" />
          <span>{{ isEditing ? 'Article Editor' : 'Compose Suite' }}</span>
        </div>

        <span
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-wider border transition-colors"
          :class="
            isPublished
              ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-500/20'
              : 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-500/20'
          "
        >
          <span
            class="h-1.5 w-1.5 rounded-full"
            :class="isPublished ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse'"
          ></span>
          {{ isPublished ? 'Live Published' : 'Draft Mode' }}
        </span>
      </div>

      <!-- Main Headline -->
      <h1 class="text-3xl sm:text-4xl md:text-5xl font-display font-bold text-slate-900 dark:text-white tracking-tight mb-2">
        {{ isEditing ? 'Edit' : 'Draft' }}
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold via-amber-300 to-amber-500">
          Article
        </span>
      </h1>
      <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm leading-relaxed">
        {{
          isEditing
            ? 'Refine your grooming guide, style advice, and salon updates with live interactive client preview.'
            : 'Craft inspiring haircut maintenance tips and styling tutorials for your salon clientele.'
        }}
      </p>
    </div>

    <!-- Header Action Controls -->
    <div class="relative z-10 flex items-center gap-3 shrink-0">
      <router-link
        to="/barber/blog"
        class="hidden sm:inline-flex items-center px-4 py-3 rounded-2xl text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-white/[0.08] hover:bg-slate-100 dark:hover:bg-white/[0.04] transition-colors"
      >
        Discard
      </router-link>

      <button
        type="button"
        @click="$emit('save')"
        :disabled="saving"
        class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 px-6 py-3.5 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-gold/20 hover:shadow-[0_8px_30px_rgba(255,153,0,0.35)] transition-all hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50"
      >
        <span
          v-if="saving"
          class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"
        ></span>
        <CheckIcon v-else class="h-4 w-4 stroke-[2.5]" />
        <span>{{ saving ? 'Saving Article...' : isEditing ? 'Update Article' : 'Publish Article' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import {
  ArrowLeftIcon,
  PencilSquareIcon,
  CheckIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  isEditing: {
    type: Boolean,
    default: false,
  },
  isPublished: {
    type: Boolean,
    default: false,
  },
  saving: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['save']);
</script>
