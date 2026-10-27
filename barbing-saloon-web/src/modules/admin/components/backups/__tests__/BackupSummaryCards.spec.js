import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BackupSummaryCards from '../BackupSummaryCards.vue'

describe('BackupSummaryCards.vue', () => {
  const mockFormatTime = (iso) => (iso ? 'Oct 27, 2026, 3:30 PM' : '—')

  it('renders all 4 redundancy metric cards accurately', () => {
    const summary = {
      total: 12,
      valid: 11,
      totalSize: '45.80 MB',
      latestTime: '2026-10-27T15:30:00Z',
    }

    const wrapper = mount(BackupSummaryCards, {
      props: {
        summary,
        loading: false,
        formatTime: mockFormatTime,
      },
    })

    const text = wrapper.text()
    expect(text).toContain('Total Snapshots')
    expect(text).toContain('12')
    expect(text).toContain('Verified Archives')
    expect(text).toContain('11')
    expect(text).toContain('Total Footprint')
    expect(text).toContain('45.80 MB')
    expect(text).toContain('Latest Recovery Point')
    expect(text).toContain('Oct 27, 2026, 3:30 PM')
  })

  it('renders 4 skeleton cards when loading is true', () => {
    const wrapper = mount(BackupSummaryCards, {
      props: {
        summary: {},
        loading: true,
        formatTime: mockFormatTime,
      },
    })

    expect(wrapper.findAll('.animate-pulse').length).toBe(4)
    expect(wrapper.text()).not.toContain('Total Snapshots')
  })
})

