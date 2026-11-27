import { describe, expect, it, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'

const { clickHandlers, markerInstances, fakeMap } = vi.hoisted(() => {
  const clicks = {}
  const markers = []
  const mapObj = {
    setView: vi.fn().mockReturnThis(),
    fitBounds: vi.fn().mockReturnThis(),
    invalidateSize: vi.fn(),
    on: vi.fn((ev, cb) => {
      clicks[ev] = cb
    }),
    remove: vi.fn(),
  }
  return { clickHandlers: clicks, markerInstances: markers, fakeMap: mapObj }
})

vi.mock('../utils/leafletMapHelpers', () => ({
  loadLeaflet: vi.fn().mockResolvedValue({
    map: vi.fn(() => fakeMap),
    tileLayer: vi.fn(() => ({ addTo: vi.fn(), remove: vi.fn() })),
    marker: vi.fn((latlng, opts) => {
      const listeners = {}
      const instance = {
        latlng,
        opts,
        setIcon: vi.fn(),
        dragging: {
          enable: vi.fn(),
          disable: vi.fn(),
        },
        setLatLng: vi.fn((next) => {
          instance.latlng = next
        }),
        on: vi.fn((ev, cb) => {
          listeners[ev] = cb
        }),
        trigger: (ev, payload) => listeners[ev]?.(payload),
        addTo: vi.fn().mockReturnThis(),
        remove: vi.fn(),
      }
      markerInstances.push(instance)
      return instance
    }),
    polyline: vi.fn(() => ({ addTo: vi.fn().mockReturnThis(), remove: vi.fn() })),
    circleMarker: vi.fn(() => ({ addTo: vi.fn().mockReturnThis(), remove: vi.fn() })),
    latLngBounds: vi.fn(() => ({ extend: vi.fn().mockReturnThis() })),
    divIcon: vi.fn(() => ({})),
  }),
  attachGoogleTileLayer: vi.fn(() => ({ remove: vi.fn() })),
  createMapPinIcon: vi.fn(() => ({})),
}))

import LiveDirectionsMap from '../components/LiveDirectionsMap.vue'

describe('LiveDirectionsMap.vue', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    markerInstances.length = 0
  })

  it('locks Pin 1 at shop coords, emits pin-origin on map click or Pin 2 drag, and toggles Google Place Info iframe', async () => {
    const adminEmbedUrl = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3941.5!2d7.912816!3d8.854541'
    const wrapper = mount(LiveDirectionsMap, {
      props: {
        shop: {
          name: 'CandyCutz Studio',
          address: 'Keffi',
          latitude: 8.854541,
          longitude: 7.915391,
          map_iframe: adminEmbedUrl,
          location_lock: { verified: true, status: 'locked', message: 'Locked' },
        },
        origin: { latitude: 8.843518, longitude: 7.915356, label: 'Home' },
        pinModeActive: true,
      },
    })

    await nextTick()
    await nextTick()

    expect(wrapper.text()).toContain('Pin 1 (Shop Locked): 8.854541, 7.915391')
    expect(wrapper.text()).toContain('Pin 2 (You): 8.843518, 7.915356')

    // Shop Pin 1 is fixed (draggable: false); Customer Pin 2 is draggable: true
    expect(markerInstances[0].opts.draggable).toBe(false)
    expect(markerInstances[1].opts.draggable).toBe(true)

    // Clicking anywhere on the map drops/moves Pin 2 directly without modals
    clickHandlers.click({ latlng: { lat: 8.849123, lng: 7.913456 } })
    expect(wrapper.emitted('pin-origin')?.[0]).toEqual([{ latitude: 8.849123, longitude: 7.913456 }])

    // Dragging Pin 2 emits updated coordinates
    markerInstances[1].trigger('dragend', { target: { getLatLng: () => ({ lat: 8.847, lng: 7.914 }) } })
    expect(wrapper.emitted('pin-origin')?.[1]).toEqual([{ latitude: 8.847, longitude: 7.914 }])

    // Optional Google Place Info toggle reveals Admin's original iframe card without leaving page
    const infoBtn = wrapper.findAll('button').find((b) => b.text() === 'Google Place Info')
    await infoBtn.trigger('click')
    const iframe = wrapper.find('iframe')
    expect(iframe.exists()).toBe(true)
    expect(iframe.attributes('src')).toBe(adminEmbedUrl)
  })

  it('prevents map clicks and Pin 2 drags when originLocked is true and emits unlock when requested', async () => {
    const wrapper = mount(LiveDirectionsMap, {
      props: {
        shop: {
          name: 'Candycutz',
          latitude: 8.854541,
          longitude: 7.915391,
          location_lock: { verified: true, status: 'locked', message: 'Locked' },
        },
        origin: { latitude: 8.843518, longitude: 7.915356, label: 'Home' },
        originLocked: true,
        pinModeActive: true,
      },
    })

    await nextTick()
    await nextTick()

    expect(wrapper.text()).toContain('Pin 2 (Locked): 8.843518, 7.915356')
    expect(wrapper.text()).toContain('Pin 2 is locked as your preferred location')
    expect(markerInstances[1].opts.draggable).toBe(false)

    // Map click while locked emits locked-interaction instead of pin-origin
    clickHandlers.click({ latlng: { lat: 8.849123, lng: 7.913456 } })
    expect(wrapper.emitted('pin-origin')).toBeUndefined()
    expect(wrapper.emitted('locked-interaction')).toHaveLength(1)

    // Pin drag while locked is ignored and snaps back
    markerInstances[1].trigger('dragend', { target: { getLatLng: () => ({ lat: 8.847, lng: 7.914 }) } })
    expect(wrapper.emitted('pin-origin')).toBeUndefined()
    expect(wrapper.emitted('locked-interaction')).toHaveLength(2)

    // Unlock Pin button emits toggle-origin-lock; unlocking enables dragging
    const unlockBtn = wrapper.findAll('button').find((b) => b.text() === 'Unlock Pin')
    expect(unlockBtn.exists()).toBe(true)
    await unlockBtn.trigger('click')
    expect(wrapper.emitted('toggle-origin-lock')).toHaveLength(1)

    await wrapper.setProps({ originLocked: false })
    await nextTick()
    expect(markerInstances[1].dragging.enable).toHaveBeenCalled()
    expect(wrapper.text()).toContain('Lock Pin')
  })
})
