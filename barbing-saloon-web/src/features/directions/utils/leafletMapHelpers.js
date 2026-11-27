/**
 * Lazy-load Leaflet and its stylesheet only when interactive map pinning/routing opens.
 *
 * @returns {Promise<import('leaflet')>}
 */
export async function loadLeaflet() {
  const [leafletMod] = await Promise.all([
    import('leaflet'),
    import('leaflet/dist/leaflet.css'),
  ])
  return leafletMod.default || leafletMod
}

/**
 * Attach Google Satellite Hybrid ('y') or Street ('m') tiles to a Leaflet map instance.
 *
 * @param {any} L
 * @param {any} map
 * @param {'y' | 'm'} styleCode
 * @param {any} previousLayer
 * @returns {any}
 */
export function attachGoogleTileLayer(L, map, styleCode = 'y', previousLayer = null) {
  if (previousLayer) {
    previousLayer.remove()
  }
  return L.tileLayer(`https://mt{s}.google.com/vt/lyrs=${styleCode}&x={x}&y={y}&z={z}`, {
    subdomains: ['0', '1', '2', '3'],
    attribution: '&copy; Google Maps',
    maxZoom: 20,
  }).addTo(map)
}

function escapePinText(text) {
  return String(text || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
}

/**
 * Build a labeled rounded map pin icon for Shop, Customer, and active turn markers.
 *
 * @param {any} L
 * @param {string} color
 * @param {string} label
 * @returns {any}
 */
export function createMapPinIcon(L, color, label) {
  return L.divIcon({
    className: 'custom-directions-pin',
    html: `<div style="width:max-content;display:flex;flex-direction:column;align-items:center;transform:translate(-50%,-100%);">
      <span style="background:${color};color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;white-space:nowrap;box-shadow:0 2px 6px rgba(0,0,0,0.35);margin-bottom:3px;">${escapePinText(label)}</span>
      <span style="width:14px;height:14px;border-radius:999px;background:${color};border:2.5px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.35);"></span>
      <span style="width:2px;height:6px;background:${color};border-radius:0 0 2px 2px;"></span>
    </div>`,
    iconSize: [0, 0],
    iconAnchor: [0, 0],
  })
}
