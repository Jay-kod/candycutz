import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import HealthKPICards from '../HealthKPICards.vue'
import { markRaw, h } from 'vue'

describe('HealthKPICards.vue', () => {
  const dummyIcon = markRaw({ render: () => h('span', 'icon') })
  const mockStatusColor = (s) => (s === 'connected' ? 'text-emerald-400' : 'text-zinc-400')
  const mockStatusBg = (s) => (s === 'connected' ? 'bg-emerald-500/10' : 'bg-white/5')
  const mockStatusIcon = () => dummyIcon

  it('renders all 8 KPI cards with labels, status pills and values', () => {
    const cards = [
      { label: 'Database Core', value: 'Connected', status: 'connected', icon: dummyIcon },
      { label: 'Open Errors', value: '3', status: 'operational', icon: dummyIcon },
      { label: 'Critical Anomalies', value: '0', status: 'operational', icon: dummyIcon },
      { label: '5xx Events (24h)', value: '1', status: 'operational', icon: dummyIcon },
      { label: 'Telemetry Hits (24h)', value: '1,420', status: 'operational', icon: dummyIcon },
      { label: 'System Snapshots', value: '4', status: 'operational', icon: dummyIcon },
      { label: 'Active Sessions', value: '18', status: 'operational', icon: dummyIcon },
      { label: 'Latest Snapshot', value: 'Oct 27, 2026', status: 'operational', icon: dummyIcon },
    ]

    const wrapper = mount(HealthKPICards, {
      props: {
        cards,
        loading: false,
        statusColor: mockStatusColor,
        statusBg: mockStatusBg,
        statusIcon: mockStatusIcon,
      },
    })

    const text = wrapper.text()
    expect(text).toContain('Database Core')
    expect(text).toContain('Connected')
    expect(text).toContain('Open Errors')
    expect(text).toContain('3')
    expect(text).toContain('Critical Anomalies')
    expect(text).toContain('5xx Events (24h)')
    expect(text).toContain('Telemetry Hits (24h)')
    expect(text).toContain('1,420')
    expect(text).toContain('System Snapshots')
    expect(text).toContain('Active Sessions')
    expect(text).toContain('18')
    expect(text).toContain('Latest Snapshot')
    expect(text).toContain('Oct 27, 2026')
  })

  it('renders skeleton shimmer state when loading is true', () => {
    const wrapper = mount(HealthKPICards, {
      props: {
        cards: [],
        loading: true,
        statusColor: mockStatusColor,
        statusBg: mockStatusBg,
        statusIcon: mockStatusIcon,
      },
    })

    expect(wrapper.findAll('.animate-pulse').length).toBe(8)
    expect(wrapper.text()).not.toContain('Database Core')
  })
})

