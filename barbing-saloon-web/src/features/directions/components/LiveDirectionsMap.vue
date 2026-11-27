<template>
  <div
    ref="wrapperRef"
    :class="[
      'relative flex flex-col overflow-hidden border border-[var(--color-border)] bg-[var(--color-bg-tertiary)] shadow-sm',
      isFullscreen ? 'h-[100dvh] w-full rounded-none' : 'rounded-2xl',
    ]"
  >
    <!-- Single Unified Map Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-[var(--color-border)] bg-[var(--color-bg-secondary)] px-3 py-2.5 sm:px-3.5">
      <div class="flex items-center justify-between gap-2 min-w-0">
        <div class="flex items-center gap-2 min-w-0">
          <span class="inline-flex shrink-0 items-center rounded-lg bg-[var(--color-accent)] px-2.5 py-1 text-[11px] sm:text-xs font-bold text-[var(--color-accent-foreground)]">
            {{ showGoogleCard ? 'Google Place Card' : 'Live Route Map' }}
          </span>
          <span class="truncate text-xs font-medium text-[var(--color-text-secondary)]">
            {{ shop?.name || 'CandyCutz Studio' }}
          </span>
        </div>

        <button
          type="button"
          :aria-label="isFullscreen ? 'Exit fullscreen map' : 'Open fullscreen map'"
          class="sm:hidden shrink-0 rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-2.5 py-1.5 text-xs font-semibold text-[var(--color-text-primary)] hover:bg-[var(--color-bg-tertiary)] cursor-pointer"
          @click="toggleFullscreen"
        >
          {{ isFullscreen ? 'Exit' : 'Expand' }}
        </button>
      </div>

      <div class="flex flex-wrap items-center gap-1.5">
        <button
          type="button"
          class="min-h-[36px] flex-1 sm:flex-none rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-2.5 sm:px-3 py-1.5 text-xs font-semibold text-[var(--color-text-primary)] hover:border-[var(--color-accent)]/50 cursor-pointer"
          @click="showGoogleCard = !showGoogleCard"
        >
          {{ showGoogleCard ? 'Back to Interactive Route' : 'Google Place Info' }}
        </button>

        <button
          v-if="!showGoogleCard"
          type="button"
          class="min-h-[36px] flex-1 sm:flex-none rounded-lg border border-emerald-600/40 bg-emerald-500/10 px-2.5 sm:px-3 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-500/20 dark:text-emerald-300 cursor-pointer"
          @click="fitMapView"
        >
          Re-center
        </button>

        <button
          type="button"
          :aria-label="isFullscreen ? 'Exit fullscreen map' : 'Open fullscreen map'"
          class="hidden sm:inline-flex rounded-lg border border-[var(--color-border)] bg-[var(--color-bg-primary)] px-2.5 py-1.5 text-xs font-semibold text-[var(--color-text-primary)] hover:bg-[var(--color-bg-tertiary)] cursor-pointer"
          @click="toggleFullscreen"
        >
          {{ isFullscreen ? 'Exit' : 'Expand' }}
        </button>
      </div>
    </div>

    <!-- Main Unified Map Stage -->
    <div :class="['relative w-full bg-[var(--color-bg-tertiary)]', isFullscreen ? 'flex-1 flex flex-col' : '']">
      <!-- Optional Original Google Maps Place Info View (Reviews/Photos card from Admin Contact Page) -->
      <template v-if="showGoogleCard">
        <iframe
          v-if="embedMapSrc"
          :src="embedMapSrc"
          title="CandyCutz Google Maps Place Card"
          :class="['w-full border-0', isFullscreen ? 'flex-1 h-full' : 'h-[300px] sm:h-[440px] lg:h-[480px]']"
          allowfullscreen
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
        />
        <div
          v-else
          class="flex h-[300px] sm:h-[440px] lg:h-[480px] flex-col items-center justify-center gap-2 px-6 text-center text-xs text-[var(--color-text-secondary)]"
        >
          <p class="font-semibold text-[var(--color-text-primary)]">Google Place embed card is not set in Admin Contact settings.</p>
        </div>
      </template>

      <!-- Always-Keep Unified Interactive Google Satellite/Street Map -->
      <div v-show="!showGoogleCard" class="relative w-full">
        <div
          ref="mapContainerEl"
          :class="[
            'w-full',
            originLocked ? 'cursor-default' : 'cursor-crosshair',
            pinModeActive && !originLocked ? 'ring-2 ring-inset ring-emerald-500' : '',
            isFullscreen ? 'h-[calc(100dvh-118px)]' : 'h-[300px] sm:h-[440px] lg:h-[480px]',
          ]"
        />

        <!-- Top-right Satellite / Street & Follow GPS Controls (clear of Leaflet top-left zoom buttons) -->
        <div class="absolute right-2.5 top-2.5 z-[400] flex max-w-[calc(100%-58px)] flex-wrap items-center justify-end gap-1.5">
          <div class="inline-flex rounded-lg border border-zinc-200/80 bg-white/95 p-0.5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/95">
            <button
              type="button"
              :class="[
                'rounded-md px-2 py-1 text-[11px] font-semibold cursor-pointer',
                tileStyle === 'y'
                  ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)]'
                  : 'text-zinc-700 dark:text-zinc-300',
              ]"
              @click="switchLayer('y')"
            >
              Satellite
            </button>
            <button
              type="button"
              :class="[
                'rounded-md px-2 py-1 text-[11px] font-semibold cursor-pointer',
                tileStyle === 'm'
                  ? 'bg-[var(--color-accent)] text-[var(--color-accent-foreground)]'
                  : 'text-zinc-700 dark:text-zinc-300',
              ]"
              @click="switchLayer('m')"
            >
              Street
            </button>
          </div>

          <button
            type="button"
            :class="[
              'rounded-lg border px-2.5 py-1 text-[11px] font-bold shadow-sm cursor-pointer',
              trackingLive
                ? 'border-emerald-600 bg-emerald-600 text-white'
                : 'border-zinc-200/80 bg-white/95 text-zinc-800 hover:bg-white dark:border-zinc-700 dark:bg-zinc-900/95 dark:text-zinc-100',
            ]"
            @click="$emit('toggle-live-gps')"
          >
            {{ trackingLive ? 'Live GPS On' : 'Follow Live GPS' }}
          </button>
        </div>

        <!-- Bottom HUD: Active Step when Routing, or Clear Tap Hint -->
        <div class="pointer-events-none absolute bottom-2.5 left-2.5 right-2.5 z-[400] flex flex-wrap items-center justify-between gap-2 rounded-xl border border-zinc-200/80 bg-white/95 px-3 py-1.5 text-[11px] shadow-md dark:border-zinc-700 dark:bg-zinc-900/95">
          <span v-if="activeStep?.instruction" class="font-bold text-zinc-900 dark:text-zinc-100 truncate">
            Next: {{ activeStep.instruction }}
          </span>
          <span v-else class="font-medium text-zinc-700 dark:text-zinc-200">
            {{
              originLocked && origin
                ? 'Pin 2 is locked as your preferred location — click Unlock Pin to choose another point'
                : pinModeActive
                  ? 'Tap your exact street or building on the map to drop Pin 2'
                  : origin
                    ? 'Tap anywhere or drag green Pin 2 to adjust your start location'
                    : 'Tap anywhere on the map to place your starting Pin 2'
            }}
          </span>
          <span
            v-if="activeStep?.distanceMeters"
            class="shrink-0 font-mono text-[11px] font-bold text-amber-600 dark:text-amber-400"
          >
            {{ activeStep.distanceMeters >= 1000 ? `${(activeStep.distanceMeters / 1000).toFixed(1)} km` : `${activeStep.distanceMeters} m` }}
          </span>
        </div>
      </div>

      <!-- Locked Shop Pin & Customer Pin 2 Footer -->
      <div class="flex flex-col gap-1.5 sm:flex-row sm:items-center sm:justify-between border-t border-[var(--color-border)] bg-[var(--color-bg-secondary)] px-3 py-2 sm:px-3.5 text-[11px]">
        <p
          :class="[
            'break-words sm:truncate',
            shop?.location_lock && !shop.location_lock.verified
              ? 'font-semibold text-rose-600 dark:text-rose-400'
              : 'text-[var(--color-text-secondary)]',
          ]"
        >
          {{
            shop?.location_lock && !shop.location_lock.verified
              ? shop.location_lock.message
              : `Pin 1 (Shop Locked): ${shop?.latitude ?? '—'}, ${shop?.longitude ?? '—'}`
          }}
        </p>
        <div class="flex flex-wrap items-center justify-between gap-2 min-w-0">
          <p
            :class="[
              'font-semibold sm:truncate',
              origin ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400',
            ]"
          >
            {{
              origin
                ? `${originLocked ? 'Pin 2 (Locked)' : 'Pin 2 (You)'}: ${origin.latitude}, ${origin.longitude}`
                : 'Pin 2 not set — use current location, tap map, or type address'
            }}
          </p>
          <button
            v-if="origin"
            type="button"
            :aria-pressed="originLocked"
            :class="[
              'shrink-0 rounded-lg border px-2.5 py-1 text-[11px] font-bold transition cursor-pointer',
              originLocked
                ? 'border-amber-600/50 bg-amber-500/15 text-amber-800 hover:bg-amber-500/25 dark:text-amber-300'
                : 'border-emerald-600/50 bg-emerald-600 text-white hover:bg-emerald-700',
            ]"
            @click="$emit('toggle-origin-lock')"
          >
            {{ originLocked ? 'Unlock Pin' : 'Lock Pin' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { resolveShopGoogleEmbedUrl } from '../utils/googleMaps'
import { attachGoogleTileLayer, createMapPinIcon, loadLeaflet } from '../utils/leafletMapHelpers'

const props = defineProps({
  shop: { type: Object, default: null },
  origin: { type: Object, default: null },
  route: { type: Object, default: null },
  fitTrigger: { type: Number, default: 0 },
  trackingLive: { type: Boolean, default: false },
  pinModeActive: { type: Boolean, default: false },
  originLocked: { type: Boolean, default: false },
  activeStep: { type: Object, default: null },
})

const emit = defineEmits(['pin-origin', 'toggle-live-gps', 'toggle-origin-lock', 'locked-interaction'])

const wrapperRef = ref(null)
const mapContainerEl = ref(null)
const isFullscreen = ref(false)
const showGoogleCard = ref(false)
const tileStyle = ref('y')

const embedMapSrc = computed(() => resolveShopGoogleEmbedUrl(props.shop))

let map = null
let L = null
let tileLayer = null
let shopMarker = null
let originMarker = null
let routeLine = null
let stepMarker = null

function switchLayer(styleCode) {
  tileStyle.value = styleCode
  if (!map || !L) return
  tileLayer = attachGoogleTileLayer(L, map, styleCode, tileLayer)
}

function fitMapView() {
  if (!map || !L || !props.shop?.latitude || !props.shop?.longitude) return
  const shopLatLng = [Number(props.shop.latitude), Number(props.shop.longitude)]

  if (!props.origin?.latitude || !props.origin?.longitude) {
    map.setView(shopLatLng, 16)
    return
  }

  const bounds = L.latLngBounds([
    shopLatLng,
    [Number(props.origin.latitude), Number(props.origin.longitude)],
  ])
  if (Array.isArray(props.route?.coordinates)) {
    for (const point of props.route.coordinates) {
      if (Array.isArray(point) && point.length >= 2) {
        bounds.extend(point)
      }
    }
  }
  map.fitBounds(bounds, { padding: [55, 55], maxZoom: 17 })
}

function syncOverlays() {
  if (!map || !L || !props.shop?.latitude || !props.shop?.longitude) return

  const shopLatLng = [Number(props.shop.latitude), Number(props.shop.longitude)]
  if (!shopMarker) {
    shopMarker = L.marker(shopLatLng, {
      icon: createMapPinIcon(L, '#d97706', `Pin 1: ${props.shop.name || 'CandyCutz Shop'}`),
      draggable: false,
    }).addTo(map)
  } else {
    shopMarker.setLatLng(shopLatLng)
  }

  if (props.origin?.latitude !== undefined && props.origin?.latitude !== null && props.origin?.longitude !== null) {
    const originLatLng = [Number(props.origin.latitude), Number(props.origin.longitude)]
    const originPinIcon = createMapPinIcon(
      L,
      props.originLocked ? '#047857' : '#059669',
      props.originLocked ? 'Pin 2: Preferred (Locked)' : 'Pin 2: You (Drag)'
    )
    if (!originMarker) {
      originMarker = L.marker(originLatLng, {
        icon: originPinIcon,
        draggable: !props.originLocked,
      }).addTo(map)
      originMarker.on('dragend', (ev) => {
        if (props.originLocked) {
          originMarker.setLatLng([Number(props.origin.latitude), Number(props.origin.longitude)])
          emit('locked-interaction')
          return
        }
        const pos = ev.target.getLatLng()
        emit('pin-origin', {
          latitude: Number(pos.lat.toFixed(6)),
          longitude: Number(pos.lng.toFixed(6)),
        })
      })
    } else {
      originMarker.setLatLng(originLatLng)
      originMarker.setIcon?.(originPinIcon)
      if (props.originLocked) {
        originMarker.dragging?.disable?.()
      } else {
        originMarker.dragging?.enable?.()
      }
    }
  } else if (originMarker) {
    originMarker.remove()
    originMarker = null
  }

  if (routeLine) {
    routeLine.remove()
    routeLine = null
  }
  if (Array.isArray(props.route?.coordinates) && props.route.coordinates.length > 1) {
    routeLine = L.polyline(props.route.coordinates, {
      color: props.route.isFallback ? '#2563eb' : '#f59e0b',
      weight: 5,
      opacity: 0.92,
      ...(props.route.isFallback ? { dashArray: '8 6' } : {}),
    }).addTo(map)
  }

  if (stepMarker) {
    stepMarker.remove()
    stepMarker = null
  }
  if (Number.isFinite(Number(props.activeStep?.latitude)) && Number.isFinite(Number(props.activeStep?.longitude))) {
    stepMarker = L.circleMarker(
      [Number(props.activeStep.latitude), Number(props.activeStep.longitude)],
      {
        radius: 8,
        color: '#ffffff',
        weight: 2.5,
        fillColor: '#2563eb',
        fillOpacity: 1,
      }
    ).addTo(map)
  }
}

async function initMap() {
  if (map || !mapContainerEl.value) return
  L = await loadLeaflet()
  if (!mapContainerEl.value) return

  const startLat = Number(props.shop?.latitude ?? 8.854541)
  const startLng = Number(props.shop?.longitude ?? 7.915391)
  map = L.map(mapContainerEl.value).setView([startLat, startLng], 16)
  switchLayer(tileStyle.value)

  map.on('click', (ev) => {
    if (props.originLocked) {
      emit('locked-interaction')
      return
    }
    emit('pin-origin', {
      latitude: Number(ev.latlng.lat.toFixed(6)),
      longitude: Number(ev.latlng.lng.toFixed(6)),
    })
  })

  syncOverlays()
  fitMapView()
}

watch(
  () => props.shop,
  (newShop, oldShop) => {
    if (!map) return
    syncOverlays()
    if (!oldShop && newShop?.latitude) {
      fitMapView()
    }
  },
  { deep: true }
)

watch(
  () => [props.origin, props.route, props.activeStep, props.originLocked],
  ([newOrigin], [oldOrigin]) => {
    if (!map) return
    syncOverlays()
    if (!oldOrigin && newOrigin) {
      fitMapView()
    }
  },
  { deep: true }
)

watch(
  () => props.fitTrigger,
  () => {
    if (map) {
      syncOverlays()
      fitMapView()
    }
  }
)

watch(
  () => showGoogleCard.value,
  async (showingCard) => {
    if (!showingCard && map) {
      await nextTick()
      map.invalidateSize?.()
    }
  }
)

async function toggleFullscreen() {
  if (typeof document === 'undefined' || !wrapperRef.value) return
  try {
    if (!document.fullscreenElement && typeof wrapperRef.value.requestFullscreen === 'function') {
      await wrapperRef.value.requestFullscreen()
      return
    }
    if (document.fullscreenElement && typeof document.exitFullscreen === 'function') {
      await document.exitFullscreen()
      return
    }
  } catch {
    // Fallback cleanly if Fullscreen API is unavailable
  }
  isFullscreen.value = !isFullscreen.value
}

function handleFullscreenChange() {
  isFullscreen.value = Boolean(typeof document !== 'undefined' && document.fullscreenElement === wrapperRef.value)
  if (map) {
    nextTick(() => map.invalidateSize?.())
  }
}

onMounted(async () => {
  await initMap()
  if (typeof document !== 'undefined') {
    document.addEventListener('fullscreenchange', handleFullscreenChange)
  }
})

onUnmounted(() => {
  if (map) {
    map.remove()
    map = null
  }
  if (typeof document !== 'undefined') {
    document.removeEventListener('fullscreenchange', handleFullscreenChange)
  }
})
</script>
