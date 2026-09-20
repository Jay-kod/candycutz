import { ref, onMounted, onUnmounted } from 'vue'
import api from '@/shared/api/client'

// Global shared state so all components see the same unread count
const notifications = ref([])
const unreadCount = ref(0)
const isLoading = ref(false)
let pollTimer = null
let knownNotificationIds = new Set()
let isInitialized = false

/**
 * Play a subtle luxury chime using Web Audio API (zero external assets required)
 */
function playChime() {
  try {
    const AudioCtx = window.AudioContext || window.webkitAudioContext
    if (!AudioCtx) return
    const ctx = new AudioCtx()

    const now = ctx.currentTime
    // Note 1: E5 (659.25 Hz)
    const osc1 = ctx.createOscillator()
    const gain1 = ctx.createGain()
    osc1.type = 'sine'
    osc1.frequency.setValueAtTime(659.25, now)
    gain1.gain.setValueAtTime(0.08, now)
    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3)
    osc1.connect(gain1)
    gain1.connect(ctx.destination)
    osc1.start(now)
    osc1.stop(now + 0.3)

    // Note 2: B5 (987.77 Hz) slightly delayed for pleasant luxury chime
    const osc2 = ctx.createOscillator()
    const gain2 = ctx.createGain()
    osc2.type = 'sine'
    osc2.frequency.setValueAtTime(987.77, now + 0.1)
    gain2.gain.setValueAtTime(0.08, now + 0.1)
    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.45)
    osc2.connect(gain2)
    gain2.connect(ctx.destination)
    osc2.start(now + 0.1)
    osc2.stop(now + 0.45)
  } catch (e) {
    // AudioContext blocked or not supported
  }
}

/**
 * Show native browser desktop notification if permitted
 */
function showDesktopNotification(title, body) {
  try {
    if (typeof window === 'undefined' || !('Notification' in window)) return
    if (Notification.permission === 'granted') {
      new Notification(title || 'CandyCutz', {
        body: body || 'You have a new update.',
        icon: '/favicon.ico',
      })
    }
  } catch (e) {
    // Notifications not permitted or error
  }
}

export function useNotifications() {
  const fetchNotifications = async () => {
    try {
      const token = localStorage.getItem('candycutz_auth_token')
      if (!token) return

      isLoading.value = true
      const res = await api.get('/v1/notifications')
      const list = res.data?.data || []
      notifications.value = list
      unreadCount.value = list.filter((n) => !n.is_read).length

      // Check for new incoming notifications to play sound / show desktop notification
      if (isInitialized) {
        const newItems = list.filter((n) => !n.is_read && !knownNotificationIds.has(n.id))
        if (newItems.length > 0) {
          playChime()
          const latest = newItems[0]
          showDesktopNotification(latest.title, latest.message)
        }
      }

      // Record known IDs
      knownNotificationIds = new Set(list.map((n) => n.id))
      isInitialized = true
    } catch (err) {
      console.error('Failed to fetch notifications:', err)
    } finally {
      isLoading.value = false
    }
  }

  const fetchUnreadCount = async () => {
    try {
      const token = localStorage.getItem('candycutz_auth_token')
      if (!token) return

      const res = await api.get('/v1/notifications/unread-count')
      const count = res.data?.data?.count ?? 0

      // If count increased, fetch full notifications to get the new item details
      if (count > unreadCount.value) {
        await fetchNotifications()
      } else {
        unreadCount.value = count
      }
    } catch (err) {
      // Fallback silently on network errors
    }
  }

  const markAsRead = async (id) => {
    try {
      // Optimistic update
      const n = notifications.value.find((item) => item.id === id)
      if (n && !n.is_read) {
        n.is_read = true
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
      await api.patch(`/v1/notifications/${id}/read`)
    } catch (err) {
      console.error('Failed to mark notification as read:', err)
    }
  }

  const markAllRead = async () => {
    try {
      // Optimistic update
      notifications.value.forEach((n) => {
        n.is_read = true
      })
      unreadCount.value = 0
      await api.patch('/v1/notifications/read-all')
    } catch (err) {
      console.error('Failed to mark all as read:', err)
    }
  }

  const deleteNotification = async (id) => {
    try {
      const index = notifications.value.findIndex((n) => n.id === id)
      if (index !== -1) {
        const wasUnread = !notifications.value[index].is_read
        notifications.value.splice(index, 1)
        if (wasUnread) {
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
      }
      await api.delete(`/v1/notifications/${id}`)
    } catch (err) {
      console.error('Failed to delete notification:', err)
    }
  }

  const requestBrowserPermission = async () => {
    if (typeof window === 'undefined' || !('Notification' in window)) return false
    if (Notification.permission === 'granted') return true
    if (Notification.permission !== 'denied') {
      const permission = await Notification.requestPermission()
      return permission === 'granted'
    }
    return false
  }

  const startPolling = (intervalMs = 30000) => {
    if (pollTimer) return
    fetchUnreadCount()
    pollTimer = setInterval(fetchUnreadCount, intervalMs)
  }

  const stopPolling = () => {
    if (pollTimer) {
      clearInterval(pollTimer)
      pollTimer = null
    }
  }

  return {
    notifications,
    unreadCount,
    isLoading,
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllRead,
    deleteNotification,
    requestBrowserPermission,
    playChime,
    startPolling,
    stopPolling,
  }
}
