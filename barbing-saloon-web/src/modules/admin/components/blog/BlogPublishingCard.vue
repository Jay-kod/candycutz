<template>
  <section class="space-y-5 rounded-3xl border border-white/[0.08] bg-[#12161C] p-5 shadow-2xl sm:p-7">
    <div class="space-y-2">
      <label for="article-author" class="text-xs font-bold uppercase tracking-wider text-white">Author / source display</label>
      <input
        id="article-author"
        :value="authorDisplay"
        type="text"
        class="w-full rounded-2xl border border-white/[0.08] bg-[#0B0F13] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-admin/50 focus:ring-1 focus:ring-admin/30"
        placeholder="Defaults to CandyCutz Team"
        @input="$emit('update:authorDisplay', $event.target.value)"
      />
    </div>

    <div>
      <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-white">Publication status</h2>
      <div class="grid gap-3 sm:grid-cols-2">
        <button
          type="button"
          class="flex items-start gap-3 rounded-2xl border p-4 text-left transition"
          :class="isPublished ? 'border-emerald-500/50 bg-emerald-500/10' : 'border-white/[0.08] bg-[#0B0F13] hover:border-white/20'"
          :aria-pressed="isPublished"
          @click="$emit('update:isPublished', true)"
        >
          <EyeIcon class="mt-0.5 h-5 w-5 shrink-0" :class="isPublished ? 'text-emerald-400' : 'text-slate-500'" />
          <span>
            <span class="block text-xs font-bold text-white">Publish live</span>
            <span class="mt-1 block text-[11px] leading-relaxed text-slate-400">Visible to clients on the public website.</span>
          </span>
        </button>
        <button
          type="button"
          class="flex items-start gap-3 rounded-2xl border p-4 text-left transition"
          :class="!isPublished ? 'border-amber-500/50 bg-amber-500/10' : 'border-white/[0.08] bg-[#0B0F13] hover:border-white/20'"
          :aria-pressed="!isPublished"
          @click="$emit('update:isPublished', false)"
        >
          <EyeSlashIcon class="mt-0.5 h-5 w-5 shrink-0" :class="!isPublished ? 'text-amber-400' : 'text-slate-500'" />
          <span>
            <span class="block text-xs font-bold text-white">Save as draft</span>
            <span class="mt-1 block text-[11px] leading-relaxed text-slate-400">Keep private until you publish it.</span>
          </span>
        </button>
      </div>
    </div>

    <div class="flex gap-3 border-t border-white/[0.08] pt-4">
      <router-link to="/admin/blog" class="rounded-xl border border-white/[0.08] px-4 py-3 text-xs font-bold text-slate-300 transition hover:bg-white/[0.04]">
        Cancel
      </router-link>
      <button type="submit" :disabled="saving" class="flex-1 rounded-xl bg-gradient-to-r from-admin to-admin-light px-4 py-3 text-xs font-bold text-obsidian transition hover:shadow-lg hover:shadow-admin/20 disabled:opacity-50">
        {{ saving ? 'Saving article...' : isEditing ? 'Update article' : 'Save article' }}
      </button>
    </div>
  </section>
</template>

<script setup>
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

defineProps({
  authorDisplay: { type: String, default: '' },
  isPublished: { type: Boolean, default: false },
  saving: { type: Boolean, default: false },
  isEditing: { type: Boolean, default: false },
});

defineEmits(['update:authorDisplay', 'update:isPublished']);
</script>
