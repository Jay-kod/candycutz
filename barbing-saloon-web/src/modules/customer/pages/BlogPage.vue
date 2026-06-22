<template>
  <CustomerLayout>
    <section class="mx-auto max-w-7xl px-4 md:px-6 py-24 text-theme-text min-h-screen">
      <div data-reveal class="max-w-3xl mb-16">
        <p class="text-sm uppercase tracking-[0.3em] font-semibold text-gold-light">The Blog</p>
        <h1 class="mt-4 font-display text-4xl sm:text-5xl text-gold md:text-7xl">Style, Trends & Updates</h1>
        <p class="mt-6 text-xl text-theme-muted font-light leading-relaxed">Discover the latest in men's grooming, expert styling tips, and news from inside CandyCutz.</p>
      </div>

      <!-- Blog Skeleton -->
      <div v-if="isLoading" class="mt-16 space-y-12">
        <!-- Featured Skeleton -->
        <div class="overflow-hidden rounded-3xl border border-theme-border bg-theme-surface flex flex-col lg:flex-row shadow-xl">
          <div class="aspect-video lg:aspect-auto lg:w-2/3 skeleton-shimmer"></div>
          <div class="p-10 lg:w-1/3 flex flex-col justify-center gap-4">
            <div class="h-4 w-24 rounded skeleton-shimmer"></div>
            <div class="h-10 w-full rounded-lg skeleton-shimmer mt-2"></div>
            <div class="space-y-3 mt-4">
              <div class="h-4 w-full rounded skeleton-shimmer"></div>
              <div class="h-4 w-5/6 rounded skeleton-shimmer"></div>
            </div>
          </div>
        </div>
        <!-- Grid Skeletons -->
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
          <div v-for="n in 3" :key="'blog-skel-'+n" class="overflow-hidden rounded-3xl border border-theme-border bg-theme-surface flex flex-col">
            <div class="aspect-[4/3] w-full skeleton-shimmer"></div>
            <div class="p-8 flex-1 flex flex-col gap-4">
              <div class="h-4 w-24 rounded skeleton-shimmer"></div>
              <div class="h-8 w-full rounded-lg skeleton-shimmer"></div>
              <div class="h-4 w-full rounded skeleton-shimmer mt-2"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Blog Real -->
      <div v-else class="mt-16 space-y-12">
        
        <!-- Featured Post (First Post) -->
        <article v-if="posts.length > 0" data-reveal class="group overflow-hidden rounded-3xl border border-theme-border bg-theme-surface flex flex-col lg:flex-row shadow-xl hover:shadow-[0_0_40px_rgba(212,175,55,0.1)] transition-all duration-500 hover:-translate-y-1">
          <div class="relative lg:w-2/3 aspect-video lg:aspect-auto overflow-hidden bg-theme-bg">
            <img v-if="posts[0].featured_image" :src="posts[0].featured_image" :alt="posts[0].title" class="absolute inset-0 h-full w-full object-cover transition-transform duration-1000 group-hover:scale-105" />
            <div class="absolute inset-0 bg-gradient-to-t from-obsidian via-transparent to-transparent opacity-60"></div>
            <div class="absolute top-6 left-6 rounded-full bg-gold/90 backdrop-blur px-4 py-1 text-xs font-bold text-obsidian uppercase tracking-wider">Featured</div>
          </div>
          <div class="p-10 lg:w-1/3 flex flex-col justify-center">
            <div class="flex items-center gap-3 text-xs uppercase tracking-[0.2em] font-semibold text-gold-light mb-4">
              <span>{{ posts[0].author?.name || 'CandyCutz' }}</span>
              <span class="w-1 h-1 rounded-full bg-theme-border"></span>
              <span>{{ formatDate(posts[0].created_at) }}</span>
            </div>
            <h2 class="font-display text-4xl text-theme-text leading-tight group-hover:text-gold transition-colors">{{ posts[0].title }}</h2>
            <p class="mt-6 text-base leading-relaxed text-theme-muted line-clamp-3">{{ posts[0].excerpt }}</p>
            <div class="mt-10">
              <RouterLink :to="`/customer/dashboard/blog/${posts[0].slug}`" class="inline-flex items-center gap-2 text-sm font-bold text-gold hover:text-gold-light transition-colors">
                Read more
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:translate-x-1"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
              </RouterLink>
            </div>
          </div>
        </article>

        <!-- Regular Posts Grid -->
        <div v-if="posts.length > 1" class="grid gap-8 md:grid-cols-2 lg:grid-cols-3 pt-8 border-t border-theme-border">
          <article v-for="(post, index) in posts.slice(1)" :key="post.id" :data-reveal-delay="index * 100" data-reveal class="group overflow-hidden rounded-3xl border border-theme-border bg-theme-surface flex flex-col shadow-lg hover:shadow-[0_0_30px_rgba(212,175,55,0.05)] transition-all duration-500 hover:-translate-y-1">
            <div class="relative aspect-[4/3] w-full overflow-hidden bg-theme-bg">
              <img v-if="post.featured_image" :src="post.featured_image" :alt="post.title" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
            </div>
            <div class="p-8 flex-1 flex flex-col">
              <div class="flex items-center justify-between text-xs uppercase tracking-[0.15em] font-semibold text-gold-light mb-3">
                <span>{{ formatDate(post.created_at) }}</span>
              </div>
              <h3 class="font-display text-2xl text-theme-text leading-snug group-hover:text-gold transition-colors line-clamp-2">{{ post.title }}</h3>
              <p class="mt-4 text-sm leading-relaxed text-theme-muted line-clamp-3">{{ post.excerpt }}</p>
              <div class="mt-auto pt-8">
                <RouterLink :to="`/customer/dashboard/blog/${post.slug}`" class="inline-flex items-center gap-2 text-sm font-bold text-gold hover:text-gold-light transition-colors">
                  Read more
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 transition-transform group-hover:translate-x-1"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </RouterLink>
              </div>
            </div>
          </article>
        </div>
        
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import CustomerLayout from '../layouts/CustomerLayout.vue';
import { publicApi } from '../../public/api/public.api';
import { useScrollReveal } from '../../../core/composables/useScrollReveal';

const posts = ref([]);
const isLoading = ref(true);

const { init: initScrollReveal } = useScrollReveal();

const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
};

onMounted(async () => {
  try {
    const response = await publicApi.blog();
    posts.value = response.data.data;
    isLoading.value = false;
    setTimeout(() => {
      initScrollReveal();
    }, 100);
  } catch (error) {
    console.error('Failed to load blog posts:', error);
    isLoading.value = false;
  }
});
</script>