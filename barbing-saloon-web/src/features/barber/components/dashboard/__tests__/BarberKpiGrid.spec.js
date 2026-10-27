import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BarberKpiGrid from '../BarberKpiGrid.vue'

describe('BarberKpiGrid.vue', () => {
  it('renders all 4 barber KPI cards accurately', () => {
    const stats = {
      today_bookings: 8,
      upcoming_bookings: 14,
      completed_bookings: 5,
      no_show_count: 1,
    }

    const wrapper = mount(BarberKpiGrid, {
      props: { stats },
    })

    const text = wrapper.text()
    expect(text).toContain("Today's Bookings")
    expect(text).toContain('8')
    expect(text).toContain('Upcoming')
    expect(text).toContain('14')
    expect(text).toContain('Completed')
    expect(text).toContain('5')
    expect(text).toContain('No Shows')
    expect(text).toContain('1')
  })

  it('handles empty stats gracefully', () => {
    const wrapper = mount(BarberKpiGrid, {
      props: { stats: {} },
    })

    const text = wrapper.text()
    expect(text).toContain("Today's Bookings")
    expect(text).toContain('0')
    expect(text).toContain('Upcoming')
    expect(text).toContain('Completed')
    expect(text).toContain('No Shows')
  })
})
