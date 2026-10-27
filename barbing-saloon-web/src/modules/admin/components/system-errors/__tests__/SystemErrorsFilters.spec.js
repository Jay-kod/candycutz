import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import SystemErrorsFilters from '../SystemErrorsFilters.vue'

describe('SystemErrorsFilters.vue', () => {
  it('renders search input, status, severity and category dropdowns', () => {
    const wrapper = mount(SystemErrorsFilters, {
      props: {
        search: '',
        statusFilter: '',
        severityFilter: '',
        categoryFilter: '',
      },
    })

    expect(wrapper.find('input[type="text"]').exists()).toBe(true)
    const selects = wrapper.findAll('select')
    expect(selects.length).toBe(3)
    expect(wrapper.text()).toContain('All Statuses')
    expect(wrapper.text()).toContain('All Severities')
    expect(wrapper.text()).toContain('All Categories')
  })

  it('emits update:search on typing in search box', async () => {
    const wrapper = mount(SystemErrorsFilters, {
      props: {
        search: '',
        statusFilter: '',
        severityFilter: '',
        categoryFilter: '',
      },
    })

    const input = wrapper.find('input[type="text"]')
    await input.setValue('QueryException')

    expect(wrapper.emitted('update:search')).toBeTruthy()
    expect(wrapper.emitted('update:search')[0]).toEqual(['QueryException'])
  })

  it('renders Reset Filters button when active filters exist and emits clearFilters', async () => {
    const wrapper = mount(SystemErrorsFilters, {
      props: {
        search: 'ERR_001',
        statusFilter: 'open',
        severityFilter: '',
        categoryFilter: '',
      },
    })

    const resetBtn = wrapper.find('button')
    expect(resetBtn.exists()).toBe(true)
    expect(resetBtn.text()).toContain('Reset Filters')

    await resetBtn.trigger('click')
    expect(wrapper.emitted('clearFilters')).toBeTruthy()
  })
})
