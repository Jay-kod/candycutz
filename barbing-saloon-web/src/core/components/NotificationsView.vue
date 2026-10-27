<template>
  <div class="space-y-6 animate-fade-in relative max-w-5xl mx-auto">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-[var(--color-accent)]/5 blur-3xl pointer-events-none"></div>
      
      <div class="relative z-10">
        <p class="text-xs uppercase tracking-[0.3em] text-[var(--color-accent)] font-bold">Activity Stream</p>
        <h1 class="mt-2 font-display text-4xl text-[var(--color-text-primary)] drop-shadow-lg flex items-center gap-3">
          Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-[var(--color-accent)] to-[var(--color-text-primary)]">Notifications</span>
          <span v-if="unreadCount > 0" class="flex items-center justify-center h-8 w-8 rounded-full bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-sm font-bold shadow-[0_0_15px_rgba(255,103,0,0.4)]">
            {{ unreadCount }}
          </span>
        </h1>
        <p class="mt-2 text-sm text-[var(--color-text-secondary)]">Stay updated on recent events and alerts.</p>
      </div>
      
      <div class="relative z-10 flex items-center gap-3">
        <button 
          @click="showFilters = !showFilters"
          class="flex items-center justify-center gap-2 rounded-xl border px-5 py-3 text-sm font-bold transition-all shrink-0"
          :class="hasActiveFilters ? 'border-[var(--color-accent)]/40 bg-[var(--color-accent)]/10 text-[var(--color-accent)]' : 'bg-[var(--color-bg-tertiary)] border-[var(--color-border)] text-[var(--color-text-primary)] hover:text-[var(--color-accent)] hover:border-[var(--color-accent)]/30'"
        >
          <FunnelIcon class="w-5 h-5" />
          Filters
          <span v-if="hasActiveFilters" class="flex items-center justify-center h-5 w-5 rounded-full bg-[var(--color-accent)] text-[var(--color-accent-foreground)] text-[10px] font-bold">
            {{ (activeTimeFilter !== 'all' ? 1 : 0) + (activeTypeFilter !== 'all' ? 1 : 0) }}
          </span>
        </button>
        <button 
          v-if="authStore.isCustomer"
          @click="router.push('/customer/dashboard/notification-settings')" 
          class="flex items-center justify-center gap-2 rounded-xl bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] text-[var(--color-text-primary)] hover:text-[var(--color-accent)] px-5 py-3 text-sm font-bold transition-all hover:border-[var(--color-accent)]/30 shrink-0"
        >
          <Cog6ToothIcon class="w-5 h-5" />
          Settings
        </button>
        <button 
          v-if="unreadCount > 0" 
          @click="markAllRead" 
          class="flex items-center justify-center gap-2 rounded-xl bg-[var(--color-accent)] text-[var(--color-accent-foreground)] px-5 py-3 text-sm font-bold hover:brightness-110 transition-all active:scale-[0.98] shrink-0"
        >
          <CheckCircleIcon class="w-5 h-5" />
          Mark all read
        </button>
      </div>
    </div>

    <!-- Decomposed Filter Bar -->
    <NotificationFilterBar
      :show-filters="showFilters"
      :time-filters="timeFilters"
      :type-filters="typeFilters"
      :active-time-filter="activeTimeFilter"
      :active-type-filter="activeTypeFilter"
      :has-active-filters="hasActiveFilters"
      :filtered-count="filteredNotifications.length"
      :total-count="notifications.length"
      @update:active-time-filter="activeTimeFilter = $event"
      @update:active-type-filter="activeTypeFilter = $event"
      @clear-filters="clearFilters"
    />

    <!-- Notification List -->
    <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-bg-secondary)] shadow-xl overflow-hidden flex flex-col min-h-[500px]">
      <div v-if="loading" class="p-24 flex flex-col items-center justify-center gap-4 text-center">
        <div class="h-12 w-12 rounded-full border-4 border-[var(--color-accent)]/20 border-t-[var(--color-accent)] animate-spin"></div>
        <p class="text-[var(--color-text-secondary)] font-medium">Loading activity stream...</p>
      </div>
      
      <div v-else-if="error" class="p-24 text-center">
        <p class="text-[var(--color-danger)] font-medium bg-[var(--color-danger)]/10 border border-[var(--color-danger)]/20 inline-block px-6 py-3 rounded-xl">{{ error }}</p>
      </div>
      
      <div v-else-if="notifications.length === 0" class="p-24 flex flex-col items-center justify-center text-center">
        <div class="h-24 w-24 bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] rounded-full flex items-center justify-center mb-6 shadow-inner relative">
          <BellIcon class="w-10 h-10 text-[var(--color-text-muted)]/50" />
          <div class="absolute -top-2 -right-2 w-6 h-6 bg-[var(--color-bg-secondary)] rounded-full flex items-center justify-center">
            <div class="w-2.5 h-2.5 bg-[var(--color-accent)] rounded-full"></div>
          </div>
        </div>
        <h2 class="font-display text-2xl text-[var(--color-text-primary)] mb-2">You're all caught up!</h2>
        <p class="text-[var(--color-text-secondary)] max-w-sm">When you have new notifications about your bookings or profile, they will appear here.</p>
      </div>

      <!-- No results for filters -->
      <div v-else-if="filteredNotifications.length === 0" class="p-24 flex flex-col items-center justify-center text-center">
        <div class="h-24 w-24 bg-[var(--color-bg-tertiary)] border border-[var(--color-border)] rounded-full flex items-center justify-center mb-6 shadow-inner">
          <FunnelIcon class="w-10 h-10 text-[var(--color-text-muted)]/50" />
        </div>
        <h2 class="font-display text-2xl text-[var(--color-text-primary)] mb-2">No matching notifications</h2>
        <p class="text-[var(--color-text-secondary)] max-w-sm mb-6">Try adjusting your filters to see more notifications.</p>
        <button @click="clearFilters" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[var(--color-accent)]/10 border border-[var(--color-accent)]/20 text-[var(--color-accent)] text-sm font-bold hover:bg-[var(--color-accent)]/20 transition-colors">
          <XMarkIcon class="h-4 w-4" />
          Clear Filters
        </button>
      </div>
      
      <!-- Grouped Notification List -->
      <div v-else>
        <div v-for="(group, gi) in groupedNotifications" :key="group.label">
          <!-- Date Group Header -->
          <div class="sticky top-0 z-10 px-6 md:px-8 py-3 bg-[var(--color-bg-primary)]/90 backdrop-blur-md border-b border-[var(--color-border)]" :class="gi > 0 ? 'border-t border-[var(--color-border)]' : ''">
            <div class="flex items-center gap-3">
              <div class="h-2 w-2 rounded-full bg-[var(--color-accent)]/50"></div>
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-[var(--color-accent)]">{{ group.label }}</span>
              <div class="flex-1 h-px bg-gradient-to-r from-[var(--color-border)] to-transparent"></div>
              <span class="text-[10px] font-bold text-[var(--color-text-secondary)] uppercase">{{ group.items.length }} {{ group.items.length === 1 ? 'notification' : 'notifications' }}</span>
            </div>
          </div>

          <div class="divide-y divide-[var(--color-border)]">
            <!-- Decomposed Notification Row -->
            <NotificationRow
              v-for="notif in group.items"
              :key="notif.id"
              :notif="notif"
              @read="markAsRead"
              @delete="deleteNotification"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { 
  BellIcon, 
  CheckCircleIcon, 
  Cog6ToothIcon,
  FunnelIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '../../modules/auth/store/auth.store'
import client from '@/shared/api/client'
import NotificationFilterBar from './notifications/NotificationFilterBar.vue'
import NotificationRow from './notifications/NotificationRow.vue'
import { timeFilters, typeFilters } from './notifications/notificationUtils'

const authStore = useAuthStore()
const router = useRouter()

const notifications = ref([])
const loading = ref(true)
const error = ref('')

const activeTimeFilter = ref('all')
const activeTypeFilter = ref('all')
const showFilters = ref(false)

const hasActiveFilters = computed(() => activeTimeFilter.value !== 'all' || activeTypeFilter.value !== 'all')
const unreadCount = computed(() => notifications.value.filter(n => !n.is_read).length)

const filteredNotifications = computed(() => {
  let result = notifications.value

  if (activeTimeFilter.value !== 'all') {
    const now = new Date()
    const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    const startOfYesterday = new Date(startOfToday)
    startOfYesterday.setDate(startOfYesterday.getDate() - 1)

    result = result.filter(n => {
      const d = new Date(n.created_at)
      switch (activeTimeFilter.value) {
        case 'today': return d >= startOfToday
        case 'yesterday': return d >= startOfYesterday && d < startOfToday
        case 'week': {
          const startOfWeek = new Date(startOfToday)
          startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay())
          return d >= startOfWeek
        }
        case 'month': {
          const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
          return d >= startOfMonth
        }
        default: return true
      }
    })
  }

  if (activeTypeFilter.value !== 'all') {
    result = result.filter(n => n.type === activeTypeFilter.value)
  }

  return result
})

const groupedNotifications = computed(() => {
  const groups = []
  let currentLabel = ''
  const now = new Date()
  const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const startOfYesterday = new Date(startOfToday)
  startOfYesterday.setDate(startOfYesterday.getDate() - 1)
  const startOfWeek = new Date(startOfToday)
  startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay())

  for (const n of filteredNotifications.value) {
    const d = new Date(n.created_at)
    let label
    if (d >= startOfToday) {
      label = 'Today'
    } else if (d >= startOfYesterday) {
      label = 'Yesterday'
    } else if (d >= startOfWeek) {
      label = 'Earlier This Week'
    } else {
      label = d.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
    }

    if (label !== currentLabel) {
      currentLabel = label
      groups.push({ label, items: [] })
    }
    groups[groups.length - 1].items.push(n)
  }
  return groups
})

function clearFilters() {
  activeTimeFilter.value = 'all'
  activeTypeFilter.value = 'all'
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const res = await client.get('/notifications')
    notifications.value = res.data.data || res.data
  } catch (err) {
    error.value = 'Network error loading notifications'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const markAsRead = async (id) => {
  try {
    await client.patch(`/notifications/${id}/read`)
    const n = notifications.value.find(n => n.id === id)
    if (n) n.is_read = true
  } catch (err) {
    console.error(err)
  }
}

const deleteNotification = async (id) => {
  try {
    await client.delete(`/notifications/${id}`)
    notifications.value = notifications.value.filter(n => n.id !== id)
  } catch (err) {
    console.error(err)
  }
}

const markAllRead = async () => {
  try {
    await client.patch(`/notifications/read-all`)
    notifications.value = notifications.value.map(n => ({...n, is_read: true}))
  } catch (err) {
    console.error(err)
  }
}

let pollInterval = null

onMounted(() => {
  fetchNotifications()
  pollInterval = setInterval(fetchNotifications, 30000)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>
