import { ref, onMounted } from 'vue';
import api from '@/shared/api/client';
import { useToast } from '@/core/composables/useToast';

export function useBarberSchedule() {
  const toast = useToast();

  const loading = ref(true);
  const isSaving = ref(false);
  const appointments = ref([]);
  const workingHours = ref([]);
  const originalHours = ref([]);

  const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

  function dayName(dayNum) {
    return dayNames[dayNum] || `Day ${dayNum}`;
  }

  function getMonth(dateStr) {
    if (!dateStr) return '';
    const [year, month, day] = dateStr.split('-').map(Number);
    const d = new Date(year, month - 1, day);
    return d.toLocaleString('en-US', { month: 'short' });
  }

  function getDay(dateStr) {
    if (!dateStr) return '';
    return Number(dateStr.split('-')[2]);
  }

  function formatTimeDisplay(timeStr) {
    if (!timeStr) return '';
    const parts = timeStr.split(':');
    const d = new Date();
    d.setHours(Number(parts[0]) || 0, Number(parts[1]) || 0, 0);
    return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
  }

  const getDefaultHours = () => {
    return Array.from({ length: 7 }, (_, i) => ({
      day_of_week: i,
      open_time: '09:00',
      close_time: '17:00',
      is_closed: i === 0 || i === 6,
    }));
  };

  async function loadData() {
    loading.value = true;
    try {
      const [scheduleResult, appointmentsResult] = await Promise.allSettled([
        api.get('/barber/schedule'),
        api.get('/barber/appointments', { params: { status: 'upcoming', per_page: 100 } }),
      ]);

      if (scheduleResult.status === 'fulfilled') {
        const scheduleData = scheduleResult.value.data?.data;
        const hours = Array.isArray(scheduleData) ? scheduleData : scheduleData?.working_hours || [];

        if (hours.length > 0) {
          const map = {};
          hours.forEach((hour) => {
            map[hour.day_of_week] = hour;
          });
          workingHours.value = Array.from({ length: 7 }, (_, day) => (
            map[day] || {
              id: null,
              day_of_week: day,
              open_time: '09:00',
              close_time: '17:00',
              is_closed: true,
            }
          ));
        } else {
          workingHours.value = getDefaultHours();
        }
        originalHours.value = JSON.parse(JSON.stringify(workingHours.value));
      } else {
        toast.error('Failed to load working hours');
        workingHours.value = getDefaultHours();
        originalHours.value = JSON.parse(JSON.stringify(workingHours.value));
      }

      if (appointmentsResult.status === 'fulfilled') {
        const response = appointmentsResult.value.data;
        const appointmentData = response?.data;
        const bookings = Array.isArray(appointmentData)
          ? appointmentData
          : appointmentData?.items || response?.items || [];
        appointments.value = [...bookings].sort((a, b) => {
          const aDateTime = `${a.appointment_date || ''}T${a.appointment_time || ''}`;
          const bDateTime = `${b.appointment_date || ''}T${b.appointment_time || ''}`;
          return aDateTime.localeCompare(bDateTime);
        });
      } else {
        toast.error('Failed to load upcoming bookings');
        appointments.value = [];
      }
    } finally {
      loading.value = false;
    }
  }

  async function saveSchedule() {
    for (const hour of workingHours.value) {
      if (!hour.is_closed && hour.open_time >= hour.close_time) {
        toast.error(`Invalid hours for ${dayName(hour.day_of_week)}: Open time must be earlier than close time.`);
        return;
      }
    }

    isSaving.value = true;
    try {
      await api.put('/barber/schedule', { working_hours: workingHours.value });
      toast.success('Roster schedule updated successfully');
      originalHours.value = JSON.parse(JSON.stringify(workingHours.value));
    } catch (e) {
      const msg = e.response?.data?.message || 'Failed to save schedule';
      toast.error(msg);
    } finally {
      isSaving.value = false;
    }
  }

  onMounted(() => {
    loadData();
  });

  return {
    loading,
    isSaving,
    appointments,
    workingHours,
    dayName,
    getMonth,
    getDay,
    formatTimeDisplay,
    loadData,
    saveSchedule,
  };
}
