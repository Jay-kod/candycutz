<template>
  <AdminLayout>
    <div class="min-h-screen space-y-6 pb-12">
      <!-- Error Alert -->
      <div
        v-if="error"
        class="flex items-center justify-between rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-xs text-rose-400"
      >
        <div class="flex items-center gap-2">
          <span>{{ error }}</span>
        </div>
        <button
          type="button"
          @click="fetchNotifications"
          class="rounded-lg bg-rose-500/20 px-3 py-1 font-semibold text-rose-300 hover:bg-rose-500/30 transition-colors"
        >
          Retry
        </button>
      </div>

      <!-- Loading Preloader Skeleton -->
      <NotificationSkeleton v-if="loading && !notifications.length" />

      <!-- Loaded Dashboard Content -->
      <template v-else>
        <!-- Executive Activity Stream Header -->
        <NotificationHeader
          :unread-count="unreadCount"
          :loading="loading"
          :last-refreshed="lastRefreshed"
          @mark-all-read="markAllRead"
          @refresh="fetchNotifications"
        />

        <!-- 4 High-Signal Telemetry Cards -->
        <NotificationStats :stats="stats" />

        <!-- Search & Facet Filters -->
        <NotificationFilters
          v-model:active-type-filter="activeTypeFilter"
          v-model:active-time-filter="activeTimeFilter"
          v-model:search="search"
          :filtered-count="filteredNotifications.length"
          :total-count="notifications.length"
          :has-active-filters="activeTypeFilter !== 'all' || activeTimeFilter !== 'all' || !!search"
          @clear-filters="clearFilters"
        />

        <!-- Activity Stream & Sticky Timeline Groups -->
        <NotificationList
          :grouped-notifications="groupedNotifications"
          :total-count="notifications.length"
          @read="markAsRead"
          @delete="deleteNotification"
          @clear-filters="clearFilters"
        />
      </template>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import NotificationHeader from '../components/notifications/NotificationHeader.vue';
import NotificationStats from '../components/notifications/NotificationStats.vue';
import NotificationFilters from '../components/notifications/NotificationFilters.vue';
import NotificationList from '../components/notifications/NotificationList.vue';
import NotificationSkeleton from '../components/notifications/NotificationSkeleton.vue';
import { useAdminNotifications } from '../composables/useAdminNotifications';

const {
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
} = useAdminNotifications();
</script>
