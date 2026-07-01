<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { 
  BellIcon, 
  CheckIcon, 
  CheckCircleIcon, 
  TrashIcon, 
  Cog6ToothIcon,
  CalendarDaysIcon,
  UserIcon,
  HeartIcon,
  DocumentTextIcon,
  ChatBubbleLeftRightIcon,
  StarIcon,
  FunnelIcon,
  XMarkIcon,
  ClockIcon,
  TagIcon,
  CreditCardIcon
} from '@heroicons/vue/24/outline'
import { useAuthStore } from '../../modules/auth/store/auth.store'
import client from '../api/axios'

const authStore = useAuthStore()
const router = useRouter()

const notifications = ref([])
const loading = ref(true)
const error = ref('')

// Filter state
const activeTimeFilter = ref('all')
const activeTypeFilter = ref('all')
const showFilters = ref(false)

const timeFilters = [
  { value: 'all', label: 'All Time', icon: ClockIcon },
  { value: 'today', label: 'Today' },
  { value: 'yesterday', label: 'Yesterday' },
  { value: 'week', label: 'This Week' },
  { value: 'month', label: 'This Month' },
]

const typeFilters = [
  { value: 'all', label: 'All Types', icon: TagIcon },
  { value: 'booking', label: 'Bookings', color: 'text-gold', bg: 'bg-gold/10 border-gold/20' },
  { value: 'payment', label: 'Payments', color: 'text-cyan-400', bg: 'bg-cyan-400/10 border-cyan-400/20' },
  { value: 'system_update', label: 'System', color: 'text-blue-400', bg: 'bg-blue-400/10 border-blue-400/20' },
  { value: 'wishlist_update', label: 'Wishlist', color: 'text-rose-400', bg: 'bg-rose-400/10 border-rose-400/20' },
  { value: 'blog_update', label: 'Blog', color: 'text-emerald-400', bg: 'bg-emerald-400/10 border-emerald-400/20' },
  { value: 'general_update', label: 'General', color: 'text-purple-400', bg: 'bg-purple-400/10 border-purple-400/20' },
  { value: 'review', label: 'Reviews', color: 'text-amber-400', bg: 'bg-amber-400/10 border-amber-400/20' },
  { value: 'review_approved', label: 'Approved', color: 'text-emerald-400', bg: 'bg-emerald-400/10 border-emerald-400/20' },
]

const hasActiveFilters = computed(() => activeTimeFilter.value !== 'all' || activeTypeFilter.value !== 'all')

const filteredNotifications = computed(() => {
  let result = notifications.value

  // Time filter
  if (activeTimeFilter.value !== 'all') {
    const now = new Date()
    const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate())
    const startOfYesterday = new Date(startOfToday)
    startOfYesterday.setDate(startOfYesterday.getDate() - 1)

    result = result.filter(n => {
      const d = new Date(n.created_at)
      switch (activeTimeFilter.value) {
        case 'today':
          return d >= startOfToday
        case 'yesterday':
          return d >= startOfYesterday && d < startOfToday
        case 'week': {
          const startOfWeek = new Date(startOfToday)
          startOfWeek.setDate(startOfWeek.getDate() - startOfWeek.getDay())
          return d >= startOfWeek
        }
        case 'month': {
          const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
          return d >= startOfMonth
        }
        default:
          return true
      }
    })
  }

  // Type filter
  if (activeTypeFilter.value !== 'all') {
    result = result.filter(n => n.type === activeTypeFilter.value)
  }

  return result
})

// Group notifications by date for display
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
    if (n) {
      n.is_read = true
    }
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
    
    // Optimistic UI update
    notifications.value = notifications.value.map(n => ({...n, is_read: true}))
  } catch (err) {
    console.error(err)
  }
}

const formatRelativeTime = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);
  
  if (diffInSeconds < 60) return 'Just now';
  
  const diffInMinutes = Math.floor(diffInSeconds / 60);
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
  
  const diffInHours = Math.floor(diffInMinutes / 60);
  if (diffInHours < 24) return `${diffInHours}h ago`;
  
  const diffInDays = Math.floor(diffInHours / 24);
  if (diffInDays === 1) return 'Yesterday';
  if (diffInDays < 7) return `${diffInDays}d ago`;
  
  return date.toLocaleDateString();
}

const formatFullDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' }) + ' at ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

const getIconForType = (type) => {
  switch (type) {
    case 'booking': return CalendarDaysIcon;
    case 'payment': return CreditCardIcon;
    case 'system_update': return UserIcon;
    case 'wishlist_update': return HeartIcon;
    case 'blog_update': return DocumentTextIcon;
    case 'general_update': return Cog6ToothIcon;
    case 'review': return ChatBubbleLeftRightIcon;
    case 'review_approved': return StarIcon;
    default: return BellIcon;
  }
}

const getLabelForType = (type) => {
  switch (type) {
    case 'booking': return 'Booking';
    case 'payment': return 'Payment';
    case 'system_update': return 'System';
    case 'wishlist_update': return 'Wishlist';
    case 'blog_update': return 'Blog';
    case 'general_update': return 'General';
    case 'review': return 'Review';
    case 'review_approved': return 'Approved';
    default: return 'Notification';
  }
}

const getIconColorForType = (type) => {
  switch (type) {
    case 'booking': return 'text-gold';
    case 'payment': return 'text-cyan-400';
    case 'system_update': return 'text-blue-400';
    case 'wishlist_update': return 'text-rose-400';
    case 'blog_update': return 'text-emerald-400';
    case 'general_update': return 'text-purple-400';
    case 'review': return 'text-amber-400';
    case 'review_approved': return 'text-emerald-400';
    default: return 'text-theme-muted';
  }
}

const getBgColorForType = (type) => {
  switch (type) {
    case 'booking': return 'bg-gold/10 border-gold/20';
    case 'payment': return 'bg-cyan-400/10 border-cyan-400/20';
    case 'system_update': return 'bg-blue-400/10 border-blue-400/20';
    case 'wishlist_update': return 'bg-rose-400/10 border-rose-400/20';
    case 'blog_update': return 'bg-emerald-400/10 border-emerald-400/20';
    case 'general_update': return 'bg-purple-400/10 border-purple-400/20';
    case 'review': return 'bg-amber-400/10 border-amber-400/20';
    case 'review_approved': return 'bg-emerald-400/10 border-emerald-400/20';
    default: return 'bg-theme-border/30 border-theme-border/50';
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

<template>
  <div class="space-y-6 animate-fade-in relative max-w-5xl mx-auto">
    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl border border-gold/20 bg-gradient-to-br from-obsidian via-charcoal to-steel p-8 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-gold/5 blur-3xl pointer-events-none"></div>
      
      <div class="relative z-10">
        <p class="text-xs uppercase tracking-[0.3em] text-gold/70 font-bold">Activity Stream</p>
        <h1 class="mt-2 font-display text-4xl text-white drop-shadow-lg flex items-center gap-3">
          Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold to-white">Notifications</span>
          <span v-if="notifications.filter(n => !n.is_read).length > 0" class="flex items-center justify-center h-8 w-8 rounded-full bg-gold text-obsidian text-sm font-bold shadow-[0_0_15px_rgba(255,103,0,0.4)]">
            {{ notifications.filter(n => !n.is_read).length }}
          </span>
        </h1>
        <p class="mt-2 text-sm text-white/70">Stay updated on recent events and alerts.</p>
      </div>
      
      <div class="relative z-10 flex items-center gap-3">
        <button 
          @click="showFilters = !showFilters"
          class="flex items-center justify-center gap-2 rounded-xl border px-5 py-3 text-sm font-bold transition-all shrink-0"
          :class="hasActiveFilters ? 'border-gold/40 bg-gold/10 text-gold' : 'bg-white/10 border-white/10 text-white hover:text-gold hover:border-gold/30'"
        >
          <FunnelIcon class="w-5 h-5" />
          Filters
          <span v-if="hasActiveFilters" class="flex items-center justify-center h-5 w-5 rounded-full bg-gold text-obsidian text-[10px] font-bold">
            {{ (activeTimeFilter !== 'all' ? 1 : 0) + (activeTypeFilter !== 'all' ? 1 : 0) }}
          </span>
        </button>
        <button 
          v-if="authStore.isCustomer"
          @click="router.push('/customer/dashboard/notification-settings')" 
          class="flex items-center justify-center gap-2 rounded-xl bg-white/10 border border-white/10 text-white hover:text-gold px-5 py-3 text-sm font-bold transition-all hover:border-gold/30 shrink-0"
        >
          <Cog6ToothIcon class="w-5 h-5" />
          Settings
        </button>
        <button 
          v-if="notifications.some(n => !n.is_read)" 
          @click="markAllRead" 
          class="flex items-center justify-center gap-2 rounded-xl bg-gold text-obsidian px-5 py-3 text-sm font-bold hover:bg-gold-light transition-all active:scale-[0.98] shrink-0"
        >
          <CheckCircleIcon class="w-5 h-5" />
          Mark all read
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-3 max-h-0"
      enter-to-class="opacity-100 translate-y-0 max-h-[500px]"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0 max-h-[500px]"
      leave-to-class="opacity-0 -translate-y-3 max-h-0"
    >
      <div v-if="showFilters" class="overflow-hidden rounded-2xl border border-theme-border bg-theme-surface/80 backdrop-blur-xl shadow-lg">
        <div class="p-6 space-y-5">

          <!-- Time Filter -->
          <div>
            <div class="flex items-center gap-2 mb-3">
              <ClockIcon class="h-4 w-4 text-gold/60" />
              <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold/70">When</span>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="f in timeFilters"
                :key="f.value"
                @click="activeTimeFilter = f.value"
                class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider border transition-all duration-200"
                :class="activeTimeFilter === f.value 
                  ? 'bg-gold/20 border-gold/40 text-gold shadow-[0_0_10px_rgba(212,175,55,0.15)]' 
                  : 'bg-theme-bg/50 border-theme-border/50 text-theme-muted hover:text-theme-text hover:border-theme-border'"
              >
                {{ f.label }}
              </button>
            </div>
          </div>

          <!-- Type Filter -->
          <div>
            <div class="flex items-center gap-2 mb-3">
              <TagIcon class="h-4 w-4 text-gold/60" />
              <span class="text-xs font-bold uppercase tracking-[0.2em] text-gold/70">Type</span>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="f in typeFilters"
                :key="f.value"
                @click="activeTypeFilter = f.value"
                class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider border transition-all duration-200"
                :class="activeTypeFilter === f.value 
                  ? (f.bg || 'bg-gold/20 border-gold/40') + ' ' + (f.color || 'text-gold') + ' shadow-sm' 
                  : 'bg-theme-bg/50 border-theme-border/50 text-theme-muted hover:text-theme-text hover:border-theme-border'"
              >
                {{ f.label }}
              </button>
            </div>
          </div>

          <!-- Active Filters Summary & Clear -->
          <div v-if="hasActiveFilters" class="flex items-center justify-between pt-3 border-t border-theme-border/30">
            <p class="text-xs text-theme-muted">
              Showing <span class="text-gold font-bold">{{ filteredNotifications.length }}</span> of 
              <span class="text-theme-muted font-bold">{{ notifications.length }}</span> notifications
            </p>
            <button @click="clearFilters" class="flex items-center gap-1.5 text-xs font-bold text-red-400 hover:text-red-300 transition-colors">
              <XMarkIcon class="h-4 w-4" />
              Clear Filters
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Notification List -->
    <div class="rounded-3xl border border-theme-border bg-theme-surface shadow-xl overflow-hidden flex flex-col min-h-[500px]">
      <div v-if="loading" class="p-24 flex flex-col items-center justify-center gap-4 text-center">
        <div class="h-12 w-12 rounded-full border-4 border-gold/20 border-t-gold animate-spin"></div>
        <p class="text-theme-muted font-medium">Loading activity stream...</p>
      </div>
      
      <div v-else-if="error" class="p-24 text-center">
        <p class="text-red-400 font-medium bg-red-400/10 border border-red-400/20 inline-block px-6 py-3 rounded-xl">{{ error }}</p>
      </div>
      
      <div v-else-if="notifications.length === 0" class="p-24 flex flex-col items-center justify-center text-center">
        <div class="h-24 w-24 bg-theme-bg border border-theme-border rounded-full flex items-center justify-center mb-6 shadow-inner relative">
          <BellIcon class="w-10 h-10 text-theme-muted/50" />
          <div class="absolute -top-2 -right-2 w-6 h-6 bg-theme-surface rounded-full flex items-center justify-center">
            <div class="w-2.5 h-2.5 bg-gold rounded-full"></div>
          </div>
        </div>
        <h2 class="font-display text-2xl text-theme-text mb-2">You're all caught up!</h2>
        <p class="text-theme-muted max-w-sm">When you have new notifications about your bookings or profile, they will appear here.</p>
      </div>

      <!-- No results for filters -->
      <div v-else-if="filteredNotifications.length === 0" class="p-24 flex flex-col items-center justify-center text-center">
        <div class="h-24 w-24 bg-theme-bg border border-theme-border rounded-full flex items-center justify-center mb-6 shadow-inner">
          <FunnelIcon class="w-10 h-10 text-theme-muted/50" />
        </div>
        <h2 class="font-display text-2xl text-theme-text mb-2">No matching notifications</h2>
        <p class="text-theme-muted max-w-sm mb-6">Try adjusting your filters to see more notifications.</p>
        <button @click="clearFilters" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gold/10 border border-gold/20 text-gold text-sm font-bold hover:bg-gold/20 transition-colors">
          <XMarkIcon class="h-4 w-4" />
          Clear Filters
        </button>
      </div>
      
      <!-- Grouped Notification List -->
      <div v-else>
        <div v-for="(group, gi) in groupedNotifications" :key="group.label">
          <!-- Date Group Header -->
          <div class="sticky top-0 z-10 px-6 md:px-8 py-3 bg-theme-bg/90 backdrop-blur-md border-b border-theme-border/30" :class="gi > 0 ? 'border-t border-theme-border/30' : ''">
            <div class="flex items-center gap-3">
              <div class="h-2 w-2 rounded-full bg-gold/50"></div>
              <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-gold/70">{{ group.label }}</span>
              <div class="flex-1 h-px bg-gradient-to-r from-theme-border/30 to-transparent"></div>
              <span class="text-[10px] font-bold text-theme-muted/70 uppercase">{{ group.items.length }} {{ group.items.length === 1 ? 'notification' : 'notifications' }}</span>
            </div>
          </div>

          <div class="divide-y divide-theme-border/30">
            <div 
              v-for="notif in group.items" 
              :key="notif.id" 
              class="group p-6 md:p-8 flex items-start gap-5 transition-all duration-300 relative overflow-hidden bg-theme-surface hover:bg-theme-bg"
            >
              <!-- Unread indicator logic -->
              <div v-if="!notif.is_read" class="absolute left-0 top-0 bottom-0 w-1.5 bg-gold shadow-[0_0_15px_rgba(255,103,0,0.6)]"></div>
              
              <div class="flex-shrink-0 mt-1">
                <div 
                  class="h-14 w-14 rounded-2xl flex items-center justify-center border shadow-sm transition-transform duration-300 group-hover:scale-105" 
                  :class="!notif.is_read ? getBgColorForType(notif.type) : 'bg-theme-bg/50 border-theme-border/50 grayscale opacity-70'"
                >
                  <component :is="getIconForType(notif.type)" class="w-7 h-7" :class="!notif.is_read ? getIconColorForType(notif.type) : 'text-theme-muted'" />
                </div>
              </div>
              
              <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1.5">
                  <div class="flex items-center gap-2.5 flex-wrap">
                    <h4 class="text-xl font-display transition-colors duration-200" :class="!notif.is_read ? 'text-theme-text group-hover:text-gold' : 'text-theme-muted'">
                      {{ notif.title }}
                    </h4>
                    <span class="text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-md border" :class="getBgColorForType(notif.type) + ' ' + getIconColorForType(notif.type)">
                      {{ getLabelForType(notif.type) }}
                    </span>
                  </div>
                  <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs font-bold text-theme-muted flex items-center gap-1.5 bg-theme-bg px-3 py-1.5 rounded-xl border border-theme-border" :title="formatFullDate(notif.created_at)">
                      <ClockIcon class="w-3.5 h-3.5 text-theme-muted/50" />
                      {{ formatRelativeTime(notif.created_at) }}
                    </span>
                  </div>
                </div>
                
                <p class="text-sm text-theme-muted mb-1.5" :title="formatFullDate(notif.created_at)">{{ formatFullDate(notif.created_at) }}</p>
                
                <p class="text-base leading-relaxed" :class="!notif.is_read ? 'text-theme-text' : 'text-theme-muted'">{{ notif.message }}</p>
                
                <div class="mt-5 flex items-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-y-2 group-hover:translate-y-0">
                  <button 
                    v-if="!notif.is_read" 
                    @click="markAsRead(notif.id)" 
                    class="inline-flex items-center gap-2 text-xs font-bold text-gold hover:text-obsidian transition-colors bg-gold/10 hover:bg-gold border border-gold/20 hover:border-gold px-4 py-2 rounded-xl"
                  >
                    <CheckIcon class="w-4 h-4" />
                    Mark as read
                  </button>
                  
                  <button 
                    @click="deleteNotification(notif.id)" 
                    class="inline-flex items-center gap-2 text-xs font-bold text-red-400 hover:text-white transition-colors bg-red-500/10 hover:bg-red-500 border border-red-500/20 hover:border-red-500 px-4 py-2 rounded-xl"
                  >
                    <TrashIcon class="w-4 h-4" />
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
