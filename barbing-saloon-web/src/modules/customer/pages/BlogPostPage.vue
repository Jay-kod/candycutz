<template>
  <CustomerLayout>
    <main class="min-h-screen bg-theme-bg pb-32">
      
      <!-- Skeleton Loader -->
      <div v-if="isLoading" class="w-full">
        <div class="h-[60vh] w-full skeleton-shimmer"></div>
        <div class="mx-auto max-w-3xl px-6 mt-16 space-y-6">
          <div class="h-6 w-32 skeleton-shimmer rounded"></div>
          <div class="h-12 w-full skeleton-shimmer rounded-lg"></div>
          <div class="h-12 w-3/4 skeleton-shimmer rounded-lg"></div>
          <div class="space-y-4 mt-12">
            <div v-for="i in 8" :key="i" class="h-4 w-full skeleton-shimmer rounded"></div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="mx-auto max-w-2xl px-6 text-center py-40">
        <h1 class="font-display text-5xl text-gold">Article not found</h1>
        <p class="mt-6 text-theme-muted text-lg">The post you're looking for doesn't exist or has been removed.</p>
        <RouterLink to="/customer/dashboard/blog" class="mt-10 inline-block rounded-full bg-gold px-8 py-4 text-obsidian font-bold tracking-wide hover:bg-gold-light transition-all">Back to Blog</RouterLink>
      </div>

      <!-- Post Content -->
      <article v-else>
        <!-- Hero Header -->
        <header class="relative h-[70vh] min-h-[500px] w-full flex items-center justify-center overflow-hidden">
          <div class="absolute inset-0">
            <img v-if="post.featured_image" :src="post.featured_image" :alt="post.title" class="h-full w-full object-cover" />
            <div class="absolute inset-0 bg-obsidian/60 backdrop-blur-[2px]"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-theme-bg via-transparent to-transparent opacity-100"></div>
          </div>
          
          <div class="relative z-10 mx-auto max-w-4xl px-6 text-center" data-reveal>
            <div class="flex items-center justify-center gap-3 text-sm uppercase tracking-[0.2em] font-semibold text-gold-light mb-8">
              <span>{{ post.author?.name || 'CandyCutz' }}</span>
              <span class="w-1 h-1 rounded-full bg-theme-border"></span>
              <span>{{ formatDate(post.created_at) }}</span>
            </div>
            
            <h1 class="font-display text-5xl md:text-7xl font-bold text-theme-text leading-tight drop-shadow-2xl">
              {{ post.title }}
            </h1>
          </div>
        </header>

        <!-- Article Body & Sidebar -->
        <div class="mx-auto max-w-7xl px-6 mt-16 lg:mt-24">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-16">
            
            <!-- Left: Main Content -->
            <div class="lg:col-span-8 min-w-0">
              <div data-reveal data-reveal-delay="100" class="blog-content text-theme-text font-light leading-relaxed">
                <!-- Render the HTML content safely -->
                <div v-html="post.content"></div>
              </div>
              
              <!-- Reactions & Sharing -->
              <div class="mt-20 pt-10 border-t border-theme-border flex flex-col md:flex-row items-center justify-between gap-8" data-reveal>
                
                <!-- Reaction Buttons -->
                <div class="flex items-center gap-4">
                  <!-- Love Button -->
                  <button @click="react('love')" class="group flex items-center gap-3 rounded-full border bg-theme-surface px-6 py-3 transition-all duration-300 hover:shadow-[0_0_20px_rgba(212,175,55,0.2)]" :class="{ 'border-gold shadow-[0_0_20px_rgba(212,175,55,0.2)] text-gold': userReaction === 'love', 'border-theme-border text-theme-text hover:border-gold hover:text-gold': userReaction !== 'love' }">
                    <HeartIcon :class="{'fill-current': userReaction === 'love'}" class="w-6 h-6 transition-transform group-hover:scale-125" />
                    <span class="font-semibold">{{ post.loves_count || 0 }}</span>
                    <span class="text-xs uppercase tracking-widest ml-1" :class="{ 'text-gold': userReaction === 'love', 'text-theme-muted group-hover:text-gold': userReaction !== 'love' }">Love</span>
                  </button>

                  <!-- Dislike Button -->
                  <button @click="react('dislike')" class="group flex items-center gap-3 rounded-full border bg-theme-surface px-6 py-3 transition-all duration-300 hover:shadow-[0_0_20px_rgba(239,68,68,0.2)]" :class="{ 'border-red-500 shadow-[0_0_20px_rgba(239,68,68,0.2)] text-red-500': userReaction === 'dislike', 'border-theme-border text-theme-text hover:border-red-500 hover:text-red-500': userReaction !== 'dislike' }">
                    <HandThumbDownIcon :class="{'fill-current': userReaction === 'dislike'}" class="w-6 h-6 transition-transform group-hover:scale-125" />
                    <span class="font-semibold">{{ post.dislikes_count || 0 }}</span>
                    <span class="text-xs uppercase tracking-widest ml-1" :class="{ 'text-red-500': userReaction === 'dislike', 'text-theme-muted group-hover:text-red-500': userReaction !== 'dislike' }">Dislike</span>
                  </button>
                </div>

                <!-- Sharing -->
                <div class="flex items-center gap-4">
                  <span class="text-xs uppercase tracking-[0.2em] text-theme-muted">Share</span>
                  <!-- Dummy share buttons -->
                  <button class="w-10 h-10 rounded-full border border-theme-border bg-theme-surface flex items-center justify-center text-theme-text hover:text-gold hover:border-gold transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                  </button>
                  <button class="w-10 h-10 rounded-full border border-theme-border bg-theme-surface flex items-center justify-center text-theme-text hover:text-gold hover:border-gold transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/></svg>
                  </button>
                </div>
              </div>
              
              <div class="mt-16">
                <RouterLink to="/blog" class="inline-flex items-center gap-2 font-semibold text-gold hover:text-gold-light transition-colors">
                  &larr; Back to Blog
                </RouterLink>
              </div>
            </div>

            <!-- Right: Sidebar (Similar Posts) -->
            <aside class="lg:col-span-4 space-y-12 lg:sticky lg:top-32" data-reveal>
              <div class="rounded-3xl border border-theme-border bg-theme-surface p-8 shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gold/5 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
                <h3 class="font-display text-2xl text-gold mb-8">Recent Articles</h3>
                
                <div v-if="recentPosts.length === 0" class="text-sm text-theme-muted">No other articles available.</div>
                
                <div class="space-y-6">
                  <article v-for="recent in recentPosts" :key="recent.id" class="group flex gap-4 items-center">
                    <RouterLink :to="`/customer/dashboard/blog/${recent.slug}`" class="block w-20 h-20 shrink-0 overflow-hidden rounded-xl bg-theme-bg">
                      <img v-if="recent.featured_image" :src="recent.featured_image" :alt="recent.title" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" />
                    </RouterLink>
                    <div>
                      <p class="text-[10px] uppercase tracking-widest text-gold-light mb-1">{{ formatDate(recent.created_at) }}</p>
                      <RouterLink :to="`/customer/dashboard/blog/${recent.slug}`" class="block text-sm font-semibold text-theme-text leading-snug group-hover:text-gold transition-colors line-clamp-2">
                        {{ recent.title }}
                      </RouterLink>
                    </div>
                  </article>
                </div>
              </div>
            </aside>

          </div>
        </div>
      </article>

    </main>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';
import { customerApi } from '../api/customer.api';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';
import { HeartIcon, HandThumbDownIcon } from '@heroicons/vue/24/outline';

const route = useRoute();
const post = ref(null);
const recentPosts = ref([]);
const isLoading = ref(true);
const error = ref(false);

const { init: initScrollReveal } = useScrollReveal();

// Reactions
const userReaction = ref(null);

const react = async (type) => {
  if (!post.value) return;
  
  try {
    if (userReaction.value === type) {
      // Toggle off
      const res = await customerApi.removeReactionFromBlogPost(post.value.id);
      post.value.loves_count = res.data.loves_count;
      post.value.dislikes_count = res.data.dislikes_count;
      userReaction.value = null;
    } else {
      // Toggle on
      const res = await customerApi.reactToBlogPost(post.value.id, type);
      post.value.loves_count = res.data.loves_count;
      post.value.dislikes_count = res.data.dislikes_count;
      userReaction.value = res.data.user_reaction;
    }
  } catch (err) {
    console.error('Failed to react:', err);
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
};

const loadData = async (slug) => {
  isLoading.value = true;
  error.value = false;
  window.scrollTo(0, 0);
  isLiked.value = false;
  
  try {
    const response = await publicApi.blogPost(slug);
    post.value = response.data.data;
    userReaction.value = post.value.user_reaction || null;
    
    // Fetch all posts for sidebar and filter out current
    const allPostsRes = await publicApi.blog();
    recentPosts.value = allPostsRes.data.data
      .filter(p => p.slug !== slug)
      .slice(0, 4); // Get top 4 recent

    isLoading.value = false;
    setTimeout(() => {
      initScrollReveal();
    }, 100);
  } catch (err) {
    console.error('Failed to load blog post:', err);
    error.value = true;
    isLoading.value = false;
  }
};

onMounted(() => {
  loadData(route.params.slug);
});

// Watch for route changes (e.g. clicking a recent post in the sidebar)
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
  color: #D4AF37;
  margin-top: 2.5rem;
  margin-bottom: 1rem;
  line-height: 1.2;
}

.blog-content :deep(h2) {
  font-size: 2rem;
}

.blog-content :deep(h3) {
  font-size: 1.5rem;
}

.blog-content :deep(p) {
  margin-bottom: 1.5rem;
  line-height: 1.8;
  font-size: 1.125rem;
}

.blog-content :deep(img) {
  max-width: 100%;
  height: auto;
  border-radius: 1.5rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 10px 10px -5px rgba(0, 0, 0, 0.3);
  margin: 2rem 0;
}

.blog-content :deep(a) {
  color: #F8E5A1;
  text-decoration: underline;
  text-underline-offset: 4px;
  transition: color 0.3s;
}

.blog-content :deep(a:hover) {
  color: #D4AF37;
}

.blog-content :deep(ul),
.blog-content :deep(ol) {
  margin-bottom: 1.5rem;
  padding-left: 1.5rem;
}

.blog-content :deep(ul) {
  list-style-type: disc;
}

.blog-content :deep(ol) {
  list-style-type: decimal;
}

.blog-content :deep(li) {
  margin-bottom: 0.5rem;
  line-height: 1.8;
  font-size: 1.125rem;
}
</style>
