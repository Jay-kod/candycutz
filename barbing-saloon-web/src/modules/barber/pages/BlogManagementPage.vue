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
          <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
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

      <!-- Editor Modal -->
      <Teleport to="body">
        <div v-if="showEditor" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm p-4" @click.self="showEditor = false">
          <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-gold/20 bg-theme-surface p-8 shadow-2xl animate-fade-in custom-scrollbar">
            <h2 class="font-display text-2xl text-theme-text">{{ editingId ? 'Edit Post' : 'New Post' }}</h2>
            <form class="mt-6 space-y-5" @submit.prevent="savePost">
              <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Title</label>
                <input v-model="form.title" type="text" required class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50" placeholder="e.g. 5 Tips for the Perfect Fade" />
              </div>
              <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Excerpt</label>
                <textarea v-model="form.excerpt" rows="2" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50 resize-none" placeholder="Brief summary shown in listings..."></textarea>
              </div>
              <div class="space-y-2">
                <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Content</label>
                <textarea v-model="form.content" rows="12" required class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50 resize-none font-mono text-sm leading-relaxed" placeholder="Write your blog post content here. You can use basic HTML tags for formatting..."></textarea>
              </div>
              <div class="flex items-center gap-3">
                <input v-model="form.is_published" type="checkbox" id="publish" class="h-4 w-4 rounded accent-gold" />
                <label for="publish" class="text-sm text-ivory/70">Publish immediately</label>
              </div>
              <div class="flex gap-3 pt-2">
                <button type="submit" :disabled="saving" class="flex-1 rounded-xl bg-gradient-to-r from-gold to-gold-dark py-3 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] disabled:opacity-50">
                  {{ saving ? 'Saving...' : (editingId ? 'Update Post' : 'Create Post') }}
                </button>
                <button type="button" @click="showEditor = false" class="rounded-xl border border-theme-border px-6 py-3 text-sm font-medium text-theme-muted hover:bg-white/5 transition-colors">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </Teleport>
    </section>
  </BarberLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import { PlusIcon, PencilIcon, PencilSquareIcon, TrashIcon, EyeIcon, EyeSlashIcon, HeartIcon, HandThumbDownIcon } from '@heroicons/vue/24/outline';

const toast = useToast();
const { confirm } = useConfirm();
const loading = ref(true);
const saving = ref(false);
const showEditor = ref(false);
const editingId = ref(null);
const posts = ref([]);

const form = reactive({
  title: '',
  excerpt: '',
  content: '',
  is_published: false,
});

function resetForm() {
  form.title = '';
  form.excerpt = '';
  form.content = '';
  form.is_published = false;
  editingId.value = null;
}

function openEditor(post = null) {
  resetForm();
  if (post) {
    editingId.value = post.id;
    form.title = post.title;
    form.excerpt = post.excerpt || '';
    form.content = post.content;
    form.is_published = post.is_published == 1;
  }
  showEditor.value = true;
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

async function savePost() {
  saving.value = true;
  try {
    if (editingId.value) {
      await barberApi.updateBlogPost(editingId.value, { ...form });
      toast.success('Post updated');
    } else {
      await barberApi.createBlogPost({ ...form });
      toast.success('Post created');
    }
    showEditor.value = false;
    await fetchPosts();
  } catch (err) {
    toast.error('Failed to save post');
  } finally {
    saving.value = false;
  }
}

async function togglePublish(post) {
  try {
    const newStatus = post.is_published == 1 ? false : true;
    await barberApi.updateBlogPost(post.id, { title: post.title, content: post.content, excerpt: post.excerpt || '', is_published: newStatus });
    post.is_published = newStatus ? 1 : 0;
    toast.success(newStatus ? 'Post published' : 'Post unpublished');
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
