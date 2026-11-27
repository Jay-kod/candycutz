import { describe, expect, it, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { reactive, ref } from 'vue'
import DirectMeToShopPage from '../pages/DirectMeToShopPage.vue'

const initMock = vi.fn()
const setPinOnMapMock = vi.fn().mockResolvedValue(true)
const pickCurrentLocationMock = vi.fn().mockResolvedValue(true)
const saveCustomerAddressMock = vi.fn().mockResolvedValue(true)
const searchPlacesMock = vi.fn()
const applySearchResultMock = vi.fn()
const selectSavedAddressMock = vi.fn()
const startLiveTrackingMock = vi.fn()
const setTravelModeMock = vi.fn()
const lockOriginMock = vi.fn()
const unlockOriginMock = vi.fn()
const toggleOriginLockMock = vi.fn()
const notifyLockedOriginMock = vi.fn()

vi.mock('vue-router', () => ({
  useRoute: () => ({ path: '/customer/dashboard/directions' }),
  RouterLink: { template: '<a><slot /></a>' },
}))

vi.mock('@/portals/customer/layouts/CustomerLayout.vue', () => ({
  default: { template: '<div><slot /></div>' },
}))

vi.mock('@/core/layouts/PublicLayout.vue', () => ({
  default: { template: '<div><slot /></div>' },
}))

vi.mock('@/modules/auth/store/auth.store', () => ({
  useAuthStore: () => ({ token: 'tok', user: { id: 1 } }),
}))

vi.mock('../components/LiveDirectionsMap.vue', () => ({
  default: {
    props: ['shop', 'origin', 'route', 'fitTrigger', 'trackingLive', 'pinModeActive', 'originLocked', 'activeStep'],
    emits: ['pin-origin', 'toggle-live-gps', 'toggle-origin-lock', 'locked-interaction'],
    template:
      '<div data-test="map-stub" :data-shop-lng="shop?.longitude" :data-origin-locked="String(originLocked)"><button data-test="drop-pin-btn" @click="$emit(\'pin-origin\', { latitude: 8.8481, longitude: 7.9142 })">Drop Pin</button></div>',
  },
}))

const shopRef = ref({
  name: 'Keffi Main Hub',
  address: 'Angwan Kare, Keffi',
  latitude: 8.854541,
  longitude: 7.915391,
  location_lock: { verified: true, status: 'locked', message: 'Locked' },
})

const draftOrigin = reactive({
  id: null,
  title: 'Home',
  street: 'Beside Wealths Khort',
  landmark: '',
  city: 'Keffi',
  state: 'Nasarawa',
  latitude: 8.843518,
  longitude: 7.915356,
  accuracy: null,
  source: 'map',
  isDirty: true,
})

const currentOriginRef = ref({ latitude: 8.843518, longitude: 7.915356, label: 'Home: Beside Wealths Khort' })
const roadRouteRef = ref({
  profile: 'driving',
  coordinates: [
    [8.843518, 7.915356],
    [8.854541, 7.915391],
  ],
  distanceKm: 1.57,
  durationMinutes: 4,
  steps: [
    { instruction: 'Head north toward CandyCutz', distanceMeters: 500, durationSeconds: 50 },
    { instruction: 'Arrive at destination (CandyCutz)', distanceMeters: 0, durationSeconds: 0 },
  ],
})

const originLockedRef = ref(false)
const locationPromptStateRef = ref('idle')
const geoErrorRef = ref('')

vi.mock('../composables/useShopDirections', () => ({
  useShopDirections: () => ({
    shop: shopRef,
    addresses: ref([
      { id: 6, title: 'Home', street: 'A234', formatted: 'A234, Keffi', latitude: 8.843518, longitude: 7.915356, is_default: true },
    ]),
    draftOrigin,
    currentOrigin: currentOriginRef,
    searchResults: ref([]),
    locatingGps: ref(false),
    trackingLive: ref(false),
    searchingPlaces: ref(false),
    savingAddress: ref(false),
    geoError: geoErrorRef,
    geoWarning: ref(''),
    locationPromptState: locationPromptStateRef,
    originLocked: originLockedRef,
    travelMode: ref('driving'),
    fitTrigger: ref(0),
    roadRoute: roadRouteRef,
    activeDistanceKm: ref(1.57),
    activeDriveMinutes: ref(4),
    activeWalkMinutes: ref(19),
    activeBearing: ref('N'),
    init: initMock,
    setTravelMode: setTravelModeMock,
    setPinOnMap: setPinOnMapMock,
    pickCurrentLocation: pickCurrentLocationMock,
    startLiveTracking: startLiveTrackingMock,
    stopLiveTracking: vi.fn(),
    lockOrigin: lockOriginMock,
    unlockOrigin: unlockOriginMock,
    toggleOriginLock: toggleOriginLockMock,
    notifyLockedOrigin: notifyLockedOriginMock,
    searchPlaces: searchPlacesMock,
    applySearchResult: applySearchResultMock,
    selectSavedAddress: selectSavedAddressMock,
    startNewAddress: vi.fn(),
    markDraftEdited: vi.fn(),
    saveCustomerAddress: saveCustomerAddressMock,
    removeAddress: vi.fn(),
  }),
}))

describe('DirectMeToShopPage.vue (Restructured Architecture)', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    originLockedRef.value = false
    locationPromptStateRef.value = 'idle'
    geoErrorRef.value = ''
    draftOrigin.id = null
    draftOrigin.isDirty = true
    shopRef.value = {
      name: 'Keffi Main Hub',
      address: 'Angwan Kare, Keffi',
      latitude: 8.854541,
      longitude: 7.915391,
      location_lock: { verified: true, status: 'locked', message: 'Locked' },
    }
  })

  it('drops Pin 2 right on map without modals, saves address edits cleanly, starts live tracking, and surfaces lock alerts', async () => {
    const wrapper = mount(DirectMeToShopPage)

    expect(initMock).toHaveBeenCalled()
    expect(wrapper.find('[data-test="map-stub"]').attributes('data-shop-lng')).toBe('7.915391')
    expect(wrapper.text()).toContain('Unsaved Edits')

    // Dropping Pin 2 directly on map calls setPinOnMap and opens route steps
    await wrapper.find('[data-test="drop-pin-btn"]').trigger('click')
    expect(setPinOnMapMock).toHaveBeenCalledWith(8.8481, 7.9142, { recenter: false })

    // Primary Save Address & Show Route persists edits immediately
    const saveBtn = wrapper.findAll('button').find((b) => b.text().includes('Save Address & Show Route'))
    await saveBtn.trigger('click')
    expect(saveCustomerAddressMock).toHaveBeenCalled()
    expect(wrapper.text()).toContain('Step 1 of 2')
    expect(wrapper.text()).toContain('Head north toward CandyCutz')

    // Start Live GPS Tracking to Shop works inside website
    const liveBtn = wrapper.findAll('button').find((b) => b.text().includes('Start Live GPS Tracking to Shop'))
    await liveBtn.trigger('click')
    expect(startLiveTrackingMock).toHaveBeenCalled()

    // Lock mismatch surfaces alert banner
    shopRef.value = {
      ...shopRef.value,
      location_lock: {
        verified: false,
        status: 'sync_mismatch',
        message: 'Shop page coordinates and Google Map pin differ by 420 m.',
      },
    }
    await wrapper.vm.$nextTick()
    expect(wrapper.find('[role="alert"]').text()).toContain('differ by 420 m')
  })

  it('supports Lock/Unlock Preferred Location and Use My Current Location with device prompt fallback', async () => {
    const wrapper = mount(DirectMeToShopPage)

    // Locking preferred location invokes toggleOriginLock
    const lockBtn = wrapper.findAll('button').find((b) => b.text().includes('Lock Preferred Location'))
    await lockBtn.trigger('click')
    expect(toggleOriginLockMock).toHaveBeenCalled()

    // Simulate locked state: map stub gets originLocked=true and Unlock controls render
    originLockedRef.value = true
    await wrapper.vm.$nextTick()
    expect(wrapper.find('[data-test="map-stub"]').attributes('data-origin-locked')).toBe('true')
    expect(wrapper.text()).toContain('Preferred Location Locked • Unlock')
    const unlockBtn = wrapper.findAll('button').find((b) => b.text().includes('Unlock Location'))
    await unlockBtn.trigger('click')
    expect(toggleOriginLockMock).toHaveBeenCalledTimes(2)

    // Device location off / unavailable shows inline prompt with Retry button
    locationPromptStateRef.value = 'needs_enable'
    geoErrorRef.value = 'Your device location appears to be turned off.'
    await wrapper.vm.$nextTick()
    expect(wrapper.find('[role="status"]').text()).toContain('Enable Device Location')
    expect(wrapper.text()).toContain('Try Current Location Again')

    // Clicking Use My Current Location requests position and opens route steps when granted
    const currentLocBtn = wrapper.findAll('button').find((b) => b.text().includes('Use My Current Location'))
    await currentLocBtn.trigger('click')
    expect(pickCurrentLocationMock).toHaveBeenCalledWith({ auto: false })
    expect(wrapper.text()).toContain('Step 1 of 2')
  })
})
