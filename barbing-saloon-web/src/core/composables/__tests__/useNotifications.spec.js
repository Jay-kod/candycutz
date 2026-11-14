import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useNotifications } from '../useNotifications'
import { getLabelForType, getIconColorForType, getBgColorForType } from '../../components/notifications/notificationUtils'
import api from '@/shared/api/client'

vi.mock('@/shared/api/client', () => ({
  default: {
    get: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}))

describe('useNotifications composable', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    localStorage.setItem('candycutz_auth_token', 'mock-token')
  })

  it('initializes with reactive state and exposes helper methods', () => {
    const { notifications, unreadCount, isLoading, markAsRead, markAllRead, deleteNotification } = useNotifications()

    expect(Array.isArray(notifications.value)).toBe(true)
    expect(typeof unreadCount.value).toBe('number')
    expect(typeof isLoading.value).toBe('boolean')
    expect(typeof markAsRead).toBe('function')
    expect(typeof markAllRead).toBe('function')
    expect(typeof deleteNotification).toBe('function')
  })

  it('fetches notifications and synchronizes unread count', async () => {
    api.get.mockResolvedValueOnce({
      data: {
        success: true,
        data: [
          { id: 101, title: 'Booking Confirmed', type: 'booking_created', is_read: false },
          { id: 102, title: 'Payment Confirmed', type: 'payment_verified', is_read: true },
        ],
      },
    })

    const { notifications, unreadCount, fetchNotifications } = useNotifications()
    await fetchNotifications()

    expect(notifications.value).toHaveLength(2)
    expect(unreadCount.value).toBe(1)
    expect(api.get).toHaveBeenCalledWith('/v1/notifications')
  })

  it('optimistically marks a notification as read and decrements unread count', async () => {
    api.patch.mockResolvedValueOnce({ data: { success: true } })

    const { notifications, unreadCount, markAsRead } = useNotifications()
    notifications.value = [
      { id: 201, title: 'Test 1', is_read: false },
      { id: 202, title: 'Test 2', is_read: false },
    ]
    unreadCount.value = 2

    await markAsRead(201)

    expect(notifications.value.find((n) => n.id === 201).is_read).toBe(true)
    expect(unreadCount.value).toBe(1)
    expect(api.patch).toHaveBeenCalledWith('/v1/notifications/201/read')
  })

  it('optimistically marks all notifications as read', async () => {
    api.patch.mockResolvedValueOnce({ data: { success: true } })

    const { notifications, unreadCount, markAllRead } = useNotifications()
    notifications.value = [
      { id: 301, title: 'Test 1', is_read: false },
      { id: 302, title: 'Test 2', is_read: false },
    ]
    unreadCount.value = 2

    await markAllRead()

    expect(notifications.value.every((n) => n.is_read)).toBe(true)
    expect(unreadCount.value).toBe(0)
    expect(api.patch).toHaveBeenCalledWith('/v1/notifications/read-all')
  })

  it('optimistically deletes notification and updates unread count', async () => {
    api.delete.mockResolvedValueOnce({ data: { success: true } })

    const { notifications, unreadCount, deleteNotification } = useNotifications()
    notifications.value = [
      { id: 401, title: 'Test 1', is_read: false },
      { id: 402, title: 'Test 2', is_read: true },
    ]
    unreadCount.value = 1

    await deleteNotification(401)

    expect(notifications.value).toHaveLength(1)
    expect(notifications.value[0].id).toBe(402)
    expect(unreadCount.value).toBe(0)
    expect(api.delete).toHaveBeenCalledWith('/v1/notifications/401')
  })
})

describe('notificationUtils mapping', () => {
  it('correctly maps labels for multi-party booking and payment event types', () => {
    expect(getLabelForType('booking_created')).toBe('New Booking')
    expect(getLabelForType('booking_approved')).toBe('Approved')
    expect(getLabelForType('booking_cancelled')).toBe('Cancelled')
    expect(getLabelForType('booking_completed')).toBe('Completed')
    expect(getLabelForType('booking_no_show')).toBe('No-Show')
    expect(getLabelForType('payment_verified')).toBe('Confirmed')
    expect(getLabelForType('payment_failed')).toBe('Failed')
  })

  it('assigns danger styles to cancelled and failed events', () => {
    expect(getIconColorForType('booking_cancelled')).toContain('color-danger')
    expect(getBgColorForType('booking_cancelled')).toContain('red')
    expect(getIconColorForType('payment_failed')).toContain('color-danger')
    expect(getBgColorForType('payment_failed')).toContain('red')
  })

  it('assigns positive emerald styles to completed and verified events', () => {
    expect(getIconColorForType('booking_completed')).toContain('emerald')
    expect(getBgColorForType('booking_completed')).toContain('emerald')
    expect(getIconColorForType('payment_verified')).toContain('emerald')
    expect(getBgColorForType('payment_verified')).toContain('emerald')
  })
})

