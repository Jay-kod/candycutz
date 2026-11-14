<template>
  <CustomerLayout>
    <main class="min-h-screen pb-32 animate-fade-in">
      <!-- Skeleton Loader -->
      <div v-if="isLoading" class="w-full space-y-8">
        <div class="h-72 w-full rounded-3xl bg-white/5 border border-white/5 animate-pulse"></div>
        <div class="mx-auto max-w-3xl space-y-4">
          <div class="h-6 w-32 rounded bg-white/5 animate-pulse"></div>
          <div class="h-10 w-full rounded-lg bg-white/5 animate-pulse"></div>
          <div class="space-y-3 pt-6">
            <div v-for="i in 6" :key="i" class="h-4 w-full rounded bg-white/5 animate-pulse"></div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="mx-auto max-w-2xl px-6 text-center py-32">
        <div class="h-16 w-16 mx-auto mb-4 rounded-2xl bg-white/[0.04] border border-white/10 flex items-center justify-center text-red-400">
          <ExclamationTriangleIcon class="w-8 h-8" />
        </div>
        <h1 class="font-display text-3xl text-white">Article Not Found</h1>
        <p class="mt-2 text-sm text-[var(--color-text-secondary)]">The article you are looking for does not exist or may have been unlisted.</p>
        <RouterLink to="/customer/dashboard/blog" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-[var(--color-accent)] px-6 py-2.5 text-xs font-bold text-white shadow-lg hover:brightness-110 transition-all">
          Back to Blog
        </RouterLink>
      </div>

      <!-- Post Content -->
      <article v-else class="space-y-8">
        <!-- Hero Header -->
        <header class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-[#12161C] p-8 lg:p-12 shadow-2xl flex flex-col justify-end min-h-[380px]">
          <div v-if="post.featured_image" class="absolute inset-0">
            <img :src="getImageUrl(post.featured_image)" :alt="post.title" class="h-full w-full object-cover opacity-30" />
            <div class="absolute inset-0 bg-gradient-to-t from-white via-white/80 dark:from-[#12161C] dark:via-[#12161C]/80 to-transparent"></div>
          </div>
          
          <div class="relative z-10 max-w-4xl">
            <div class="flex items-center gap-3 text-xs uppercase tracking-widest font-semibold text-[var(--color-accent)] mb-4">
              <span>{{ post.author?.name || 'CandyCutz Editorial' }}</span>
              <span class="w-1 h-1 rounded-full bg-slate-400 dark:bg-white/30"></span>
              <span>{{ formatDate(post.created_at) }}</span>
            </div>
            
            <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 dark:text-white leading-tight">
              {{ post.title }}
            </h1>
          </div>
        </header>

        <!-- Article Body & Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
          <!-- Main Content -->
          <div class="lg:col-span-8 min-w-0">
            <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 lg:p-10 shadow-xl">
              <div class="blog-content text-slate-700 dark:text-[var(--color-text-secondary)] font-normal leading-relaxed">
                <div v-html="sanitizedContent"></div>
              </div>
              
              <!-- Reactions Bar -->
              <div class="mt-12 pt-8 border-t border-slate-100 dark:border-white/[0.06] flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                  <!-- Love Button -->
                  <button 
                    @click="react('love')" 
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold transition-all"
                    :class="userReaction === 'love' 
                      ? 'border-[var(--color-accent)] bg-[var(--color-accent)]/15 text-[var(--color-accent)] shadow-[0_0_15px_rgba(255,103,0,0.2)]' 
                      : 'border-slate-200 dark:border-white/10 bg-slate-100 dark:bg-white/[0.04] text-slate-700 dark:text-white hover:border-[var(--color-accent)] hover:text-[var(--color-accent)]'"
                  >
                    <HeartIcon :class="{'fill-current': userReaction === 'love'}" class="w-4 h-4" />
                    <span>{{ post.loves_count || 0 }}</span>
                    <span class="opacity-70 font-normal">Love</span>
                  </button>

                  <!-- Dislike Button -->
                  <button 
                    @click="react('dislike')" 
                    class="flex items-center gap-2 rounded-xl border px-4 py-2 text-xs font-bold transition-all"
                    :class="userReaction === 'dislike' 
                      ? 'border-red-500 bg-red-500/15 text-red-400' 
                      : 'border-slate-200 dark:border-white/10 bg-slate-100 dark:bg-white/[0.04] text-slate-700 dark:text-white hover:border-red-500 hover:text-red-400'"
                  >
                    <HandThumbDownIcon :class="{'fill-current': userReaction === 'dislike'}" class="w-4 h-4" />
                    <span>{{ post.dislikes_count || 0 }}</span>
                    <span class="opacity-70 font-normal">Dislike</span>
                  </button>
                </div>

                <RouterLink 
                  to="/customer/dashboard/blog" 
                  class="inline-flex items-center gap-1.5 text-xs font-bold text-[var(--color-accent)] hover:underline"
                >
                  &larr; Back to all stories
                </RouterLink>
              </div>
            </div>
          </div>

          <!-- Sidebar (Recent Posts) -->
          <aside class="lg:col-span-4 space-y-6">
            <div class="rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] p-6 shadow-xl">
              <h3 class="font-display text-lg text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <BookOpenIcon class="w-5 h-5 text-[var(--color-accent)]" />
                Recent <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-500 dark:to-amber-200">Articles</span>
              </h3>
              
              <div v-if="recentPosts.length === 0" class="text-xs text-slate-500 dark:text-[var(--color-text-secondary)]">No other articles available.</div>
              
              <div class="space-y-4">
                <article v-for="recent in recentPosts" :key="recent.id" class="group flex gap-3.5 items-center">
                  <RouterLink :to="`/customer/dashboard/blog/${recent.slug}`" class="block w-16 h-16 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10">
                    <img v-if="recent.featured_image" :src="getImageUrl(recent.featured_image)" :alt="recent.title" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                  </RouterLink>
                  <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-wider text-slate-500 dark:text-[var(--color-text-muted)] font-bold mb-1">{{ formatDate(recent.created_at) }}</p>
                    <RouterLink :to="`/customer/dashboard/blog/${recent.slug}`" class="block text-xs font-semibold text-slate-900 dark:text-white leading-snug group-hover:text-[var(--color-accent)] transition-colors line-clamp-2">
                      {{ recent.title }}
                    </RouterLink>
                  </div>
                </article>
              </div>
            </div>
          </aside>
        </div>
      </article>
    </main>
  </CustomerLayout>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { client } from '@/shared/api/client';
import { getApiCollection } from '@/shared/api/getApiCollection';
import { HeartIcon, HandThumbDownIcon, BookOpenIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { getStorageUrl as getImageUrl } from '@/core/utils/url';
import { sanitizeBlogContent } from '../utils/sanitizeBlogContent';

const route = useRoute();
const post = ref(null);
const recentPosts = ref([]);
const isLoading = ref(true);
const error = ref(false);
const sanitizedContent = computed(() => sanitizeBlogContent(post.value?.content));
const userReaction = ref(null);

const react = async (type) => {
  if (!post.value) return;
  
  try {
    if (userReaction.value === type) {
      const res = await client.delete(`/customer/blog/${post.value.id}/react`);
      post.value.loves_count = res.data?.loves_count ?? (post.value.loves_count - 1);
      post.value.dislikes_count = res.data?.dislikes_count ?? post.value.dislikes_count;
      userReaction.value = null;
    } else {
      const res = await client.post(`/customer/blog/${post.value.id}/react`, { type });
      post.value.loves_count = res.data?.loves_count ?? post.value.loves_count;
      post.value.dislikes_count = res.data?.dislikes_count ?? post.value.dislikes_count;
      userReaction.value = res.data?.user_reaction || type;
    }
  } catch (err) {
    console.error('Failed to react:', err);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
};

const loadData = async (slug) => {
  isLoading.value = true;
  error.value = false;
  window.scrollTo(0, 0);
  
  try {
    const response = await client.get(`/blog/${slug}`);
    post.value = response.data?.data || response.data;
    userReaction.value = post.value?.user_reaction || null;
    
    const allPostsRes = await client.get('/blog');
    const all = getApiCollection(allPostsRes);
    recentPosts.value = all.filter(p => p.slug !== slug).slice(0, 4);
  } catch (err) {
    console.error('Failed to load blog post:', err);
    error.value = true;
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadData(route.params.slug);
});

watch(() => route.params.slug, (newSlug) => {
  if (newSlug) {
    loadData(newSlug);
  }
});
</script>

<style scoped>
.blog-content :deep(h1),
.blog-content :deep(h2),
.blog-content :deep(h3),
.blog-content :deep(h4) {
  font-family: 'Playfair Display', serif;
  color: var(--color-accent);
  margin-top: 2rem;
  margin-bottom: 0.75rem;
  line-height: 1.3;
}
.blog-content :deep(h2) { font-size: 1.5rem; }
.blog-content :deep(h3) { font-size: 1.25rem; }
.blog-content :deep(p) { margin-bottom: 1.25rem; line-height: 1.7; font-size: 0.95rem; }
.blog-content :deep(img) { max-width: 100%; border-radius: 1rem; margin: 1.5rem 0; }
.blog-content :deep(a) { color: var(--color-accent); text-decoration: underline; }
.blog-content :deep(ul) { list-style-type: disc; padding-left: 1.5rem; margin-bottom: 1.25rem; }
.blog-content :deep(ol) { list-style-type: decimal; padding-left: 1.5rem; margin-bottom: 1.25rem; }
</style>