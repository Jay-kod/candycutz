import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import SettingsTabs from '../SettingsTabs.vue'

describe('SettingsTabs.vue', () => {
  const categories = [
    { id: 'general', label: 'General & Business' },
    { id: 'booking', label: 'Booking & Policies' },
    { id: 'contact', label: 'Contact & Location' },
  ]
  const categoryCounts = { general: 6, booking: 5, contact: 8 }

  it('renders all category tabs with correct counts', () => {
    const wrapper = mount(SettingsTabs, {
      props: {
        categories,
        activeTab: 'general',
        categoryCounts,
        searchQuery: '',
      },
    })

    const text = wrapper.text()
    expect(text).toContain('General & Business')
    expect(text).toContain('6')
    expect(text).toContain('Booking & Policies')
    expect(text).toContain('5')
    expect(text).toContain('Contact & Location')
    expect(text).toContain('8')
  })

  it('emits update:activeTab when a tab button is clicked', async () => {
    const wrapper = mount(SettingsTabs, {
      props: {
        categories,
        activeTab: 'general',
        categoryCounts,
        searchQuery: '',
      },
    })

    const buttons = wrapper.findAll('button')
    // Click on the second tab: Booking & Policies
    await buttons[1].trigger('click')

    expect(wrapper.emitted('update:activeTab')).toBeTruthy()
    expect(wrapper.emitted('update:activeTab')[0]).toEqual(['booking'])
  })

  it('emits update:searchQuery when typing in the search filter', async () => {
    const wrapper = mount(SettingsTabs, {
      props: {
        categories,
        activeTab: 'general',
        categoryCounts,
        searchQuery: '',
      },
    })

    const input = wrapper.find('input[type="text"]')
    await input.setValue('deposit')

    expect(wrapper.emitted('update:searchQuery')).toBeTruthy()
    expect(wrapper.emitted('update:searchQuery')[0]).toEqual(['deposit'])
  })
})
