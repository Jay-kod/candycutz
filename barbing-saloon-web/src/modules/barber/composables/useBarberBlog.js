import { ref, computed } from 'vue';
import api from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';
import { getStorageUrl } from '@/core/utils/url';
import { getApiCollection } from '@/shared/api/getApiCollection';

export function useBarberBlog() {
  const toast = useToast();
  const { confirm } = useConfirm();

  const loading = ref(true);
  const saving = ref(false);
  const posts = ref([]);

  const searchQuery = ref('');
  const statusFilter = ref('all'); // 'all' | 'published' | 'draft'
  const sortOrder = ref('newest'); // 'newest' | 'oldest' | 'title-asc' | 'most-liked'
  const viewMode = ref('grid'); // 'grid' | 'list'

  function getImageUrl(path) {
    if (!path) return '';
    if (path.startsWith('http') || path.startsWith('blob:') || path.startsWith('data:')) return path;
    return getStorageUrl(path);
  }

  function formatDate(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  }

  function calculateReadTime(content) {
    if (!content) return '1 min read';
    const words = content.replace(/<[^>]*>?/gm, '').trim().split(/\s+/).filter(Boolean).length;
    const minutes = Math.max(1, Math.ceil(words / 200));
    return `${minutes} min read`;
  }

  const stats = computed(() => {
    const total = posts.value.length;
    const published = posts.value.filter(
      (p) => Number(p.is_published) === 1 || p.status === 'published'
    ).length;
    const drafts = total - published;
    const loves = posts.value.reduce((acc, p) => acc + (Number(p.loves_count) || 0), 0);
    const rate = total > 0 ? Math.round((published / total) * 100) : 0;

    return {
      totalCount: total,
      publishedCount: published,
      draftsCount: drafts,
      totalLoves: loves,
      publishRate: rate,
    };
  });

  const hasActiveFilters = computed(() => {
    return Boolean(
      searchQuery.value.trim() !== '' ||
      statusFilter.value !== 'all' ||
      sortOrder.value !== 'newest'
    );
  });

  function resetFilters() {
    searchQuery.value = '';
    statusFilter.value = 'all';
    sortOrder.value = 'newest';
  }

  const filteredPosts = computed(() => {
    let result = [...posts.value];

    // Status filter
    if (statusFilter.value === 'published') {
      result = result.filter(
        (p) => Number(p.is_published) === 1 || p.status === 'published'
      );
    } else if (statusFilter.value === 'draft') {
      result = result.filter(
        (p) => Number(p.is_published) !== 1 && p.status !== 'published'
      );
    }

    // Search query filter
    const q = searchQuery.value.trim().toLowerCase();
    if (q) {
      result = result.filter((p) => {
        const title = (p.title || '').toLowerCase();
        const excerpt = (p.excerpt || '').toLowerCase();
        const content = (p.content || p.body || '').toLowerCase();
        const author = (p.author_display || p.author?.name || '').toLowerCase();
        return title.includes(q) || excerpt.includes(q) || content.includes(q) || author.includes(q);
      });
    }

    // Sorting
    result.sort((a, b) => {
      if (sortOrder.value === 'newest') {
        return new Date(b.created_at || 0) - new Date(a.created_at || 0);
      }
      if (sortOrder.value === 'oldest') {
        return new Date(a.created_at || 0) - new Date(b.created_at || 0);
      }
      if (sortOrder.value === 'title-asc') {
        return (a.title || '').localeCompare(b.title || '');
      }
      if (sortOrder.value === 'most-liked') {
        return (Number(b.loves_count) || 0) - (Number(a.loves_count) || 0);
      }
      return 0;
    });

    return result;
  });

  async function fetchPosts() {
    loading.value = true;
    try {
      const res = await api.get('/barber/blog');
      posts.value = getApiCollection(res);
    } catch {
      toast.error('Failed to load blog posts');
      posts.value = [];
    } finally {
      loading.value = false;
    }
  }

  async function togglePublish(post) {
    const currentlyPublished = Number(post.is_published) === 1 || post.status === 'published';
    const newStatus = !currentlyPublished;
    saving.value = true;
    try {
      const response = await api.post(`/barber/blog/${post.id}`, {
        title: post.title,
        content: post.content || post.body || '',
        excerpt: post.excerpt || '',
        is_published: newStatus ? 1 : 0,
        status: newStatus ? 'published' : 'draft',
      });
      const updatedPost = response.data?.data;
      post.is_published = updatedPost?.is_published ?? (newStatus ? 1 : 0);
      post.status = updatedPost?.status ?? (newStatus ? 'published' : 'draft');
      toast.success(newStatus ? 'Article published live!' : 'Article moved to drafts');
    } catch {
      toast.error('Failed to update publication status');
    } finally {
      saving.value = false;
    }
  }

  async function deletePost(id) {
    const ok = await confirm({
      title: 'Delete Article',
      message: 'Are you sure you want to delete this blog post? This action cannot be undone.',
      confirmText: 'Delete',
    });
    if (!ok) return;

    try {
      await api.delete(`/barber/blog/${id}`);
      posts.value = posts.value.filter((p) => p.id !== id);
      toast.success('Article deleted successfully');
    } catch (error) {
      console.error('[BarberBlog] Failed to delete article:', error);
      toast.error('Failed to delete post');
    }
  }

  return {
    loading,
    saving,
    posts,
    filteredPosts,
    stats,
    searchQuery,
    statusFilter,
    sortOrder,
    viewMode,
    hasActiveFilters,
    resetFilters,
    getImageUrl,
    formatDate,
    calculateReadTime,
    fetchPosts,
    togglePublish,
    deletePost,
  };
}
