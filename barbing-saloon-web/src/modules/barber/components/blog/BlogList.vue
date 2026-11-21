<template>
  <div>
    <!-- Empty State -->
    <div
      v-if="posts.length === 0"
      class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-12 text-center shadow-sm dark:shadow-xl space-y-4"
    >
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.06]">
        <PencilSquareIcon class="h-8 w-8 text-slate-500" />
      </div>

      <div class="max-w-md mx-auto space-y-1.5">
        <h3 class="text-lg font-display font-bold text-slate-900 dark:text-white">
          {{ hasActiveFilters ? 'No Matching Articles' : 'No Blog Articles Yet' }}
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
          {{
            hasActiveFilters
              ? 'No articles match your current search or filter criteria. Try clearing search filters to see all articles.'
              : 'Begin publishing hair grooming tips, trend updates, and styling advice to engage your salon clientele.'
          }}
        </p>
      </div>

      <div class="pt-2">
        <button
          v-if="hasActiveFilters"
          type="button"
          @click="$emit('reset-filters')"
          class="inline-flex items-center gap-2 rounded-2xl border border-gold/30 bg-gold/10 px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-gold hover:bg-gold/20 transition-all"
        >
          <ArrowPathIcon class="h-4 w-4" />
          Clear Filters
        </button>

        <router-link
          v-else
          to="/barber/blog/new"
          class="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-gold via-amber-400 to-amber-500 px-6 py-3 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-gold/20 hover:shadow-[0_8px_30px_rgba(255,153,0,0.35)] transition-all"
        >
          <PlusIcon class="h-4 w-4 stroke-[2.5]" />
          Draft First Article
        </router-link>
      </div>
    </div>

    <!-- Grid View Mode -->
    <div
      v-else-if="viewMode === 'grid'"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <article
        v-for="post in posts"
        :key="post.id"
        class="group flex flex-col rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] overflow-hidden shadow-sm dark:shadow-xl hover:border-gold/40 transition-all duration-300"
        data-test="post-card"
      >
        <!-- Media Header -->
        <div class="relative aspect-video w-full overflow-hidden bg-slate-100 dark:bg-[#0B0F13]">
          <template v-if="post.featured_image">
            <img
              :src="getImageUrl(post.featured_image)"
              :alt="post.title"
              class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-white dark:from-[#12161C] via-transparent to-black/30"></div>
          </template>
          <template v-else>
            <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-slate-100 via-slate-200 to-slate-100 dark:from-[#161B22] dark:via-[#0E1217] dark:to-black p-4 text-center">
              <div class="h-10 w-10 rounded-xl bg-gold/10 border border-gold/20 flex items-center justify-center mb-1">
                <SparklesIcon class="h-5 w-5 text-gold" />
              </div>
              <span class="text-[10px] uppercase tracking-widest text-slate-500 dark:text-slate-400 font-semibold">CandyCutz Insight</span>
            </div>
          </template>

          <!-- Floating Pill Badges -->
          <div class="absolute top-3 left-3 right-3 flex items-center justify-between z-10 pointer-events-none">
            <span
              class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider backdrop-blur-md shadow-md"
              :class="
                isLive(post)
                  ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/30'
                  : 'bg-amber-950/80 text-amber-300 border border-amber-500/30'
              "
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="isLive(post) ? 'bg-emerald-400' : 'bg-amber-400'"
              ></span>
              {{ isLive(post) ? 'Live' : 'Draft' }}
            </span>

            <span class="px-2.5 py-1 rounded-full bg-black/70 backdrop-blur-md border border-white/10 text-[9px] font-medium text-slate-200 shadow-md">
              {{ getReadTime(post.content || post.body) }}
            </span>
          </div>
        </div>

        <!-- Article Content -->
        <div class="p-6 flex flex-col flex-1">
          <!-- Date and Author metadata -->
          <div class="flex items-center justify-between text-xs text-slate-500 mb-2.5">
            <span class="text-[11px] font-medium">{{ formatDate(post.created_at) }}</span>
            <span class="text-[11px] text-slate-600 dark:text-slate-400 truncate max-w-[130px]">
              {{ post.author_display || post.author?.name || 'CandyCutz Stylist' }}
            </span>
          </div>

          <!-- Title -->
          <h3 class="font-display text-base sm:text-lg font-bold text-slate-900 dark:text-white group-hover:text-gold transition-colors line-clamp-2 leading-snug mb-2">
            {{ post.title }}
          </h3>

          <!-- Excerpt -->
          <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-3 leading-relaxed mb-4 flex-1">
            {{ post.excerpt || (post.content || post.body || '').replace(/<[^>]*>?/gm, '').replace(/[#*`_>]/g, '').substring(0, 140) }}
          </p>

          <!-- Engagement Stats & Action Bar -->
          <div class="pt-4 border-t border-slate-200 dark:border-white/[0.06] flex items-center justify-between gap-2 mt-auto">
            <!-- Left: Reaction Count -->
            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 shrink-0">
              <HeartIcon class="h-4 w-4 text-rose-500 dark:text-rose-400" />
              <span class="font-mono text-[11px]">{{ post.loves_count || 0 }}</span>
            </div>

            <!-- Right: Action Buttons -->
            <div class="flex items-center gap-1.5 shrink-0">
              <router-link
                :to="`/barber/blog/${post.id}/edit`"
                class="inline-flex items-center gap-1 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white dark:hover:bg-white/[0.08] hover:border-gold/30 transition-all"
                title="Edit Article"
                data-test="edit-post-btn"
              >
                <PencilSquareIcon class="h-3.5 w-3.5 text-gold" />
                <span>Edit</span>
              </router-link>

              <button
                type="button"
                @click="$emit('toggle-publish', post)"
                class="inline-flex items-center px-2.5 py-1.5 rounded-xl border text-xs font-semibold transition-all"
                :class="
                  isLive(post)
                    ? 'bg-amber-500/10 border-amber-500/20 text-amber-700 dark:text-amber-400 hover:bg-amber-500/20'
                    : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-500/20'
                "
                :title="isLive(post) ? 'Switch to private draft' : 'Publish to live website'"
                data-test="toggle-publish-btn"
              >
                {{ isLive(post) ? 'Unpublish' : 'Publish' }}
              </button>

              <router-link
                v-if="isLive(post)"
                :to="`/customer/dashboard/blog/${post.slug || post.id}`"
                target="_blank"
                class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.04] border border-slate-200 dark:border-white/[0.08] text-slate-500 dark:text-slate-400 hover:text-gold hover:border-gold/30 transition-all"
                title="View on Live Salon Blog"
              >
                <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
              </router-link>

              <button
                type="button"
                @click="$emit('delete', post.id)"
                class="p-1.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-500/20 transition-colors"
                title="Delete Article"
                data-test="delete-post-btn"
              >
                <TrashIcon class="h-3.5 w-3.5" />
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>

    <!-- List View Mode (Horizontal Cards) -->
    <div v-else class="space-y-4">
      <div
        v-for="post in posts"
        :key="post.id"
        class="group rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 hover:border-gold/40 transition-all duration-300 shadow-sm dark:shadow-xl"
        data-test="post-card"
      >
        <div class="flex flex-col md:flex-row md:items-start gap-5">
          <!-- Thumbnail -->
          <div
            class="w-full md:w-44 h-36 shrink-0 rounded-2xl overflow-hidden border border-slate-200 dark:border-white/[0.08] relative group-hover:border-gold/30 transition-colors bg-slate-100 dark:bg-[#0B0F13]"
          >
            <template v-if="post.featured_image">
              <img
                :src="getImageUrl(post.featured_image)"
                :alt="post.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              />
            </template>
            <template v-else>
              <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-slate-100 via-slate-200 to-slate-100 dark:from-[#161B22] dark:via-[#0E1217] dark:to-black p-4 text-center">
                <SparklesIcon class="h-6 w-6 text-gold mb-1" />
                <span class="text-[9px] uppercase tracking-widest text-slate-500 font-bold">CandyCutz</span>
              </div>
            </template>
          </div>

          <!-- Body details -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-3 mb-2">
              <span
                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                :class="
                  isLive(post)
                    ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20'
                    : 'bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-500/20'
                "
              >
                <span
                  class="h-1.5 w-1.5 rounded-full"
                  :class="isLive(post) ? 'bg-emerald-500' : 'bg-amber-500'"
                ></span>
                {{ isLive(post) ? 'Live' : 'Draft' }}
              </span>

              <span class="text-xs text-slate-500 font-medium">
                {{ formatDate(post.created_at) }}
              </span>

              <span class="text-xs text-slate-500">
                • {{ getReadTime(post.content || post.body) }}
              </span>

              <span v-if="post.loves_count" class="flex items-center gap-1 text-xs text-rose-500 dark:text-rose-400 ml-auto">
                <HeartIcon class="h-3.5 w-3.5" />
                <span class="font-mono text-[11px]">{{ post.loves_count }}</span>
              </span>
            </div>

            <h3 class="text-lg font-display font-bold text-slate-900 dark:text-white group-hover:text-gold transition-colors mb-2">
              {{ post.title }}
            </h3>

            <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2 leading-relaxed mb-4">
              {{ post.excerpt || (post.content || post.body || '').replace(/<[^>]*>?/gm, '').replace(/[#*`_>]/g, '').substring(0, 140) }}
            </p>

            <!-- Actions Bar -->
            <div class="flex items-center gap-2 pt-3 border-t border-slate-200 dark:border-white/[0.04]">
              <router-link
                :to="`/barber/blog/${post.id}/edit`"
                class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white dark:hover:bg-white/[0.06] hover:border-gold/30 transition-all"
                data-test="edit-post-btn"
              >
                <PencilSquareIcon class="h-3.5 w-3.5 text-gold" />
                <span>Edit</span>
              </router-link>

              <button
                type="button"
                @click="$emit('toggle-publish', post)"
                class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-xs font-semibold transition-colors"
                :class="
                  isLive(post)
                    ? 'bg-amber-500/10 border-amber-500/20 text-amber-700 dark:text-amber-400 hover:bg-amber-500/20'
                    : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-500/20'
                "
                data-test="toggle-publish-btn"
              >
                {{ isLive(post) ? 'Unpublish' : 'Publish' }}
              </button>

              <router-link
                v-if="isLive(post)"
                :to="`/customer/dashboard/blog/${post.slug || post.id}`"
                target="_blank"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-white/[0.03] border border-slate-200 dark:border-white/[0.08] text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-gold hover:border-gold/30 transition-all"
                title="View on Live Salon Blog"
              >
                <ArrowTopRightOnSquareIcon class="h-3.5 w-3.5" />
                <span>View Live</span>
              </router-link>

              <button
                type="button"
                @click="$emit('delete', post.id)"
                class="p-1.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 hover:bg-red-500/20 transition-colors ml-auto"
                title="Delete Post"
                data-test="delete-post-btn"
              >
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  PencilSquareIcon,
  PlusIcon,
  TrashIcon,
  SparklesIcon,
  HeartIcon,
  ArrowTopRightOnSquareIcon,
  ArrowPathIcon,
} from '@heroicons/vue/24/outline';

defineProps({
  posts: {
    type: Array,
    required: true,
  },
  getImageUrl: {
    type: Function,
    required: true,
  },
  formatDate: {
    type: Function,
    required: true,
  },
  viewMode: {
    type: String,
    default: 'grid',
  },
  hasActiveFilters: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['toggle-publish', 'delete', 'reset-filters']);

function isLive(post) {
  return Number(post.is_published) === 1 || post.status === 'published';
}

function getReadTime(content) {
  if (!content) return '1 min read';
  const words = content.replace(/<[^>]*>?/gm, '').trim().split(/\s+/).filter(Boolean).length;
  const minutes = Math.max(1, Math.ceil(words / 200));
  return `${minutes} min read`;
}
</script>
