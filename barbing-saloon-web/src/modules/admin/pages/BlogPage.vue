<template>
  <AdminLayout>
    <section class="space-y-6 animate-fade-in">
      <!-- Header Banner -->
      <div class="relative overflow-hidden rounded-3xl border border-admin/20 bg-gradient-to-br from-obsidian via-charcoal to-[#2A1100] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-admin/5 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-48 w-48 rounded-full bg-admin/5 blur-3xl"></div>
        
        <div class="relative z-10">
          <p class="text-xs uppercase tracking-[0.3em] text-admin/70 font-bold">Blog</p>
          <h1 class="mt-2 font-display text-4xl text-theme-text drop-shadow-lg flex items-center gap-3">
            Content <span class="text-transparent bg-clip-text bg-gradient-to-r from-admin to-admin-light">Publishing</span>
            <span class="flex items-center justify-center h-8 px-3 rounded-full bg-admin/20 border border-admin/30 text-lg text-admin-light">{{ posts.length }}</span>
          </h1>
          <p class="mt-2 text-sm text-ivory/60">Create and manage blog posts for your saloon's website.</p>
        </div>
        
        <button @click="openEditor()" class="relative z-10 flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light px-6 py-3.5 text-sm font-bold text-obsidian transition-all hover:shadow-[0_4px_25px_rgba(255,103,0,0.35)] hover:scale-[1.02] active:scale-[0.98] shrink-0">
          <PencilSquareIcon class="h-5 w-5" />
          New Post
        </button>
      </div>

      <div class="grid gap-5 md:grid-cols-2">
        <article 
          v-for="post in posts" 
          :key="post.id" 
          class="group relative overflow-hidden rounded-2xl border border-white/5 bg-black/20 p-6 backdrop-blur-sm transition-all duration-300 hover:border-admin/30 hover:bg-black/40 hover:-translate-y-1 hover:shadow-[0_10px_30px_rgba(255,103,0,0.08)]"
        >
          <div class="absolute inset-0 bg-gradient-to-br from-admin/0 via-admin/[0.02] to-admin/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
          
          <div class="relative z-10 flex flex-col h-full">
            <div class="flex items-center justify-between gap-3 mb-4">
              <div class="flex items-center gap-2">
                <div class="h-2 w-2 rounded-full" :class="post.is_published ? 'bg-emerald-500' : 'bg-admin animate-pulse'"></div>
                <span class="text-[10px] font-bold uppercase tracking-widest" :class="post.is_published ? 'text-emerald-400' : 'text-admin'">
                  {{ post.is_published ? 'Published' : 'Draft' }}
                </span>
              </div>
              <span v-if="post.created_at" class="text-xs text-ivory/40 flex items-center gap-1">
                <CalendarIcon class="h-3.5 w-3.5" />
                {{ new Date(post.created_at).toLocaleDateString() }}
              </span>
            </div>
            
            <h2 class="font-display text-2xl text-theme-text mb-2 group-hover:text-admin-light transition-colors line-clamp-2">{{ post.title }}</h2>
            <p v-if="post.excerpt" class="text-sm text-ivory/50 line-clamp-3 mb-4 flex-1">{{ post.excerpt }}</p>
            
            <div class="mt-auto pt-4 flex items-center justify-between border-t border-white/5">
              <p v-if="post.author_name" class="text-xs text-ivory/40 flex items-center gap-1.5">
                <UserCircleIcon class="h-4 w-4" />
                {{ post.author_name }}
              </p>
              <div class="flex items-center gap-1">
                <button @click="togglePublish(post)" class="p-1.5 rounded-lg text-ivory/30 hover:text-blue-400 hover:bg-blue-500/10 transition-colors" :title="post.is_published ? 'Unpublish' : 'Publish'">
                  <EyeSlashIcon v-if="post.is_published" class="h-4 w-4" />
                  <EyeIcon v-else class="h-4 w-4" />
                </button>
                <button @click="openEditor(post)" class="p-1.5 rounded-lg text-ivory/30 hover:text-admin hover:bg-admin/10 transition-colors" title="Edit Post">
                  <PencilSquareIcon class="h-4 w-4" />
                </button>
                <button @click="deletePost(post.id)" class="p-1.5 rounded-lg text-ivory/30 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Delete Post">
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>
        </article>

        <div v-if="posts.length === 0" class="col-span-full flex flex-col items-center justify-center py-20 text-center border border-dashed border-white/10 rounded-3xl bg-theme-surface/30">
          <DocumentTextIcon class="h-12 w-12 text-ivory/20 mb-4" />
          <p class="text-ivory/60 font-medium">No blog posts found.</p>
          <p class="text-sm text-ivory/40 mt-1">Create your first blog post to engage with clients.</p>
        </div>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import AdminLayout from '../layouts/AdminLayout.vue';
import { adminApi } from '../api/admin.api';
import { useToast } from '../../../core/composables/useToast';
import { useConfirm } from '../../../core/composables/useConfirm';
import { PencilSquareIcon, CalendarIcon, UserCircleIcon, TrashIcon, DocumentTextIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const toast = useToast();
const { confirm } = useConfirm();
const posts = ref([]);

function openEditor(post = null) {
  if (post) {
    router.push(`/admin/blog/${post.id}/edit`);
  } else {
    router.push('/admin/blog/new');
  }
}

async function fetchPosts() {
  try {
    const response = await adminApi.blogPosts();
    posts.value = response.data.data;
  } catch (err) {
    console.error('Failed to fetch blog posts:', err);
    toast.error('Failed to fetch blog posts');
  }
}

async function togglePublish(post) {
  try {
    const newStatus = post.is_published ? false : true;
    
    const formData = new FormData();
    formData.append('title', post.title);
    formData.append('content', post.content);
    formData.append('excerpt', post.excerpt || '');
    formData.append('is_published', newStatus ? 1 : 0);
    if (post.featured_image) {
      formData.append('featured_image', post.featured_image);
    }
    
    await adminApi.updateBlogPost(post.id, formData);
    post.is_published = newStatus ? 1 : 0;
    if (newStatus) {
      toast.success('Post published');
    } else {
      toast.info('Post unpublished');
    }
  } catch (err) {
    toast.error('Failed to update post status');
  }
}

async function deletePost(id) {
  const ok = await confirm({ title: 'Delete Blog Post', message: 'Are you sure you want to delete this blog post? This action cannot be undone.', confirmText: 'Delete' });
  if (!ok) return;
  try {
    await adminApi.deleteBlogPost(id);
    posts.value = posts.value.filter(p => p.id !== id);
    toast.success('Post deleted');
  } catch (err) {
    toast.error('Failed to delete post');
  }
}

onMounted(() => {
  fetchPosts();
});
</script>