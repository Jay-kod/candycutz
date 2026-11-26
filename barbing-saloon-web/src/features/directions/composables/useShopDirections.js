import { computed, onUnmounted, ref } from 'vue'
import { directionsApi } from '../api/directionsApi'
import { useToast } from '@/core/composables/useToast'

export function useShopDirections(isAuthenticated = false) {
  const toast = useToast()

  const shop = ref(null)
  const directions = ref(null)
  const roadRoute = ref(null)
  const addresses = ref([])

  const loadingShop = ref(true)
  const trackingLive = ref(false)
  const resolvingRoute = ref(false)
  const savingAddress = ref(false)
  const geoError = ref('')

  const originMode = ref('manual') // 'live' | 'saved' | 'manual'
  const selectedAddressId = ref(null)
  const manualAddressText = ref('')
  const currentOrigin = ref(null) // { latitude, longitude, label }
  const geocodeResults = ref([])

  let watchId = null
  let routeSeq = 0
  let geocodeSeq = 0

  const links = computed(() => directions.value?.directions_links ?? shop.value?.directions_links ?? {})
  const activeDistanceKm = computed(() => roadRoute.value?.distanceKm ?? directions.value?.estimated_distance_km ?? null)
  const activeDriveMinutes = computed(() => roadRoute.value?.durationMinutes ?? directions.value?.estimated_drive_minutes ?? null)
  const activeWalkMinutes = computed(() => directions.value?.estimated_walk_minutes ?? null)
  const activeBearing = computed(() => directions.value?.compass_bearing ?? null)

  async function refreshDirections(lat = null, lng = null, label = null) {
    const seq = ++routeSeq
    resolvingRoute.value = true
    try {
      const data = await directionsApi.getDirections({
        from_lat: lat ?? undefined,
        from_lng: lng ?? undefined,
        from_address: label ?? (lat === null ? manualAddressText.value || undefined : undefined),
      })
      if (seq !== routeSeq) return
      directions.value = data
      if (lat !== null && lng !== null && shop.value) {
        const fetched = await directionsApi.fetchRoadRoute(lat, lng, shop.value.latitude, shop.value.longitude)
        if (seq === routeSeq) {
          roadRoute.value = fetched
        }
      } else {
        roadRoute.value = null
      }
    } catch (err) {
      if (seq === routeSeq) {
        toast.error(err?.response?.data?.message || 'Unable to compute directions.')
      }
    } finally {
      if (seq === routeSeq) {
        resolvingRoute.value = false
      }
    }
  }

  async function selectOrigin(lat, lng, label, mode = 'manual') {
    if (mode !== 'live') stopLiveTracking()
    originMode.value = mode
    currentOrigin.value = lat !== null && lng !== null ? { latitude: lat, longitude: lng, label } : null
    await refreshDirections(lat, lng, label)
  }

  function startLiveTracking() {
    geoError.value = ''
    if (typeof navigator === 'undefined' || !navigator.geolocation) {
      geoError.value = 'Browser location services are not available on this device.'
      return
    }
    stopLiveTracking()
    trackingLive.value = true
    originMode.value = 'live'

    const handlePosition = async (pos) => {
      const lat = pos.coords.latitude
      const lng = pos.coords.longitude
      currentOrigin.value = { latitude: lat, longitude: lng, label: 'Your Live GPS Location' }
      await refreshDirections(lat, lng, 'Your Live GPS Location')
    }

    const handleError = (err) => {
      trackingLive.value = false
      geoError.value = err?.message || 'Location permission denied. Pick a saved address or search manually instead.'
    }

    navigator.geolocation.getCurrentPosition(handlePosition, handleError, { enableHighAccuracy: true, timeout: 10000 })
    watchId = navigator.geolocation.watchPosition(handlePosition, handleError, {
      enableHighAccuracy: true,
      maximumAge: 5000,
      timeout: 15000,
    })
  }

  function stopLiveTracking() {
    if (watchId !== null && typeof navigator !== 'undefined' && navigator.geolocation) {
      navigator.geolocation.clearWatch(watchId)
    }
    watchId = null
    trackingLive.value = false
  }

  async function searchManualAddress() {
    const q = manualAddressText.value.trim()
    if (!q) return
    const seq = ++geocodeSeq
    stopLiveTracking()
    originMode.value = 'manual'
    const results = await directionsApi.geocodeQuery(q)
    if (seq !== geocodeSeq) return
    geocodeResults.value = results
    if (geocodeResults.value.length > 0) {
      const top = geocodeResults.value[0]
      await selectOrigin(top.latitude, top.longitude, top.label, 'manual')
    } else {
      await refreshDirections(null, null, q)
      toast.info('Opened turn-by-turn search using your typed address.')
    }
  }

  async function chooseSavedAddress(addr) {
    if (!addr) return
    selectedAddressId.value = addr.id
    if (addr.latitude !== null && addr.longitude !== null) {
      await selectOrigin(addr.latitude, addr.longitude, `${addr.title}: ${addr.formatted}`, 'saved')
    } else {
      stopLiveTracking()
      originMode.value = 'saved'
      const matches = await directionsApi.geocodeQuery(addr.formatted)
      if (matches.length > 0) {
        await selectOrigin(matches[0].latitude, matches[0].longitude, `${addr.title}: ${addr.formatted}`, 'saved')
      } else {
        manualAddressText.value = addr.formatted
        currentOrigin.value = null
        await refreshDirections(null, null, addr.formatted)
      }
    }
  }

  async function saveNewAddress(payload) {
    savingAddress.value = true
    try {
      const created = await directionsApi.createAddress(payload)
      toast.success('Address saved to your profile.')
      await loadAddresses()
      if (created) {
        await chooseSavedAddress(created)
      }
      return true
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Could not save address.')
      return false
    } finally {
      savingAddress.value = false
    }
  }

  async function removeAddress(id) {
    try {
      await directionsApi.deleteAddress(id)
      addresses.value = addresses.value.filter((a) => a.id !== id)
      if (selectedAddressId.value === id) {
        selectedAddressId.value = null
      }
      toast.success('Saved address removed.')
    } catch (err) {
      toast.error(err?.response?.data?.message || 'Could not remove address.')
    }
  }

  async function loadAddresses() {
    if (!isAuthenticated) return
    try {
      const list = await directionsApi.listAddresses()
      addresses.value = Array.isArray(list) ? list : []
      if (!currentOrigin.value && addresses.value.length > 0) {
        const def = addresses.value.find((a) => a.is_default) || addresses.value[0]
        await chooseSavedAddress(def)
      }
    } catch {
      addresses.value = []
    }
  }

  async function init() {
    loadingShop.value = true
    try {
      shop.value = await directionsApi.getShopLocation()
      directions.value = { to: shop.value, directions_links: shop.value?.directions_links }
      await loadAddresses()
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
    loadingShop,
    trackingLive,
    resolvingRoute,
    savingAddress,
    geoError,
    originMode,
    selectedAddressId,
    manualAddressText,
    currentOrigin,
    geocodeResults,
    links,
    roadRoute,
    activeDistanceKm,
    activeDriveMinutes,
    activeWalkMinutes,
    activeBearing,
    init,
    startLiveTracking,
    stopLiveTracking,
    searchManualAddress,
    selectOrigin,
    chooseSavedAddress,
    saveNewAddress,
    removeAddress,
  }
}
