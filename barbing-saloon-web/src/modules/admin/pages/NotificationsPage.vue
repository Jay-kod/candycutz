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

      <!-- Executive Activity Stream Header -->
      <NotificationHeader
        :unread-count="unreadCount"
        :loading="loading"
        :last-refreshed="lastRefreshed"
        @mark-all-read="markAllRead"
        @refresh="handleRefresh"
      />

      <!-- Navigation Tabs -->
      <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-[var(--color-border)]">
        <button
          type="button"
          v-for="tab in tabs"
          :key="tab.id"
          @click="selectTab(tab.id)"
          class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all"
          :class="activeTab === tab.id
            ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)] shadow-md shadow-[var(--color-accent)]/20'
            : 'text-[var(--color-text-secondary)] hover:text-[var(--color-text-primary)] hover:bg-[var(--color-bg-secondary)]'"
        >
          <component :is="tab.icon" class="w-4 h-4" />
          <span>{{ tab.label }}</span>
        </button>
      </div>

      <!-- Tab 1: Live Activity Stream -->
      <div v-if="activeTab === 'stream'" class="space-y-6">
        <NotificationSkeleton v-if="loading && !notifications.length" />
        <template v-else>
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

      <!-- Tab 2: Channel Deliveries & Audit -->
      <div v-else-if="activeTab === 'deliveries'" class="space-y-6">
        <!-- Delivery Filter Controls -->
        <div class="bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-2xl p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
          <div class="flex flex-wrap items-center gap-3">
            <select
              v-model="deliveryFilter.channel"
              @change="fetchDeliveries(1)"
              class="px-3 py-2 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-xs text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)]"
            >
              <option value="all">All Channels</option>
              <option value="in_app">In-App DB</option>
              <option value="push">Expo Push</option>
              <option value="email">Brevo Email</option>
            </select>

            <select
              v-model="deliveryFilter.status"
              @change="fetchDeliveries(1)"
              class="px-3 py-2 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-xs text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)]"
            >
              <option value="all">All Statuses</option>
              <option value="delivered">Delivered</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
              <option value="suppressed">Suppressed</option>
            </select>
          </div>

          <div class="relative">
            <input
              type="text"
              v-model="deliveryFilter.search"
              @keydown.enter="fetchDeliveries(1)"
              placeholder="Search recipient or error..."
              class="w-full sm:w-64 pl-3 pr-8 py-2 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-xs text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)]"
            />
            <button
              type="button"
              @click="fetchDeliveries(1)"
              class="absolute right-2 top-2.5 text-xs text-[var(--color-text-tertiary)] hover:text-[var(--color-text-primary)]"
            >
              Search
            </button>
          </div>
        </div>

        <!-- Deliveries Table -->
        <div class="bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-2xl overflow-hidden shadow-sm">
          <div v-if="loadingDeliveries" class="p-8 text-center text-xs text-[var(--color-text-secondary)]">
            Loading delivery records...
          </div>
          <div v-else-if="!deliveries.length" class="p-8 text-center text-xs text-[var(--color-text-secondary)]">
            No delivery records found matching criteria.
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[var(--color-text-secondary)]">
              <thead class="bg-[var(--color-bg-tertiary)]/50 text-[var(--color-text-tertiary)] uppercase font-semibold border-b border-[var(--color-border)]">
                <tr>
                  <th class="px-4 py-3">Channel</th>
                  <th class="px-4 py-3">Recipient / Target</th>
                  <th class="px-4 py-3">Notification Title</th>
                  <th class="px-4 py-3">Status</th>
                  <th class="px-4 py-3">Attempts</th>
                  <th class="px-4 py-3">Delivered / Time</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[var(--color-border)]">
                <tr v-for="d in deliveries" :key="d.id" class="hover:bg-[var(--color-bg-tertiary)]/20 transition-colors">
                  <td class="px-4 py-3 font-medium">
                    <span
                      class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md font-semibold text-[11px]"
                      :class="{
                        'bg-amber-500/10 text-amber-500 border border-amber-500/20': d.channel === 'in_app',
                        'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20': d.channel === 'push',
                        'bg-blue-500/10 text-blue-500 border border-blue-500/20': d.channel === 'email',
                      }"
                    >
                      {{ d.channel === 'in_app' ? 'In-App DB' : d.channel === 'push' ? 'Expo Push' : 'Brevo Email' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 truncate max-w-[200px]" :title="d.recipient_email || d.recipient_token || 'In-App Feed'">
                    {{ d.recipient_email || (d.recipient_token ? d.recipient_token.slice(0, 24) + '...' : 'In-App Direct') }}
                  </td>
                  <td class="px-4 py-3 text-[var(--color-text-primary)] font-medium max-w-[240px] truncate" :title="d.notification?.title">
                    {{ d.notification?.title || 'System Notification' }}
                  </td>
                  <td class="px-4 py-3">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                      :class="{
                        'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20': d.status === 'delivered',
                        'bg-rose-500/10 text-rose-500 border border-rose-500/20': d.status === 'failed',
                        'bg-amber-500/10 text-amber-500 border border-amber-500/20': d.status === 'suppressed',
                        'bg-sky-500/10 text-sky-500 border border-sky-500/20': d.status === 'pending' || d.status === 'processing',
                      }"
                    >
                      {{ d.status }}
                    </span>
                    <div v-if="d.failure_reason" class="text-[10px] text-rose-400 mt-0.5 truncate max-w-[150px]" :title="d.failure_reason">
                      {{ d.failure_reason }}
                    </div>
                  </td>
                  <td class="px-4 py-3 text-center">{{ d.attempts }}</td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    {{ new Date(d.delivered_at || d.created_at).toLocaleString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="deliveriesPagination.last_page > 1" class="p-4 border-t border-[var(--color-border)] flex items-center justify-between text-xs">
            <span>Page {{ deliveriesPagination.current_page }} of {{ deliveriesPagination.last_page }} ({{ deliveriesPagination.total }} records)</span>
            <div class="flex items-center gap-2">
              <button
                type="button"
                :disabled="deliveriesPagination.current_page <= 1"
                @click="fetchDeliveries(deliveriesPagination.current_page - 1)"
                class="px-3 py-1.5 rounded-lg border border-[var(--color-border)] disabled:opacity-40"
              >
                Previous
              </button>
              <button
                type="button"
                :disabled="deliveriesPagination.current_page >= deliveriesPagination.last_page"
                @click="fetchDeliveries(deliveriesPagination.current_page + 1)"
                class="px-3 py-1.5 rounded-lg border border-[var(--color-border)] disabled:opacity-40"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 3: Broadcast Composer -->
      <div v-else-if="activeTab === 'broadcast'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <!-- Composer Form -->
          <div class="lg:col-span-7 bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
            <div>
              <h2 class="text-xl font-display text-[var(--color-text-primary)]">Compose Broadcast Campaign</h2>
              <p class="text-xs text-[var(--color-text-secondary)] mt-1">Dispatch announcements simultaneously across In-App feeds, mobile push, and Brevo emails.</p>
            </div>

            <form @submit.prevent="handleSendBroadcast" class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Campaign Title *</label>
                <input
                  type="text"
                  v-model="broadcastForm.title"
                  required
                  placeholder="e.g. Holiday Season Styling Special ✂️"
                  class="w-full px-4 py-2.5 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-sm text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)]"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Target Audience *</label>
                <select
                  v-model="broadcastForm.audience"
                  class="w-full px-4 py-2.5 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-sm text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)]"
                >
                  <option value="all">All Users (Customers, Barbers, Staff)</option>
                  <option value="customers">Customers Only</option>
                  <option value="barbers">Barbers & Stylists Only</option>
                  <option value="admins">Admins & Branch Managers</option>
                </select>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[var(--color-text-secondary)] mb-2">Delivery Channels *</label>
                <div class="grid grid-cols-3 gap-3">
                  <label class="flex items-center gap-2 p-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 cursor-pointer text-xs font-medium text-[var(--color-text-primary)]">
                    <input type="checkbox" value="in_app" v-model="broadcastForm.channels" class="rounded text-[var(--color-accent)]" />
                    <span>In-App Feed</span>
                  </label>
                  <label class="flex items-center gap-2 p-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 cursor-pointer text-xs font-medium text-[var(--color-text-primary)]">
                    <input type="checkbox" value="push" v-model="broadcastForm.channels" class="rounded text-emerald-500" />
                    <span>Expo Push</span>
                  </label>
                  <label class="flex items-center gap-2 p-3 rounded-xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/50 cursor-pointer text-xs font-medium text-[var(--color-text-primary)]">
                    <input type="checkbox" value="email" v-model="broadcastForm.channels" class="rounded text-blue-500" />
                    <span>Brevo Email</span>
                  </label>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Message Body *</label>
                <textarea
                  v-model="broadcastForm.message"
                  required
                  rows="4"
                  placeholder="Enter your announcement details..."
                  class="w-full px-4 py-2.5 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-sm text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)]"
                ></textarea>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[var(--color-text-secondary)] mb-1">Action URL (Optional)</label>
                <input
                  type="text"
                  v-model="broadcastForm.action_url"
                  placeholder="/booking or /customer/wishlist"
                  class="w-full px-4 py-2.5 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-sm text-[var(--color-text-primary)] focus:outline-none focus:border-[var(--color-accent)]"
                />
              </div>

              <div class="pt-2 flex justify-end">
                <button
                  type="submit"
                  :disabled="sendingBroadcast || !broadcastForm.channels.length"
                  class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] font-bold text-sm hover:brightness-110 active:scale-95 transition-all shadow-lg shadow-[var(--color-accent)]/20 disabled:opacity-50"
                >
                  <PaperAirplaneIcon class="w-4 h-4" />
                  <span>{{ sendingBroadcast ? 'Dispatching...' : 'Dispatch Campaign' }}</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Broadcast History List -->
          <div class="lg:col-span-5 bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
            <h3 class="text-base font-semibold text-[var(--color-text-primary)]">Recent Campaigns</h3>
            <div v-if="loadingBroadcasts" class="text-xs text-[var(--color-text-secondary)]">Loading campaign logs...</div>
            <div v-else-if="!broadcasts.length" class="text-xs text-[var(--color-text-secondary)]">No previous broadcast campaigns recorded.</div>
            <div v-else class="space-y-3 max-h-[460px] overflow-y-auto pr-1">
              <div
                v-for="b in broadcasts"
                :key="b.id"
                class="p-4 rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-tertiary)]/40 space-y-2 text-xs"
              >
                <div class="flex items-center justify-between">
                  <span class="font-bold text-[var(--color-text-primary)]">{{ b.title }}</span>
                  <span class="text-[10px] uppercase font-semibold text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-500/20">
                    {{ b.status }}
                  </span>
                </div>
                <p class="text-[var(--color-text-secondary)] line-clamp-2">{{ b.message }}</p>
                <div class="flex items-center justify-between text-[10px] text-[var(--color-text-tertiary)] pt-1 border-t border-[var(--color-border)]/50">
                  <span>Audience: {{ b.audience }}</span>
                  <span>{{ new Date(b.created_at).toLocaleDateString() }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 4: Delivery Analytics -->
      <div v-else-if="activeTab === 'analytics'" class="space-y-6">
        <div v-if="loadingAnalytics" class="p-8 text-center text-xs text-[var(--color-text-secondary)]">
          Aggregating notification metrics...
        </div>
        <div v-else-if="analytics" class="space-y-6">
          <!-- 4 Stats Cards -->
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)]">
              <span class="text-xs text-[var(--color-text-secondary)]">Notifications Today</span>
              <div class="text-2xl font-bold text-[var(--color-text-primary)] mt-1">{{ analytics.total_notifications_today }}</div>
            </div>
            <div class="p-5 rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)]">
              <span class="text-xs text-[var(--color-text-secondary)]">Volume (30 Days)</span>
              <div class="text-2xl font-bold text-[var(--color-text-primary)] mt-1">{{ analytics.total_notifications_30d }}</div>
            </div>
            <div class="p-5 rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)]">
              <span class="text-xs text-[var(--color-text-secondary)]">Registered Push Devices</span>
              <div class="text-2xl font-bold text-emerald-500 mt-1">{{ analytics.active_push_tokens }}</div>
            </div>
            <div class="p-5 rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)]">
              <span class="text-xs text-[var(--color-text-secondary)]">Reach (Active Users)</span>
              <div class="text-2xl font-bold text-blue-500 mt-1">{{ analytics.active_users_with_push }}</div>
            </div>
          </div>

          <!-- Channel Deliveries Breakdown -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-6 rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] space-y-4">
              <h3 class="text-sm font-semibold text-[var(--color-text-primary)]">Platform Token Distribution</h3>
              <div class="space-y-2 text-xs">
                <div v-for="(count, plat) in analytics.devices_by_platform" :key="plat" class="flex items-center justify-between p-3 rounded-xl bg-[var(--color-bg-tertiary)]/50">
                  <span class="uppercase font-semibold text-[var(--color-text-primary)]">{{ plat }}</span>
                  <span class="font-bold text-[var(--color-accent)]">{{ count }} tokens</span>
                </div>
              </div>
            </div>

            <div class="p-6 rounded-2xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] space-y-4">
              <h3 class="text-sm font-semibold text-[var(--color-text-primary)]">Channel Delivery Statuses (30 Days)</h3>
              <div class="space-y-2 text-xs max-h-60 overflow-y-auto">
                <div v-for="c in analytics.deliveries_by_channel" :key="c.channel + c.status" class="flex items-center justify-between p-3 rounded-xl bg-[var(--color-bg-tertiary)]/50">
                  <div class="flex items-center gap-2">
                    <span class="font-semibold uppercase text-[var(--color-text-primary)]">{{ c.channel }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full" :class="c.status === 'delivered' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500'">
                      {{ c.status }}
                    </span>
                  </div>
                  <span class="font-bold text-[var(--color-text-primary)]">{{ c.total }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import {
  BellAlertIcon,
  TableCellsIcon,
  MegaphoneIcon,
  ChartBarIcon,
  PaperAirplaneIcon,
} from '@heroicons/vue/24/outline';
import AdminLayout from '@/portals/admin/layouts/AdminLayout.vue';
import NotificationHeader from '../components/notifications/NotificationHeader.vue';
import NotificationStats from '../components/notifications/NotificationStats.vue';
import NotificationFilters from '../components/notifications/NotificationFilters.vue';
import NotificationList from '../components/notifications/NotificationList.vue';
import NotificationSkeleton from '../components/notifications/NotificationSkeleton.vue';
import { useAdminNotifications } from '../composables/useAdminNotifications';

const tabs = [
  { id: 'stream', label: 'Live Activity Stream', icon: BellAlertIcon },
  { id: 'deliveries', label: 'Channel Deliveries', icon: TableCellsIcon },
  { id: 'broadcast', label: 'Broadcast Composer', icon: MegaphoneIcon },
  { id: 'analytics', label: 'Delivery Analytics', icon: ChartBarIcon },
];

const {
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
} = useAdminNotifications();

const sendingBroadcast = ref(false);
const broadcastForm = reactive({
  title: '',
  message: '',
  audience: 'all',
  channels: ['in_app', 'push'],
  action_url: '',
});

const selectTab = (tabId) => {
  activeTab.value = tabId;
  if (tabId === 'deliveries') {
    fetchDeliveries(1);
  } else if (tabId === 'broadcast') {
    fetchBroadcasts();
  } else if (tabId === 'analytics') {
    fetchAnalytics();
  }
};

const handleRefresh = () => {
  if (activeTab.value === 'stream') {
    fetchNotifications();
  } else if (activeTab.value === 'deliveries') {
    fetchDeliveries(1);
  } else if (activeTab.value === 'broadcast') {
    fetchBroadcasts();
  } else if (activeTab.value === 'analytics') {
    fetchAnalytics();
  }
};

const handleSendBroadcast = async () => {
  sendingBroadcast.value = true;
  try {
    const success = await sendBroadcast({
      title: broadcastForm.title,
      message: broadcastForm.message,
      audience: broadcastForm.audience,
      channels: broadcastForm.channels,
      action_url: broadcastForm.action_url || null,
    });
    if (success) {
      broadcastForm.title = '';
      broadcastForm.message = '';
      broadcastForm.action_url = '';
    }
  } finally {
    sendingBroadcast.value = false;
  }
};
</script>
