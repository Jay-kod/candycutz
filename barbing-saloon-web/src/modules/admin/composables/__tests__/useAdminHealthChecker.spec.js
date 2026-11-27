import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  client: {
    get: vi.fn(),
    post: vi.fn(),
    delete: vi.fn(),
  },
  toast: {
    error: vi.fn(),
    success: vi.fn(),
    info: vi.fn(),
  },
}))

vi.mock('vue', async (importOriginal) => {
  const vue = await importOriginal()
  return {
    ...vue,
    onMounted: vi.fn(),
    onUnmounted: vi.fn(),
  }
})

vi.mock('@/shared/api/client', () => ({ default: mocks.client }))
vi.mock('@/core/composables/useToast', () => ({ useToast: () => mocks.toast }))

import { useAdminHealthChecker } from '../useAdminHealthChecker'

describe('useAdminHealthChecker', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    mocks.client.get.mockResolvedValue({ data: { data: {} } })
    mocks.client.post.mockResolvedValue({
      data: { data: { all_passed: true, results: {} } },
    })
    mocks.client.delete.mockResolvedValue({
      data: { message: 'Dismissed' },
    })
  })

  it('refreshes overview, subsystem details, failed jobs, and all live probes without database writes', async () => {
    const health = useAdminHealthChecker()

    await expect(health.refreshAll()).resolves.toBe(true)

    expect(mocks.client.get).toHaveBeenCalledWith('/admin/system/overview')
    expect(mocks.client.get).toHaveBeenCalledWith('/admin/system/health/detail')
    expect(mocks.client.get).toHaveBeenCalledWith('/admin/system/failed-jobs')
    expect(mocks.client.post).toHaveBeenCalledWith('/admin/system/health/test', { type: 'all' })
    expect(health.selfTestResults.value.all_passed).toBe(true)
    expect(health.lastRefreshed.value).toBeInstanceOf(Date)
    expect(mocks.toast.success).toHaveBeenCalledWith(
      'All health checks refreshed. No database data was changed.'
    )
  })

  it('reports an incomplete refresh when a diagnostic probe fails', async () => {
    mocks.client.post.mockResolvedValue({
      data: { data: { all_passed: false, results: { database: { passed: false } } } },
    })
    const health = useAdminHealthChecker()

    await expect(health.refreshAll()).resolves.toBe(false)

    expect(health.selfTestResults.value.all_passed).toBe(false)
    expect(mocks.toast.error).toHaveBeenCalledWith(
      'Health refresh incomplete. Review the failed checks and try again.'
    )
    expect(mocks.toast.success).not.toHaveBeenCalled()
  })

  it('does not repeat full probes during lightweight auto-refresh', async () => {
    const health = useAdminHealthChecker()

    await expect(health.refreshAll(true, false)).resolves.toBe(true)

    expect(mocks.client.get).toHaveBeenCalledTimes(3)
    expect(mocks.client.post).not.toHaveBeenCalled()
  })

  it('identifies the active database separately from backup inventory', () => {
    const health = useAdminHealthChecker()
    health.healthDetail.value = {
      checks: {
        database: {
          status: 'connected',
          connection: 'mysql',
          driver: 'mysql',
          database_name: 'candycutz_db',
          tables_count: 35,
        },
      },
    }

    expect(health.healthCards.value[0].items).toContainEqual({
      label: 'Current Database',
      value: 'candycutz_db',
    })
  })

  it('surfaces sweeper daemon and circuit breaker matrix in healthCards', () => {
    const health = useAdminHealthChecker()
    health.healthDetail.value = {
      checks: {
        sweeper: {
          status: 'operational',
          last_heartbeat: '2026-11-22T03:00:00Z',
          last_duration_ms: 12.5,
          recent_cancelled_count: 3,
        },
        circuit_breakers: {
          paystack: { state: 'closed', failures_count: 0 },
          resend: { state: 'open', failures_count: 5 },
        },
      },
    }

    const sweeperCard = health.healthCards.value.find((c) => c.title === 'Draft Sweeper Daemon')
    expect(sweeperCard).toBeDefined()
    expect(sweeperCard.status).toBe('operational')
    expect(sweeperCard.items).toContainEqual({ label: 'Recent Slots Released', value: 3 })

    const breakerCard = health.healthCards.value.find((c) => c.title === 'Circuit Breaker Matrix')
    expect(breakerCard).toBeDefined()
    expect(breakerCard.status).toBe('warning')
    expect(breakerCard.items).toContainEqual({
      label: 'RESEND Status',
      value: 'OPEN (5 errs)',
      warn: true,
    })
  })

  it('safely retries a quarantined failed task and notifies the admin', async () => {
    mocks.client.post.mockResolvedValue({
      data: { message: 'Task #42 queued for retry' },
    })
    const health = useAdminHealthChecker()

    const result = await health.retryJob(42)

    expect(result).toBe(true)
    expect(mocks.client.post).toHaveBeenCalledWith('/admin/system/failed-jobs/42/retry')
    expect(mocks.toast.success).toHaveBeenCalledWith('Task #42 queued for retry')
    expect(mocks.client.get).toHaveBeenCalledWith('/admin/system/failed-jobs')
  })

  it('safely resets an open circuit breaker and refreshes telemetry', async () => {
    mocks.client.post.mockResolvedValue({
      data: { message: 'Circuit breaker for paystack reset' },
    })
    const health = useAdminHealthChecker()

    const result = await health.resetBreaker('paystack')

    expect(result).toBe(true)
    expect(mocks.client.post).toHaveBeenCalledWith('/admin/system/circuit-breakers/paystack/reset')
    expect(mocks.toast.success).toHaveBeenCalledWith('Circuit breaker for paystack reset')
    expect(mocks.client.get).toHaveBeenCalledWith('/admin/system/health/detail')
  })
})
