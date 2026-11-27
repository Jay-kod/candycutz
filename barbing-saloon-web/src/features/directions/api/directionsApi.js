import api from '@/shared/api/client'
import { extractGoogleMapsCoordinates } from '../utils/googleMaps'

const unwrap = (response) => response?.data?.data ?? response?.data
const majorPlacesCache = new Map()

const PLACE_CATEGORY_PRIORITY = {
  university: 1,
  college: 2,
  hospital: 3,
  marketplace: 4,
  bus_station: 5,
  mall: 6,
  town: 7,
  suburb: 8,
  neighbourhood: 9,
  quarter: 10,
  supermarket: 11,
  fuel: 12,
  bank: 13,
  courthouse: 14,
  police: 15,
  clinic: 16,
  place_of_worship: 17,
}

function formatPlaceCategory(tags = {}) {
  const raw = tags.amenity || tags.shop || tags.place || 'landmark'
  return String(raw)
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (c) => c.toUpperCase())
}

function bearingToCardinal(deg) {
  if (!Number.isFinite(Number(deg))) return ''
  const dirs = ['north', 'northeast', 'east', 'southeast', 'south', 'southwest', 'west', 'northwest']
  return dirs[Math.round((((Number(deg) % 360) + 360) % 360) / 45) % 8]
}

function findNearestPlaceHint(lat, lng, places = []) {
  if (!Number.isFinite(lat) || !Number.isFinite(lng) || !Array.isArray(places) || !places.length) return ''
  let bestName = ''
  let bestDist = 180 // within 180 meters of a landmark
  for (const p of places) {
    if (!Number.isFinite(p?.latitude) || !Number.isFinite(p?.longitude) || !p?.name) continue
    const d = approxDistanceMeters(lat, lng, p.latitude, p.longitude)
    if (d < bestDist) {
      bestDist = d
      bestName = p.name
    }
  }
  return bestName ? ` (near ${bestName})` : ''
}

function formatManeuverInstruction(step, places = []) {
  const m = step?.maneuver || {}
  const type = String(m.type || '').toLowerCase()
  const mod = m.modifier ? String(m.modifier).replace(/_/g, ' ') : ''
  const road = String(step?.name || step?.ref || '').trim()
  const onto = road ? ` onto ${road}` : ''
  const cardinal = !road ? bearingToCardinal(m.bearing_after) : ''
  const headingSuffix = cardinal ? ` toward ${cardinal}` : ''
  const loc = Array.isArray(m.location) && m.location.length >= 2 ? { lng: Number(m.location[0]), lat: Number(m.location[1]) } : null
  const near = loc ? findNearestPlaceHint(loc.lat, loc.lng, places) : ''

  if (type === 'depart') return `Head ${mod || cardinal || 'forward'}${road ? ` on ${road}` : ''}${near}`
  if (type === 'arrive') return 'Arrive at destination (CandyCutz)'
  if (type === 'roundabout' || type === 'rotary') {
    const exit = m.exit ? `take exit ${m.exit}` : 'enter roundabout'
    return `${exit.charAt(0).toUpperCase() + exit.slice(1)}${onto}${near}`
  }
  if (type === 'turn' || type === 'end of road' || type === 'fork' || type === 'merge') {
    const dir = mod ? `${type === 'merge' ? 'merge' : 'turn'} ${mod}` : type
    return `${dir.charAt(0).toUpperCase() + dir.slice(1)}${onto}${headingSuffix}${near}`
  }
  if (type === 'continue' || type === 'new name') {
    return `Continue${mod ? ` ${mod}` : ''}${onto}${headingSuffix}${near}`
  }
  return `${road ? `Proceed on ${road}` : `Continue${headingSuffix || ' straight'}`}${near}`
}

function approxDistanceMeters(lat1, lng1, lat2, lng2) {
  const dLat = (lat2 - lat1) * 111320
  const dLng = (lng2 - lng1) * 111320 * Math.cos((lat1 * Math.PI) / 180)
  return Math.hypot(dLat, dLng)
}

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

  async fetchRoadRoute(fromLat, fromLng, toLat, toLng, options = {}) {
    const travelMode = options?.travelMode === 'walking' ? 'walking' : 'driving'
    const profile = travelMode === 'walking' ? 'foot' : 'driving'
    const places = Array.isArray(options?.places) ? options.places : []

    const queryOsrmProfile = async (osrmProfile) => {
      const controller = new AbortController()
      const timer = setTimeout(() => controller.abort(), 5000)
      try {
        const url = `https://router.project-osrm.org/route/v1/${osrmProfile}/${fromLng},${fromLat};${toLng},${toLat}?overview=full&geometries=geojson&steps=true&alternatives=true&continue_straight=false`
        const res = await fetch(url, { signal: controller.signal })
        if (!res.ok) return null
        const json = await res.json()
        const candidates = Array.isArray(json?.routes)
          ? json.routes.filter((r) => r?.geometry?.coordinates?.length && Number.isFinite(Number(r?.distance)))
          : []
        if (!candidates.length) return null

        // Always pick the shortest-distance alternative route
        return candidates.reduce((shortest, current) =>
          Number(current.distance) < Number(shortest.distance) ? current : shortest
        )
      } catch {
        return null
      } finally {
        clearTimeout(timer)
      }
    }

    let resolvedMode = travelMode
    let route = await queryOsrmProfile(profile)

    // When pins are close (<= 1.2 km straight-line) and driving forces a huge (> 2.5x) loop detour, compare walking cut-through
    const straightMeters = approxDistanceMeters(fromLat, fromLng, toLat, toLng)
    if (
      travelMode === 'driving' &&
      route &&
      straightMeters <= 1200 &&
      Number(route.distance) > Math.max(400, straightMeters * 2.5)
    ) {
      const walkRoute = await queryOsrmProfile('foot')
      if (walkRoute && Number(walkRoute.distance) < Number(route.distance) * 0.65) {
        route = walkRoute
        resolvedMode = 'walking'
      }
    }

    if (!route) {
      // Graceful direct geodesic fallback when public OSRM routing is temporarily unreachable
      const km = Number((straightMeters / 1000).toFixed(2))
      const mins = travelMode === 'walking' ? Math.max(3, Math.ceil((km / 5) * 60)) : Math.max(2, Math.ceil((km / 28) * 60))
      return {
        profile: travelMode,
        coordinates: [
          [Number(fromLat), Number(fromLng)],
          [Number(toLat), Number(toLng)],
        ],
        distanceKm: km,
        durationMinutes: mins,
        steps: [
          {
            instruction: `Head toward CandyCutz Shop (${km} km direct route)`,
            distanceMeters: Math.round(straightMeters),
            durationSeconds: mins * 60,
            latitude: Number(fromLat),
            longitude: Number(fromLng),
          },
          {
            instruction: 'Arrive at destination (CandyCutz)',
            distanceMeters: 0,
            durationSeconds: 0,
            latitude: Number(toLat),
            longitude: Number(toLng),
          },
        ],
        isFallback: true,
      }
    }

    const rawSteps = route.legs?.[0]?.steps || []
    const steps = Array.isArray(rawSteps)
      ? rawSteps.map((s) => {
          const loc = s?.maneuver?.location
          return {
            instruction: formatManeuverInstruction(s, places),
            distanceMeters: Math.round(Number(s?.distance || 0)),
            durationSeconds: Math.round(Number(s?.duration || 0)),
            latitude: Array.isArray(loc) && Number.isFinite(Number(loc[1])) ? Number(loc[1]) : null,
            longitude: Array.isArray(loc) && Number.isFinite(Number(loc[0])) ? Number(loc[0]) : null,
          }
        })
      : []

    return {
      profile: resolvedMode,
      coordinates: route.geometry.coordinates.map(([lng, lat]) => [lat, lng]),
      distanceKm: Number((route.distance / 1000).toFixed(2)),
      durationMinutes: Math.max(2, Math.ceil(route.duration / 60)),
      steps,
    }
  },

  async geocodeQuery(query) {
    const trimmed = String(query || '').trim()
    if (!trimmed) return []

    // Support direct coordinates or Google Maps share/place links pasted into the address field
    const coordMatch = trimmed.match(/^\s*(-?\d+(?:\.\d+)?)\s*[, ]\s*(-?\d+(?:\.\d+)?)\s*$/)
    if (coordMatch) {
      const lat = Number(Number(coordMatch[1]).toFixed(6))
      const lng = Number(Number(coordMatch[2]).toFixed(6))
      if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
        const rev = await this.reverseGeocode(lat, lng)
        return [{ ...rev, latitude: lat, longitude: lng, source: 'coordinates' }]
      }
    }

    const linkCoords = extractGoogleMapsCoordinates(trimmed)
    if (linkCoords) {
      const lat = Number(linkCoords.latitude)
      const lng = Number(linkCoords.longitude)
      const rev = await this.reverseGeocode(lat, lng)
      return [{ ...rev, latitude: lat, longitude: lng, source: 'google_link' }]
    }

    // Prefer our backend Keffi-biased multi-provider proxy
    try {
      const res = await api.get('/shop/geocode', { params: { q: trimmed } })
      const backendRows = unwrap(res)
      if (Array.isArray(backendRows) && backendRows.length > 0) {
        return backendRows.map((r) => ({
          label: r.label,
          street: r.street || r.label,
          city: r.city || 'Keffi',
          state: r.state || 'Nasarawa',
          latitude: Number(r.latitude),
          longitude: Number(r.longitude),
          distance_km: r.distance_km ?? null,
          source: r.source || 'server',
        }))
      }
    } catch {
      // Fall back cleanly if offline/unproxied
    }

    const controller = new AbortController()
    const timer = setTimeout(() => controller.abort(), 5000)
    try {
      const url = `https://nominatim.openstreetmap.org/search?format=json&countrycodes=ng&limit=4&q=${encodeURIComponent(trimmed)}`
      const res = await fetch(url, {
        signal: controller.signal,
        headers: { Accept: 'application/json' },
      })
      if (!res.ok) return []
      const rows = await res.json()
      return Array.isArray(rows)
        ? rows.map((r) => ({
            label: r.display_name,
            street: r.display_name,
            city: 'Keffi',
            state: 'Nasarawa',
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

  async reverseGeocode(latitude, longitude) {
    const lat = Number(latitude)
    const lng = Number(longitude)
    const fallbackLabel = `Pinned Location (${lat.toFixed(5)}, ${lng.toFixed(5)})`
    const fallback = {
      label: fallbackLabel,
      street: fallbackLabel,
      city: 'Keffi',
      state: 'Nasarawa',
    }

    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return fallback

    try {
      const res = await api.get('/shop/reverse-geocode', { params: { lat, lng } })
      const data = unwrap(res)
      if (data?.label && data?.street) {
        return {
          label: data.label,
          street: data.street,
          city: data.city || 'Keffi',
          state: data.state || 'Nasarawa',
        }
      }
    } catch {
      // Fall back to direct Nominatim if backend reverse endpoint is unavailable
    }

    const controller = new AbortController()
    const timer = setTimeout(() => controller.abort(), 5000)
    try {
      const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=18&addressdetails=1&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`
      const res = await fetch(url, {
        signal: controller.signal,
        headers: { Accept: 'application/json' },
      })
      if (!res.ok) return fallback
      const data = await res.json()
      const addr = data?.address || {}
      const road = [addr.house_number, addr.road || addr.neighbourhood || addr.suburb || addr.quarter || addr.hamlet]
        .filter(Boolean)
        .join(' ')
        .trim()
      const rawCity = addr.city || addr.town || addr.village || addr.county || 'Keffi'
      const city = String(rawCity).toLowerCase() === 'kokona' ? 'Keffi' : rawCity
      const state = addr.state || 'Nasarawa'
      const conciseParts = [road, city, state].filter(
        (part, idx, arr) => part && arr.findIndex((p) => p.toLowerCase() === part.toLowerCase()) === idx
      )
      const label = data?.display_name || conciseParts.join(', ') || fallbackLabel
      return {
        label,
        street: road || fallbackLabel,
        city,
        state,
      }
    } catch {
      return fallback
    } finally {
      clearTimeout(timer)
    }
  },

  async fetchNearbyMajorPlaces(latitude, longitude, radiusMeters = 5000) {
    const lat = Number(latitude)
    const lng = Number(longitude)
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return []

    const cacheKey = `${lat.toFixed(4)},${lng.toFixed(4)},${radiusMeters}`
    if (majorPlacesCache.has(cacheKey)) {
      return majorPlacesCache.get(cacheKey)
    }

    const query = `[out:json][timeout:8];
(
  node["name"]["amenity"~"^(university|college|hospital|clinic|marketplace|fuel|bus_station|bank|courthouse|police|place_of_worship)$"](around:${radiusMeters},${lat},${lng});
  way["name"]["amenity"~"^(university|college|hospital|marketplace|fuel|bus_station|bank|courthouse|police|place_of_worship)$"](around:${radiusMeters},${lat},${lng});
  node["name"]["shop"~"^(mall|supermarket|beauty|hairdresser)$"](around:${radiusMeters},${lat},${lng});
  node["name"]["tourism"~"^(hotel|guest_house|hostel)$"](around:${radiusMeters},${lat},${lng});
  way["name"]["leisure"~"^(pitch|stadium|sports_centre|park)$"](around:${radiusMeters},${lat},${lng});
  node["name"]["highway"="bus_stop"](around:${radiusMeters},${lat},${lng});
  node["name"]["place"~"^(town|suburb|neighbourhood|quarter)$"](around:${radiusMeters},${lat},${lng});
);
out center 50;`

    const endpoints = [
      'https://overpass-api.de/api/interpreter',
      'https://overpass.kumi.systems/api/interpreter',
    ]

    for (const endpoint of endpoints) {
      const controller = new AbortController()
      const timer = setTimeout(() => controller.abort(), 5500)
      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          signal: controller.signal,
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
            Accept: 'application/json',
          },
          body: `data=${encodeURIComponent(query)}`,
        })
        if (!res.ok) continue
        const json = await res.json()
        const elements = Array.isArray(json?.elements) ? json.elements : []

        const seenNames = new Set()
        const places = []

        for (const el of elements) {
          const name = String(el?.tags?.name || '').trim()
          const pLat = el?.lat ?? el?.center?.lat
          const pLng = el?.lon ?? el?.center?.lon
          if (!name || !Number.isFinite(pLat) || !Number.isFinite(pLng)) continue

          const normName = name.toLowerCase()
          if (seenNames.has(normName)) continue
          seenNames.add(normName)

          const rawType = el.tags?.amenity || el.tags?.shop || el.tags?.tourism || el.tags?.leisure || el.tags?.place || 'landmark'
          places.push({
            id: `${el.type || 'node'}-${el.id}`,
            name,
            category: formatPlaceCategory(el.tags),
            priority: PLACE_CATEGORY_PRIORITY[rawType] ?? 99,
            distanceMeters: Math.round(approxDistanceMeters(lat, lng, pLat, pLng)),
            latitude: Number(Number(pLat).toFixed(6)),
            longitude: Number(Number(pLng).toFixed(6)),
          })
        }

        const sorted = places
          .sort((a, b) => a.priority - b.priority || a.distanceMeters - b.distanceMeters)
          .slice(0, 18)

        majorPlacesCache.set(cacheKey, sorted)
        return sorted
      } catch {
        // Try fallback Overpass mirror
      } finally {
        clearTimeout(timer)
      }
    }

    return []
  },
}
