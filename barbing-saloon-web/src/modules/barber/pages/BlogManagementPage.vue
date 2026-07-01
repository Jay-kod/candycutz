<template>
  <BarberLayout>
    <section class="space-y-8 animate-fade-in">
      <!-- Page Header -->
      <div class="relative overflow-hidden rounded-2xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-medium">Content</p>
            <h1 class="mt-2 font-display text-3xl lg:text-4xl text-theme-text">
              Your <span class="text-gold">Blog</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm text-ivory/50 leading-relaxed">
              Write articles and tips about grooming, styling, and haircare for your audience.
            </p>
          </div>
          <button
            @click="openEditor()"
            class="flex items-center gap-2 shrink-0 rounded-xl bg-gradient-to-r from-gold to-gold-dark px-5 py-3 text-sm font-bold text-obsidian shadow-[0_0_20px_rgba(212,175,55,0.25)] transition-all hover:shadow-[0_0_30px_rgba(212,175,55,0.4)] hover:scale-[1.02]"
          >
            <PlusIcon class="h-5 w-5" />
            New Post
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-20">
        <div class="h-8 w-8 animate-spin rounded-full border-4 border-gold border-t-transparent"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="posts.length === 0" class="rounded-2xl border border-theme-border bg-theme-surface/80 py-20 text-center">
        <PencilSquareIcon class="mx-auto h-12 w-12 text-ivory/20" />
        <h3 class="mt-4 font-display text-xl text-theme-text">No blog posts yet</h3>
        <p class="mt-2 text-sm text-ivory/50">Start writing to engage with your audience.</p>
      </div>

      <!-- Posts List -->
      <div v-else class="space-y-4">
        <div
          v-for="post in posts"
          :key="post.id"
          class="group rounded-2xl border border-theme-border bg-theme-surface/80 p-6 backdrop-blur-sm transition-all hover:border-gold/20 hover:shadow-[0_0_20px_rgba(212,175,55,0.06)]"
        >
          <div class="flex flex-col md:flex-row md:items-start gap-4">
            <div v-if="post.featured_image" class="w-full md:w-32 h-32 shrink-0 rounded-xl overflow-hidden border border-gold/10 relative group-hover:border-gold/30 transition-colors">
              <img :src="getImageUrl(post.featured_image)" :alt="post.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3">
                <span
                  class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                  :class="post.is_published == 1 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400'"
                >
                  <span class="h-1.5 w-1.5 rounded-full" :class="post.is_published == 1 ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                  {{ post.is_published == 1 ? 'Published' : 'Draft' }}
                </span>
                <span class="text-xs text-ivory/30">{{ formatDate(post.created_at) }}</span>
              </div>
              <h3 class="mt-3 font-display text-xl text-theme-text group-hover:text-gold transition-colors line-clamp-1">{{ post.title }}</h3>
              <p v-if="post.excerpt" class="mt-2 text-sm text-ivory/50 line-clamp-2">{{ post.excerpt }}</p>
              
              <div class="mt-4 flex items-center gap-4 text-xs font-medium">
                <div class="flex items-center gap-1.5 text-gold" title="Loves">
                  <HeartIcon class="h-4 w-4" />
                  <span>{{ post.loves_count || 0 }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-red-400" title="Dislikes">
                  <HandThumbDownIcon class="h-4 w-4" />
                  <span>{{ post.dislikes_count || 0 }}</span>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <button @click="openEditor(post)" class="rounded-lg p-2.5 text-ivory/40 hover:bg-gold/10 hover:text-gold transition-colors" title="Edit">
                <PencilIcon class="h-4 w-4" />
              </button>
              <button @click="togglePublish(post)" class="rounded-lg p-2.5 text-ivory/40 hover:bg-blue-500/10 hover:text-blue-400 transition-colors" :title="post.is_published == 1 ? 'Unpublish' : 'Publish'">
                <EyeIcon v-if="post.is_published != 1" class="h-4 w-4" />
                <EyeSlashIcon v-else class="h-4 w-4" />
              </button>
              <button @click="deletePost(post.id)" class="rounded-lg p-2.5 text-ivory/40 hover:bg-red-500/10 hover:text-red-400 transition-colors" title="Delete">
                <TrashIcon class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>


    </section>
  </BarberLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import { PlusIcon, PencilIcon, PencilSquareIcon, TrashIcon, EyeIcon, EyeSlashIcon, HeartIcon, HandThumbDownIcon, PhotoIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const posts = ref([]);

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  if (path.startsWith('/images/')) return path;
  return `${import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '')}${path}`;
}

function openEditor(post = null) {
  if (post) {
    router.push(`/barber/blog/${post.id}/edit`);
  } else {
    router.push('/barber/blog/new');
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

async function fetchPosts() {
  try {
    const res = await barberApi.getBlogPosts();
    posts.value = res.data.data || [];
  } catch (err) {
    toast.error('Failed to load blog posts');
  } finally {
    loading.value = false;
  }
}



async function togglePublish(post) {
  try {
    const newStatus = post.is_published == 1 ? false : true;
    await barberApi.updateBlogPost(post.id, { title: post.title, content: post.content, excerpt: post.excerpt || '', is_published: newStatus });
    post.is_published = newStatus ? 1 : 0;
    if (newStatus) {
      toast.success('Post published');
    } else {
      toast.info('Post unpublished');
    }
  } catch (err) {
    toast.error('Failed to update post');
  }
}

async function deletePost(id) {
  const ok = await confirm({ title: 'Delete Blog Post', message: 'Are you sure you want to delete this blog post? This action cannot be undone.', confirmText: 'Delete' });
  if (!ok) return;
  try {
    await barberApi.deleteBlogPost(id);
    posts.value = posts.value.filter(p => p.id !== id);
    toast.success('Post deleted');
  } catch (err) {
    toast.error('Failed to delete post');
  }
}

onMounted(fetchPosts);
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
}
</style>
