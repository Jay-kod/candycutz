<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { BellIcon, CheckIcon } from '@heroicons/vue/24/outline'
import { useNotifications } from '@/core/composables/useNotifications'

const router = useRouter()
const route = useRoute()

const {
  notifications,
  unreadCount,
  fetchNotifications,
  markAsRead,
  markAllRead,
  startPolling,
  stopPolling,
} = useNotifications()

const isOpen = ref(false)

// Close when clicking outside
const closeOnClickOutside = (e) => {
  if (isOpen.value && !e.target.closest('.notification-bell')) {
    isOpen.value = false
  }
}

onMounted(() => {
  fetchNotifications()
  startPolling(30000)
  document.addEventListener('click', closeOnClickOutside)
})

onUnmounted(() => {
  stopPolling()
  document.removeEventListener('click', closeOnClickOutside)
})

const toggleOpen = () => {
  isOpen.value = !isOpen.value
}

const viewAll = () => {
  isOpen.value = false
  const path = route.path
  if (path.startsWith('/admin')) {
    router.push('/admin/notifications')
  } else if (path.startsWith('/barber')) {
    router.push('/barber/notifications')
  } else {
    router.push('/customer/dashboard/notifications')
  }
}
</script>

<template>
  <div class="relative notification-bell">
    <button 
      @click="toggleOpen" 
      class="relative min-w-[44px] min-h-[44px] flex items-center justify-center p-2 text-[var(--color-text-secondary)] hover:text-[var(--color-accent)] transition-colors focus:outline-none"
      aria-label="View notifications"
    >
      <BellIcon class="h-6 w-6" />
      <span v-if="unreadCount > 0" class="absolute top-1.5 right-1.5 h-4 w-4 bg-[var(--color-danger)] rounded-full flex items-center justify-center text-[10px] font-bold text-white border border-[var(--color-bg-secondary)]">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>
    
    <div v-if="isOpen" class="absolute right-0 mt-2 w-[calc(100vw-2rem)] max-w-xs sm:w-80 bg-[var(--color-bg-secondary)] border border-[var(--color-border)] rounded-2xl shadow-[0_10px_40px_var(--color-shadow)] z-50 overflow-hidden transform transition-all">
      <div class="p-4 border-b border-[var(--color-border-subtle)] flex justify-between items-center bg-[var(--color-bg-tertiary)]">
        <h3 class="font-bold text-[var(--color-text-primary)]">Notifications</h3>
        <button v-if="unreadCount > 0" @click.stop="markAllRead" class="text-xs text-[var(--color-accent)] hover:text-[var(--color-accent-hover)] transition-colors">Mark all read</button>
      </div>
      
      <div class="max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="p-8 text-center text-[var(--color-text-muted)] text-sm">
          No notifications yet.
        </div>
        <div v-else>
          <div v-for="notif in notifications" :key="notif.id" 
               class="p-4 border-b border-[var(--color-border-subtle)] hover:bg-[var(--color-bg-tertiary)] transition-colors cursor-pointer group flex items-start gap-3"
               :class="{ 'bg-[var(--color-bg-tertiary)]/50': !notif.is_read }"
               @click="markAsRead(notif.id)">
               
            <div class="mt-1 flex-shrink-0">
              <div class="h-2 w-2 rounded-full" :class="notif.is_read ? 'bg-transparent' : 'bg-[var(--color-accent)]'"></div>
            </div>
            
            <div class="flex-1">
              <h4 class="text-sm font-semibold" :class="notif.is_read ? 'text-[var(--color-text-secondary)]' : 'text-[var(--color-text-primary)]'">{{ notif.title }}</h4>
              <p class="text-xs text-[var(--color-text-secondary)] mt-1 line-clamp-2">{{ notif.message }}</p>
              <span class="text-[10px] text-[var(--color-text-muted)] mt-2 block">{{ new Date(notif.created_at).toLocaleString() }}</span>
            </div>
            
            <button v-if="!notif.is_read" class="opacity-0 group-hover:opacity-100 transition-opacity text-[var(--color-accent)] hover:text-[var(--color-accent-hover)]" title="Mark as read">
              <CheckIcon class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
      
      <div class="p-3 border-t border-[var(--color-border-subtle)] bg-[var(--color-bg-tertiary)]">
        <button @click="viewAll" class="w-full text-center text-sm font-semibold text-[var(--color-accent)] hover:text-[var(--color-accent-hover)] transition-colors py-2">
          View all notifications
        </button>
      </div>
    </div>
  </div>
</template>
