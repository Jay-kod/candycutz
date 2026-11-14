<template>
  <BarberLayout>
    <section class="max-w-7xl mx-auto space-y-8 animate-fade-in pb-16 pt-2 px-4 sm:px-6 lg:px-8">
      <!-- Loading Skeleton State -->
      <BlogEditorSkeleton v-if="loading" />

      <!-- Main Editor Content -->
      <template v-else>
        <!-- Top Hero Header -->
        <BlogEditorHeader
          :is-editing="isEditing"
          :is-published="form.is_published"
          :saving="saving"
          @save="savePost"
        />

        <!-- Two-Column Editor Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          <!-- Left Column: Editorial Input Form (8 cols) -->
          <form @submit.prevent="savePost" class="lg:col-span-8 space-y-6">
            <!-- 1. Cover Image Card -->
            <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-sm dark:shadow-2xl space-y-4">
              <div class="flex items-center justify-between">
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                    Featured Cover Image
                  </label>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    High-resolution horizontal banner displayed at the top of your article and on salon blog cards.
                  </p>
                </div>
                <span v-if="previewImage" class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase tracking-wider bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-full">
                  Image Ready
                </span>
              </div>

              <!-- Drag and drop zone -->
              <div
                @dragover="onDragOver"
                @dragleave="onDragLeave"
                @drop="onDrop"
                class="relative w-full rounded-2xl border-2 transition-all overflow-hidden group flex items-center justify-center"
                :class="[
                  isDragging
                    ? 'border-gold bg-gold/5 scale-[0.99]'
                    : previewImage
                    ? 'border-slate-200 dark:border-white/[0.1] bg-slate-100 dark:bg-[#0B0F13]'
                    : 'border-dashed border-slate-300 dark:border-white/[0.15] bg-slate-50 dark:bg-[#0B0F13] hover:border-gold/50 cursor-pointer'
                ]"
              >
                <input
                  type="file"
                  accept="image/*"
                  @change="onFileSelected"
                  class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                />

                <template v-if="previewImage">
                  <div class="relative w-full aspect-video sm:h-72">
                    <img :src="previewImage" alt="Cover Preview" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                      <span class="bg-[#0B0F13]/90 text-white border border-white/[0.15] px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 pointer-events-none">
                        <PhotoIcon class="h-4 w-4 text-gold" /> Replace Image
                      </span>
                      <button
                        type="button"
                        @click.stop="removeImage"
                        class="bg-red-500/90 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 transition-colors z-20"
                      >
                        <TrashIcon class="h-4 w-4" /> Remove
                      </button>
                    </div>
                  </div>
                </template>

                <template v-else>
                  <div class="py-12 px-6 text-center text-slate-500 dark:text-slate-400 group-hover:text-gold transition-colors">
                    <div class="h-14 w-14 rounded-2xl bg-slate-100 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] flex items-center justify-center mx-auto mb-3 group-hover:border-gold/30 group-hover:scale-105 transition-all">
                      <PhotoIcon class="h-7 w-7 opacity-70 group-hover:text-gold" />
                    </div>
                    <p class="text-xs font-semibold text-slate-900 dark:text-white">Click or drag image to upload header cover</p>
                    <p class="text-[10px] text-slate-500 mt-1">Recommended 1200x630px JPG, PNG, or WebP (Max 2MB)</p>
                  </div>
                </template>
              </div>
            </div>

            <!-- 2. Title & Excerpt Card -->
            <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-sm dark:shadow-2xl space-y-6">
              <!-- Title -->
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                    Article Title <span class="text-gold">*</span>
                  </label>
                  <span class="text-[11px] font-mono" :class="form.title.length > 70 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-500'">
                    {{ form.title.length }} / 70 chars
                  </span>
                </div>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="w-full rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] px-4 py-3.5 text-base text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all font-display"
                  placeholder="e.g. 5 Barber-Approved Ways to Maintain a Razor-Sharp Fade"
                />

                <!-- Permalink Preview -->
                <div class="flex items-center gap-2 pt-1 text-[11px] text-slate-500 dark:text-slate-400">
                  <LinkIcon class="h-3.5 w-3.5 text-gold shrink-0" />
                  <span class="text-slate-500">Permalink:</span>
                  <span class="font-mono text-slate-700 dark:text-slate-300 truncate">
                    candycutz.com/blog/{{ computedSlug }}
                  </span>
                </div>
              </div>

              <!-- Excerpt -->
              <div class="space-y-2">
                <div class="flex items-center justify-between">
                  <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                    Excerpt / Brief Summary
                  </label>
                  <span class="text-[11px] font-mono text-slate-500">
                    {{ (form.excerpt || '').length }} / 160 chars
                  </span>
                </div>
                <textarea
                  v-model="form.excerpt"
                  rows="3"
                  class="w-full rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] px-4 py-3 text-xs sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all resize-none leading-relaxed"
                  placeholder="Provide a concise summary teaser that entices clients to open and read this grooming guide..."
                ></textarea>
                <p class="text-[10px] text-slate-500">
                  Shown in article card previews, search results, and client mobile notifications.
                </p>
              </div>
            </div>

            <!-- 3. Article Body Content & Toolbar Card -->
            <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-sm dark:shadow-2xl space-y-4">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                  <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                    Article Body Content <span class="text-gold">*</span>
                  </label>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    Compose detailed hair care advice, styling recommendations, or salon announcements.
                  </p>
                </div>
                <div class="flex items-center gap-3 text-xs font-mono text-slate-500 dark:text-slate-400">
                  <span>{{ wordCount }} words</span>
                  <span>•</span>
                  <span>{{ readingTime }}</span>
                </div>
              </div>

              <!-- Formatting Toolbar -->
              <BlogEditorToolbar @format="insertFormatting" />

              <!-- Content Textarea -->
              <textarea
                ref="textareaRef"
                v-model="form.content"
                rows="14"
                required
                class="w-full rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] p-4 text-sm text-slate-900 dark:text-slate-200 placeholder:text-slate-400 dark:placeholder:text-slate-600 outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all resize-y leading-relaxed font-sans"
                placeholder="Write your article content here. Use paragraphs, bullet points, and pro tips to make it engaging for salon visitors..."
              ></textarea>
            </div>

            <!-- 4. Author Attribution & Publishing Status Card -->
            <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 sm:p-8 shadow-sm dark:shadow-2xl space-y-6">
              <!-- Author Display Attribution -->
              <div class="space-y-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                  Author / Stylist Attribution
                </label>
                <input
                  v-model="form.author_display"
                  type="text"
                  class="w-full rounded-2xl border border-slate-300 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] px-4 py-3.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-all"
                  placeholder="e.g. Master Barber Marcus (Leave blank to use your profile name)"
                />
              </div>

              <!-- Publication Status Selector -->
              <div class="space-y-3">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                  Publication Status
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <!-- Live Option -->
                  <div
                    @click="form.is_published = true"
                    class="cursor-pointer rounded-2xl border p-4.5 transition-all flex items-start gap-3.5"
                    :class="
                      form.is_published
                        ? 'border-emerald-500/50 bg-emerald-500/10 shadow-[0_0_20px_rgba(16,185,129,0.1)]'
                        : 'border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] hover:border-slate-300 dark:hover:border-white/[0.15]'
                    "
                  >
                    <div
                      class="h-8 w-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                      :class="form.is_published ? 'bg-emerald-500 text-obsidian' : 'bg-slate-200 dark:bg-white/[0.04] text-slate-600 dark:text-slate-400'"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </div>
                    <div>
                      <p class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        Publish Live
                        <span v-if="form.is_published" class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                      </p>
                      <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-normal">
                        Visible immediately to all salon visitors on the website and client app.
                      </p>
                    </div>
                  </div>

                  <!-- Draft Option -->
                  <div
                    @click="form.is_published = false"
                    class="cursor-pointer rounded-2xl border p-4.5 transition-all flex items-start gap-3.5"
                    :class="
                      !form.is_published
                        ? 'border-amber-500/50 bg-amber-500/10 shadow-[0_0_20px_rgba(245,158,11,0.1)]'
                        : 'border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-[#0B0F13] hover:border-slate-300 dark:hover:border-white/[0.15]'
                    "
                  >
                    <div
                      class="h-8 w-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                      :class="!form.is_published ? 'bg-amber-400 text-obsidian' : 'bg-slate-200 dark:bg-white/[0.04] text-slate-600 dark:text-slate-400'"
                    >
                      <EyeSlashIcon class="h-4 w-4" />
                    </div>
                    <div>
                      <p class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        Save as Draft
                        <span v-if="!form.is_published" class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                      </p>
                      <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-normal">
                        Private to you. Will not appear publicly until you choose to publish it.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>

          <!-- Right Column: Live Client Preview & Sidebar Actions (4 cols) -->
          <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-8">
            <!-- Client Live Preview Card -->
            <BlogLivePreviewCard
              :title="form.title"
              :excerpt="form.excerpt"
              :content="form.content"
              :preview-image="previewImage"
              :author-display="form.author_display"
              :is-published="form.is_published"
              :reading-time="readingTime"
              :formatted-date="formattedDate"
            />

            <!-- Sidebar Actions & Publishing Card -->
            <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 shadow-sm dark:shadow-2xl space-y-4">
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Publishing Actions</h3>

              <div class="space-y-3 pt-1">
                <button
                  type="button"
                  @click="savePost"
                  :disabled="saving"
                  class="w-full flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 py-3.5 px-4 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-gold/20 hover:shadow-[0_8px_30px_rgba(255,153,0,0.35)] transition-all hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50"
                >
                  <span
                    v-if="saving"
                    class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"
                  ></span>
                  <CheckIcon v-else class="h-4 w-4 stroke-[2.5]" />
                  <span>{{ saving ? 'Saving Article...' : isEditing ? 'Update Article' : 'Publish Article' }}</span>
                </button>

                <router-link
                  to="/barber/blog"
                  class="w-full flex items-center justify-center py-3 px-4 rounded-2xl text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-white/[0.08] hover:bg-slate-100 dark:hover:bg-white/[0.04] transition-colors"
                >
                  Discard Changes
                </router-link>

                <router-link
                  v-if="isEditing && form.is_published"
                  :to="`/customer/dashboard/blog/${form.slug || computedSlug}`"
                  target="_blank"
                  class="w-full flex items-center justify-center gap-1.5 py-2.5 px-4 rounded-2xl text-[11px] font-semibold text-amber-800 dark:text-gold/80 hover:text-gold border border-gold/20 bg-gold/5 hover:bg-gold/10 transition-colors"
                >
                  <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
                  <span>View Public Post</span>
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile Bottom Action Bar (Fixed at bottom for small screens) -->
        <div class="md:hidden sticky bottom-4 z-30 flex items-center gap-3 p-3 rounded-2xl bg-white/95 dark:bg-[#12161C]/95 backdrop-blur-xl border border-slate-200 dark:border-white/10 shadow-lg dark:shadow-2xl">
          <router-link
            to="/barber/blog"
            class="flex-1 text-center py-3 px-3 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-white/[0.08]"
          >
            Cancel
          </router-link>
          <button
            type="button"
            @click="savePost"
            :disabled="saving"
            class="flex-[2] flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 py-3 px-4 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-gold/20 disabled:opacity-50"
          >
            <span
              v-if="saving"
              class="h-4 w-4 animate-spin rounded-full border-2 border-obsidian/30 border-t-obsidian"
            ></span>
            <CheckIcon v-else class="h-4 w-4 stroke-[2.5]" />
            <span>{{ saving ? 'Saving...' : isEditing ? 'Update' : 'Publish' }}</span>
          </button>
        </div>
      </template>
    </section>
  </BarberLayout>
</template>

<script setup>
import { onMounted } from 'vue';
import BarberLayout from '@/portals/barber/layouts/BarberLayout.vue';
import { useBarberBlogEditor } from '../composables/useBarberBlogEditor';
import BlogEditorHeader from '../components/blog/BlogEditorHeader.vue';
import BlogLivePreviewCard from '../components/blog/BlogLivePreviewCard.vue';
import BlogEditorToolbar from '../components/blog/BlogEditorToolbar.vue';
import BlogEditorSkeleton from '../components/blog/BlogEditorSkeleton.vue';
import {
  PhotoIcon,
  TrashIcon,
  LinkIcon,
  EyeIcon,
  EyeSlashIcon,
  CheckIcon,
  ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline';

const {
  isEditing,
  loading,
  saving,
  previewImage,
  isDragging,
  textareaRef,
  form,
  wordCount,
  readingTime,
  computedSlug,
  formattedDate,
  onFileSelected,
  removeImage,
  onDragOver,
  onDragLeave,
  onDrop,
  insertFormatting,
  loadPost,
  savePost,
} = useBarberBlogEditor();

onMounted(() => {
  loadPost();
});
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
