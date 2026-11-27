import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest'

const { mockDirectionsApi, mockToast } = vi.hoisted(() => ({
  mockDirectionsApi: {
    getShopLocation: vi.fn(),
    getDirections: vi.fn(),
    fetchRoadRoute: vi.fn(),
    listAddresses: vi.fn(),
    createAddress: vi.fn(),
    updateAddress: vi.fn(),
    deleteAddress: vi.fn(),
    geocodeQuery: vi.fn(),
    reverseGeocode: vi.fn(),
    fetchNearbyMajorPlaces: vi.fn(),
  },
  mockToast: {
    success: vi.fn(),
    error: vi.fn(),
    info: vi.fn(),
  },
}))

vi.mock('../api/directionsApi', () => ({
  directionsApi: mockDirectionsApi,
}))

vi.mock('@/core/composables/useToast', () => ({
  useToast: () => mockToast,
}))

import { useShopDirections } from '../composables/useShopDirections'

describe('useShopDirections (Restructured 3-State Flow)', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    localStorage.clear()
    mockDirectionsApi.getShopLocation.mockResolvedValue({
      name: 'Keffi Main Hub',
      latitude: 8.854541,
      longitude: 7.915391,
      location_lock: { verified: true, status: 'locked' },
      directions_links: {},
    })
    mockDirectionsApi.getDirections.mockResolvedValue({
      estimated_distance_km: 1.5,
      estimated_drive_minutes: 4,
      estimated_walk_minutes: 18,
      compass_bearing: 'N',
      directions_links: {},
    })
    mockDirectionsApi.fetchRoadRoute.mockResolvedValue({
      profile: 'driving',
      coordinates: [
        [8.843518, 7.915356],
        [8.854541, 7.915391],
      ],
      distanceKm: 1.73,
      durationMinutes: 5,
      steps: [],
    })
    mockDirectionsApi.listAddresses.mockResolvedValue([])
    mockDirectionsApi.fetchNearbyMajorPlaces.mockResolvedValue([])
    mockDirectionsApi.reverseGeocode.mockResolvedValue({
      label: 'Angwan Kare Road, Keffi, Nasarawa',
      street: 'Angwan Kare Road',
      city: 'Keffi',
      state: 'Nasarawa',
    })
  })

  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('rejects coarse desktop IP geolocation (>500m) on init so customer pin is not moved somewhere else', async () => {
    vi.stubGlobal('navigator', {
      permissions: { query: vi.fn().mockResolvedValue({ state: 'granted' }) },
      geolocation: {
        getCurrentPosition: vi.fn((success) => {
          success({ coords: { latitude: 9.0765, longitude: 7.3986, accuracy: 18000 } })
        }),
        watchPosition: vi.fn(),
        clearWatch: vi.fn(),
      },
    })

    const state = useShopDirections(false)
    await state.init()

    expect(state.currentOrigin.value).toBeNull()
    expect(state.draftOrigin.latitude).toBeNull()
  })

  it('accepts accurate device GPS (<=400m) on init when no saved address exists', async () => {
    vi.stubGlobal('navigator', {
      permissions: { query: vi.fn().mockResolvedValue({ state: 'granted' }) },
      geolocation: {
        getCurrentPosition: vi.fn((success) => {
          success({ coords: { latitude: 8.851111, longitude: 7.912222, accuracy: 25 } })
        }),
        watchPosition: vi.fn(),
        clearWatch: vi.fn(),
      },
    })

    const state = useShopDirections(false)
    await state.init()

    expect(state.currentOrigin.value.latitude).toBe(8.851111)
    expect(state.currentOrigin.value.longitude).toBe(7.912222)
    expect(state.draftOrigin.street).toBe('Angwan Kare Road')
  })

  it('sets Pin 2 on map without snapping zoom and preserves custom typed customer street', async () => {
    const state = useShopDirections(false)
    state.shop.value = { latitude: 8.854541, longitude: 7.915391 }
    state.draftOrigin.title = 'My House'
    state.draftOrigin.street = 'Beside Wealths Khort'

    await state.setPinOnMap(8.852222, 7.914444)

    expect(state.fitTrigger.value).toBe(0)
    expect(state.draftOrigin.isDirty).toBe(true)
    expect(state.draftOrigin.street).toBe('Beside Wealths Khort')
    expect(state.currentOrigin.value.latitude).toBe(8.852222)
    expect(state.currentOrigin.value.label).toBe('My House: Beside Wealths Khort')
    expect(state.activeWalkMinutes.value).toBe(21)
  })

  it('creates new saved addresses and updates existing edits via saveCustomerAddress', async () => {
    mockDirectionsApi.createAddress.mockResolvedValue({
      id: 9,
      title: 'Office',
      street: 'Opposite Total Station',
      city: 'Keffi',
      state: 'Nasarawa',
      latitude: 8.849,
      longitude: 7.911,
      is_default: true,
    })
    mockDirectionsApi.updateAddress.mockResolvedValue({
      id: 9,
      title: 'Office Main',
      street: 'Updated Street',
      city: 'Keffi',
      state: 'Nasarawa',
      latitude: 8.85,
      longitude: 7.912,
      is_default: true,
    })

    const state = useShopDirections(true)
    state.shop.value = { latitude: 8.854541, longitude: 7.915391 }
    state.draftOrigin.title = 'Office'
    state.draftOrigin.street = 'Opposite Total Station'
    await state.setPinOnMap(8.849, 7.911)

    const savedNew = await state.saveCustomerAddress()
    expect(savedNew).toBe(true)
    expect(mockDirectionsApi.createAddress).toHaveBeenCalledWith(
      expect.objectContaining({ title: 'Office', latitude: 8.849, longitude: 7.911 })
    )
    expect(state.draftOrigin.id).toBe(9)
    expect(state.draftOrigin.isDirty).toBe(false)

    state.draftOrigin.title = 'Office Main'
    state.draftOrigin.street = 'Updated Street'
    await state.setPinOnMap(8.85, 7.912)
    const savedEdit = await state.saveCustomerAddress()
    expect(savedEdit).toBe(true)
    expect(mockDirectionsApi.updateAddress).toHaveBeenCalledWith(
      9,
      expect.objectContaining({ title: 'Office Main', street: 'Updated Street', latitude: 8.85, longitude: 7.912 })
    )
    expect(state.draftOrigin.isDirty).toBe(false)
  })

  it('locks preferred Pin 2 against accidental changes and allows selecting another point after unlock', async () => {
    const state = useShopDirections(false)
    state.shop.value = { latitude: 8.854541, longitude: 7.915391 }

    // Cannot lock before a point exists
    expect(state.lockOrigin()).toBe(false)
    expect(state.originLocked.value).toBe(false)

    // Pin a point and lock it
    await state.setPinOnMap(8.850111, 7.913222)
    expect(state.lockOrigin()).toBe(true)
    expect(state.originLocked.value).toBe(true)

    // Accidental map pin, search selection, or live tracking are blocked while locked
    const mapBlocked = await state.setPinOnMap(8.86, 7.92)
    expect(mapBlocked).toBe(false)
    expect(state.currentOrigin.value.latitude).toBe(8.850111)
    expect(state.geoWarning.value).toContain('locked')

    const searchBlocked = await state.applySearchResult({
      label: 'Other Place',
      latitude: 8.86,
      longitude: 7.92,
    })
    expect(searchBlocked).toBe(false)
    expect(state.startLiveTracking()).toBe(false)

    // Saving & reloading preserves the locked state
    await state.saveCustomerAddress()
    const reloaded = useShopDirections(false)
    await reloaded.init()
    expect(reloaded.originLocked.value).toBe(true)
    expect(reloaded.currentOrigin.value.latitude).toBe(8.850111)

    // Unlocking allows selecting another point on the map cleanly
    expect(reloaded.unlockOrigin()).toBe(true)
    expect(reloaded.originLocked.value).toBe(false)
    const moved = await reloaded.setPinOnMap(8.8515, 7.9145)
    expect(moved).toBe(true)
    expect(reloaded.currentOrigin.value.latitude).toBe(8.8515)
  })

  it('prompts when device location is off/denied without breaking lock, then routes to shop once location is on', async () => {
    const state = useShopDirections(false)
    state.shop.value = { latitude: 8.854541, longitude: 7.915391 }
    await state.setPinOnMap(8.850111, 7.913222)
    state.lockOrigin()

    // 1. Device location turned off / unavailable (code 2)
    vi.stubGlobal('navigator', {
      permissions: { query: vi.fn().mockResolvedValue({ state: 'prompt' }) },
      geolocation: {
        getCurrentPosition: vi.fn((_, errorCb) => {
          errorCb({ code: 2, message: 'Location unavailable' })
        }),
        watchPosition: vi.fn(),
        clearWatch: vi.fn(),
      },
    })

    const offResult = await state.pickCurrentLocation({ auto: false })
    expect(offResult).toBe(false)
    expect(state.locationPromptState.value).toBe('needs_enable')
    expect(state.geoError.value).toContain('Device Location')
    expect(state.originLocked.value).toBe(true)
    expect(state.currentOrigin.value.latitude).toBe(8.850111)

    // 2. Permission denied (code 1)
    vi.stubGlobal('navigator', {
      permissions: { query: vi.fn().mockResolvedValue({ state: 'denied' }) },
      geolocation: {
        getCurrentPosition: vi.fn((_, errorCb) => {
          errorCb({ code: 1, message: 'User denied Geolocation' })
        }),
        watchPosition: vi.fn(),
        clearWatch: vi.fn(),
      },
    })

    const deniedResult = await state.pickCurrentLocation({ auto: false })
    expect(deniedResult).toBe(false)
    expect(state.locationPromptState.value).toBe('denied')
    expect(state.originLocked.value).toBe(true)

    // 3. Device location enabled -> detects current coordinates & computes route to shop
    vi.stubGlobal('navigator', {
      permissions: { query: vi.fn().mockResolvedValue({ state: 'granted' }) },
      geolocation: {
        getCurrentPosition: vi.fn((success) => {
          success({ coords: { latitude: 8.847777, longitude: 7.912345, accuracy: 18 } })
        }),
        watchPosition: vi.fn(),
        clearWatch: vi.fn(),
      },
    })

    const onResult = await state.pickCurrentLocation({ auto: false })
    expect(onResult).toBe(true)
    expect(state.locationPromptState.value).toBe('ready')
    expect(state.originLocked.value).toBe(false)
    expect(state.currentOrigin.value.latitude).toBe(8.847777)
    expect(mockDirectionsApi.fetchRoadRoute).toHaveBeenLastCalledWith(
      8.847777,
      7.912345,
      8.854541,
      7.915391,
      expect.any(Object)
    )
  })
})
