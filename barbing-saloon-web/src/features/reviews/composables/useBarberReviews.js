import { ref, computed } from 'vue';
import { client } from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useBarberReviews() {
  const toast = useToast();

  const reviews = ref([]);
  const services = ref([]);
  const isLoading = ref(true);
  const isRefreshing = ref(false);

  // Filters & Search
  const search = ref('');
  const activeStatusFilter = ref('all'); // 'all', 'approved', 'pending'
  const activeServiceFilter = ref('all'); // 'all' or service id
  const activeRatingFilter = ref('all'); // 'all', 5, 4, 3, 2, 1
  const sortOrder = ref('newest'); // 'newest', 'oldest', 'highest_rating', 'lowest_rating'

  const totalCount = computed(() => reviews.value.length);

  const approvedCount = computed(() => {
    return reviews.value.filter((r) => Boolean(r.is_approved)).length;
  });

  const pendingCount = computed(() => {
    return reviews.value.filter((r) => !r.is_approved).length;
  });

  const fiveStarCount = computed(() => {
    return reviews.value.filter((r) => Number(r.rating) === 5).length;
  });

  const averageRating = computed(() => {
    if (!reviews.value.length) return '—';
    const sum = reviews.value.reduce((acc, r) => acc + (Number(r.rating) || 5), 0);
    return (sum / reviews.value.length).toFixed(1);
  });

  const satisfactionRate = computed(() => {
    if (!reviews.value.length) return 100;
    const high = reviews.value.filter((r) => Number(r.rating) >= 4).length;
    return Math.round((high / reviews.value.length) * 100);
  });

  const stats = computed(() => ({
    totalCount: totalCount.value,
    approvedCount: approvedCount.value,
    pendingCount: pendingCount.value,
    fiveStarCount: fiveStarCount.value,
    averageRating: averageRating.value,
    satisfactionRate: satisfactionRate.value,
  }));

  const filteredTestimonials = computed(() => {
    let list = [...reviews.value];

    // Status filter
    if (activeStatusFilter.value === 'approved') {
      list = list.filter((r) => Boolean(r.is_approved));
    } else if (activeStatusFilter.value === 'pending') {
      list = list.filter((r) => !r.is_approved);
    }

    // Service filter
    if (activeServiceFilter.value !== 'all') {
      const targetId = Number(activeServiceFilter.value);
      list = list.filter((r) => {
        return r.service_id === targetId || r.service?.id === targetId;
      });
    }

    // Rating filter
    if (activeRatingFilter.value !== 'all') {
      const targetRating = Number(activeRatingFilter.value);
      list = list.filter((r) => Number(r.rating) === targetRating);
    }

    // Search query
    const q = search.value.trim().toLowerCase();
    if (q) {
      list = list.filter((r) => {
        const clientName = (r.customer_name || r.client_name || '').toLowerCase();
        const reviewText = (r.review || r.comment || '').toLowerCase();
        const serviceName = (r.service?.name || '').toLowerCase();
        return (
          clientName.includes(q) ||
          reviewText.includes(q) ||
          serviceName.includes(q)
        );
      });
    }

    // Sort order
    if (sortOrder.value === 'oldest') {
      list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    } else if (sortOrder.value === 'highest_rating') {
      list.sort((a, b) => (b.rating || 0) - (a.rating || 0) || new Date(b.created_at) - new Date(a.created_at));
    } else if (sortOrder.value === 'lowest_rating') {
      list.sort((a, b) => (a.rating || 0) - (b.rating || 0) || new Date(b.created_at) - new Date(a.created_at));
    } else {
      // Default: newest
      list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }

    return list;
  });

  const hasActiveFilters = computed(() => {
    return (
      search.value.trim() !== '' ||
      activeStatusFilter.value !== 'all' ||
      activeServiceFilter.value !== 'all' ||
      activeRatingFilter.value !== 'all' ||
      sortOrder.value !== 'newest'
    );
  });

  async function loadReviews(isRefresh = false) {
    if (isRefresh) {
      isRefreshing.value = true;
    } else {
      isLoading.value = true;
    }

    try {
      const response = await client.get('/barber/reviews');
      reviews.value = response.data?.data || response.data || [];
      if (isRefresh) {
        toast.success('Reviews refreshed');
      }
    } catch {
      toast.error('Failed to load reviews');
    } finally {
      isLoading.value = false;
      isRefreshing.value = false;
    }
  }

  async function loadServices() {
    try {
      const response = await client.get('/barber/services');
      services.value = response.data?.data || response.data || [];
    } catch {
      // Non-blocking fallback
      try {
        const fallback = await client.get('/services');
        services.value = fallback.data?.data || fallback.data || [];
      } catch {
        services.value = [];
      }
    }
  }

  function resetFilters() {
    search.value = '';
    activeStatusFilter.value = 'all';
    activeServiceFilter.value = 'all';
    activeRatingFilter.value = 'all';
    sortOrder.value = 'newest';
  }

  function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  }

  return {
    reviews,
    services,
    isLoading,
    isRefreshing,
    search,
    activeStatusFilter,
    activeServiceFilter,
    activeRatingFilter,
    sortOrder,
    totalCount,
    approvedCount,
    pendingCount,
    fiveStarCount,
    averageRating,
    satisfactionRate,
    stats,
    filteredTestimonials,
    hasActiveFilters,
    loadReviews,
    loadServices,
    resetFilters,
    formatDate,
  };
}

