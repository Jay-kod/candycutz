<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { BellIcon, CheckIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()

const notifications = ref([])
const unreadCount = ref(0)
const isOpen = ref(false)
let pollInterval = null

const fetchNotifications = async () => {
  try {
    const token = localStorage.getItem('candycutz_auth_token')
    if (!token) return
    
    const res = await fetch('http://localhost:8000/notifications', {
      headers: { 'Authorization': `Bearer ${token}` }
    })
    
    if (res.ok) {
      const data = await res.json()
      notifications.value = data.data
      unreadCount.value = data.data.filter(n => !n.is_read).length
    }
  } catch (err) {
    console.error('Failed to fetch notifications:', err)
  }
}

const markAsRead = async (id) => {
  try {
    const token = localStorage.getItem('candycutz_auth_token')
    await fetch(`http://localhost:8000/notifications/${id}/read`, {
      method: 'PATCH',
      headers: { 'Authorization': `Bearer ${token}` }
    })
    // Optimistic update
    const n = notifications.value.find(n => n.id === id)
    if (n) {
      n.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
  } catch (err) {
    console.error(err)
  }
}

const markAllRead = () => {
  notifications.value.filter(n => !n.is_read).forEach(n => markAsRead(n.id))
}

const toggleOpen = () => {
  isOpen.value = !isOpen.value
}

// Close when clicking outside
const closeOnClickOutside = (e) => {
  if (isOpen.value && !e.target.closest('.notification-bell')) {
    isOpen.value = false
  }
}

onMounted(() => {
  fetchNotifications()
  pollInterval = setInterval(fetchNotifications, 30000) // Poll every 30s
  document.addEventListener('click', closeOnClickOutside)
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
  document.removeEventListener('click', closeOnClickOutside)
})

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
    <button @click="toggleOpen" class="relative p-2 text-ivory/70 hover:text-gold transition-colors focus:outline-none">
      <BellIcon class="h-6 w-6" />
      <span v-if="unreadCount > 0" class="absolute top-1 right-1 h-4 w-4 bg-red-500 rounded-full flex items-center justify-center text-[10px] font-bold text-white border border-obsidian">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>
    
    <div v-if="isOpen" class="absolute right-0 mt-2 w-80 bg-obsidian border border-theme-border rounded-2xl shadow-[0_10px_40px_rgba(0,0,0,0.8)] z-50 overflow-hidden transform transition-all">
      <div class="p-4 border-b border-white/5 flex justify-between items-center bg-white/5">
        <h3 class="font-bold text-ivory">Notifications</h3>
        <button v-if="unreadCount > 0" @click.stop="markAllRead" class="text-xs text-gold hover:text-gold/80 transition-colors">Mark all read</button>
      </div>
      
      <div class="max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="p-8 text-center text-ivory/50 text-sm">
          No notifications yet.
        </div>
        <div v-else>
          <div v-for="notif in notifications" :key="notif.id" 
               class="p-4 border-b border-white/5 hover:bg-white/5 transition-colors cursor-pointer group flex items-start gap-3"
               :class="{ 'bg-white/5': !notif.is_read }"
               @click="markAsRead(notif.id)">
               
            <div class="mt-1 flex-shrink-0">
              <div class="h-2 w-2 rounded-full" :class="notif.is_read ? 'bg-transparent' : 'bg-gold'"></div>
            </div>
            
            <div class="flex-1">
              <h4 class="text-sm font-semibold" :class="notif.is_read ? 'text-ivory/70' : 'text-ivory'">{{ notif.title }}</h4>
              <p class="text-xs text-ivory/50 mt-1 line-clamp-2">{{ notif.message }}</p>
              <span class="text-[10px] text-ivory/30 mt-2 block">{{ new Date(notif.created_at).toLocaleString() }}</span>
            </div>
            
            <button v-if="!notif.is_read" class="opacity-0 group-hover:opacity-100 transition-opacity text-gold hover:text-white" title="Mark as read">
              <CheckIcon class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
      
      <div class="p-3 border-t border-white/5 bg-white/[0.02]">
        <button @click="viewAll" class="w-full text-center text-sm font-semibold text-gold hover:text-white transition-colors py-2">
          View all notifications
        </button>
      </div>
    </div>
  </div>
</template>
