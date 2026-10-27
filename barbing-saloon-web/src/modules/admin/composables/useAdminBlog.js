import { ref, computed } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';
import { useConfirm } from '@/core/composables/useConfirm';

export function useAdminBlog() {
  const toast = useToast();
  const { confirm } = useConfirm();

  const posts = ref([]);
  const loading = ref(true);
  const isRefreshing = ref(false);
  const search = ref('');
  const statusFilter = ref('all'); // 'all', 'published', 'draft'
  const authorFilter = ref('all');
  const sortOrder = ref('newest'); // 'newest', 'oldest', 'title-asc', 'most-liked'

  // Extract unique authors for filter dropdown
  const authors = computed(() => {
    const set = new Set();
    posts.value.forEach(p => {
      const name = p.author_display || p.author?.name;
      if (name) set.add(name.trim());
    });
    return Array.from(set).sort();
  });

  // Calculate high-density KPIs
  const stats = computed(() => {
    const list = posts.value;
    const totalCount = list.length;
    const publishedCount = list.filter(p => p.is_published == 1 || p.status === 'published').length;
    const draftsCount = list.filter(p => !p.is_published || p.status === 'draft').length;
    const totalReactions = list.reduce((acc, p) => acc + (Number(p.loves_count) || 0), 0);
    const authorsCount = authors.value.length;
    const publishRate = totalCount > 0 ? Math.round((publishedCount / totalCount) * 100) : 0;

    return {
      totalCount,
      publishedCount,
      draftsCount,
      totalReactions,
      authorsCount,
      publishRate,
    };
  });

  // Filter and sort articles
  const filteredPosts = computed(() => {
    let list = [...posts.value];

    // Status filter
    if (statusFilter.value === 'published') {
      list = list.filter(p => p.is_published == 1 || p.status === 'published');
    } else if (statusFilter.value === 'draft') {
      list = list.filter(p => !p.is_published || p.status === 'draft');
    }

    // Author filter
    if (authorFilter.value !== 'all') {
      list = list.filter(p => {
        const name = p.author_display || p.author?.name;
        return name?.toLowerCase().trim() === authorFilter.value.toLowerCase().trim();
      });
    }

    // Search query
    if (search.value.trim()) {
      const q = search.value.toLowerCase().trim();
      list = list.filter(p => {
        const titleMatch = p.title?.toLowerCase().includes(q);
        const excerptMatch = p.excerpt?.toLowerCase().includes(q);
        const contentMatch = (p.content || p.body)?.toLowerCase().includes(q);
        const authorMatch = (p.author_display || p.author?.name)?.toLowerCase().includes(q);
        return titleMatch || excerptMatch || contentMatch || authorMatch;
      });
    }

    // Sort order
    if (sortOrder.value === 'newest') {
      list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0) || (b.id - a.id));
    } else if (sortOrder.value === 'oldest') {
      list.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0) || (a.id - b.id));
    } else if (sortOrder.value === 'title-asc') {
      list.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
    } else if (sortOrder.value === 'most-liked') {
      list.sort((a, b) => (Number(b.loves_count) || 0) - (Number(a.loves_count) || 0));
    }

    return list;
  });

  // Fetch posts from unified client
  const fetchPosts = async (silent = false) => {
    if (silent) {
      isRefreshing.value = true;
    } else {
      loading.value = true;
    }

    try {
      const res = await client.get('/admin/blog');
      const data = res.data?.data;
      if (Array.isArray(data)) {
        posts.value = data;
      } else if (data && Array.isArray(data.data)) {
        posts.value = data.data;
      } else {
        posts.value = [];
      }
    } catch (err) {
      console.error('[AdminBlog] Failed to fetch blog posts:', err);
      toast.error('Failed to load blog posts. Please check server connection.');
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  };

  // Toggle publish / unpublish
  const togglePublish = async (post) => {
    const isCurrentlyPublished = post.is_published == 1 || post.status === 'published';
    const nextPublished = !isCurrentlyPublished;

    try {
      const formData = new FormData();
      formData.append('title', post.title);
      formData.append('content', post.content || post.body || '');
      formData.append('excerpt', post.excerpt || '');
      formData.append('is_published', nextPublished ? '1' : '0');
      formData.append('status', nextPublished ? 'published' : 'draft');
      if (post.featured_image) {
        formData.append('featured_image', post.featured_image);
      }

      await client.post(`/admin/blog/${post.id}`, formData);

      // Optimistic local update
      post.is_published = nextPublished ? 1 : 0;
      post.status = nextPublished ? 'published' : 'draft';

      if (nextPublished) {
        toast.success(`"${post.title}" is now published live`);
      } else {
        toast.info(`"${post.title}" moved to draft queue`);
      }
    } catch (err) {
      console.error('[AdminBlog] Failed to update post status:', err);
      toast.error('Failed to update article status');
    }
  };

  // Delete article with confirm dialog
  const deletePost = async (id) => {
    const target = posts.value.find(p => p.id === id);
    const postTitle = target ? `"${target.title}"` : 'this article';

    const confirmed = await confirm({
      title: 'Delete Article',
      message: `Are you sure you want to delete ${postTitle}? This action cannot be reversed.`,
      confirmText: 'Delete Permanently',
      confirmVariant: 'danger',
    });

    if (!confirmed) return;

    try {
      await client.delete(`/admin/blog/${id}`);
      posts.value = posts.value.filter(p => p.id !== id);
      toast.success('Article deleted successfully');
    } catch (err) {
      console.error('[AdminBlog] Failed to delete article:', err);
      toast.error('Failed to delete blog post');
    }
  };

  // Reset all filters
  const resetFilters = () => {
    search.value = '';
    statusFilter.value = 'all';
    authorFilter.value = 'all';
    sortOrder.value = 'newest';
  };

  return {
    posts,
    loading,
    isRefreshing,
    search,
    statusFilter,
    authorFilter,
    sortOrder,
    authors,
    stats,
    filteredPosts,
    fetchPosts,
    togglePublish,
    deletePost,
    resetFilters,
  };
}
