import api from '@/shared/api/client'

const unwrap = (response) => response?.data?.data ?? response?.data

export const directionsApi = {
  async getShopLocation(params = {}) {
    const res = await api.get('/shop/location', { params })
    return unwrap(res)
  },

  async getDirections(params = {}) {
    const res = await api.get('/shop/directions', { params })
    return unwrap(res)
  },

  async listAddresses() {
    const res = await api.get('/customer/addresses')
    return unwrap(res)
  },

  async createAddress(payload) {
    const res = await api.post('/customer/addresses', payload)
    return unwrap(res)
  },

  async updateAddress(id, payload) {
    const res = await api.put(`/customer/addresses/${id}`, payload)
    return unwrap(res)
  },

  async setDefaultAddress(id) {
    const res = await api.patch(`/customer/addresses/${id}/default`)
    return unwrap(res)
  },

  async deleteAddress(id) {
    const res = await api.delete(`/customer/addresses/${id}`)
    return unwrap(res)
  },

  async fetchRoadRoute(fromLat, fromLng, toLat, toLng) {
    const controller = new AbortController()
    const timer = setTimeout(() => controller.abort(), 5000)
    try {
      const url = `https://router.project-osrm.org/route/v1/driving/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson`
      const res = await fetch(url, { signal: controller.signal })
      if (!res.ok) return null
      const json = await res.json()
      const route = json?.routes?.[0]
      if (!route?.geometry?.coordinates?.length) return null
      return {
        coordinates: route.geometry.coordinates.map(([lng, lat]) => [lat, lng]),
        distanceKm: Number((route.distance / 1000).toFixed(2)),
        durationMinutes: Math.max(2, Math.ceil(route.duration / 60)),
      }
    } catch {
      return null
    } finally {
      clearTimeout(timer)
    }
  },

  async geocodeQuery(query) {
    const trimmed = String(query || '').trim()
    if (!trimmed) return []
    const controller = new AbortController()
    const timer = setTimeout(() => controller.abort(), 5000)
    try {
      const url = `https://nominatim.openstreetmap.org/search?format=json&limit=4&q=${encodeURIComponent(trimmed)}`
      const res = await fetch(url, {
        signal: controller.signal,
        headers: { Accept: 'application/json' },
      })
      if (!res.ok) return []
      const rows = await res.json()
      return Array.isArray(rows)
        ? rows.map((r) => ({
            label: r.display_name,
            latitude: Number(r.lat),
            longitude: Number(r.lon),
          }))
        : []
    } catch {
      return []
    } finally {
      clearTimeout(timer)
    }
  },
}
