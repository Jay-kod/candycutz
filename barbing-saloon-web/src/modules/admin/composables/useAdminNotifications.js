import { ref, computed, onMounted, getCurrentInstance } from 'vue';
import { useNotifications } from '@/core/composables/useNotifications';
import { useToast } from '@/core/composables/useToast';
import { client } from '@/shared/api/client';

export function useAdminNotifications() {
  const toast = useToast();
  const {
    notifications,
    unreadCount,
    isLoading: loading,
    fetchNotifications: baseFetchNotifications,
    markAsRead: baseMarkAsRead,
    markAllRead: baseMarkAllRead,
    deleteNotification: baseDeleteNotification,
  } = useNotifications();

  const error = ref(null);
  const activeTab = ref('stream'); // 'stream' | 'deliveries' | 'broadcast' | 'analytics'
  const activeTimeFilter = ref('all');
  const activeTypeFilter = ref('all');
  const search = ref('');
  const lastRefreshed = ref(null);

  const deliveries = ref([]);
  const deliveriesPagination = ref({ current_page: 1, last_page: 1, total: 0 });
  const loadingDeliveries = ref(false);
  const deliveryFilter = ref({ channel: 'all', status: 'all', search: '' });

  const broadcasts = ref([]);
  const loadingBroadcasts = ref(false);

  const analytics = ref(null);
  const loadingAnalytics = ref(false);

  const updateTimestamp = () => {
    const now = new Date();
    lastRefreshed.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  const fetchNotifications = async () => {
    error.value = null;
    try {
      await baseFetchNotifications();
      updateTimestamp();
    } catch (err) {
      console.error('Failed to load notifications:', err);
      error.value = 'Failed to load notifications from gateway';
      toast.error('Network error loading notifications');
    }
  };

  const stats = computed(() => {
    const total = notifications.value.length;
    const unread = unreadCount.value;

    const bookings = notifications.value.filter(n => {
      const t = (n.type || '').toLowerCase();
      const title = (n.title || '').toLowerCase();
      return t.includes('booking') || t.includes('appointment') || title.includes('booking') || title.includes('appointment');
    }).length;

    const alerts = notifications.value.filter(n => {
      const t = (n.type || '').toLowerCase();
      const title = (n.title || '').toLowerCase();
      return t.includes('payment') || t.includes('security') || t.includes('system') || title.includes('payment') || title.includes('alert');
    }).length;

    return {
      total,
      unread,
      bookings,
      alerts
    };
  });

  const filteredNotifications = computed(() => {
    let result = notifications.value;

    // Time filter
    if (activeTimeFilter.value !== 'all') {
      const now = new Date();
      const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      const startOfYesterday = new Date(startOfToday);
      startOfYesterday.setDate(startOfYesterday.getDate() - 1);

      result = result.filter(n => {
        const d = new Date(n.created_at);
        switch (activeTimeFilter.value) {
          case 'today': return d >= startOfToday;
          case 'yesterday': return d >= startOfYesterday && d < startOfToday;
          case 'week': {
            const startOfWeek = new Date(startOfToday);
            startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());
            return d >= startOfWeek;
          }
          case 'month': {
            const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
            return d >= startOfMonth;
          }
          default: return true;
        }
      });
    }

    // Type filter
    if (activeTypeFilter.value !== 'all') {
      result = result.filter(n => {
        const t = (n.type || '').toLowerCase();
        const title = (n.title || '').toLowerCase();
        if (activeTypeFilter.value === 'booking') {
          return t.includes('booking') || t.includes('appointment') || title.includes('booking');
        }
        if (activeTypeFilter.value === 'payment') {
          return t.includes('payment') || title.includes('payment');
        }
        if (activeTypeFilter.value === 'security') {
          return t.includes('security') || t.includes('auth') || title.includes('security');
        }
        if (activeTypeFilter.value === 'system') {
          return t.includes('system') || title.includes('system');
        }
        return t === activeTypeFilter.value;
      });
    }

    // Search query
    if (search.value.trim()) {
      const q = search.value.trim().toLowerCase();
      result = result.filter(n => {
        return (n.title || '').toLowerCase().includes(q) ||
          (n.message || '').toLowerCase().includes(q) ||
          (n.type || '').toLowerCase().includes(q);
      });
    }

    return result;
  });

  const groupedNotifications = computed(() => {
    const groups = [];
    let currentLabel = '';
    const now = new Date();
    const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const startOfYesterday = new Date(startOfToday);
    startOfYesterday.setDate(startOfYesterday.getDate() - 1);
    const startOfWeek = new Date(startOfToday);
    startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay());

    for (const n of filteredNotifications.value) {
      const d = new Date(n.created_at);
      let label = '';
      if (d >= startOfToday) {
        label = 'Today';
      } else if (d >= startOfYesterday) {
        label = 'Yesterday';
      } else if (d >= startOfWeek) {
        label = 'Earlier This Week';
      } else {
        label = d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
      }

      if (label !== currentLabel) {
        currentLabel = label;
        groups.push({ label, items: [] });
      }
      groups[groups.length - 1].items.push(n);
    }
    return groups;
  });

  const markAsRead = async (id) => {
    try {
      await baseMarkAsRead(id);
    } catch (err) {
      console.error('Failed to mark notification as read:', err);
      toast.error('Failed to update notification');
    }
  };

  const markAllRead = async () => {
    try {
      await baseMarkAllRead();
      toast.success('All notifications marked as read');
    } catch (err) {
      console.error('Failed to mark all notifications as read:', err);
      toast.error('Failed to mark all as read');
    }
  };

  const deleteNotification = async (id) => {
    try {
      await baseDeleteNotification(id);
      toast.success('Notification removed');
    } catch (err) {
      console.error('Failed to delete notification:', err);
      toast.error('Failed to remove notification');
    }
  };

  const clearFilters = () => {
    activeTimeFilter.value = 'all';
    activeTypeFilter.value = 'all';
    search.value = '';
  };

  const fetchDeliveries = async (page = 1) => {
    loadingDeliveries.value = true;
    try {
      const res = await client.get('/admin/notifications/deliveries', {
        params: {
          page,
          channel: deliveryFilter.value.channel !== 'all' ? deliveryFilter.value.channel : undefined,
          status: deliveryFilter.value.status !== 'all' ? deliveryFilter.value.status : undefined,
          search: deliveryFilter.value.search ? deliveryFilter.value.search : undefined,
        }
      });
      if (res.data?.success && res.data?.data) {
        deliveries.value = res.data.data.data || [];
        deliveriesPagination.value = {
          current_page: res.data.data.current_page || 1,
          last_page: res.data.data.last_page || 1,
          total: res.data.data.total || 0,
        };
      }
    } catch (err) {
      console.error('Failed to load deliveries:', err);
      toast.error('Failed to load delivery logs');
    } finally {
      loadingDeliveries.value = false;
    }
  };

  const fetchBroadcasts = async () => {
    loadingBroadcasts.value = true;
    try {
      const res = await client.get('/admin/notifications/broadcasts');
      if (res.data?.success && res.data?.data) {
        broadcasts.value = res.data.data;
      }
    } catch (err) {
      console.error('Failed to load broadcasts:', err);
    } finally {
      loadingBroadcasts.value = false;
    }
  };

  const sendBroadcast = async (payload) => {
    try {
      const res = await client.post('/admin/notifications/broadcasts', payload);
      if (res.data?.success) {
        toast.success('Broadcast campaign dispatched successfully');
        await fetchBroadcasts();
        await fetchNotifications();
        return true;
      }
    } catch (err) {
      console.error('Failed to send broadcast:', err);
      toast.error(err.response?.data?.message || 'Failed to dispatch broadcast');
      return false;
    }
  };

  const fetchAnalytics = async () => {
    loadingAnalytics.value = true;
    try {
      const res = await client.get('/admin/notifications/analytics');
      if (res.data?.success && res.data?.data) {
        analytics.value = res.data.data;
      }
    } catch (err) {
      console.error('Failed to load notification analytics:', err);
    } finally {
      loadingAnalytics.value = false;
    }
  };

  if (getCurrentInstance()) {
    onMounted(() => {
      fetchNotifications();
    });
  }

  return {
    notifications,
    loading,
    error,
    activeTab,
    activeTimeFilter,
    activeTypeFilter,
    search,
    lastRefreshed,
    unreadCount,
    stats,
    filteredNotifications,
    groupedNotifications,
    fetchNotifications,
    markAsRead,
    markAllRead,
    deleteNotification,
    clearFilters,
    deliveries,
    deliveriesPagination,
    loadingDeliveries,
    deliveryFilter,
    fetchDeliveries,
    broadcasts,
    loadingBroadcasts,
    fetchBroadcasts,
    sendBroadcast,
    analytics,
    loadingAnalytics,
    fetchAnalytics,
  };
}

