<template>
  <BarberLayout>
    <section class="animate-fade-in max-w-4xl mx-auto space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <button @click="router.push('/barber/blog')" class="flex items-center gap-2 text-ivory/50 hover:text-gold transition-colors text-sm font-medium">
          <ArrowLeftIcon class="h-4 w-4" />
          Back to Blog
        </button>
      </div>

      <div class="rounded-2xl border border-gold/20 bg-theme-surface p-8 shadow-2xl">
        <h1 class="font-display text-3xl text-theme-text mb-2">{{ isEditing ? 'Edit Post' : 'New Post' }}</h1>
        <p class="text-sm text-ivory/50 mb-8">{{ isEditing ? 'Update your blog post details below.' : 'Create a new blog post for your audience.' }}</p>
        
        <form @submit.prevent="savePost" class="space-y-8">
          
          <!-- Image Upload Section -->
          <div class="space-y-3">
            <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Featured Image</label>
            <div class="relative w-full h-64 rounded-2xl border-2 border-dashed border-white/10 bg-black/30 overflow-hidden group hover:border-gold/50 transition-colors cursor-pointer flex items-center justify-center">
              <input type="file" accept="image/*" @change="onFileSelected" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
              
              <template v-if="previewImage">
                <img :src="previewImage" alt="Preview" class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity" />
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                  <span class="bg-black/80 text-white px-4 py-2 rounded-lg text-sm font-medium backdrop-blur-md flex items-center gap-2">
                    <PhotoIcon class="h-5 w-5" /> Change Image
                  </span>
                </div>
              </template>
              <template v-else>
                <div class="text-center text-ivory/40 group-hover:text-gold/80 transition-colors pointer-events-none">
                  <PhotoIcon class="h-12 w-12 mx-auto mb-3" />
                  <p class="text-sm font-medium">Click to upload featured image</p>
                </div>
              </template>
            </div>
          </div>

          <div class="space-y-3">
            <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Title</label>
            <input v-model="form.title" type="text" required class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50 focus:bg-black/50" placeholder="e.g. 5 Tips for the Perfect Fade" />
          </div>

          <div class="space-y-3">
            <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Excerpt</label>
            <textarea v-model="form.excerpt" rows="2" class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50 focus:bg-black/50 resize-none" placeholder="Brief summary shown in listings..."></textarea>
          </div>

          <div class="space-y-3">
            <label class="text-xs font-semibold uppercase tracking-widest text-ivory/50">Content</label>
            <textarea v-model="form.content" rows="15" required class="w-full rounded-xl border border-theme-border bg-theme-bg px-4 py-3 text-theme-text placeholder-theme-muted outline-none transition-colors focus:border-gold/50 focus:bg-black/50 resize-y font-mono text-sm leading-relaxed" placeholder="Write your blog post content here. You can use basic HTML tags for formatting..."></textarea>
          </div>

          <div class="flex items-center gap-3 p-4 rounded-xl border border-white/5 bg-white/5">
            <input v-model="form.is_published" type="checkbox" id="publish" class="h-5 w-5 rounded accent-gold cursor-pointer" />
            <label for="publish" class="text-sm text-ivory/80 cursor-pointer font-medium select-none">Publish immediately</label>
          </div>

          <div class="flex gap-4 pt-4 border-t border-white/10">
            <button type="submit" :disabled="saving" class="flex-1 rounded-xl bg-gradient-to-r from-gold to-gold-dark py-4 text-sm font-bold text-obsidian transition-all hover:shadow-[0_0_20px_rgba(212,175,55,0.3)] hover:scale-[1.01] disabled:opacity-50 disabled:hover:scale-100 flex items-center justify-center gap-2">
              <span v-if="saving" class="h-4 w-4 border-2 border-obsidian border-t-transparent rounded-full animate-spin"></span>
              {{ saving ? 'Saving...' : (isEditing ? 'Update Post' : 'Create Post') }}
            </button>
          </div>
        </form>
      </div>
    </section>
  </BarberLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import BarberLayout from '../layouts/BarberLayout.vue';
import { barberApi } from '../api/barber.api';
import { useToast } from '../../../core/composables/useToast';
import { ArrowLeftIcon, PhotoIcon } from '@heroicons/vue/24/outline';

const router = useRouter();
const route = useRoute();
const toast = useToast();

const isEditing = computed(() => !!route.params.id);
const saving = ref(false);
const previewImage = ref('');
const selectedFile = ref(null);

const form = reactive({
  title: '',
  excerpt: '',
  content: '',
  is_published: false,
  featured_image: '',
});

function getImageUrl(path) {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  if (path.startsWith('/images/')) return path;
  return `${import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '')}${path}`;
}

function onFileSelected(event) {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    previewImage.value = URL.createObjectURL(file);
  } else {
    selectedFile.value = null;
    previewImage.value = form.featured_image ? getImageUrl(form.featured_image) : '';
  }
}

async function loadPost() {
  if (!isEditing.value) return;
  
  try {
    const res = await barberApi.getBlogPosts();
    const post = res.data.data.find(p => p.id == route.params.id);
    if (post) {
      form.title = post.title;
      form.excerpt = post.excerpt || '';
      form.content = post.content;
      form.is_published = post.is_published == 1;
      form.featured_image = post.featured_image || '';
      if (form.featured_image) {
        previewImage.value = getImageUrl(form.featured_image);
      }
    } else {
      toast.error('Post not found');
      router.push('/barber/blog');
    }
  } catch (err) {
    toast.error('Failed to load post');
  }
}

async function savePost() {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('title', form.title);
    formData.append('content', form.content);
    formData.append('excerpt', form.excerpt);
    formData.append('is_published', form.is_published ? 1 : 0);
    
    if (form.featured_image && !selectedFile.value) {
      formData.append('featured_image', form.featured_image);
    }
    if (selectedFile.value) {
      formData.append('image', selectedFile.value);
    }

    if (isEditing.value) {
      await barberApi.updateBlogPost(route.params.id, formData);
      toast.success('Post updated');
    } else {
      await barberApi.createBlogPost(formData);
      toast.success('Post created');
    }
    router.push('/barber/blog');
  } catch (err) {
    const errorMsg = err.response?.data?.error || 'Failed to save post';
    toast.error(errorMsg);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  loadPost();
});
</script>
