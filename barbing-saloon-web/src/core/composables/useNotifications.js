import { ref } from 'vue'
import api from '@/shared/api/client'
import { API_V1_BASE_URL } from '../utils/url'

// Global shared state so all components (header bell, view, portals) share identical reactive state
const notifications = ref([])
const unreadCount = ref(0)
const isLoading = ref(false)
const isRefreshing = ref(false)
let pollTimer = null
let subscriberCount = 0
let knownNotificationIds = new Set()
let isInitialized = false
let currentIntervalMs = 15000
let visibilityListenerAttached = false
let sseAbortController = null
let sseReconnectTimer = null
let lastSeenSseId = 0

function handleVisibilityChange() {
  if (typeof document === 'undefined') return
  if (document.hidden) {
    if (pollTimer) {
      clearInterval(pollTimer)
      pollTimer = null
    }
  } else {
    if (subscriberCount > 0) {
      useNotifications().fetchUnreadCount()
      if (!pollTimer) {
        pollTimer = setInterval(() => useNotifications().fetchUnreadCount(), currentIntervalMs)
      }
      useNotifications().connectStream()
    }
  }
}

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

/**
 * Hard reset of all notifications reactive state.
 * Prevents cross-account state bleeding upon logout or switching users.
 */
export function resetNotifications() {
  notifications.value = []
  unreadCount.value = 0
  isLoading.value = false
  isRefreshing.value = false
  knownNotificationIds.clear()
  isInitialized = false
  lastSeenSseId = 0
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
  if (sseAbortController) {
    sseAbortController.abort()
    sseAbortController = null
  }
  if (sseReconnectTimer) {
    clearTimeout(sseReconnectTimer)
    sseReconnectTimer = null
  }
  if (visibilityListenerAttached && typeof document !== 'undefined') {
    document.removeEventListener('visibilitychange', handleVisibilityChange)
    visibilityListenerAttached = false
  }
  subscriberCount = 0
}

export function useNotifications() {
  /**
   * Fetch full notifications list.
   * Supports silent background refresh to prevent UI flickering.
   */
  const fetchNotifications = async ({ silent = false } = {}) => {
    try {
      const token = localStorage.getItem('candycutz_auth_token') || localStorage.getItem('token')
      if (!token) {
        resetNotifications()
        return
      }

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

  /**
   * Instant optimistic update when clearing all notifications
   */
  const clearAll = async () => {
    try {
      notifications.value = []
      unreadCount.value = 0
      knownNotificationIds.clear()
      await api.delete('/v1/notifications')
    } catch (err) {
      console.error('Failed to clear all notifications:', err)
      await fetchNotifications({ silent: true })
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
   * Connect to Server-Sent Events stream for immediate real-time notifications
   */
  const connectStream = () => {
    if (typeof window === 'undefined' || typeof fetch === 'undefined') return
    const token = localStorage.getItem('candycutz_auth_token') || localStorage.getItem('token')
    if (!token) return

    if (sseAbortController) {
      sseAbortController.abort()
      sseAbortController = null
    }

    sseAbortController = new AbortController()
    const signal = sseAbortController.signal

    const streamUrl = lastSeenSseId > 0
      ? `${API_V1_BASE_URL}/notifications/stream?last_event_id=${lastSeenSseId}`
      : `${API_V1_BASE_URL}/notifications/stream`

    const headers = {
      Authorization: `Bearer ${token}`,
      Accept: 'text/event-stream',
    }
    if (lastSeenSseId > 0) {
      headers['Last-Event-ID'] = String(lastSeenSseId)
    }

    fetch(streamUrl, {
      headers,
      signal,
    })
      .then(async (response) => {
        if (!response.ok || !response.body) return

        const reader = response.body.getReader()
        const decoder = new TextDecoder()
        let buffer = ''

        while (!signal.aborted) {
          const { value, done } = await reader.read()
          if (done) break

          buffer += decoder.decode(value, { stream: true })
          const blocks = buffer.split('\n\n')
          buffer = blocks.pop() || ''

          for (const block of blocks) {
            // Track cursor ID
            const idLine = block.split('\n').find((l) => l.startsWith('id: '))
            if (idLine) {
              const parsedId = parseInt(idLine.slice(4).trim(), 10)
              if (!isNaN(parsedId)) {
                lastSeenSseId = Math.max(lastSeenSseId, parsedId)
              }
            }

            if (block.includes('event: notification')) {
              const dataLine = block.split('\n').find((l) => l.startsWith('data: '))
              if (dataLine) {
                try {
                  const payload = JSON.parse(dataLine.slice(6))
                  const items = Array.isArray(payload) ? payload : [payload]
                  for (const item of items) {
                    if (!knownNotificationIds.has(item.id)) {
                      notifications.value.unshift(item)
                      knownNotificationIds.add(item.id)
                      if (!item.is_read) {
                        unreadCount.value++
                      }
                      if (item.id && typeof item.id === 'number') {
                        lastSeenSseId = Math.max(lastSeenSseId, item.id)
                      }
                      playChime()
                      showDesktopNotification(item.title, item.message)
                    }
                  }
                } catch (e) {
                  void e
                }
              }
            }
          }
        }
      })
      .catch((err) => {
        if (err.name !== 'AbortError') {
          // SSE stream dropped or unsupported, fallback polling remains active
        }
      })
      .finally(() => {
        if (subscriberCount > 0 && !signal.aborted) {
          if (sseReconnectTimer) clearTimeout(sseReconnectTimer)
          sseReconnectTimer = setTimeout(() => {
            if (subscriberCount > 0 && !signal.aborted) {
              connectStream()
            }
          }, 2000)
        }
      })
  }

  /**
   * Reference-counted polling: multiple components can subscribe without duplicate timers
   */
  const startPolling = (intervalMs = 15000) => {
    currentIntervalMs = intervalMs
    subscriberCount++
    if (!visibilityListenerAttached && typeof document !== 'undefined') {
      document.addEventListener('visibilitychange', handleVisibilityChange)
      visibilityListenerAttached = true
    }
    if (!pollTimer) {
      fetchUnreadCount()
      pollTimer = setInterval(fetchUnreadCount, intervalMs)
      connectStream()
    }
  }

  const stopPolling = () => {
    subscriberCount = Math.max(0, subscriberCount - 1)
    if (subscriberCount === 0) {
      if (pollTimer) {
        clearInterval(pollTimer)
        pollTimer = null
      }
      if (sseAbortController) {
        sseAbortController.abort()
        sseAbortController = null
      }
      if (sseReconnectTimer) {
        clearTimeout(sseReconnectTimer)
        sseReconnectTimer = null
      }
      if (visibilityListenerAttached && typeof document !== 'undefined') {
        document.removeEventListener('visibilitychange', handleVisibilityChange)
        visibilityListenerAttached = false
      }
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
    clearAll,
    requestBrowserPermission,
    playChime,
    startPolling,
    stopPolling,
    connectStream,
    resetNotifications,
  }
}
