<template>
  <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
    <div ref="mapContainer" class="h-[340px] w-full sm:h-[420px] z-0" />

    <div class="pointer-events-none absolute top-3 left-3 right-3 z-[400] flex flex-wrap items-start justify-between gap-2">
      <div class="pointer-events-auto rounded-xl border border-zinc-200/90 bg-white/95 px-3 py-2 text-xs shadow-md backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/95">
        <p class="font-bold text-amber-600">Shop Destination Locked</p>
        <p class="max-w-xs truncate font-medium text-zinc-900 dark:text-white">{{ shop?.name }}</p>
      </div>

      <button
        type="button"
        class="pointer-events-auto rounded-lg border border-zinc-200 bg-white/95 px-3 py-1.5 text-xs font-semibold text-zinc-800 shadow-md backdrop-blur hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900/95 dark:text-white"
        @click="fitMapView"
      >
        Re-center Route
      </button>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  shop: { type: Object, default: null },
  origin: { type: Object, default: null },
  roadRoute: { type: Object, default: null },
})

const mapContainer = ref(null)
let map = null
let shopMarker = null
let originMarker = null
let routeLine = null

function createPinIcon(color, label) {
  return L.divIcon({
    className: 'custom-map-pin',
    html: `<div style="display:flex;flex-direction:column;align-items:center;transform:translate(-50%,-100%);">
      <span style="background:${color};color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:999px;white-space:nowrap;box-shadow:0 2px 6px rgba(0,0,0,0.35);margin-bottom:3px;">${label}</span>
      <span style="width:14px;height:14px;border-radius:999px;background:${color};border:2.5px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.35);"></span>
    </div>`,
    iconSize: [0, 0],
    iconAnchor: [0, 0],
  })
}

function fitMapView() {
  if (!map || !props.shop) return
  const points = [[props.shop.latitude, props.shop.longitude]]
  if (props.origin?.latitude && props.origin?.longitude) {
    points.push([props.origin.latitude, props.origin.longitude])
    map.fitBounds(L.latLngBounds(points), { padding: [55, 55], maxZoom: 16 })
  } else {
    map.setView([props.shop.latitude, props.shop.longitude], 15)
  }
}

function buildPopupNode(title, subtitle) {
  const wrapper = document.createElement('div')
  const strong = document.createElement('strong')
  strong.textContent = title || ''
  wrapper.appendChild(strong)
  if (subtitle) {
    wrapper.appendChild(document.createElement('br'))
    const span = document.createElement('span')
    span.style.fontSize = '11px'
    span.textContent = subtitle
    wrapper.appendChild(span)
  }
  return wrapper
}

function renderOverlays() {
  if (!map || !props.shop) return

  if (shopMarker) shopMarker.remove()
  shopMarker = L.marker([props.shop.latitude, props.shop.longitude], {
    icon: createPinIcon('#d97706', 'CandyCutz Shop'),
    draggable: false,
  })
    .addTo(map)
    .bindPopup(buildPopupNode(props.shop.name, props.shop.address))

  if (originMarker) {
    originMarker.remove()
    originMarker = null
  }
  if (routeLine) {
    routeLine.remove()
    routeLine = null
  }

  if (props.origin?.latitude && props.origin?.longitude) {
    originMarker = L.marker([props.origin.latitude, props.origin.longitude], {
      icon: createPinIcon('#059669', 'You'),
      draggable: false,
    })
      .addTo(map)
      .bindPopup(buildPopupNode('Starting Point', props.origin.label || 'Your location'))

    const latLngs = props.roadRoute?.coordinates?.length
      ? props.roadRoute.coordinates
      : [
          [props.origin.latitude, props.origin.longitude],
          [props.shop.latitude, props.shop.longitude],
        ]

    routeLine = L.polyline(latLngs, {
      color: props.roadRoute?.coordinates?.length ? '#d97706' : '#059669',
      weight: 4,
      opacity: 0.9,
      dashArray: props.roadRoute?.coordinates?.length ? null : '8 6',
    }).addTo(map)
  }

  fitMapView()
}

onMounted(() => {
  if (!mapContainer.value) return
  const initLat = props.shop?.latitude ?? 8.8486
  const initLng = props.shop?.longitude ?? 7.8736

  map = L.map(mapContainer.value, { zoomControl: true }).setView([initLat, initLng], 15)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map)

  renderOverlays()
})

watch(() => [props.shop, props.origin, props.roadRoute], renderOverlays, { deep: true })

onUnmounted(() => {
  if (map) {
    map.remove()
    map = null
  }
})
</script>
