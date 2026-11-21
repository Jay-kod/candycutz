import { ref, reactive, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { getStorageUrl } from '@/core/utils/url';
import { getApiCollection } from '@/shared/api/getApiCollection';

export function useBarberBlogEditor() {
  const route = useRoute();
  const router = useRouter();
  const toast = useToast();

  const isEditing = computed(() => Boolean(route.params.id));
  const loading = ref(false);
  const saving = ref(false);
  const previewImage = ref('');
  const selectedFile = ref(null);
  const isDragging = ref(false);
  const textareaRef = ref(null);

  const form = reactive({
    title: '',
    excerpt: '',
    content: '',
    author_display: '',
    is_published: false,
    featured_image: '',
    slug: '',
    created_at: null,
  });

  const wordCount = computed(() => {
    const text = form.content?.trim();
    if (!text) return 0;
    return text.split(/\s+/).filter(Boolean).length;
  });

  const charCount = computed(() => {
    return form.content?.length || 0;
  });

  const readingTime = computed(() => {
    const words = wordCount.value;
    const minutes = Math.max(1, Math.ceil(words / 200));
    return `${minutes} min read`;
  });

  const computedSlug = computed(() => {
    if (form.slug) return form.slug;
    if (!form.title.trim()) return 'new-article-preview';
    return form.title
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  });

  const formattedDate = computed(() => {
    const dateToFormat = form.created_at ? new Date(form.created_at) : new Date();
    return dateToFormat.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  });

  function onFileSelected(event) {
    const file = event.target?.files?.[0];
    if (file) {
      handleFile(file);
    }
  }

  function handleFile(file) {
    if (!file.type.startsWith('image/')) {
      toast.error('Please select a valid image file (PNG, JPG, WebP)');
      return;
    }
    selectedFile.value = file;
    previewImage.value = URL.createObjectURL(file);
  }

  function removeImage() {
    selectedFile.value = null;
    previewImage.value = '';
    form.featured_image = '';
  }

  function onDragOver(e) {
    e.preventDefault();
    isDragging.value = true;
  }

  function onDragLeave(e) {
    e.preventDefault();
    isDragging.value = false;
  }

  function onDrop(e) {
    e.preventDefault();
    isDragging.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) {
      handleFile(file);
    }
  }

  function insertFormatting(type) {
    const textarea = textareaRef.value;
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const currentVal = form.content;
    const selectedText = currentVal.substring(start, end);

    let before = '';
    let after = '';
    let defaultText = '';

    switch (type) {
      case 'bold':
        before = '**';
        after = '**';
        defaultText = 'bold text';
        break;
      case 'italic':
        before = '*';
        after = '*';
        defaultText = 'italic text';
        break;
      case 'h2':
        before = '\n## ';
        after = '\n';
        defaultText = 'Section Heading';
        break;
      case 'h3':
        before = '\n### ';
        after = '\n';
        defaultText = 'Subheading';
        break;
      case 'bullet':
        before = '\n- ';
        after = '';
        defaultText = 'List item';
        break;
      case 'number':
        before = '\n1. ';
        after = '';
        defaultText = 'Step description';
        break;
      case 'quote':
        before = '\n> ';
        after = '\n';
        defaultText = 'Barber wisdom or client advice...';
        break;
      case 'tip':
        before = '\n> 💡 **Barber Pro Tip:** ';
        after = '\n';
        defaultText = 'Always blow-dry following the grain before applying pomade.';
        break;
      case 'divider':
        before = '\n\n---\n\n';
        after = '';
        defaultText = '';
        break;
      default:
        return;
    }

    const insertion = selectedText || defaultText;
    const newText =
      currentVal.substring(0, start) +
      before +
      insertion +
      after +
      currentVal.substring(end);

    form.content = newText;

    setTimeout(() => {
      textarea.focus();
      const newCursorPos = start + before.length + insertion.length;
      textarea.setSelectionRange(newCursorPos, newCursorPos);
    }, 0);
  }

  async function loadPost() {
    if (!isEditing.value) return;
    loading.value = true;

    try {
      let post = null;

      // Try fetching specific post first
      try {
        const directRes = await api.get(`/blog/${route.params.id}`);
        post = directRes.data?.data || directRes.data;
      } catch {
        // Fallback to barber blog list
        const res = await api.get('/barber/blog');
        const list = getApiCollection(res);
        post = list.find((p) => String(p.id) === String(route.params.id));
      }

      if (post) {
        form.title = post.title || '';
        form.excerpt = post.excerpt || '';
        form.content = post.content || post.body || '';
        form.author_display = post.author_display || post.author?.name || '';
        form.is_published = Number(post.is_published) === 1 || post.status === 'published';
        form.slug = post.slug || '';
        form.created_at = post.created_at || null;
        form.featured_image = post.featured_image || post.featured_image_url || '';

        if (form.featured_image) {
          previewImage.value = form.featured_image.startsWith('http')
            ? form.featured_image
            : getStorageUrl(form.featured_image);
        }
      } else {
        toast.error('Article not found');
        router.push('/barber/blog');
      }
    } catch (err) {
      console.error('[useBarberBlogEditor] Failed to load post:', err);
      toast.error('Failed to load article');
      router.push('/barber/blog');
    } finally {
      loading.value = false;
    }
  }

  async function savePost() {
    if (!form.title.trim()) {
      toast.error('Please enter an article title');
      return;
    }

    if (!form.content.trim()) {
      toast.error('Please write some content for the article body');
      return;
    }

    saving.value = true;
    try {
      const formData = new FormData();
      formData.append('title', form.title.trim());
      formData.append('content', form.content);
      formData.append('body', form.content);
      formData.append('excerpt', form.excerpt || '');
      formData.append('author_display', form.author_display || '');
      formData.append('is_published', form.is_published ? '1' : '0');
      formData.append('status', form.is_published ? 'published' : 'draft');

      if (selectedFile.value) {
        formData.append('image', selectedFile.value);
        formData.append('featured_image', selectedFile.value);
      }

      if (isEditing.value) {
        formData.append('_method', 'PUT');
        await api.post(`/barber/blog/${route.params.id}`, formData);
        toast.success('Article updated successfully');
      } else {
        await api.post('/barber/blog', formData);
        toast.success('Article published successfully');
      }
      router.push('/barber/blog');
    } catch (err) {
      console.error('[useBarberBlogEditor] Failed to save post:', err);
      const msg =
        err.response?.data?.message ||
        err.response?.data?.error ||
        'Failed to save article. Please check required fields.';
      toast.error(msg);
    } finally {
      saving.value = false;
    }
  }

  return {
    isEditing,
    loading,
    saving,
    previewImage,
    selectedFile,
    isDragging,
    textareaRef,
    form,
    wordCount,
    charCount,
    readingTime,
    computedSlug,
    formattedDate,
    onFileSelected,
    removeImage,
    onDragOver,
    onDragLeave,
    onDrop,
    insertFormatting,
    loadPost,
    savePost,
  };
}

