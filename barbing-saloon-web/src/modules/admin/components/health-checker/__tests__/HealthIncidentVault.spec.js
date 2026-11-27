import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import HealthIncidentVault from '../HealthIncidentVault.vue'

describe('HealthIncidentVault.vue', () => {
  it('renders all clear state when there are no quarantined tasks', () => {
    const wrapper = mount(HealthIncidentVault, {
      props: {
        failedJobs: [],
        circuitBreakers: {
          paystack: { state: 'closed', failures_count: 0 },
        },
      },
    })

    expect(wrapper.text()).toContain('Incident Vault Clear')
    expect(wrapper.text()).toContain('Vault All Clear')
    expect(wrapper.text()).toContain('paystack:')
    expect(wrapper.text()).toContain('closed')
  })

  it('renders quarantined tasks with retry and dismiss actions', async () => {
    const failedJobs = [
      {
        id: 101,
        command_name: 'SendAppointmentReminderJob',
        queue: 'default',
        error_summary: 'Connection timed out on smtp.resend.com:587',
        failed_at: '2026-11-22T03:00:00Z',
      },
    ]

    const circuitBreakers = {
      resend: { state: 'open', failures_count: 5 },
      paystack: { state: 'closed', failures_count: 0 },
    }

    const wrapper = mount(HealthIncidentVault, {
      props: {
        failedJobs,
        circuitBreakers,
      },
    })

    expect(wrapper.text()).toContain('1 Quarantined Tasks')
    expect(wrapper.text()).toContain('SendAppointmentReminderJob')
    expect(wrapper.text()).toContain('Connection timed out on smtp.resend.com:587')

    const buttons = wrapper.findAll('button')
    const retryButton = buttons.find((b) => b.text().includes('Retry Task'))
    expect(retryButton).toBeDefined()
    await retryButton.trigger('click')

    expect(wrapper.emitted('retry-job')).toBeTruthy()
    expect(wrapper.emitted('retry-job')[0]).toEqual([101])

    const dismissButton = buttons.find((b) => b.text().includes('Dismiss'))
    expect(dismissButton).toBeDefined()
    await dismissButton.trigger('click')

    expect(wrapper.emitted('dismiss-job')).toBeTruthy()
    expect(wrapper.emitted('dismiss-job')[0]).toEqual([101])

    const resetBreakerButton = buttons.find((b) => b.text().trim() === 'Reset')
    expect(resetBreakerButton).toBeDefined()
    await resetBreakerButton.trigger('click')

    expect(wrapper.emitted('reset-breaker')).toBeTruthy()
    expect(wrapper.emitted('reset-breaker')[0]).toEqual(['resend'])
  })

  it('renders database fallback incidents with error details and copy action', async () => {
    const fallbackIncidents = [
      {
        timestamp: '2026-11-22T02:55:28Z',
        error_code: 'SCHEMA_DRIFT',
        message: "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'draft_expires_at'",
        sql: 'select * from appointments where draft_expires_at is null',
        route_uri: 'api/v1/available-slots',
        is_connection_failure: false,
      },
    ]

    const wrapper = mount(HealthIncidentVault, {
      props: {
        failedJobs: [],
        fallbackIncidents,
      },
    })

    expect(wrapper.text()).toContain('Database Disconnects & Disk Fallback Log')
    expect(wrapper.text()).toContain('SCHEMA_DRIFT')
    expect(wrapper.text()).toContain('draft_expires_at')
    expect(wrapper.text()).toContain('Route: api/v1/available-slots')

    const copyBtn = wrapper.findAll('button').find((b) => b.text().includes('Copy Error'))
    expect(copyBtn).toBeDefined()
  })
})
