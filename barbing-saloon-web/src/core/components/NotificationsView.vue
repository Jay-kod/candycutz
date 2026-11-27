<template>
  <div class="space-y-4 sm:space-y-5 animate-fade-in relative max-w-4xl mx-auto">
    <!-- Header & Balanced Controls -->
    <NotificationsHeader
      :total-count="notifications.length"
      :unread-count="unreadCount"
      :is-loading="isLoading"
      :is-refreshing="isRefreshing"
      :is-clearing="isClearing"
      :show-filters="showFilters"
      :show-settings="authStore.isCustomer"
      :has-active-filters="hasActiveFilters"
      :active-filter-count="activeFilterCount"
      :read-filter="readFilter"
      @update:read-filter="readFilter = $event"
      @refresh="refresh"
      @open-settings="router.push('/customer/dashboard/notification-settings')"
      @toggle-filters="showFilters = !showFilters"
      @mark-all-read="handleMarkAllRead"
      @clear-all="handleClearAll"
    />

    <!-- Expandable Search & Facet Filter Panel -->
    <NotificationFilterBar
      :show-filters="showFilters"
      :time-filters="timeFilters"
      :type-filters="typeFilters"
      :active-time-filter="activeTimeFilter"
      :active-type-filter="activeTypeFilter"
      :search-query="searchQuery"
      :type-counts="typeCounts"
      :hide-staff-filters="authStore.isCustomer"
      :has-active-filters="hasActiveFilters"
      :filtered-count="filteredNotifications.length"
      :total-count="notifications.length"
      @update:active-time-filter="activeTimeFilter = $event"
      @update:active-type-filter="activeTypeFilter = $event"
      @update:search-query="searchQuery = $event"
      @clear-filters="clearFilters"
    />

    <!-- Loading or Empty States -->
    <NotificationFeedStates
      v-if="feedStateMode"
      :mode="feedStateMode"
      :is-customer="authStore.isCustomer"
      @browse-services="router.push('/customer/dashboard/services')"
      @refresh="refresh"
      @show-all="readFilter = 'all'"
      @reset-filters="clearAllFilters"
    />

    <!-- Grouped Notification Cards -->
    <div v-else class="space-y-5">
      <section v-for="group in groupedNotifications" :key="group.label" class="space-y-2.5 sm:space-y-3">
        <!-- Group Header -->
        <div class="flex items-center gap-2.5 px-1">
          <span class="h-2 w-2 rounded-full bg-[var(--color-accent)]/70 shrink-0" aria-hidden="true"></span>
          <h2 class="text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--color-accent)]">
            {{ group.label }}
          </h2>
          <div class="flex-1 h-px bg-[var(--color-border)]"></div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--color-text-muted)] tabular-nums">
            {{ group.items.length }} {{ group.items.length === 1 ? 'notification' : 'notifications' }}
          </span>
        </div>

        <!-- Individual Notification Cards -->
        <div class="space-y-2.5 sm:space-y-3">
          <NotificationRow
            v-for="notif in group.items"
            :key="notif.id"
            :notif="notif"
            @read="handleMarkAsRead"
            @delete="handleDeleteNotification"
            @select="handleSelectNotification"
            @copy="handleCopy"
          />
        </div>
      </section>

      <!-- Progressive Load More -->
      <div v-if="remainingCount > 0" class="pt-1 flex justify-center">
        <button
          type="button"
          @click="visibleLimit += 20"
          class="min-h-[42px] px-5 py-2 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-secondary)] text-xs font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/40 hover:text-[var(--color-accent)] transition-colors tabular-nums"
        >
          Show older notifications ({{ remainingCount }} remaining)
        </button>
      </div>
    </div>

    <!-- Interactive Detail Bottom Sheet / Modal -->
    <NotificationDetailSheet
      :notif="selectedNotification"
      @close="selectedNotification = null"
      @delete="handleDeleteNotification"
      @copy="handleCopy"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../modules/auth/store/auth.store';
import { useNotifications } from '@/core/composables/useNotifications';
import { useConfirm } from '@/core/composables/useConfirm';
import { useToast } from '@/core/composables/useToast';
import NotificationsHeader from './notifications/NotificationsHeader.vue';
import NotificationFilterBar from './notifications/NotificationFilterBar.vue';
import NotificationFeedStates from './notifications/NotificationFeedStates.vue';
import NotificationRow from './notifications/NotificationRow.vue';
import NotificationDetailSheet from './notifications/NotificationDetailSheet.vue';
import {
  groupNotificationsByDate,
  matchesSearchQuery,
  matchesTimeFilter,
  matchesTypeFilter,
  timeFilters,
  typeFilters,
} from './notifications/notificationUtils';

const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();
const { confirm } = useConfirm();

const {
  notifications,
  unreadCount,
  isLoading,
  isRefreshing,
  fetchNotifications,
  markAsRead,
  markAllRead,
  deleteNotification,
  clearAll,
  startPolling,
  stopPolling,
} = useNotifications();

const isClearing = ref(false);
const readFilter = ref('all');
const activeTimeFilter = ref('all');
const activeTypeFilter = ref('all');
const searchQuery = ref('');
const showFilters = ref(false);
const visibleLimit = ref(20);
const selectedNotification = ref(null);

const activeFilterCount = computed(() => {
  return (
    (activeTimeFilter.value !== 'all' ? 1 : 0) +
    (activeTypeFilter.value !== 'all' ? 1 : 0) +
    (searchQuery.value.trim() ? 1 : 0)
  );
});

const hasActiveFilters = computed(() => activeFilterCount.value > 0);

const baseScopedNotifications = computed(() => {
  return notifications.value.filter((n) => {
    if (readFilter.value === 'unread' && n.is_read) return false;
    if (!matchesTimeFilter(n, activeTimeFilter.value)) return false;
    if (!matchesSearchQuery(n, searchQuery.value)) return false;
    return true;
  });
});

const typeCounts = computed(() => {
  const counts = {};
  for (const f of typeFilters) {
    counts[f.value] = baseScopedNotifications.value.filter((n) =>
      matchesTypeFilter(n, f.value)
    ).length;
  }
  return counts;
});

const filteredNotifications = computed(() => {
  return baseScopedNotifications.value.filter((n) =>
    matchesTypeFilter(n, activeTypeFilter.value)
  );
});

const feedStateMode = computed(() => {
  if (isLoading.value && notifications.value.length === 0) return 'loading';
  if (notifications.value.length === 0) return 'empty';
  if (readFilter.value === 'unread' && filteredNotifications.value.length === 0 && !hasActiveFilters.value) {
    return 'empty-unread';
  }
  if (filteredNotifications.value.length === 0) return 'empty-filtered';
  return '';
});

const remainingCount = computed(() => Math.max(0, filteredNotifications.value.length - visibleLimit.value));
const groupedNotifications = computed(() =>
  groupNotificationsByDate(filteredNotifications.value.slice(0, visibleLimit.value))
);

watch([readFilter, activeTimeFilter, activeTypeFilter, searchQuery], () => {
  visibleLimit.value = 20;
});

function clearFilters() {
  activeTimeFilter.value = 'all';
  activeTypeFilter.value = 'all';
  searchQuery.value = '';
}

function clearAllFilters() {
  clearFilters();
  readFilter.value = 'all';
}

const refresh = async () => {
  await fetchNotifications({ silent: notifications.value.length > 0 });
};

async function handleMarkAsRead(id) {
  if (!(await markAsRead(id))) toast.error('Could not mark notification as read');
}

async function handleMarkAllRead() {
  if (await markAllRead()) toast.success('All notifications marked as read');
  else toast.error('Could not mark all notifications as read');
}

async function handleDeleteNotification(id) {
  if (await deleteNotification(id)) {
    if (selectedNotification.value?.id === id) selectedNotification.value = null;
    toast.success('Notification removed');
  } else {
    toast.error('Could not remove notification');
  }
}

async function handleClearAll() {
  const ok = await confirm({
    title: 'Clear All Notifications',
    message: 'All notifications will be removed from your inbox. This cannot be undone.',
    confirmText: 'Clear All',
    cancelText: 'Keep',
    variant: 'danger',
    resolutionType: 'deleted',
    resolutionTitle: 'Inbox Cleared',
    resolutionMessage: 'All notifications have been removed.',
    resolutionBadgeText: 'CLEARED',
  });
  if (!ok) return;

  isClearing.value = true;
  try {
    if (!(await clearAll())) toast.error('Failed to clear notifications');
  } finally {
    isClearing.value = false;
  }
}

function handleSelectNotification(notif) {
  selectedNotification.value = notif;
  if (notif && !notif.is_read) handleMarkAsRead(notif.id);
}

async function handleCopy({ value, label }) {
  if (!value) return;
  try {
    await navigator.clipboard.writeText(String(value));
    toast.success(`${label || 'Value'} copied to clipboard`);
  } catch {
    toast.error('Could not copy to clipboard');
  }
}

onMounted(() => {
  fetchNotifications();
  startPolling(15000);
});

onUnmounted(() => {
  stopPolling();
});
</script>
