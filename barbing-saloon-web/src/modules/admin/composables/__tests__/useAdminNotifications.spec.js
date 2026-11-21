import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useAdminNotifications } from '../useAdminNotifications'
import { useNotifications, resetNotifications } from '@/core/composables/useNotifications'
import api from '@/shared/api/client'

vi.mock('@/shared/api/client', () => ({
  default: {
    get: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}))

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
    info: vi.fn(),
    warning: vi.fn(),
  }),
}))

describe('useAdminNotifications composable', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    resetNotifications()
    localStorage.setItem('candycutz_auth_token', 'mock-admin-token')
  })

  it('shares reactive notification state with the global useNotifications', () => {
    const admin = useAdminNotifications()
    const globalState = useNotifications()

    globalState.notifications.value = [
      { id: 1, title: 'New Booking', type: 'booking_created', is_read: false, created_at: new Date().toISOString() },
      { id: 2, title: 'Payment Confirmed', type: 'payment_verified', is_read: true, created_at: new Date().toISOString() },
    ]
    globalState.unreadCount.value = 1

    expect(admin.notifications.value).toHaveLength(2)
    expect(admin.unreadCount.value).toBe(1)
  })

  it('computes stats accurately from notifications', () => {
    const admin = useAdminNotifications()
    const globalState = useNotifications()

    globalState.notifications.value = [
      { id: 1, title: 'New Booking', type: 'booking_created', is_read: false, created_at: new Date().toISOString() },
      { id: 2, title: 'Payment Confirmed', type: 'payment_verified', is_read: false, created_at: new Date().toISOString() },
      { id: 3, title: 'System Notice', type: 'system_update', is_read: true, created_at: new Date().toISOString() },
    ]
    globalState.unreadCount.value = 2

    const stats = admin.stats.value
    expect(stats.total).toBe(3)
    expect(stats.unread).toBe(2)
    expect(stats.bookings).toBe(1)
    expect(stats.alerts).toBe(2) // payment + system
  })

  it('filters notifications by type accurately', () => {
    const admin = useAdminNotifications()
    const globalState = useNotifications()

    globalState.notifications.value = [
      { id: 1, title: 'New Booking', type: 'booking_created', is_read: false, created_at: new Date().toISOString() },
      { id: 2, title: 'Payment Received', type: 'payment_received', is_read: true, created_at: new Date().toISOString() },
      { id: 3, title: 'System Reboot', type: 'system_update', is_read: false, created_at: new Date().toISOString() },
    ]

    admin.activeTypeFilter.value = 'booking'
    expect(admin.filteredNotifications.value).toHaveLength(1)
    expect(admin.filteredNotifications.value[0].id).toBe(1)

    admin.activeTypeFilter.value = 'payment'
    expect(admin.filteredNotifications.value).toHaveLength(1)
    expect(admin.filteredNotifications.value[0].id).toBe(2)

    admin.clearFilters()
    expect(admin.filteredNotifications.value).toHaveLength(3)
  })

  it('delegates markAsRead and markAllRead seamlessly', async () => {
    api.patch.mockResolvedValue({ data: { success: true } })

    const admin = useAdminNotifications()
    const globalState = useNotifications()

    globalState.notifications.value = [
      { id: 10, title: 'Test 10', is_read: false, created_at: new Date().toISOString() },
      { id: 11, title: 'Test 11', is_read: false, created_at: new Date().toISOString() },
    ]
    globalState.unreadCount.value = 2

    await admin.markAsRead(10)
    expect(globalState.notifications.value.find((n) => n.id === 10).is_read).toBe(true)
    expect(admin.unreadCount.value).toBe(1)

    await admin.markAllRead()
    expect(globalState.notifications.value.every((n) => n.is_read)).toBe(true)
    expect(admin.unreadCount.value).toBe(0)
  })
})
