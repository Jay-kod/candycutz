<template>
  <AdminLayout>
    <section class="mx-auto max-w-7xl space-y-6 px-4 pb-16 pt-2 sm:px-6 lg:px-8">
      <div v-if="loading" class="animate-pulse space-y-6">
        <div class="h-44 rounded-3xl border border-white/[0.08] bg-[#12161C]"></div>
        <div class="grid gap-6 lg:grid-cols-12">
          <div class="h-[760px] rounded-3xl bg-[#12161C] lg:col-span-8"></div>
          <div class="h-[520px] rounded-3xl bg-[#12161C] lg:col-span-4"></div>
        </div>
      </div>

      <template v-else>
        <BlogEditorHeader :is-editing="isEditing" :is-published="form.is_published" :saving="saving" @save="savePost" />

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
          <form class="space-y-5 lg:col-span-8" @submit.prevent="savePost">
            <section class="space-y-4 rounded-3xl border border-white/[0.08] bg-[#12161C] p-5 shadow-2xl sm:p-7">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                  <h2 class="text-xs font-bold uppercase tracking-wider text-white">Featured cover image</h2>
                  <p class="mt-1 text-[11px] text-slate-400">Horizontal banner for the article and blog cards.</p>
                </div>
                <span v-if="previewImage" class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-emerald-400">
                  Image ready
                </span>
              </div>

              <div
                class="group relative flex min-h-52 items-center justify-center overflow-hidden rounded-2xl border-2 transition"
                :class="[
                  isDragging ? 'scale-[0.99] border-admin bg-admin/5' : 'border-dashed border-white/[0.15] bg-[#0B0F13] hover:border-admin/50',
                  previewImage ? 'border-solid' : '',
                ]"
                @dragover="onDragOver"
                @dragleave="onDragLeave"
                @drop="onDrop"
              >
                <input
                  type="file"
                  accept="image/*"
                  aria-label="Upload featured cover image"
                  class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
                  @change="onFileSelected"
                />
                <img v-if="previewImage" :src="previewImage" alt="Cover preview" class="absolute inset-0 h-full w-full object-cover" />
                <div v-if="previewImage" class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 transition group-hover:opacity-100">
                  <span class="rounded-xl border border-white/10 bg-black/80 px-4 py-2 text-xs font-semibold text-white">
                    <PhotoIcon class="mr-2 inline h-4 w-4 text-admin-light" />Replace cover image
                  </span>
                </div>
                <div v-else class="pointer-events-none px-6 py-10 text-center text-slate-400 transition group-hover:text-admin-light">
                  <PhotoIcon class="mx-auto mb-3 h-8 w-8" />
                  <p class="text-xs font-semibold text-white">Click or drag a cover image here</p>
                  <p class="mt-1 text-[10px] text-slate-500">Recommended 1200 x 630px · JPG, PNG, or WebP · Max 2MB</p>
                </div>
              </div>
            </section>

            <BlogTitleExcerptCard v-model:title="form.title" v-model:excerpt="form.excerpt" :preview-slug="previewSlug" />

            <section class="space-y-4 rounded-3xl border border-white/[0.08] bg-[#12161C] p-5 shadow-2xl sm:p-7">
              <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                  <label for="article-content" class="text-xs font-bold uppercase tracking-wider text-white">Article content <span class="text-admin-light">*</span></label>
                  <p class="mt-1 text-[11px] text-slate-400">Write the full article. Basic Markdown formatting is available below.</p>
                </div>
                <span class="text-xs text-slate-400">{{ wordCount }} words · {{ readingTime }}</span>
              </div>
              <div class="flex flex-wrap items-center gap-1.5 rounded-xl border border-white/[0.08] bg-[#0B0F13] p-2">
                <span class="px-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Format</span>
                <button v-for="format in formats" :key="format.type" type="button" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-slate-300 transition hover:bg-white/[0.08] hover:text-white" :title="format.title" @click="insertFormatting(format.type)">
                  {{ format.label }}
                </button>
              </div>
              <textarea
                id="article-content"
                ref="contentInput"
                v-model="form.content"
                rows="16"
                required
                class="w-full resize-y rounded-2xl border border-white/[0.08] bg-[#0B0F13] p-4 text-sm leading-relaxed text-slate-200 outline-none transition placeholder:text-slate-500 focus:border-admin/50 focus:ring-1 focus:ring-admin/30"
                placeholder="Write your article content here..."
              ></textarea>
            </section>

            <BlogPublishingCard
              v-model:author-display="form.author_display"
              v-model:is-published="form.is_published"
              :saving="saving"
              :is-editing="isEditing"
            />
          </form>

          <div class="space-y-5 lg:sticky lg:top-8 lg:col-span-4">
            <AdminBlogLivePreview
              :title="form.title"
              :excerpt="form.excerpt"
              :content="form.content"
              :preview-image="previewImage"
              :author-display="form.author_display"
              :is-published="form.is_published"
              :reading-time="readingTime"
              :formatted-date="formattedDate"
            />
            <button type="button" :disabled="saving" class="hidden w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-admin to-admin-light py-3.5 text-xs font-bold uppercase tracking-wider text-obsidian shadow-lg shadow-admin/20 transition hover:-translate-y-0.5 disabled:opacity-50 lg:flex" @click="savePost">
              <CheckIcon class="h-4 w-4" />
              {{ saving ? 'Saving...' : isEditing ? 'Update article' : 'Publish article' }}
            </button>
          </div>
        </div>
      </template>
    </section>
  </AdminLayout>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { CheckIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { getStorageUrl } from '@/core/utils/url';
import AdminBlogLivePreview from '@/modules/admin/components/blog/AdminBlogLivePreview.vue';
import BlogEditorHeader from '@/modules/admin/components/blog/BlogEditorHeader.vue';
import BlogPublishingCard from '@/modules/admin/components/blog/BlogPublishingCard.vue';
import BlogTitleExcerptCard from '@/modules/admin/components/blog/BlogTitleExcerptCard.vue';

const router = useRouter();
const route = useRoute();
const toast = useToast();
const isEditing = computed(() => Boolean(route.params.id));
const loading = ref(Boolean(route.params.id));
const saving = ref(false);
const previewImage = ref('');
const selectedFile = ref(null);
const isDragging = ref(false);
const contentInput = ref(null);

const form = reactive({
  title: '',
  excerpt: '',
  content: '',
  is_published: false,
  featured_image: '',
  author_display: '',
  created_at: null,
});

const formats = [
  { type: 'bold', label: 'B', title: 'Bold' },
  { type: 'italic', label: 'I', title: 'Italic' },
  { type: 'h2', label: 'H2', title: 'Heading 2' },
  { type: 'h3', label: 'H3', title: 'Heading 3' },
  { type: 'bullet', label: '• List', title: 'Bullet list' },
  { type: 'number', label: '1. Steps', title: 'Numbered list' },
  { type: 'quote', label: '“ Quote', title: 'Quote' },
  { type: 'divider', label: '― Divider', title: 'Divider' },
];

const wordCount = computed(() => form.content.trim().split(/\s+/).filter(Boolean).length);
const readingTime = computed(() => `${Math.max(1, Math.ceil(wordCount.value / 200))} min read`);
const previewSlug = computed(() => form.title.trim().toLowerCase().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-') || 'article-title');
const formattedDate = computed(() => new Date(form.created_at || Date.now()).toLocaleDateString('en-US', {
  month: 'short',
  day: 'numeric',
  year: 'numeric',
}));

function getImageUrl(path) {
  return path?.startsWith('http') ? path : getStorageUrl(path);
}

function selectFile(file) {
  if (!file) return;
  selectedFile.value = file;
  previewImage.value = URL.createObjectURL(file);
}

function onFileSelected(event) {
  selectFile(event.target.files?.[0]);
}

function onDragOver(event) {
  event.preventDefault();
  isDragging.value = true;
}

function onDragLeave(event) {
  event.preventDefault();
  isDragging.value = false;
}

function onDrop(event) {
  event.preventDefault();
  isDragging.value = false;
  selectFile(event.dataTransfer?.files?.[0]);
}

function insertFormatting(type) {
  const textarea = contentInput.value;
  if (!textarea) return;
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const selected = form.content.slice(start, end);
  const formatsByType = {
    bold: ['**', '**', 'bold text'],
    italic: ['*', '*', 'italic text'],
    h2: ['\n## ', '\n', 'Section heading'],
    h3: ['\n### ', '\n', 'Subheading'],
    bullet: ['\n- ', '', 'List item'],
    number: ['\n1. ', '', 'Step description'],
    quote: ['\n> ', '\n', 'Quote'],
    divider: ['\n\n---\n\n', '', ''],
  };
  const [before, after, fallback] = formatsByType[type] || [];
  if (before === undefined) return;
  const insertion = selected || fallback;
  form.content = `${form.content.slice(0, start)}${before}${insertion}${after}${form.content.slice(end)}`;
  requestAnimationFrame(() => {
    textarea.focus();
    const cursor = start + before.length + insertion.length;
    textarea.setSelectionRange(cursor, cursor);
  });
}

async function loadPost() {
  if (!isEditing.value) return;
  try {
    const res = await client.get('/admin/blog');
    const posts = Array.isArray(res.data?.data)
      ? res.data.data
      : (res.data?.data?.data || []);
    const post = posts.find((item) => String(item.id) === String(route.params.id));
    if (!post) {
      toast.error('Article not found');
      router.push('/admin/blog');
      return;
    }

    form.title = post.title || '';
    form.excerpt = post.excerpt || '';
    form.content = post.content || post.body || '';
    form.is_published = post.is_published == 1 || post.status === 'published';
    form.featured_image = post.featured_image || post.featured_image_url || '';
    form.author_display = post.author_display || post.author?.name || '';
    form.created_at = post.created_at || null;
    if (form.featured_image) previewImage.value = getImageUrl(form.featured_image);
  } catch (err) {
    console.error('[BlogEditor] Failed to load post:', err);
    toast.error('Failed to load article');
  } finally {
    loading.value = false;
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
    if (selectedFile.value) formData.append('featured_image', selectedFile.value);

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
    toast.error(err.response?.data?.message || err.response?.data?.error || 'Failed to save article');
  } finally {
    saving.value = false;
  }
}

onMounted(loadPost);
</script>
