import { ref, reactive, computed } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useAdminFeedback() {
  const toast = useToast();

  const submissions = ref([]);
  const loading = ref(true);
  const isRefreshing = ref(false);
  const savingId = ref(null);
  const errorMessage = ref('');
  const page = ref(1);
  const lastPage = ref(1);
  const totalSubmissions = ref(0);

  // Filters & Search
  const search = ref('');
  const activeStatus = ref('all'); // 'all', 'new', 'reviewing', 'planned', 'completed', 'declined'
  const activeCategory = ref('all'); // 'all', 'feature_request', 'maintenance', 'improvement'
  const sortOrder = ref('newest'); // 'newest', 'oldest', 'name-asc'

  // Drafts for editing status and internal notes
  const drafts = reactive({});

  const statuses = [
    { value: 'new', label: 'New' },
    { value: 'reviewing', label: 'Reviewing' },
    { value: 'planned', label: 'Planned' },
    { value: 'completed', label: 'Completed' },
    { value: 'declined', label: 'Declined' }
  ];

  const categories = [
    { value: 'all', label: 'All Topics' },
    { value: 'feature_request', label: 'Feature Request' },
    { value: 'maintenance', label: 'Maintenance' },
    { value: 'improvement', label: 'Improvement' }
  ];

  const stats = computed(() => {
    const list = submissions.value;
    const totalCount = totalSubmissions.value || list.length;
    const newCount = list.filter(i => i.status === 'new').length;
    const inProgressCount = list.filter(i => i.status === 'reviewing' || i.status === 'planned').length;
    const completedCount = list.filter(i => i.status === 'completed').length;
    const maintenanceCount = list.filter(i => i.category === 'maintenance').length;
    const featureCount = list.filter(i => i.category === 'feature_request').length;

    return {
      totalCount,
      newCount,
      inProgressCount,
      completedCount,
      maintenanceCount,
      featureCount
    };
  });

  const filteredSubmissions = computed(() => {
    let list = [...submissions.value];

    // Status Filter (client-side in case not paginated strictly by status)
    if (activeStatus.value !== 'all') {
      list = list.filter(i => i.status === activeStatus.value);
    }

    // Category Filter
    if (activeCategory.value !== 'all') {
      list = list.filter(i => i.category === activeCategory.value);
    }

    // Search query
    if (search.value.trim()) {
      const q = search.value.toLowerCase().trim();
      list = list.filter(i => {
        const titleMatch = i.title?.toLowerCase().includes(q);
        const descMatch = i.description?.toLowerCase().includes(q);
        const userMatch = i.user?.name?.toLowerCase().includes(q) || i.user?.email?.toLowerCase().includes(q);
        const noteMatch = i.admin_note?.toLowerCase().includes(q);
        return titleMatch || descMatch || userMatch || noteMatch;
      });
    }

    // Sort order
    if (sortOrder.value === 'newest') {
      list.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0) || (b.id - a.id));
    } else if (sortOrder.value === 'oldest') {
      list.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0) || (a.id - b.id));
    } else if (sortOrder.value === 'name-asc') {
      list.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
    }

    return list;
  });

  const loadSubmissions = async (requestedPage = 1, silent = false) => {
    if (silent) {
      isRefreshing.value = true;
    } else {
      loading.value = true;
    }
    errorMessage.value = '';

    try {
      const params = { page: requestedPage };
      if (activeCategory.value !== 'all') params.category = activeCategory.value;
      if (activeStatus.value !== 'all') params.status = activeStatus.value;

      const response = await client.get('/admin/feedback', { params });
      const payload = response.data?.data || [];
      submissions.value = payload;
      page.value = response.data?.meta?.current_page || requestedPage;
      lastPage.value = response.data?.meta?.last_page || 1;
      totalSubmissions.value = response.data?.meta?.total || payload.length;

      // Populate draft buffers
      for (const item of submissions.value) {
        drafts[item.id] = {
          status: item.status,
          admin_note: item.admin_note || ''
        };
      }
    } catch (error) {
      console.error('Failed to load feedback submissions:', error);
      errorMessage.value = 'Feedback submissions could not be loaded. Please refresh and try again.';
      toast.error('Could not load feedback submissions');
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  };

  const saveSubmission = async (submission) => {
    savingId.value = submission.id;
    try {
      const draft = drafts[submission.id] || {};
      const response = await client.patch(`/admin/feedback/${submission.id}`, {
        status: draft.status,
        admin_note: draft.admin_note
      });

      const updated = response.data?.data;
      if (updated) {
        const index = submissions.value.findIndex(item => item.id === submission.id);
        if (index !== -1) {
          submissions.value[index] = {
            ...submissions.value[index],
            status: updated.status,
            admin_note: updated.admin_note,
            updated_at: updated.updated_at
          };
        }
        drafts[submission.id] = {
          status: updated.status,
          admin_note: updated.admin_note || ''
        };
      }
      toast.success('Feedback review saved successfully');
    } catch (error) {
      console.error(`Failed to update feedback submission ${submission.id}:`, error);
      toast.error('Could not save feedback review');
    } finally {
      savingId.value = null;
    }
  };

  const resetFilters = () => {
    search.value = '';
    activeStatus.value = 'all';
    activeCategory.value = 'all';
    sortOrder.value = 'newest';
    loadSubmissions(1);
  };

  return {
    submissions,
    loading,
    isRefreshing,
    savingId,
    errorMessage,
    page,
    lastPage,
    totalSubmissions,
    search,
    activeStatus,
    activeCategory,
    sortOrder,
    drafts,
    statuses,
    categories,
    stats,
    filteredSubmissions,
    loadSubmissions,
    saveSubmission,
    resetFilters
  };
}
