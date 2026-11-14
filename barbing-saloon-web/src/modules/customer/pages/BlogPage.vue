<template>
  <CustomerLayout>
    <section class="space-y-8 animate-fade-in pb-12">
      <!-- Luxury Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-gradient-to-br from-amber-500/10 via-amber-400/5 to-white dark:from-[#12161C] dark:via-[#0E1217] dark:to-[#12161C] p-8 lg:p-10 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-[var(--color-accent)]/5 blur-[80px] pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-[60px] pointer-events-none"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-bold mb-2 flex items-center gap-2">
            <BookOpenIcon class="w-4 h-4 text-[var(--color-accent)]" />
            Editorial & Insights
          </p>
          <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl text-slate-900 dark:text-white drop-shadow-sm">
            Style, Trends & <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-amber-500 dark:to-amber-200">Grooming</span>
          </h1>
          <p class="mt-2 max-w-xl text-xs sm:text-sm text-slate-600 dark:text-[var(--color-text-secondary)] leading-relaxed">
            Discover expert haircut advice, beard maintenance routines, and styling news from the master barbers at CandyCutz.
          </p>
        </div>

        <div class="relative z-10 flex items-center gap-3">
          <RouterLink 
            to="/customer/dashboard/services"
            class="inline-flex items-center gap-2 rounded-xl bg-[var(--color-accent)] px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-[var(--color-accent)]/20 hover:brightness-110 active:scale-[0.98] transition-all"
          >
            <ScissorsIcon class="w-4 h-4" />
            Book a Stylist
          </RouterLink>
        </div>
      </div>

      <!-- Zero-CLS Skeleton State -->
      <div v-if="isLoading" class="space-y-8">
        <div class="h-80 rounded-3xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/5 animate-pulse"></div>
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <div v-for="n in 3" :key="'blog-skel-'+n" class="h-72 rounded-3xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/5 animate-pulse"></div>
        </div>
      </div>

      <!-- Blog Real Content -->
      <div v-else class="space-y-8">
        <!-- Featured Post (First Post) -->
        <article 
          v-if="posts.length > 0" 
          class="group overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] flex flex-col lg:flex-row shadow-xl hover:border-gold/40 transition-all duration-300"
        >
          <div class="relative lg:w-3/5 aspect-video lg:aspect-auto overflow-hidden bg-slate-100 dark:bg-white/[0.02]">
            <img 
              v-if="posts[0].featured_image" 
              :src="getImageUrl(posts[0].featured_image)" 
              :alt="posts[0].title" 
              class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" 
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 lg:from-transparent via-transparent to-transparent opacity-80"></div>
            <div class="absolute top-5 left-5 rounded-full bg-[var(--color-accent)]/90 backdrop-blur px-3.5 py-1 text-[10px] font-bold text-white uppercase tracking-wider shadow-md">
              Featured Article
            </div>
          </div>
          
          <div class="p-8 lg:p-10 lg:w-2/5 flex flex-col justify-center">
            <div class="flex items-center gap-3 text-xs uppercase tracking-wider font-semibold text-slate-500 dark:text-[var(--color-text-secondary)] mb-3">
              <span>{{ posts[0].author?.name || 'CandyCutz Editorial' }}</span>
              <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-white/20"></span>
              <span>{{ formatDate(posts[0].created_at) }}</span>
            </div>
            <h2 class="font-display text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white leading-tight group-hover:text-[var(--color-accent)] transition-colors">
              {{ posts[0].title }}
            </h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-[var(--color-text-secondary)] line-clamp-3">
              {{ posts[0].excerpt }}
            </p>
            <div class="mt-6">
              <RouterLink 
                :to="`/customer/dashboard/blog/${posts[0].slug}`" 
                class="inline-flex items-center gap-2 text-xs font-bold text-[var(--color-accent)] hover:underline"
              >
                <span>Read Full Story</span>
                <ChevronRightIcon class="w-4 h-4" />
              </RouterLink>
            </div>
          </div>
        </article>

        <!-- Regular Posts Grid -->
        <div v-if="posts.length > 1" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <article 
            v-for="post in posts.slice(1)" 
            :key="post.id" 
            class="group overflow-hidden rounded-3xl border border-slate-200 dark:border-white/[0.08] bg-white dark:bg-[#12161C] flex flex-col shadow-lg hover:border-gold/40 hover:-translate-y-1 transition-all duration-300"
          >
            <div class="relative aspect-[16/10] w-full overflow-hidden bg-slate-100 dark:bg-white/[0.02]">
              <img 
                v-if="post.featured_image" 
                :src="getImageUrl(post.featured_image)" 
                :alt="post.title" 
                class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" 
              />
              <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-70"></div>
            </div>
            
            <div class="p-6 flex-1 flex flex-col">
              <div class="flex items-center justify-between text-[11px] uppercase tracking-wider font-semibold text-slate-500 dark:text-[var(--color-text-secondary)] mb-2">
                <span>{{ formatDate(post.created_at) }}</span>
              </div>
              <h3 class="font-display text-lg font-bold text-slate-900 dark:text-white group-hover:text-[var(--color-accent)] transition-colors line-clamp-2">
                {{ post.title }}
              </h3>
              <p class="mt-2 text-xs leading-relaxed text-slate-600 dark:text-[var(--color-text-secondary)] line-clamp-3">
                {{ post.excerpt }}
              </p>
              <div class="mt-auto pt-5 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between">
                <RouterLink 
                  :to="`/customer/dashboard/blog/${post.slug}`" 
                  class="inline-flex items-center gap-1.5 text-xs font-bold text-[var(--color-accent)] hover:underline"
                >
                  <span>Read Article</span>
                  <ChevronRightIcon class="w-3.5 h-3.5" />
                </RouterLink>
              </div>
            </div>
          </article>
        </div>

        <!-- Empty State -->
        <div v-if="posts.length === 0" class="rounded-3xl border border-dashed border-slate-300 dark:border-white/10 p-16 text-center bg-slate-50/50 dark:bg-white/[0.02]">
          <BookOpenIcon class="w-12 h-12 text-slate-400 dark:text-white/30 mx-auto mb-3" />
          <h3 class="font-display text-xl text-slate-900 dark:text-white mb-1 font-bold">No Articles Published</h3>
          <p class="text-xs text-slate-600 dark:text-[var(--color-text-secondary)]">Check back soon for styling guides and grooming tips.</p>
        </div>
      </div>
    </section>
  </CustomerLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import CustomerLayout from '@/portals/customer/layouts/CustomerLayout.vue';
import { client } from '@/shared/api/client';
import { getApiCollection } from '@/shared/api/getApiCollection';
import { getStorageUrl as getImageUrl } from '@/core/utils/url';
import { BookOpenIcon, ScissorsIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

const posts = ref([]);
const isLoading = ref(true);

const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
};

onMounted(async () => {
  try {
    const response = await client.get('/blog');
    posts.value = getApiCollection(response);
  } catch (error) {
    console.error('Failed to load blog posts:', error);
  } finally {
    isLoading.value = false;
  }
});
</script>