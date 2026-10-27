<template>
  <AdminLayout>
    <section class="animate-fade-in max-w-4xl mx-auto space-y-8 pb-12">
      <!-- Top Navigation Header -->
      <div class="flex items-center justify-between">
        <button 
          @click="router.push('/admin/blog')" 
          class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white/70 hover:text-white hover:bg-white/10 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4" />
          <span>Back to Articles</span>
        </button>

        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border" :class="form.is_published ? 'bg-emerald-950/80 text-emerald-400 border-emerald-500/30' : 'bg-amber-950/80 text-amber-400 border-amber-500/30'">
          <span class="h-1.5 w-1.5 rounded-full" :class="form.is_published ? 'bg-emerald-400' : 'bg-amber-400 animate-pulse'"></span>
          {{ form.is_published ? 'Will Publish Live' : 'Saving as Draft' }}
        </span>
      </div>

      <!-- Editor Card -->
      <div class="relative overflow-hidden rounded-[2rem] border border-white/[0.08] bg-[#12161C] p-8 shadow-2xl backdrop-blur-2xl">
        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-admin/10 blur-[90px] pointer-events-none"></div>

        <div class="relative z-10 mb-8 border-b border-white/[0.06] pb-6">
          <h1 class="font-display text-2xl sm:text-3xl text-white tracking-tight">
            {{ isEditing ? 'Edit Article' : 'Compose New Article' }}
          </h1>
          <p class="mt-1 text-xs text-white/50">
            {{ isEditing ? 'Refine your article content, update images, and manage publication status.' : 'Write engaging saloon announcements, hair styling guides and care tips.' }}
          </p>
        </div>
        
        <form @submit.prevent="savePost" class="relative z-10 space-y-6">
          <!-- Featured Cover Image Upload -->
          <div class="space-y-2">
            <label class="text-[11px] font-bold uppercase tracking-wider text-white/50">Featured Cover Image</label>
            <div class="relative w-full h-64 rounded-2xl border-2 border-dashed border-white/10 bg-black/40 overflow-hidden group hover:border-admin/50 transition-colors cursor-pointer flex items-center justify-center">
              <input 
                type="file" 
                accept="image/*" 
                @change="onFileSelected" 
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
              />
              
              <template v-if="previewImage">
                <img :src="previewImage" alt="Preview" class="absolute inset-0 w-full h-full object-cover opacity-85 group-hover:opacity-100 transition-opacity" />
                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                  <span class="bg-black/80 text-white px-4 py-2 rounded-xl text-xs font-semibold backdrop-blur-md flex items-center gap-2 border border-white/10">
                    <PhotoIcon class="h-4 w-4 text-admin" /> Change Cover Image
                  </span>
                </div>
              </template>
              <template v-else>
                <div class="text-center text-white/40 group-hover:text-admin transition-colors pointer-events-none px-4">
                  <PhotoIcon class="h-10 w-10 mx-auto mb-2 opacity-60" />
                  <p class="text-xs font-semibold">Click or drag image to upload cover</p>
                  <p class="text-[10px] text-white/30 mt-1">Recommended: 1200x630px JPG, PNG, WebP (Max 2MB)</p>
                </div>
              </template>
            </div>
          </div>

          <!-- Title Input -->
          <div class="space-y-2">
            <label class="text-[11px] font-bold uppercase tracking-wider text-white/50">Article Title <span class="text-admin">*</span></label>
            <input 
              v-model="form.title" 
              type="text" 
              required 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm text-white placeholder:text-white/30 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15" 
              placeholder="e.g. Master the Perfect Beard Fade: Expert Barbering Guide" 
            />
          </div>

          <!-- Author Display -->
          <div class="space-y-2">
            <label class="text-[11px] font-bold uppercase tracking-wider text-white/50">Author / Source Display</label>
            <input 
              v-model="form.author_display" 
              type="text" 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm text-white placeholder:text-white/30 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15" 
              placeholder="Defaults to 'CandyCutz Team' or enter specific barber / stylist name" 
            />
          </div>

          <!-- Excerpt Summary -->
          <div class="space-y-2">
            <label class="text-[11px] font-bold uppercase tracking-wider text-white/50">Excerpt / Brief Summary</label>
            <textarea 
              v-model="form.excerpt" 
              rows="2" 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm text-white placeholder:text-white/30 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 resize-none" 
              placeholder="Short summary displayed on public blog cards and social previews..."
            ></textarea>
          </div>

          <!-- Article Content Body -->
          <div class="space-y-2">
            <label class="text-[11px] font-bold uppercase tracking-wider text-white/50">Article Content <span class="text-admin">*</span></label>
            <textarea 
              v-model="form.content" 
              rows="14" 
              required 
              class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm font-sans text-white/90 leading-relaxed placeholder:text-white/30 outline-none transition-all focus:border-admin/50 focus:bg-white/[0.06] focus:ring-2 focus:ring-admin/15 resize-y" 
              placeholder="Write your article content here. Paragraphs and formatting are preserved..."
            ></textarea>
          </div>

          <!-- Publication Checkbox -->
          <div class="flex items-center gap-3 p-4 rounded-xl border border-white/[0.08] bg-white/[0.02]">
            <input 
              v-model="form.is_published" 
              type="checkbox" 
              id="publish" 
              class="h-4 w-4 rounded accent-admin cursor-pointer" 
            />
            <label for="publish" class="text-xs text-white/80 cursor-pointer font-medium select-none">
              Publish immediately (visible to all clients on public website)
            </label>
          </div>

          <!-- Form Actions -->
          <div class="flex items-center gap-4 pt-4 border-t border-white/[0.08]">
            <button 
              type="button" 
              @click="router.push('/admin/blog')" 
              class="px-5 py-3 rounded-xl border border-white/10 bg-white/5 text-xs font-semibold text-white/70 hover:text-white hover:bg-white/10 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="saving" 
              class="flex-1 rounded-xl bg-gradient-to-r from-admin to-admin-light py-3 text-xs font-bold text-obsidian shadow-[0_4px_20px_rgba(255,103,0,0.25)] hover:shadow-[0_6px_28px_rgba(255,103,0,0.4)] hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 transition-all flex items-center justify-center gap-2"
            >
              <span v-if="saving" class="h-4 w-4 border-2 border-obsidian border-t-transparent rounded-full animate-spin"></span>
              <span>{{ saving ? 'Saving Article...' : (isEditing ? 'Update Article' : 'Publish Article') }}</span>
            </button>
          </div>
        </form>
      </div>
    </section>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { ArrowLeftIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import { getStorageUrl } from '@/core/utils/url';

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
  author_display: '',
});

function getImageUrl(path) {
  return getStorageUrl(path);
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
    const res = await client.get('/admin/blog');
    const posts = Array.isArray(res.data?.data) 
      ? res.data.data 
      : (res.data?.data?.data || []);
      
    const post = posts.find(p => String(p.id) === String(route.params.id));
    if (post) {
      form.title = post.title || '';
      form.excerpt = post.excerpt || '';
      form.content = post.content || post.body || '';
      form.is_published = post.is_published == 1 || post.status === 'published';
      form.featured_image = post.featured_image || post.featured_image_url || '';
      form.author_display = post.author_display || post.author?.name || '';
      if (form.featured_image) {
        previewImage.value = getImageUrl(form.featured_image);
      }
    } else {
      toast.error('Article not found');
      router.push('/admin/blog');
    }
  } catch (err) {
    console.error('[BlogEditor] Failed to load post:', err);
    toast.error('Failed to load article');
  }
}

async function savePost() {
  saving.value = true;
  try {
    const formData = new FormData();
    formData.append('title', form.title);
    formData.append('content', form.content);
    formData.append('excerpt', form.excerpt || '');
    formData.append('is_published', form.is_published ? '1' : '0');
    formData.append('status', form.is_published ? 'published' : 'draft');
    formData.append('author_display', form.author_display || '');
    
    if (form.featured_image && !selectedFile.value) {
      formData.append('featured_image', form.featured_image);
    }
    if (selectedFile.value) {
      formData.append('featured_image', selectedFile.value);
      formData.append('image', selectedFile.value);
    }

    if (isEditing.value) {
      await client.post(`/admin/blog/${route.params.id}`, formData);
      toast.success('Article updated successfully');
    } else {
      await client.post('/admin/blog', formData);
      toast.success('Article created successfully');
    }
    router.push('/admin/blog');
  } catch (err) {
    console.error('[BlogEditor] Failed to save post:', err);
    const errorMsg = err.response?.data?.message || err.response?.data?.error || 'Failed to save article';
    toast.error(errorMsg);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  loadPost();
});
</script>
