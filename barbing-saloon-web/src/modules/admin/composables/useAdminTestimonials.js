import { ref, computed } from 'vue';
import client from '@/shared/api/client';
import { useConfirm } from '@/core/composables/useConfirm';
import { useToast } from '@/core/composables/useToast';

export function useAdminTestimonials() {
  const { confirm } = useConfirm();
  const toast = useToast();

  const testimonials = ref([]);
  const services = ref([]);
  const isLoading = ref(true);
  const isRefreshing = ref(false);
  const loadError = ref(false);
  const moderatingIds = ref(new Set());

  // Search & Filters
  const search = ref('');
  const activeStatusFilter = ref('all'); // 'all', 'pending', 'approved', 'featured'
  const activeServiceFilter = ref('all');
  const activeRatingFilter = ref('all');
  const sortOrder = ref('newest'); // 'newest', 'oldest', 'rating-desc', 'rating-asc'

  const pendingCount = computed(() => {
    return testimonials.value.filter(t => !t.is_approved).length;
  });

  const approvedCount = computed(() => {
    return testimonials.value.filter(t => t.is_approved).length;
  });

  const featuredCount = computed(() => {
    return testimonials.value.filter(t => t.is_featured).length;
  });

  const stats = computed(() => {
    const list = testimonials.value;
    const totalCount = list.length;
    const pending = list.filter(t => !t.is_approved).length;
    const approved = list.filter(t => t.is_approved).length;
    const featured = list.filter(t => t.is_featured).length;

    let avgRating = 0;
    let fiveStarCount = 0;
    let highSatisfactionCount = 0;

    if (totalCount > 0) {
      const sum = list.reduce((acc, t) => acc + (Number(t.rating) || 5), 0);
      avgRating = Number((sum / totalCount).toFixed(1));
      fiveStarCount = list.filter(t => Number(t.rating) === 5).length;
      highSatisfactionCount = list.filter(t => Number(t.rating) >= 4).length;
    }

    const satisfactionPct = totalCount > 0 
      ? Math.round((highSatisfactionCount / totalCount) * 100) 
      : 100;

    return {
      totalCount,
      pendingCount: pending,
      approvedCount: approved,
      featuredCount: featured,
      avgRating,
      fiveStarCount,
      satisfactionPct
    };
  });

  const filteredTestimonials = computed(() => {
    let list = [...testimonials.value];

    // 1. Status Filter
    if (activeStatusFilter.value === 'pending') {
      list = list.filter(t => !t.is_approved);
    } else if (activeStatusFilter.value === 'approved') {
      list = list.filter(t => t.is_approved);
    } else if (activeStatusFilter.value === 'featured') {
      list = list.filter(t => t.is_featured);
    }

    // 2. Service Filter
    if (activeServiceFilter.value !== 'all') {
      list = list.filter(t => (
        String(t.service_id) === String(activeServiceFilter.value) || 
        String(t.service?.id) === String(activeServiceFilter.value)
      ));
    }

    // 3. Rating Filter
    if (activeRatingFilter.value !== 'all') {
      list = list.filter(t => Number(t.rating) === Number(activeRatingFilter.value));
    }

    // 4. Search Filter
    if (search.value.trim()) {
      const q = search.value.toLowerCase().trim();
      list = list.filter(t => {
        const clientMatch = (t.client_name || t.customer_name || '').toLowerCase().includes(q);
        const reviewMatch = (t.review || t.comment || '').toLowerCase().includes(q);
        const serviceMatch = (t.service?.name || t.service_name || '').toLowerCase().includes(q);
        return clientMatch || reviewMatch || serviceMatch;
      });
    }

    // 5. Sorting
    if (sortOrder.value === 'newest') {
      list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0) || (b.id - a.id));
    } else if (sortOrder.value === 'oldest') {
      list.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0) || (a.id - b.id));
    } else if (sortOrder.value === 'rating-desc') {
      list.sort((a, b) => (Number(b.rating) || 0) - (Number(a.rating) || 0));
    } else if (sortOrder.value === 'rating-asc') {
      list.sort((a, b) => (Number(a.rating) || 0) - (Number(b.rating) || 0));
    }

    return list;
  });

  const loadServices = async () => {
    try {
      const response = await client.get('/services');
      services.value = response.data?.data || response.data || [];
    } catch (err) {
      console.error('Failed to load services for filter', err);
    }
  };

  const loadTestimonials = async (silent = false) => {
    if (silent) {
      isRefreshing.value = true;
    } else {
      isLoading.value = true;
    }
    loadError.value = false;

    try {
      const response = await client.get('/admin/testimonials');
      const payload = response.data?.data;
      const items = Array.isArray(payload)
        ? payload
        : Array.isArray(payload?.data)
          ? payload.data
          : [];

      testimonials.value = items;
    } catch (error) {
      console.error('Failed to load testimonials', error);
      loadError.value = true;
      toast.error('Failed to load testimonials');
    } finally {
      isLoading.value = false;
      isRefreshing.value = false;
    }
  };

  const toggleApproval = async (item) => {
    const isApproved = !item.is_approved;
    moderatingIds.value = new Set(moderatingIds.value).add(item.id);

    try {
      const response = await client.patch(`/admin/testimonials/${item.id}/approve`, {
        is_approved: isApproved
      });
      item.is_approved = response.data?.data?.is_approved ?? isApproved;
      if (!isApproved) {
        item.is_featured = false;
      }
      toast.success(isApproved ? 'Testimonial approved' : 'Testimonial unapproved and hidden from public view');
    } catch (error) {
      console.error('Failed to update testimonial approval', error);
      toast.error(isApproved ? 'Failed to approve testimonial' : 'Failed to unapprove testimonial');
    } finally {
      const nextIds = new Set(moderatingIds.value);
      nextIds.delete(item.id);
      moderatingIds.value = nextIds;
    }
  };

  const toggleWebsiteVisibility = async (item) => {
    if (!item.is_approved) {
      toast.error('Approve this testimonial before displaying it on the website');
      return;
    }

    const isFeatured = !item.is_featured;
    moderatingIds.value = new Set(moderatingIds.value).add(item.id);

    try {
      const response = await client.patch(`/admin/testimonials/${item.id}/feature`, {
        is_featured: isFeatured
      });
      item.is_featured = response.data?.data?.is_featured ?? isFeatured;
      toast.success(isFeatured ? 'Testimonial is now featured on the homepage ribbon' : 'Testimonial removed from homepage ribbon');
    } catch (error) {
      console.error('Failed to update testimonial website visibility', error);
      toast.error(isFeatured ? 'Failed to feature testimonial' : 'Failed to hide testimonial');
    } finally {
      const nextIds = new Set(moderatingIds.value);
      nextIds.delete(item.id);
      moderatingIds.value = nextIds;
    }
  };

  const deleteReview = async (id) => {
    const ok = await confirm({
      title: 'Delete Testimonial',
      message: 'Are you sure you want to delete this testimonial? This action cannot be undone.',
      confirmText: 'Delete',
      type: 'danger'
    });

    if (!ok) return;

    try {
      await client.delete(`/admin/testimonials/${id}`);
      testimonials.value = testimonials.value.filter(t => t.id !== id);
      toast.success('Testimonial deleted successfully');
    } catch (error) {
      console.error('Failed to delete testimonial', error);
      toast.error('Failed to delete testimonial');
    }
  };

  const resetFilters = () => {
    search.value = '';
    activeStatusFilter.value = 'all';
    activeServiceFilter.value = 'all';
    activeRatingFilter.value = 'all';
    sortOrder.value = 'newest';
  };

  return {
    testimonials,
    services,
    isLoading,
    isRefreshing,
    loadError,
    moderatingIds,
    search,
    activeStatusFilter,
    activeServiceFilter,
    activeRatingFilter,
    sortOrder,
    pendingCount,
    approvedCount,
    featuredCount,
    stats,
    filteredTestimonials,
    loadServices,
    loadTestimonials,
    toggleApproval,
    toggleWebsiteVisibility,
    deleteReview,
    resetFilters
  };
}
