import { describe, expect, it, vi, beforeEach } from 'vitest'

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
})
