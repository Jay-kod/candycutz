const GOOGLE_EMBED_HOSTS = new Set(['www.google.com', 'maps.google.com', 'google.com'])

/**
 * Validate whether a URL is a trusted HTTPS Google Maps embed URL.
 *
 * @param {string} url
 * @returns {boolean}
 */
export function isValidGoogleEmbedUrl(url) {
  if (!url || typeof url !== 'string') return false
  try {
    const parsed = new URL(url.trim())
    if (parsed.protocol !== 'https:' || !GOOGLE_EMBED_HOSTS.has(parsed.hostname.toLowerCase())) {
      return false
    }
    return (
      parsed.pathname.startsWith('/maps/embed') ||
      (parsed.pathname.startsWith('/maps') && (parsed.searchParams.has('pb') || parsed.searchParams.get('output') === 'embed'))
    )
  } catch {
    return false
  }
}

/**
 * Extract a sanitized HTTPS Google Maps embed URL from a raw iframe snippet or direct URL.
 *
 * @param {string | null | undefined} rawInput
 * @returns {string}
 */
export function extractGoogleMapsEmbedUrl(rawInput) {
  const raw = String(rawInput || '').trim()
  if (!raw) return ''
  if (isValidGoogleEmbedUrl(raw)) return raw

  const srcMatch = raw.match(/src\s*=\s*(?:"([^"]*)"|'([^']*)')/i)
  if (srcMatch) {
    const candidate = (srcMatch[1] || srcMatch[2] || '').replace(/&amp;/g, '&').trim()
    if (isValidGoogleEmbedUrl(candidate)) return candidate
  }

  return ''
}

/**
 * Build a Google Maps embed fallback URL from locked shop coordinates.
 *
 * @param {{ latitude?: number | string, longitude?: number | string } | null | undefined} shop
 * @returns {string}
 */
export function resolveShopGoogleEmbedUrl(shop) {
  const configured = extractGoogleMapsEmbedUrl(shop?.map_iframe)
  if (configured) return configured

  const lat = Number(shop?.latitude)
  const lng = Number(shop?.longitude)
  if (Number.isFinite(lat) && Number.isFinite(lng) && (lat !== 0 || lng !== 0)) {
    return `https://maps.google.com/maps?q=${encodeURIComponent(`${lat},${lng}`)}&z=16&output=embed`
  }

  return ''
}

/**
 * Extract [latitude, longitude] coordinates plus precision flag from a Google Maps snippet or link.
 * Prioritizes exact place pin (!3d<lat>!4d<lng>) before viewport camera center (!2d<lng>!3d<lat>).
 *
 * @param {string | null | undefined} rawInput
 * @returns {{ latitude: string, longitude: string, precise: boolean } | null}
 */
export function extractGoogleMapsLocation(rawInput) {
  const raw = String(rawInput || '').trim()
  if (!raw) return null

  const patterns = [
    { regex: /!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/, latIdx: 1, lngIdx: 2, precise: true },
    { regex: /[?&](?:q|ll|query|center)=(-?\d+(?:\.\d+)?)(?:%2C|,)(-?\d+(?:\.\d+)?)/i, latIdx: 1, lngIdx: 2, precise: true },
    { regex: /!2d(-?\d+(?:\.\d+)?)!3d(-?\d+(?:\.\d+)?)/, lngIdx: 1, latIdx: 2, precise: false },
    { regex: /@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/, latIdx: 1, lngIdx: 2, precise: false },
  ]

  for (const { regex, latIdx, lngIdx, precise } of patterns) {
    const m = raw.match(regex)
    if (m) {
      const lat = Number(Number(m[latIdx]).toFixed(6))
      const lng = Number(Number(m[lngIdx]).toFixed(6))
      if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
        return { latitude: String(lat), longitude: String(lng), precise }
      }
    }
  }
  return null
}

/**
 * Backwards-compatible wrapper returning { latitude, longitude }.
 *
 * @param {string | null | undefined} rawInput
 * @returns {{ latitude: string, longitude: string } | null}
 */
export function extractGoogleMapsCoordinates(rawInput) {
  const loc = extractGoogleMapsLocation(rawInput)
  return loc ? { latitude: loc.latitude, longitude: loc.longitude } : null
}

/**
 * Compute straight-line distance in meters between two WGS84 coordinate pairs.
 *
 * @param {number} lat1
 * @param {number} lng1
 * @param {number} lat2
 * @param {number} lng2
 * @returns {number}
 */
export function haversineDistanceMeters(lat1, lng1, lat2, lng2) {
  const toRad = (deg) => (deg * Math.PI) / 180
  const dLat = toRad(lat2 - lat1)
  const dLng = toRad(lng2 - lng1)
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2
  return Math.round(6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(Math.max(0, 1 - a))))
}
