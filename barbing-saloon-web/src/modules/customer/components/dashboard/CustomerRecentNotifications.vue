<template>
  <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-6 shadow-sm lg:col-span-2">
    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-[var(--color-border-subtle)]">
      <h2 class="font-display text-lg sm:text-xl text-[var(--color-text-primary)] flex items-center gap-2">
        <BellIcon class="w-5 h-5 text-[var(--color-accent)]" />
        <span>Recent <strong class="text-[var(--color-accent)] font-semibold">Activity & Alerts</strong></span>
      </h2>
      <div class="flex items-center gap-3">
        <button
          v-if="recentActivities.length > 0 && !activityReset"
          type="button"
          aria-label="Reset activity list for this view"
          title="Clear the list on this screen only. Reload to restore it."
          class="text-xs font-semibold text-[var(--color-text-secondary)] hover:text-[var(--color-accent)]"
          @click="activityReset = true"
        >
          Reset
        </button>
        <RouterLink to="/customer/dashboard/notifications" class="text-xs font-bold text-[var(--color-accent)] hover:underline">
          View notification inbox &rarr;
        </RouterLink>
      </div>
    </div>

    <div class="mt-5 space-y-3">
      <div
        v-for="activity in visibleActivities"
        :key="activity.key"
        class="flex gap-3.5 p-3.5 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 hover:bg-[var(--color-bg-tertiary)] transition-colors"
      >
        <div class="h-9 w-9 shrink-0 overflow-hidden rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 flex items-center justify-center text-[var(--color-accent)] font-display text-xs font-bold">
          {{ activity.initial }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center justify-between gap-2">
            <h3 class="font-semibold text-xs sm:text-sm text-[var(--color-text-primary)] truncate">
              {{ activity.title }}
            </h3>
            <span class="text-[10px] font-mono text-[var(--color-text-muted)] shrink-0">
              {{ activity.dateLabel }}
            </span>
          </div>
          <p class="mt-0.5 text-xs text-[var(--color-text-secondary)] leading-relaxed">
            {{ activity.message }}
          </p>
        </div>
      </div>
      
      <div v-if="visibleActivities.length === 0" class="rounded-xl border border-dashed border-[var(--color-border)] p-8 text-center bg-[var(--color-bg-tertiary)]/30">
        <BellIcon class="h-6 w-6 text-[var(--color-text-muted)] mx-auto mb-2 opacity-50" />
        <p v-if="activityReset" class="text-xs text-[var(--color-text-secondary)]">
          Activity cleared from this view. Reload the page to restore it.
        </p>
        <p v-else class="text-xs text-[var(--color-text-secondary)]">No recent activity or alerts yet.</p>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { BellIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  notifications: {
    type: Array,
    default: () => [],
  },
  appointments: {
    type: Array,
    default: () => [],
  },
  accountActivities: {
    type: Array,
    default: () => [],
  },
});

const activityReset = ref(false);

const recentActivities = computed(() => {
  const notifications = Array.isArray(props.notifications) ? props.notifications : [];
  const appointments = Array.isArray(props.appointments) ? props.appointments : [];
  const accountActivities = Array.isArray(props.accountActivities) ? props.accountActivities : [];
  const hasPasswordChangeNotification = notifications.some(notification => notification.type === 'password_changed');
  const notifiedAppointmentIds = new Set(
    notifications
      .filter(notification => (
        notification.related_entity_id != null &&
        (['booking', 'payment'].includes(notification.category) || /^(booking|payment)_/.test(notification.type || ''))
      ))
      .map(notification => String(notification.related_entity_id))
  );

  const notificationActivities = notifications.map(notification => ({
    key: `notification-${notification.id}`,
    title: notification.title || notification.data?.title || 'System Notification',
    message: notification.message || notification.data?.message || '',
    createdAt: notification.created_at || '',
    dateLabel: notification.created_at ? new Date(notification.created_at).toLocaleString() : '',
    initial: notification.sender_name?.charAt(0) || 'C',
  }));

  const appointmentActivities = appointments
    .filter(appointment => !notifiedAppointmentIds.has(String(appointment.id)))
    .map(appointment => {
      const status = typeof appointment.status === 'string'
        ? appointment.status
        : appointment.status?.value || 'updated';
      const titleByStatus = {
        pending: 'Booking reserved',
        confirmed: 'Booking confirmed',
        completed: 'Appointment completed',
        cancelled: 'Booking cancelled',
        no_show: 'No-show recorded',
      };
      const serviceName = appointment.service?.name || 'Appointment';
      const appointmentDate = appointment.appointment_date
        ? new Date(`${String(appointment.appointment_date).slice(0, 10)}T00:00:00`).toLocaleDateString()
        : '';
      const appointmentTime = appointment.appointment_time
        ? String(appointment.appointment_time).slice(0, 5)
        : '';

      return {
        key: `appointment-${appointment.id}`,
        title: titleByStatus[status] || `Booking ${status.replace(/_/g, ' ')}`,
        message: `${serviceName}${appointmentDate ? ` on ${appointmentDate}` : ''}${appointmentTime ? ` at ${appointmentTime}` : ''}.`,
        createdAt: appointment.updated_at || appointment.created_at || appointment.appointment_date || '',
        dateLabel: appointment.updated_at || appointment.created_at
          ? new Date(appointment.updated_at || appointment.created_at).toLocaleString()
          : '',
        initial: serviceName.charAt(0) || 'B',
      };
    });

  const accountActivityItems = accountActivities
    .filter(activity => !(
      hasPasswordChangeNotification &&
      ['auth.reset_password', 'auth.change_password'].includes(activity.action)
    ))
    .map(activity => {
      const action = String(activity.action || '');

      return {
        key: `account-${activity.id}`,
        title: activity.title || 'Account activity',
        message: activity.message || '',
        createdAt: activity.created_at || '',
        dateLabel: activity.created_at ? new Date(activity.created_at).toLocaleString() : '',
        initial: action.includes('login') ? 'L' : action.includes('password') ? 'P' : 'A',
      };
    });

  return [...notificationActivities, ...appointmentActivities, ...accountActivityItems]
    .sort((first, second) => Date.parse(second.createdAt) - Date.parse(first.createdAt))
    .slice(0, 5);
});

const visibleActivities = computed(() => (
  activityReset.value ? [] : recentActivities.value
));
</script>

