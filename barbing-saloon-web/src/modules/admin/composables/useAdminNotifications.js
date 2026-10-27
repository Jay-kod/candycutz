import { ref, computed, onMounted } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useAdminNotifications() {
  const toast = useToast();

  const notifications = ref([]);
  const loading = ref(true);
  const error = ref(null);
  const activeTimeFilter = ref('all');
  const activeTypeFilter = ref('all');
  const search = ref('');
  const lastRefreshed = ref(null);

  const updateTimestamp = () => {
    const now = new Date();
    lastRefreshed.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  const fetchNotifications = async () => {
    loading.value = true;
    error.value = null;
    try {
      const res = await client.get('/notifications');
      notifications.value = res.data?.data || res.data || [];
      updateTimestamp();
    } catch (err) {
      console.error('Failed to load notifications:', err);
      error.value = 'Failed to load notifications from gateway';
      toast.error('Network error loading notifications');
    } finally {
      loading.value = false;
    }
  };

  const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.is_read).length;
  });

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
      await client.patch(`/notifications/${id}/read`);
      const n = notifications.value.find(item => item.id === id);
      if (n) n.is_read = true;
    } catch (err) {
      console.error('Failed to mark notification as read:', err);
      toast.error('Failed to update notification');
    }
  };

  const markAllRead = async () => {
    try {
      await client.patch('/notifications/read-all');
      notifications.value.forEach(n => { n.is_read = true; });
      toast.success('All notifications marked as read');
    } catch (err) {
      console.error('Failed to mark all notifications as read:', err);
      toast.error('Failed to mark all as read');
    }
  };

  const deleteNotification = async (id) => {
    try {
      await client.delete(`/notifications/${id}`);
      notifications.value = notifications.value.filter(n => n.id !== id);
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

  onMounted(() => {
    fetchNotifications();
  });

  return {
    notifications,
    loading,
    error,
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
    clearFilters
  };
}

