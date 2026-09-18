import { ref, computed } from 'vue';
import { barberApi } from '@/shared/api/old_barberApi';
import { useToast } from '@/core/composables/useToast';

export function useBarberDashboard() {
  const dashboard = ref({ stats: {}, today_appointments: [], pending_payments: [] });
  const loading = ref(true);

  const progressWidth = computed(() => {
    const total = dashboard.value.stats?.today_bookings ?? 0;
    const completed = dashboard.value.stats?.completed_bookings ?? 0;
    if (total === 0) return 0;
    return Math.round((completed / total) * 100);
  });

  async function loadDashboard() {
    try {
      loading.value = true;
      const [dashRes, bookingsRes] = await Promise.allSettled([
        barberApi.dashboard(),
        barberApi.getBookings()
      ]);
      
      let stats = { today_bookings: 0, upcoming_bookings: 0, completed_bookings: 0, no_show_count: 0 };
      let todayAppointments = [];
      let pendingPayments = [];

      if (dashRes.status === 'fulfilled' && dashRes.value.data?.data) {
        const d = dashRes.value.data.data;
        stats = d.stats || stats;
        todayAppointments = (d.today_appointments || []).map(a => ({
          id: a.id,
          client_name: a.client_name || a.customer?.name || 'Client',
          appointment_time: a.appointment_time,
          status: a.status,
          service: a.service || { name: 'General' }
        }));
      }

      if (bookingsRes.status === 'fulfilled' && bookingsRes.value.data?.data) {
        const bookings = bookingsRes.value.data.data || [];
        pendingPayments = bookings.filter(b => b.status === 'pending' && b.payment_status === 'awaiting_verification');
        
        if (!todayAppointments.length && bookings.length) {
          const today = new Date().toISOString().split('T')[0];
          const todayBookings = bookings.filter(b => b.appointment_date === today);
          if (todayBookings.length) {
            todayAppointments = todayBookings.map(b => ({
              id: b.id,
              client_name: b.customer_name || b.client_name || 'Client',
              appointment_time: b.appointment_time,
              status: b.status,
              service: { name: b.service_name }
            }));
          }
        }
      }

      dashboard.value = {
        stats,
        today_appointments: todayAppointments,
        pending_payments: pendingPayments
      };
    } catch (e) {
      // Ignore
    } finally {
      loading.value = false;
    }
  }

  async function completeAppointment(id) {
    await barberApi.complete(id);
    await loadDashboard();
  }

  async function markNoShow(id) {
    await barberApi.noShow(id);
    await loadDashboard();
  }

  return {
    dashboard,
    loading,
    progressWidth,
    loadDashboard,
    completeAppointment,
    markNoShow
  };
}

export function useBarberStatus() {
  const toast = useToast();
  const myStatus = ref('active');

  async function loadMyStatus() {
    try {
      const res = await barberApi.profile();
      if (res.data?.data) {
        myStatus.value = res.data.data.status || 'active';
      }
    } catch (e) {
      // Ignore error
    }
  }

  function formatStatus(status) {
    switch (status) {
      case 'active': return 'Available';
      case 'on_leave': return 'Not Active';
      case 'pending_approval': return 'Pending';
      case 'suspended': return 'Suspended';
      default: return status;
    }
  }

  async function toggleMyStatus(status) {
    if (myStatus.value === 'suspended' || myStatus.value === 'pending_approval') {
      toast.error(`You cannot change your status while your account is ${formatStatus(myStatus.value)}`);
      return;
    }
    
    try {
      await barberApi.updateMyStatus({ status, is_available: status === 'active' });
      myStatus.value = status;
      toast.success(`Status updated to ${status === 'active' ? 'Active' : 'Not Active'}`);
    } catch (error) {
      toast.error(error.response?.data?.error || 'Failed to update status');
    }
  }

  return {
    myStatus,
    loadMyStatus,
    formatStatus,
    toggleMyStatus
  };
}

export function useBarberNotification() {
  const toast = useToast();
  const showNotificationModal = ref(false);
  const sendingNotification = ref(false);
  const notificationForm = ref({ title: '', message: '' });

  async function sendNotification() {
    if (!notificationForm.value.title || !notificationForm.value.message) {
      toast.error('Please enter a title and message');
      return;
    }
    
    sendingNotification.value = true;
    try {
      await barberApi.sendNotification(notificationForm.value);
      toast.success('Notification sent successfully!');
      showNotificationModal.value = false;
      notificationForm.value = { title: '', message: '' };
    } catch (error) {
      toast.error('Failed to send notification');
    } finally {
      sendingNotification.value = false;
    }
  }

  return {
    showNotificationModal,
    sendingNotification,
    notificationForm,
    sendNotification
  };
}
