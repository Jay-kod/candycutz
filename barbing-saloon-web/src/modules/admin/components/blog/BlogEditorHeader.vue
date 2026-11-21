<template>
  <header class="relative overflow-hidden rounded-3xl border border-white/[0.08] bg-[#12161C] p-6 shadow-2xl sm:p-8">
    <div class="pointer-events-none absolute -right-24 -top-32 h-72 w-72 rounded-full bg-admin/15 blur-[100px]"></div>
    <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
      <div class="max-w-2xl">
        <div class="mb-4 flex flex-wrap items-center gap-3">
          <router-link
            to="/admin/blog"
            class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/[0.04] px-3 py-1.5 text-xs font-semibold text-slate-300 transition hover:border-admin/30 hover:bg-white/[0.08] hover:text-white"
          >
            <ArrowLeftIcon class="h-3.5 w-3.5" />
            Articles List
          </router-link>
          <span class="inline-flex items-center gap-1.5 rounded-xl border border-admin/20 bg-admin/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-admin-light">
            <PencilSquareIcon class="h-3.5 w-3.5" />
            Article Editor
          </span>
          <span
            class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest"
            :class="isPublished
              ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
              : 'border-amber-500/20 bg-amber-500/10 text-amber-400'"
          >
            <span class="h-1.5 w-1.5 rounded-full" :class="isPublished ? 'bg-emerald-400' : 'animate-pulse bg-amber-400'"></span>
            {{ isPublished ? 'Live Published' : 'Draft Mode' }}
          </span>
        </div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">
          {{ isEditing ? 'Edit' : 'Compose' }}
          <span class="bg-gradient-to-r from-admin to-admin-light bg-clip-text text-transparent">Article</span>
        </h1>
        <p class="mt-2 text-xs leading-relaxed text-slate-400 sm:text-sm">
          {{ isEditing
            ? 'Refine your article, preview how it appears to clients, and manage its publication status.'
            : 'Create salon announcements, styling guides, and care tips for your clients.' }}
        </p>
      </div>

      <div class="flex shrink-0 items-center gap-3">
        <router-link
          to="/admin/blog"
          class="hidden rounded-xl border border-white/[0.08] px-4 py-3 text-xs font-bold text-slate-300 transition hover:bg-white/[0.04] hover:text-white sm:inline-flex"
        >
          Discard
        </router-link>
        <button
          type="button"
          :disabled="saving"
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-5 py-3 text-xs font-bold text-obsidian shadow-lg shadow-admin/20 transition hover:-translate-y-0.5 hover:shadow-admin/30 disabled:cursor-not-allowed disabled:opacity-50"
          @click="$emit('save')"
        >
          <span v-if="saving" class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"></span>
          <CheckIcon v-else class="h-4 w-4" />
          {{ saving ? 'Saving Article...' : isEditing ? 'Update Article' : 'Publish Article' }}
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ArrowLeftIcon, CheckIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';

defineProps({
  isEditing: { type: Boolean, default: false },
  isPublished: { type: Boolean, default: false },
  saving: { type: Boolean, default: false },
});

defineEmits(['save']);
</script>
