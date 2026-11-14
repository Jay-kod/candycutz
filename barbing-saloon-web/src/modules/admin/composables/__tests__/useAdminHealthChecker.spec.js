import { beforeEach, describe, expect, it, vi } from 'vitest'

const mocks = vi.hoisted(() => ({
  client: {
    get: vi.fn(),
    post: vi.fn(),
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
  })

  it('refreshes overview, subsystem details, and all live probes without database writes', async () => {
    const health = useAdminHealthChecker()

    await expect(health.refreshAll()).resolves.toBe(true)

    expect(mocks.client.get).toHaveBeenCalledWith('/admin/system/overview')
    expect(mocks.client.get).toHaveBeenCalledWith('/admin/system/health/detail')
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

    expect(mocks.client.get).toHaveBeenCalledTimes(2)
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
})
