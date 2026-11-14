import { ref } from 'vue'
import api from '@/shared/api/client'

// Global shared state so all components (header bell, view, portals) share identical reactive state
const notifications = ref([])
const unreadCount = ref(0)
const isLoading = ref(false)
const isRefreshing = ref(false)
let pollTimer = null
let subscriberCount = 0
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
    void e // AudioContext blocked by browser policy or not supported
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
    void e // Desktop notifications not permitted or unsupported
  }
}

export function useNotifications() {
  /**
   * Fetch full notifications list.
   * Supports silent background refresh to prevent UI flickering.
   */
  const fetchNotifications = async ({ silent = false } = {}) => {
    try {
      const token = localStorage.getItem('candycutz_auth_token') || localStorage.getItem('token')
      if (!token) return

      if (silent) {
        isRefreshing.value = true
      } else {
        isLoading.value = true
      }

      const res = await api.get('/v1/notifications')
      const list = res.data?.data || []
      notifications.value = list
      unreadCount.value = list.filter((n) => !n.is_read).length

      // Detect new incoming unread notifications to trigger auditory chime and desktop notification
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
      isRefreshing.value = false
    }
  }

  /**
   * Lightweight unread count check (performs fast indexed COUNT query)
   */
  const fetchUnreadCount = async () => {
    try {
      const token = localStorage.getItem('candycutz_auth_token') || localStorage.getItem('token')
      if (!token) return

      const res = await api.get('/v1/notifications/unread-count')
      const count = res.data?.data?.count ?? 0

      // If count increased or changed, silently re-fetch full payload
      if (count > unreadCount.value) {
        await fetchNotifications({ silent: true })
      } else {
        unreadCount.value = count
      }
    } catch (err) {
      void err // Fallback silently on network errors
    }
  }

  /**
   * Instant optimistic update when marking a single notification as read
   */
  const markAsRead = async (id) => {
    try {
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

  /**
   * Instant optimistic update when marking all notifications as read
   */
  const markAllRead = async () => {
    try {
      notifications.value.forEach((n) => {
        n.is_read = true
      })
      unreadCount.value = 0
      await api.patch('/v1/notifications/read-all')
    } catch (err) {
      console.error('Failed to mark all as read:', err)
    }
  }

  /**
   * Instant optimistic update when deleting a notification
   */
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

  /**
   * Reference-counted polling: multiple components can subscribe without duplicate timers
   */
  const startPolling = (intervalMs = 15000) => {
    subscriberCount++
    if (!pollTimer) {
      fetchUnreadCount()
      pollTimer = setInterval(fetchUnreadCount, intervalMs)
    }
  }

  const stopPolling = () => {
    subscriberCount = Math.max(0, subscriberCount - 1)
    if (subscriberCount === 0 && pollTimer) {
      clearInterval(pollTimer)
      pollTimer = null
    }
  }

  return {
    notifications,
    unreadCount,
    isLoading,
    isRefreshing,
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
