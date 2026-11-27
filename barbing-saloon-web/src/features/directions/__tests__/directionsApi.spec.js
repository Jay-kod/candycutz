import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest'

const { mockClient } = vi.hoisted(() => ({
  mockClient: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
    delete: vi.fn(),
  },
}))

vi.mock('@/shared/api/client', () => ({
  default: mockClient,
}))

import { directionsApi } from '../api/directionsApi'

describe('directionsApi', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('calls public shop location, directions, and saved customer address endpoints', async () => {
    await directionsApi.getShopLocation()
    expect(mockClient.get).toHaveBeenCalledWith('/shop/location', { params: {} })

    await directionsApi.getDirections({ from_lat: 8.85, from_lng: 7.88 })
    expect(mockClient.get).toHaveBeenCalledWith('/shop/directions', { params: { from_lat: 8.85, from_lng: 7.88 } })

    await directionsApi.listAddresses()
    expect(mockClient.get).toHaveBeenCalledWith('/customer/addresses')

    await directionsApi.createAddress({ title: 'Home', street: '14 Main Street' })
    expect(mockClient.post).toHaveBeenCalledWith('/customer/addresses', { title: 'Home', street: '14 Main Street' })

    await directionsApi.setDefaultAddress(5)
    expect(mockClient.patch).toHaveBeenCalledWith('/customer/addresses/5/default')

    await directionsApi.deleteAddress(5)
    expect(mockClient.delete).toHaveBeenCalledWith('/customer/addresses/5')
  })

  it('reverse-geocodes coordinates and falls back cleanly when offline or failing', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({
          display_name: '12 Unity Road, Keffi, Nasarawa',
          address: {
            house_number: '12',
            road: 'Unity Road',
            city: 'Keffi',
            state: 'Nasarawa',
          },
        }),
      })
    )

    const resolved = await directionsApi.reverseGeocode(8.851, 7.876)
    expect(resolved).toEqual({
      label: '12 Unity Road, Keffi, Nasarawa',
      street: '12 Unity Road',
      city: 'Keffi',
      state: 'Nasarawa',
    })

    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('Network down')))
    const fallback = await directionsApi.reverseGeocode(8.851, 7.876)
    expect(fallback.label).toBe('Pinned Location (8.85100, 7.87600)')
    expect(fallback.street).toBe('Pinned Location (8.85100, 7.87600)')
  })

  it('fetches, deduplicates, and prioritizes nearby major places around the shop', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({
          elements: [
            {
              type: 'node',
              id: 1,
              lat: 8.851,
              lon: 7.882,
              tags: { name: 'Total Station', amenity: 'fuel' },
            },
            {
              type: 'way',
              id: 2,
              center: { lat: 8.847, lon: 7.899 },
              tags: { name: 'Nasarawa State University', amenity: 'university' },
            },
            {
              type: 'node',
              id: 3,
              lat: 8.847,
              lon: 7.899,
              tags: { name: 'Nasarawa State University', amenity: 'university' },
            },
          ],
        }),
      })
    )

    const places = await directionsApi.fetchNearbyMajorPlaces(8.8486, 7.8736)
    expect(places).toHaveLength(2)
    expect(places[0].name).toBe('Nasarawa State University')
    expect(places[0].category).toBe('University')
    expect(places[1].name).toBe('Total Station')
    expect(places[1].category).toBe('Fuel')
  })

  it('picks the shortest-distance alternative route from OSRM and parses step-by-step instructions', async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      json: async () => ({
        routes: [
          {
            distance: 8450,
            duration: 540,
            geometry: {
              coordinates: [
                [7.9, 8.84],
                [7.92, 8.86],
                [7.8736, 8.8486],
              ],
            },
            legs: [
              {
                steps: [{ distance: 8450, duration: 540, name: 'Long Highway Loop', maneuver: { type: 'depart' } }],
              },
            ],
          },
          {
            distance: 2150,
            duration: 360,
            geometry: {
              coordinates: [
                [7.9, 8.84],
                [7.8736, 8.8486],
              ],
            },
            legs: [
              {
                steps: [
                  { distance: 650, duration: 75, name: 'Keffi Road', maneuver: { type: 'depart', modifier: 'northeast' } },
                  { distance: 0, duration: 0, name: '', maneuver: { type: 'arrive' } },
                ],
              },
            ],
          },
        ],
      }),
    })
    vi.stubGlobal('fetch', fetchMock)

    const route = await directionsApi.fetchRoadRoute(8.84, 7.9, 8.8486, 7.8736, {
      places: [{ name: 'Keffi Total', latitude: 8.8401, longitude: 7.8999 }],
    })
    expect(fetchMock).toHaveBeenCalledWith(expect.stringContaining('alternatives=true&continue_straight=false'), expect.any(Object))
    expect(route.distanceKm).toBe(2.15)
    expect(route.durationMinutes).toBe(6)
    expect(route.steps).toHaveLength(2)
    expect(route.steps[0].instruction).toBe('Head northeast on Keffi Road')
    expect(route.steps[1].instruction).toBe('Arrive at destination (CandyCutz)')
  })

  it('rescues short neighborhood trips from long driving detours using shortest footpath cut-through', async () => {
    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({
          routes: [
            {
              distance: 1730,
              duration: 300,
              geometry: {
                coordinates: [
                  [7.8736, 8.847],
                  [7.8736, 8.8486],
                ],
              },
              legs: [
                {
                  steps: [{ distance: 1730, duration: 300, name: '', maneuver: { type: 'turn', modifier: 'left', bearing_after: 0 } }],
                },
              ],
            },
          ],
        }),
      })
      .mockResolvedValueOnce({
        ok: true,
        json: async () => ({
          routes: [
            {
              distance: 210,
              duration: 180,
              geometry: {
                coordinates: [
                  [7.8736, 8.847],
                  [7.8736, 8.8486],
                ],
              },
              legs: [
                {
                  steps: [
                    {
                      distance: 210,
                      duration: 180,
                      name: '',
                      maneuver: { type: 'depart', bearing_after: 5, location: [7.8736, 8.8471] },
                    },
                  ],
                },
              ],
            },
          ],
        }),
      })

    vi.stubGlobal('fetch', fetchMock)

    const route = await directionsApi.fetchRoadRoute(8.847, 7.8736, 8.8486, 7.8736, {
      places: [{ name: 'SanSiro Turf Arena', latitude: 8.8472, longitude: 7.8737 }],
    })
    expect(route.profile).toBe('walking')
    expect(route.distanceKm).toBe(0.21)
    expect(route.steps[0].instruction).toContain('north')
    expect(route.steps[0].instruction).toContain('SanSiro Turf Arena')
  })

  it('uses backend /shop/geocode proxy, parses coordinates directly, and falls back to direct geodesic route when OSRM fails', async () => {
    mockClient.get.mockResolvedValueOnce({
      data: {
        data: [
          {
            label: 'NSUK Gate, Keffi, Nasarawa',
            street: 'NSUK Gate',
            city: 'Keffi',
            state: 'Nasarawa',
            latitude: 8.8471,
            longitude: 7.9012,
            distance_km: 1.76,
            source: 'photon',
          },
        ],
      },
    })

    const places = await directionsApi.geocodeQuery('NSUK Gate')
    expect(mockClient.get).toHaveBeenCalledWith('/shop/geocode', { params: { q: 'NSUK Gate' } })
    expect(places[0].latitude).toBe(8.8471)
    expect(places[0].city).toBe('Keffi')

    mockClient.get.mockResolvedValueOnce({
      data: {
        data: { label: 'Angwan Kare, Keffi, Nasarawa', street: 'Angwan Kare', city: 'Keffi', state: 'Nasarawa' },
      },
    })
    const directCoord = await directionsApi.geocodeQuery('8.854541, 7.915391')
    expect(directCoord[0].source).toBe('coordinates')
    expect(directCoord[0].latitude).toBe(8.854541)

    vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('OSRM offline')))
    const fallbackRoute = await directionsApi.fetchRoadRoute(8.85, 7.9, 8.854541, 7.915391)
    expect(fallbackRoute.isFallback).toBe(true)
    expect(fallbackRoute.coordinates).toHaveLength(2)
    expect(fallbackRoute.steps[0].instruction).toContain('CandyCutz Shop')
  })
})
