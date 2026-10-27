import { ref, computed } from 'vue';
import client from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

export function formatScheduleTime(time) {
  if (!time) return '';
  const [hours, minutes] = time.split(':');
  const h = parseInt(hours, 10);
  if (isNaN(h)) return time;
  const ampm = h >= 12 ? 'PM' : 'AM';
  const h12 = h % 12 || 12;
  return `${h12}:${minutes} ${ampm}`;
}

export function ensureAllSevenDays(hours) {
  const map = {};
  if (Array.isArray(hours)) {
    hours.forEach(h => { map[h.day_of_week] = h; });
  }
  const full = [];
  for (let d = 0; d < 7; d++) {
    full.push(map[d] || {
      id: null,
      day_of_week: d,
      open_time: '09:00',
      close_time: '18:00',
      is_closed: d === 0, // Sunday default closed
    });
  }
  return full;
}

export function useAdminWorkingHours() {
  const toast = useToast();

  const barbers = ref([]);
  const loading = ref(true);
  const isRefreshing = ref(false);
  const editingBarberId = ref(null);
  const savingBarberId = ref(null);
  const originalHoursSnapshot = ref(null);

  // Search & Filtering
  const search = ref('');
  const dayFilter = ref('all'); // 'all', 'today', 0..6
  const statusFilter = ref('all'); // 'all', 'open', 'closed'

  const todayIndex = new Date().getDay();
  const todayName = DAY_NAMES[todayIndex];

  // High-density KPI Metrics
  const stats = computed(() => {
    const list = barbers.value;
    const totalBarbers = list.length;

    // Working today count
    const workingTodayCount = list.filter(b => {
      const todayHour = b.hours?.find(h => Number(h.day_of_week) === todayIndex);
      return todayHour && !todayHour.is_closed;
    }).length;

    // Total weekly shifts (all open days across team)
    const totalWeeklyShifts = list.reduce((acc, b) => {
      const openCount = b.hours?.filter(h => !h.is_closed).length || 0;
      return acc + openCount;
    }, 0);

    const averageDaysPerBarber = totalBarbers > 0
      ? (totalWeeklyShifts / totalBarbers).toFixed(1)
      : '0.0';

    const coverageRate = totalBarbers > 0
      ? Math.round((workingTodayCount / totalBarbers) * 100)
      : 0;

    return {
      totalBarbers,
      workingTodayCount,
      totalWeeklyShifts,
      averageDaysPerBarber,
      coverageRate,
      todayName,
    };
  });

  // Filtered Barbers
  const filteredBarbers = computed(() => {
    let list = [...barbers.value];

    // Search query
    if (search.value.trim()) {
      const q = search.value.toLowerCase().trim();
      list = list.filter(b => b.barber_name?.toLowerCase().includes(q));
    }

    // Specific Day filter
    if (dayFilter.value !== 'all') {
      const targetDay = dayFilter.value === 'today' ? todayIndex : Number(dayFilter.value);
      list = list.filter(b => {
        const hour = b.hours?.find(h => Number(h.day_of_week) === targetDay);
        if (statusFilter.value === 'open') return hour && !hour.is_closed;
        if (statusFilter.value === 'closed') return !hour || hour.is_closed;
        return true;
      });
    } else if (statusFilter.value !== 'all') {
      // General status filter without a specific day
      list = list.filter(b => {
        const hasOpen = b.hours?.some(h => !h.is_closed);
        if (statusFilter.value === 'open') return hasOpen;
        if (statusFilter.value === 'closed') return !hasOpen;
        return true;
      });
    }

    return list;
  });

  // API: Fetch working hours
  const fetchWorkingHours = async (silent = false) => {
    if (silent) {
      isRefreshing.value = true;
    } else {
      loading.value = true;
    }

    try {
      const res = await client.get('/admin/working-hours');
      const rawData = res.data?.data;
      const list = Array.isArray(rawData) ? rawData : (rawData?.data || []);

      // Normalize all barbers to guarantee complete 7-day schedule objects
      barbers.value = list.map(b => ({
        ...b,
        hours: ensureAllSevenDays(b.hours),
      }));
    } catch (err) {
      console.error('[AdminWorkingHours] Failed to fetch working hours:', err);
      toast.error('Failed to load barber working schedules');
    } finally {
      loading.value = false;
      isRefreshing.value = false;
    }
  };

  // Edit actions
  const startEdit = (barber) => {
    barber.hours = ensureAllSevenDays(barber.hours);
    originalHoursSnapshot.value = JSON.parse(JSON.stringify(barber.hours));
    editingBarberId.value = barber.barber_id;
  };

  const cancelEdit = (barber) => {
    if (originalHoursSnapshot.value) {
      barber.hours = JSON.parse(JSON.stringify(originalHoursSnapshot.value));
    }
    editingBarberId.value = null;
    originalHoursSnapshot.value = null;
  };

  const applyDefaults = (barber) => {
    barber.hours = barber.hours.map(h => ({
      ...h,
      open_time: '09:00',
      close_time: '18:00',
      is_closed: Number(h.day_of_week) === 0, // Sunday closed
    }));
    toast.info('Applied standard saloon template (Mon–Sat 9AM–6PM, Sun Closed)');
  };

  const saveBarberHours = async (barber) => {
    savingBarberId.value = barber.barber_id;
    try {
      await client.put(`/admin/working-hours/${barber.barber_id}`, {
        hours: barber.hours,
      });
      toast.success(`${barber.barber_name}'s schedule updated successfully`);
      editingBarberId.value = null;
      originalHoursSnapshot.value = null;
    } catch (err) {
      console.error('[AdminWorkingHours] Failed to save hours:', err);
      const msg = err.response?.data?.message || err.response?.data?.error || 'Failed to save working hours';
      toast.error(msg);
    } finally {
      savingBarberId.value = null;
    }
  };

  const resetFilters = () => {
    search.value = '';
    dayFilter.value = 'all';
    statusFilter.value = 'all';
  };

  return {
    barbers,
    loading,
    isRefreshing,
    editingBarberId,
    savingBarberId,
    search,
    dayFilter,
    statusFilter,
    stats,
    filteredBarbers,
    todayIndex,
    todayName,
    fetchWorkingHours,
    startEdit,
    cancelEdit,
    applyDefaults,
    saveBarberHours,
    resetFilters,
  };
}
