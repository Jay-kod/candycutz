import { computed, onUnmounted, reactive, ref, unref } from 'vue'
import { directionsApi } from '../api/directionsApi'
import { useToast } from '@/core/composables/useToast'

const LOCAL_ORIGIN_STORAGE_KEY = 'candycutz_customer_origin_v1'
const LOCAL_LOCK_STORAGE_KEY = 'candycutz_customer_origin_lock_v1'
const COARSE_GPS_LIMIT_METERS = 500
const AUTO_PIN_LABEL_PREFIX = 'Pinned Location ('

function approxKmBetween(lat1, lng1, lat2, lng2) {
  const toRad = (deg) => (deg * Math.PI) / 180
  const dLat = toRad(lat2 - lat1)
  const dLng = toRad(lng2 - lng1)
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) ** 2
  return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(Math.max(0, 1 - a)))
}

function emptyDraft() {
  return {
    id: null,
    title: 'Home',
    street: '',
    landmark: '',
    city: 'Keffi',
    state: 'Nasarawa',
    latitude: null,
    longitude: null,
    accuracy: null,
    source: 'none', // 'none' | 'saved' | 'map' | 'gps' | 'search'
    isDirty: false,
  }
}

export function useShopDirections(isAuthenticated = false) {
  const toast = useToast()

  // 1. Immutable Locked Shop Anchor
  const shop = ref(null)
  const directions = ref(null)
  const roadRoute = ref(null)
  const addresses = ref([])
  const majorPlaces = ref([])

  // 2. Unified Customer Start Address & Pin 2
  const draftOrigin = reactive(emptyDraft())
  const searchResults = ref([])

  // UI & Async State
  const loadingShop = ref(true)
  const locatingGps = ref(false)
  const trackingLive = ref(false)
  const resolvingRoute = ref(false)
  const searchingPlaces = ref(false)
  const savingAddress = ref(false)
  const geoError = ref('')
  const geoWarning = ref('')
  const locationPermission = ref('prompt')
  const locationPromptState = ref('idle') // 'idle' | 'requesting' | 'needs_enable' | 'denied' | 'unavailable' | 'ready'
  const originLocked = ref(false)
  const travelMode = ref('driving') // 'driving' | 'walking'
  const fitTrigger = ref(0)

  let watchId = null
  let routeSeq = 0
  let geocodeSeq = 0
  let reverseSeq = 0

  const currentOrigin = computed(() => {
    if (draftOrigin.latitude === null || draftOrigin.longitude === null) return null
    const streetOrCoord =
      draftOrigin.street?.trim() ||
      `Pinned (${Number(draftOrigin.latitude).toFixed(5)}, ${Number(draftOrigin.longitude).toFixed(5)})`
    const label = draftOrigin.title?.trim()
      ? `${draftOrigin.title.trim()}: ${streetOrCoord}`
      : streetOrCoord
    return {
      latitude: Number(draftOrigin.latitude),
      longitude: Number(draftOrigin.longitude),
      label,
      ...(draftOrigin.accuracy ? { accuracy: draftOrigin.accuracy } : {}),
    }
  })

  const links = computed(() => directions.value?.directions_links ?? shop.value?.directions_links ?? {})
  const activeDistanceKm = computed(() => roadRoute.value?.distanceKm ?? directions.value?.estimated_distance_km ?? null)
  const activeDriveMinutes = computed(() => {
    if (roadRoute.value?.profile !== 'walking' && roadRoute.value?.durationMinutes) {
      return roadRoute.value.durationMinutes
    }
    if (roadRoute.value?.distanceKm !== undefined && roadRoute.value?.distanceKm !== null) {
      return Math.max(2, Math.ceil((Number(roadRoute.value.distanceKm) / 28.0) * 60))
    }
    return directions.value?.estimated_drive_minutes ?? null
  })
  const activeWalkMinutes = computed(() => {
    if (roadRoute.value?.profile === 'walking' && roadRoute.value?.durationMinutes) {
      return roadRoute.value.durationMinutes
    }
    if (roadRoute.value?.distanceKm !== undefined && roadRoute.value?.distanceKm !== null) {
      return Math.max(3, Math.ceil((Number(roadRoute.value.distanceKm) / 5.0) * 60))
    }
    return directions.value?.estimated_walk_minutes ?? null
  })
  const activeBearing = computed(() => directions.value?.compass_bearing ?? null)

  function readLocalSavedOrigin() {
    if (typeof window === 'undefined' || !window.localStorage) return null
    try {
      const raw = window.localStorage.getItem(LOCAL_ORIGIN_STORAGE_KEY)
      if (!raw) return null
      const parsed = JSON.parse(raw)
      const lat = Number(parsed?.latitude)
      const lng = Number(parsed?.longitude)
      if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null
      return {
        title: String(parsed?.title || 'My Location'),
        street: String(parsed?.street || ''),
        landmark: String(parsed?.landmark || ''),
        city: String(parsed?.city || 'Keffi'),
        state: String(parsed?.state || 'Nasarawa'),
        latitude: Number(lat.toFixed(6)),
        longitude: Number(lng.toFixed(6)),
        locked: Boolean(parsed?.locked),
      }
    } catch {
      return null
    }
  }

  function writeLocalSavedOrigin() {
    if (typeof window === 'undefined' || !window.localStorage) return
    try {
      window.localStorage.setItem(
        LOCAL_ORIGIN_STORAGE_KEY,
        JSON.stringify({
          title: draftOrigin.title,
          street: draftOrigin.street,
          landmark: draftOrigin.landmark,
          city: draftOrigin.city,
          state: draftOrigin.state,
          latitude: draftOrigin.latitude,
          longitude: draftOrigin.longitude,
          locked: originLocked.value,
        })
      )
    } catch {
      // Ignore storage quota errors gracefully
    }
  }

  function readLocalLockState() {
    if (typeof window === 'undefined' || !window.localStorage) return null
    try {
      const raw = window.localStorage.getItem(LOCAL_LOCK_STORAGE_KEY)
      if (!raw) return null
      const parsed = JSON.parse(raw)
      const lat = Number(parsed?.latitude)
      const lng = Number(parsed?.longitude)
      if (!parsed?.locked || !Number.isFinite(lat) || !Number.isFinite(lng)) return null
      return {
        locked: true,
        latitude: Number(lat.toFixed(6)),
        longitude: Number(lng.toFixed(6)),
      }
    } catch {
      return null
    }
  }

  function writeLocalLockState(locked, lat = draftOrigin.latitude, lng = draftOrigin.longitude) {
    if (typeof window === 'undefined' || !window.localStorage) return
    try {
      if (!locked || lat === null || lng === null) {
        window.localStorage.removeItem(LOCAL_LOCK_STORAGE_KEY)
        return
      }
      window.localStorage.setItem(
        LOCAL_LOCK_STORAGE_KEY,
        JSON.stringify({
          locked: true,
          latitude: Number(Number(lat).toFixed(6)),
          longitude: Number(Number(lng).toFixed(6)),
        })
      )
    } catch {
      // Ignore storage errors gracefully
    }
  }

  function notifyLockedOrigin() {
    geoWarning.value = 'Preferred location is locked. Click Unlock Location to select another point.'
  }

  function lockOrigin() {
    if (draftOrigin.latitude === null || draftOrigin.longitude === null) {
      geoError.value = 'Drop your Pin 2 on the map or use your current location before locking it.'
      return false
    }
    stopLiveTracking()
    geoError.value = ''
    geoWarning.value = ''
    originLocked.value = true
    writeLocalLockState(true, draftOrigin.latitude, draftOrigin.longitude)
    toast.success('Preferred location locked on the map.')
    return true
  }

  function unlockOrigin() {
    originLocked.value = false
    geoError.value = ''
    geoWarning.value = ''
    writeLocalLockState(false)
    toast.info('Location unlocked — tap the map, drag Pin 2, or use your current location.')
    return true
  }

  function toggleOriginLock() {
    return originLocked.value ? unlockOrigin() : lockOrigin()
  }

  async function computeShortestRoute({ recenter = false } = {}) {
    if (recenter) fitTrigger.value += 1
    if (draftOrigin.latitude === null || draftOrigin.longitude === null || !shop.value) {
      roadRoute.value = null
      return
    }

    const seq = ++routeSeq
    resolvingRoute.value = true
    const lat = Number(draftOrigin.latitude)
    const lng = Number(draftOrigin.longitude)
    const label = currentOrigin.value?.label || draftOrigin.street || undefined

    try {
      const [backendDir, road] = await Promise.all([
        directionsApi
          .getDirections({ from_lat: lat, from_lng: lng, from_address: label })
          .catch(() => null),
        directionsApi.fetchRoadRoute(lat, lng, shop.value.latitude, shop.value.longitude, {
          travelMode: travelMode.value,
          places: majorPlaces.value,
        }),
      ])
      if (seq !== routeSeq) return
      if (backendDir) directions.value = backendDir
      roadRoute.value = road
    } finally {
      if (seq === routeSeq) resolvingRoute.value = false
    }
  }

  async function setTravelMode(nextMode) {
    const mode = nextMode === 'walking' ? 'walking' : 'driving'
    if (travelMode.value === mode) return
    travelMode.value = mode
    await computeShortestRoute({ recenter: false })
  }

  async function fillEmptyStreetFromCoords(lat, lng) {
    const seq = ++reverseSeq
    const resolved = await directionsApi.reverseGeocode(lat, lng)
    if (seq !== reverseSeq) return
    if (draftOrigin.latitude !== lat || draftOrigin.longitude !== lng) return

    const currentStreet = draftOrigin.street.trim()
    if (!currentStreet || currentStreet.startsWith(AUTO_PIN_LABEL_PREFIX)) {
      draftOrigin.street = resolved.street
    }
    if (!draftOrigin.city) draftOrigin.city = resolved.city || 'Keffi'
    if (!draftOrigin.state) draftOrigin.state = resolved.state || 'Nasarawa'
  }

  async function setPinOnMap(latitude, longitude, { recenter = false } = {}) {
    if (originLocked.value) {
      notifyLockedOrigin()
      return false
    }
    const lat = Number(Number(latitude).toFixed(6))
    const lng = Number(Number(longitude).toFixed(6))
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return false

    stopLiveTracking()
    geoError.value = ''
    geoWarning.value = ''
    locationPromptState.value = 'idle'
    draftOrigin.latitude = lat
    draftOrigin.longitude = lng
    draftOrigin.accuracy = null
    draftOrigin.source = 'map'
    draftOrigin.isDirty = true
    if (!draftOrigin.street.trim()) {
      draftOrigin.street = `Pinned Location (${lat.toFixed(5)}, ${lng.toFixed(5)})`
    }

    await computeShortestRoute({ recenter })
    fillEmptyStreetFromCoords(lat, lng)
    return true
  }

  function requestPosition({ auto = false } = {}) {
    return new Promise((resolve) => {
      navigator.geolocation.getCurrentPosition(
        (pos) => resolve({ pos, error: null }),
        (err) => {
          if (!auto && err?.code !== 1) {
            navigator.geolocation.getCurrentPosition(
              (pos) => resolve({ pos, error: null }),
              (fallbackErr) => resolve({ pos: null, error: fallbackErr || err }),
              { enableHighAccuracy: false, timeout: 6000, maximumAge: 30000 }
            )
            return
          }
          resolve({ pos: null, error: err })
        },
        { enableHighAccuracy: true, timeout: 9000, maximumAge: 0 }
      )
    })
  }

  async function pickCurrentLocation({ auto = false } = {}) {
    if (auto && originLocked.value) return false

    geoError.value = ''
    geoWarning.value = ''
    if (typeof navigator === 'undefined' || !navigator.geolocation) {
      locationPermission.value = 'unsupported'
      if (!auto) {
        locationPromptState.value = 'unavailable'
        geoError.value = 'Location services are unavailable in this browser. Enable device location or tap your street directly on the map.'
      }
      return false
    }

    stopLiveTracking()
    locatingGps.value = true
    if (!auto) {
      locationPromptState.value = 'requesting'
      await checkGeolocationPermission()
    }
    const { pos, error } = await requestPosition({ auto })
    locatingGps.value = false

    if (!pos) {
      if (error?.code === 1) locationPermission.value = 'denied'
      if (!auto) {
        if (error?.code === 1) {
          locationPromptState.value = 'denied'
          geoError.value =
            'Location permission or device location is off. Turn on Location in your browser/device settings, then tap "Try Current Location Again".'
        } else if (error?.code === 2 || error?.code === 3) {
          locationPromptState.value = 'needs_enable'
          geoError.value =
            'Your device location appears to be turned off. Please turn on Device Location (GPS) and tap "Try Current Location Again".'
        } else {
          locationPromptState.value = 'needs_enable'
          geoError.value =
            error?.message || 'Could not detect your current location. Turn on device location or tap directly on the map.'
        }
      }
      return false
    }

    locationPermission.value = 'granted'
    const lat = Number(pos.coords.latitude.toFixed(6))
    const lng = Number(pos.coords.longitude.toFixed(6))
    const acc = Number.isFinite(Number(pos.coords.accuracy)) ? Math.round(Number(pos.coords.accuracy)) : null

    // Reject blurry IP-level positions on automatic init, and warn visibly on explicit clicks
    const farFromShop =
      shop.value?.latitude && shop.value?.longitude
        ? approxKmBetween(lat, lng, Number(shop.value.latitude), Number(shop.value.longitude)) > 120
        : false

    if (auto && ((acc !== null && acc > COARSE_GPS_LIMIT_METERS) || farFromShop)) {
      return false
    }

    if (farFromShop || (acc !== null && acc > COARSE_GPS_LIMIT_METERS)) {
      geoWarning.value = farFromShop
        ? 'Your browser reported a far-away IP location instead of GPS. Please turn on device GPS or tap your exact street on the map.'
        : `Low-precision Wi-Fi/IP location (±${acc} m). Drag Pin 2 on the map to adjust cleanly.`
      if (farFromShop) {
        if (!auto) locationPromptState.value = 'needs_enable'
        return false
      }
    }

    locationPromptState.value = 'ready'
    if (!auto && originLocked.value) {
      originLocked.value = false
      writeLocalLockState(false)
      toast.info('Switched from locked pin to your current location.')
    }
    if (!auto && draftOrigin.source === 'saved') {
      draftOrigin.id = null
      draftOrigin.title = 'Current Location'
      draftOrigin.street = ''
    }

    draftOrigin.latitude = lat
    draftOrigin.longitude = lng
    draftOrigin.accuracy = acc
    draftOrigin.source = 'gps'
    draftOrigin.isDirty = true
    await computeShortestRoute({ recenter: true })
    await fillEmptyStreetFromCoords(lat, lng)
    return true
  }

  function startLiveTracking() {
    if (originLocked.value) {
      notifyLockedOrigin()
      return false
    }
    geoError.value = ''
    geoWarning.value = ''
    if (typeof navigator === 'undefined' || !navigator.geolocation) {
      locationPermission.value = 'unsupported'
      locationPromptState.value = 'unavailable'
      geoError.value = 'Live GPS tracking is not supported in this browser.'
      return false
    }

    stopLiveTracking()
    trackingLive.value = true

    const handlePos = async (pos) => {
      locationPermission.value = 'granted'
      const lat = Number(pos.coords.latitude.toFixed(6))
      const lng = Number(pos.coords.longitude.toFixed(6))
      const acc = Number.isFinite(Number(pos.coords.accuracy)) ? Math.round(Number(pos.coords.accuracy)) : null

      if (acc !== null && acc > 1000) {
        geoWarning.value = `Waiting for clearer GPS signal (currently ±${acc} m).`
      } else {
        geoWarning.value = ''
      }

      const firstFix = draftOrigin.latitude === null
      draftOrigin.latitude = lat
      draftOrigin.longitude = lng
      draftOrigin.accuracy = acc
      draftOrigin.source = 'gps'
      await computeShortestRoute({ recenter: firstFix })
    }

    const handleErr = (err) => {
      trackingLive.value = false
      if (err?.code === 1) locationPermission.value = 'denied'
      geoError.value = err?.message || 'Live GPS tracking was interrupted. Tap the map to pin your location.'
    }

    navigator.geolocation.getCurrentPosition(handlePos, handleErr, { enableHighAccuracy: true, timeout: 10000 })
    watchId = navigator.geolocation.watchPosition(handlePos, handleErr, {
      enableHighAccuracy: true,
      maximumAge: 4000,
      timeout: 12000,
    })
  }

  function stopLiveTracking() {
    if (watchId !== null && typeof navigator !== 'undefined' && navigator.geolocation) {
      navigator.geolocation.clearWatch(watchId)
    }
    watchId = null
    trackingLive.value = false
  }

  async function searchPlaces(queryText = null) {
    const q = String(queryText ?? draftOrigin.street ?? '').trim()
    if (q.length < 2) {
      searchResults.value = []
      return []
    }
    const seq = ++geocodeSeq
    searchingPlaces.value = true
    try {
      const rows = await directionsApi.geocodeQuery(q)
      if (seq !== geocodeSeq) return []
      searchResults.value = Array.isArray(rows) ? rows : []
      return searchResults.value
    } finally {
      if (seq === geocodeSeq) searchingPlaces.value = false
    }
  }

  async function applySearchResult(item) {
    if (!item) return false
    if (originLocked.value) {
      notifyLockedOrigin()
      return false
    }
    stopLiveTracking()
    geoError.value = ''
    geoWarning.value = ''
    locationPromptState.value = 'idle'
    draftOrigin.street = item.street || item.label
    draftOrigin.city = item.city || 'Keffi'
    draftOrigin.state = item.state || 'Nasarawa'
    draftOrigin.latitude = Number(Number(item.latitude).toFixed(6))
    draftOrigin.longitude = Number(Number(item.longitude).toFixed(6))
    draftOrigin.accuracy = null
    draftOrigin.source = 'search'
    draftOrigin.isDirty = true
    searchResults.value = []
    await computeShortestRoute({ recenter: true })
    return true
  }

  async function selectSavedAddress(addr) {
    if (!addr) return
    stopLiveTracking()
    originLocked.value = false
    writeLocalLockState(false)
    geoError.value = ''
    geoWarning.value = ''
    locationPromptState.value = 'idle'
    searchResults.value = []

    draftOrigin.id = addr.id
    draftOrigin.title = addr.title || 'Home'
    draftOrigin.street = addr.street || ''
    draftOrigin.landmark = addr.landmark || ''
    draftOrigin.city = addr.city || 'Keffi'
    draftOrigin.state = addr.state || 'Nasarawa'
    draftOrigin.accuracy = null
    draftOrigin.source = 'saved'
    draftOrigin.isDirty = false

    if (addr.latitude !== null && addr.longitude !== null) {
      draftOrigin.latitude = Number(Number(addr.latitude).toFixed(6))
      draftOrigin.longitude = Number(Number(addr.longitude).toFixed(6))
      await computeShortestRoute({ recenter: true })
      return
    }

    // Legacy saved text without coordinates: locate via biased geocoder or prompt user cleanly to drop pin once
    draftOrigin.latitude = null
    draftOrigin.longitude = null
    const matches = await directionsApi.geocodeQuery(addr.formatted || addr.street)
    if (matches.length > 0) {
      draftOrigin.latitude = Number(Number(matches[0].latitude).toFixed(6))
      draftOrigin.longitude = Number(Number(matches[0].longitude).toFixed(6))
      draftOrigin.isDirty = true
      geoWarning.value = 'Loaded street match — verify Pin 2 on the map and click Save Changes to lock it.'
      await computeShortestRoute({ recenter: true })
    } else {
      roadRoute.value = null
      geoWarning.value = 'This saved address has no map pin yet. Tap directly on the map and click Save Changes.'
    }
  }

  function startNewAddress({ keepCoordinates = true } = {}) {
    const prevLat = keepCoordinates ? draftOrigin.latitude : null
    const prevLng = keepCoordinates ? draftOrigin.longitude : null
    originLocked.value = false
    writeLocalLockState(false)
    geoError.value = ''
    geoWarning.value = ''
    locationPromptState.value = 'idle'
    Object.assign(draftOrigin, emptyDraft())
    if (keepCoordinates && prevLat !== null && prevLng !== null) {
      draftOrigin.latitude = prevLat
      draftOrigin.longitude = prevLng
      draftOrigin.source = 'map'
      draftOrigin.isDirty = true
    }
    searchResults.value = []
  }

  function markDraftEdited() {
    draftOrigin.isDirty = true
  }

  async function saveCustomerAddress() {
    const cleanTitle = (draftOrigin.title || 'Home').trim()
    const cleanStreet = draftOrigin.street.trim()
    const hasCoords = draftOrigin.latitude !== null && draftOrigin.longitude !== null

    if (!hasCoords && cleanStreet.length < 3) {
      toast.error('Please type a street/landmark or tap your spot on the map before saving.')
      return false
    }

    const payload = {
      title: cleanTitle || 'Home',
      street: cleanStreet || `Pinned Location (${Number(draftOrigin.latitude).toFixed(5)}, ${Number(draftOrigin.longitude).toFixed(5)})`,
      landmark: draftOrigin.landmark.trim() || '',
      city: (draftOrigin.city || 'Keffi').trim(),
      state: (draftOrigin.state || 'Nasarawa').trim(),
      latitude: hasCoords ? Number(draftOrigin.latitude) : null,
      longitude: hasCoords ? Number(draftOrigin.longitude) : null,
      is_default: true,
    }

    if (!unref(isAuthenticated)) {
      draftOrigin.title = payload.title
      draftOrigin.street = payload.street
      writeLocalSavedOrigin()
      draftOrigin.isDirty = false
      toast.success('Your address and pin have been saved on this device.')
      return true
    }

    savingAddress.value = true
    try {
      const saved = draftOrigin.id
        ? await directionsApi.updateAddress(draftOrigin.id, payload)
        : await directionsApi.createAddress(payload)

      await loadAddresses({ autoSelect: false })
      if (saved) {
        draftOrigin.id = saved.id
        draftOrigin.title = saved.title || payload.title
        draftOrigin.street = saved.street || payload.street
        draftOrigin.landmark = saved.landmark || ''
        draftOrigin.city = saved.city || payload.city
        draftOrigin.state = saved.state || payload.state
        draftOrigin.latitude = saved.latitude ?? payload.latitude
        draftOrigin.longitude = saved.longitude ?? payload.longitude
        draftOrigin.source = 'saved'
      }
      draftOrigin.isDirty = false
      writeLocalSavedOrigin()
      toast.success(draftOrigin.id ? 'Address & pin saved successfully.' : 'Address saved to your account.')
      return true
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Unable to save address right now.')
      return false
    } finally {
      savingAddress.value = false
    }
  }

  async function removeAddress(id) {
    try {
      await directionsApi.deleteAddress(id)
      addresses.value = addresses.value.filter((a) => a.id !== id)
      if (draftOrigin.id === id) {
        draftOrigin.id = null
      }
      toast.success('Saved address removed.')
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Could not remove saved address.')
    }
  }

  async function loadAddresses({ autoSelect = true } = {}) {
    if (!unref(isAuthenticated)) return
    try {
      const list = await directionsApi.listAddresses()
      addresses.value = Array.isArray(list) ? list : []
      if (autoSelect && draftOrigin.latitude === null && addresses.value.length > 0) {
        const def = addresses.value.find((a) => a.is_default) || addresses.value[0]
        await selectSavedAddress(def)
      }
    } catch {
      addresses.value = []
    }
  }

  async function checkGeolocationPermission() {
    if (typeof navigator === 'undefined' || !navigator.geolocation) {
      locationPermission.value = 'unsupported'
      return 'unsupported'
    }
    try {
      if (navigator.permissions?.query) {
        const status = await navigator.permissions.query({ name: 'geolocation' })
        locationPermission.value = status.state
        return status.state
      }
    } catch {
      // Safari / browsers without Permissions API
    }
    return locationPermission.value
  }

  async function loadMajorPlaces() {
    if (!shop.value?.latitude || !shop.value?.longitude) return
    const places = await directionsApi.fetchNearbyMajorPlaces(shop.value.latitude, shop.value.longitude)
    majorPlaces.value = Array.isArray(places) ? places : []
  }

  async function init() {
    loadingShop.value = true
    try {
      shop.value = await directionsApi.getShopLocation()
      directions.value = { to: shop.value, directions_links: shop.value?.directions_links }
      loadMajorPlaces()

      await loadAddresses({ autoSelect: true })

      let localSavedLocked = false
      if (draftOrigin.latitude === null) {
        const localSaved = readLocalSavedOrigin()
        if (localSaved) {
          localSavedLocked = Boolean(localSaved.locked)
          Object.assign(draftOrigin, { ...emptyDraft(), ...localSaved, source: 'saved', isDirty: false })
          await computeShortestRoute({ recenter: true })
        }
      }

      const localLock = readLocalLockState()
      if (
        draftOrigin.latitude !== null &&
        draftOrigin.longitude !== null &&
        (localSavedLocked ||
          (localLock?.locked &&
            draftOrigin.latitude === localLock.latitude &&
            draftOrigin.longitude === localLock.longitude))
      ) {
        originLocked.value = true
      }

      const perm = await checkGeolocationPermission()
      if (draftOrigin.latitude === null && !originLocked.value && perm === 'granted') {
        await pickCurrentLocation({ auto: true })
      }
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Unable to load shop location.')
    } finally {
      loadingShop.value = false
    }
  }

  onUnmounted(stopLiveTracking)

  return {
    shop,
    addresses,
    majorPlaces,
    draftOrigin,
    currentOrigin,
    searchResults,
    loadingShop,
    locatingGps,
    trackingLive,
    resolvingRoute,
    searchingPlaces,
    savingAddress,
    geoError,
    geoWarning,
    locationPermission,
    locationPromptState,
    originLocked,
    travelMode,
    fitTrigger,
    links,
    roadRoute,
    activeDistanceKm,
    activeDriveMinutes,
    activeWalkMinutes,
    activeBearing,
    init,
    setTravelMode,
    setPinOnMap,
    pickCurrentLocation,
    startLiveTracking,
    stopLiveTracking,
    lockOrigin,
    unlockOrigin,
    toggleOriginLock,
    notifyLockedOrigin,
    searchPlaces,
    applySearchResult,
    selectSavedAddress,
    startNewAddress,
    markDraftEdited,
    saveCustomerAddress,
    removeAddress,
  }
}
